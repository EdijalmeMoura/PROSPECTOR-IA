<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Enums\AiKind;

class AiPrompt
{
    /**
     * @param  array<string, mixed>  $context  Apenas dados necessários e verificados.
     * @param  array<int, int>  $evidenceIds
     */
    public function __construct(
        public readonly AiKind $kind,
        public readonly string $systemInstruction,
        public readonly array $context,
        public readonly array $evidenceIds = [],
    ) {}
}
