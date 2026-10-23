<?php

declare(strict_types=1);

namespace App\Services\Ai;

class AiManager
{
    public function make(?string $key = null): AiProviderInterface
    {
        $key ??= (string) config('prospector.providers.ai', 'null');

        return match ($key) {
            'openai_compatible' => app(OpenAiCompatibleProvider::class),
            default => app(NullAiProvider::class),
        };
    }
}
