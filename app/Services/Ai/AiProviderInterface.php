<?php

declare(strict_types=1);

namespace App\Services\Ai;

interface AiProviderInterface
{
    public function key(): string;

    public function label(): string;

    public function isConfigured(): bool;

    /** @return array<int, string> */
    public function configurationHelp(): array;

    public function generate(AiPrompt $prompt): AiResponse;
}
