<?php

declare(strict_types=1);

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Provedor genérico compatível com o padrão OpenAI Chat Completions.
 * A chave fica apenas no servidor (.env) e nunca é logada.
 */
class OpenAiCompatibleProvider implements AiProviderInterface
{
    public function key(): string
    {
        return 'openai_compatible';
    }

    public function label(): string
    {
        return 'API compatível com OpenAI';
    }

    public function isConfigured(): bool
    {
        return filled(env('AI_API_KEY')) && filled(env('AI_BASE_URL')) && filled(env('AI_MODEL'));
    }

    public function configurationHelp(): array
    {
        return [
            'Defina AI_PROVIDER=openai_compatible no .env.',
            'Preencha AI_BASE_URL (ex.: https://api.exemplo.com/v1), AI_API_KEY e AI_MODEL.',
            'Confirme custos, limites e política de dados do provedor contratado.',
        ];
    }

    public function generate(AiPrompt $prompt): AiResponse
    {
        if (! $this->isConfigured()) {
            return AiResponse::unavailable('Credenciais da IA incompletas (AI_BASE_URL, AI_API_KEY ou AI_MODEL).');
        }

        try {
            $response = Http::timeout((int) config('prospector.ai.timeout', 30))
                ->retry((int) config('prospector.ai.retry', 1), 500, fn ($e) => $e instanceof ConnectionException, false)
                ->withToken((string) env('AI_API_KEY'))
                ->acceptJson()
                ->post(rtrim((string) env('AI_BASE_URL'), '/') . '/chat/completions', [
                    'model' => env('AI_MODEL'),
                    'temperature' => 0.2,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $prompt->systemInstruction],
                        ['role' => 'user', 'content' => json_encode($prompt->context, JSON_UNESCAPED_UNICODE)],
                    ],
                ]);
        } catch (ConnectionException $e) {
            Log::warning('IA: falha de conexão', ['error' => $e->getMessage()]);
            return new AiResponse(false, [], [], null, null, 'Falha de conexão com o provedor de IA.');
        }

        if ($response->failed()) {
            Log::warning('IA: erro HTTP', ['status' => $response->status()]);
            return new AiResponse(false, [], [], null, null, "Provedor de IA retornou erro HTTP {$response->status()}.");
        }

        $content = $response->json('choices.0.message.content');
        if (! is_string($content)) {
            return new AiResponse(false, [], [], null, null, 'Resposta da IA em formato inesperado.');
        }

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            return new AiResponse(false, [], [], null, null, 'A IA não retornou um JSON válido.');
        }

        return new AiResponse(
            success: true,
            structured: $decoded,
            uncertainties: $this->extractStrings($decoded['incertezas'] ?? $decoded['uncertainties'] ?? []),
            promptTokens: $response->json('usage.prompt_tokens'),
            completionTokens: $response->json('usage.completion_tokens'),
        );
    }

    /**
     * @param  mixed  $value
     * @return array<int, string>
     */
    private function extractStrings(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_map(fn ($v) => (string) $v, $value));
    }
}
