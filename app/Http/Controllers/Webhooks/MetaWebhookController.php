<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Enums\MessageStatus;
use App\Http\Controllers\Controller;
use App\Models\MessageLog;
use App\Models\OptOut;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Webhook da Meta (WhatsApp/Instagram). A verificação usa o token de
 * verify configurado no .env; eventos só alteram status de mensagens
 * existentes (nunca disparam envios).
 */
class MetaWebhookController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && hash_equals((string) env('WHATSAPP_VERIFY_TOKEN'), (string) $token)) {
            return response()->json((int) $challenge);
        }

        Log::warning('Webhook Meta: verificação recusada (token inválido).');

        return response()->json(['error' => 'forbidden'], 403);
    }

    public function handle(Request $request): JsonResponse
    {
        // Sem assinatura válida do app, rejeitar o payload.
        $signature = $request->header('X-Hub-Signature-256', '');
        $secret = (string) env('WHATSAPP_APP_SECRET');
        if ($secret === '' || ! $this->validSignature($signature, $request->getContent(), $secret)) {
            Log::warning('Webhook Meta: assinatura inválida.');

            return response()->json(['error' => 'invalid signature'], 403);
        }

        foreach (($request->input('entry', []) ?? []) as $entry) {
            foreach (($entry['changes'] ?? []) as $change) {
                $this->processChange($change['value'] ?? []);
            }
        }

        return response()->json(['ok' => true]);
    }

    private function processChange(mixed $value): void
    {
        if (! is_array($value)) {
            return;
        }

        foreach (($value['messages'] ?? []) as $message) {
            $waId = $message['id'] ?? null;
            if (! $waId) {
                continue;
            }
            $log = MessageLog::where('provider_message_id', $waId)->first();
            if (! $log) {
                continue;
            }

            $status = match ($message['type'] ?? '') {
                'reaction', 'text' => MessageStatus::Respondida,
                default => MessageStatus::Entregue,
            };
            $log->update([
                'status' => $status,
                'replied_at' => $status === MessageStatus::Respondida ? now() : $log->replied_at,
                'delivered_at' => $status === MessageStatus::Entregue ? now() : $log->delivered_at,
            ]);

            // Resposta com palavra de descadastro vira opt-out imediato.
            if (isset($message['text']['body']) && preg_match('/(pare|sair|descadastrar|n[aã]o quero)/iu', (string) $message['text']['body'])) {
                OptOut::firstOrCreate(
                    ['channel' => 'whatsapp', 'identifier' => (string) ($message['from'] ?? '')],
                    ['source' => 'webhook', 'reason' => 'Solicitação via mensagem', 'requested_at' => now()],
                );
            }
        }
    }

    private function validSignature(string $signature, string $payload, string $secret): bool
    {
        if (! str_starts_with($signature, 'sha256=')) {
            return false;
        }

        return hash_equals('sha256=' . hash_hmac('sha256', $payload, $secret), $signature);
    }
}
