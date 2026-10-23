<?php

declare(strict_types=1);

namespace App\Services\Communication;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Business Platform oficial (Meta Cloud API).
 * O envio é bloqueado sem credenciais válidas e sem opt-in/template
 * quando aplicável. Nenhuma automação de navegador é utilizada.
 */
class WhatsAppAdapter implements ChannelAdapterInterface
{
    public function key(): string
    {
        return 'whatsapp';
    }

    public function label(): string
    {
        return 'WhatsApp Business Platform';
    }

    public function isConfigured(): bool
    {
        return filled(env('WHATSAPP_PHONE_NUMBER_ID')) && filled(env('WHATSAPP_ACCESS_TOKEN'));
    }

    public function configurationHelp(): array
    {
        return [
            'Crie uma conta Meta Business e um app WhatsApp Business.',
            'Defina WHATSAPP_PHONE_NUMBER_ID e WHATSAPP_ACCESS_TOKEN no .env.',
            'Registre webhooks autenticados (WHATSAPP_VERIFY_TOKEN, WHATSAPP_APP_SECRET).',
            'Respeite opt-in do destinatário e as regras de templates/janela de 24h.',
        ];
    }

    public function send(OutboundMessage $message): SendResult
    {
        if (! $this->isConfigured()) {
            return new SendResult(false, 'falha', null, null, 'WhatsApp não configurado.');
        }

        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            env('WHATSAPP_API_VERSION', 'v21.0'),
            env('WHATSAPP_PHONE_NUMBER_ID'),
        );

        try {
            $response = Http::timeout(20)
                ->withToken((string) env('WHATSAPP_ACCESS_TOKEN'))
                ->acceptJson()
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => preg_replace('/\D+/', '', $message->recipient),
                    'type' => 'text',
                    'text' => ['preview_url' => false, 'body' => $message->text],
                ]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp: falha de conexão', ['lead' => $message->lead->id, 'error' => $e->getMessage()]);
            return new SendResult(false, 'falha', null, null, 'Falha de conexão com a Meta.');
        }

        if ($response->failed()) {
            Log::warning('WhatsApp: envio recusado', ['status' => $response->status(), 'body' => $response->json()]);
            return new SendResult(false, 'rejeitada', null, ['status' => $response->status()], 'A Meta recusou o envio (verifique opt-in/template).');
        }

        return new SendResult(
            true,
            'enviada',
            $response->json('messages.0.id'),
            ['whatsapp_message_id' => $response->json('messages.0.id')],
        );
    }
}
