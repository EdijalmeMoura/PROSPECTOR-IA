<?php

declare(strict_types=1);

namespace App\Services\Ai;

class NullAiProvider implements AiProviderInterface
{
    public function key(): string
    {
        return 'null';
    }

    public function label(): string
    {
        return 'IA não configurada';
    }

    public function isConfigured(): bool
    {
        return false;
    }

    public function configurationHelp(): array
    {
        return [
            'Defina AI_PROVIDER=openai_compatible, AI_BASE_URL, AI_API_KEY e AI_MODEL no .env.',
            'Sem credenciais, a geração por IA fica indisponível; a revisão manual continua ativa.',
        ];
    }

    public function generate(AiPrompt $prompt): AiResponse
    {
        return AiResponse::unavailable('Nenhum provedor de IA configurado. Use a revisão manual.');
    }
}
