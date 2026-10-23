<?php

declare(strict_types=1);

namespace App\Services\Ai;

class AiResponse
{
    /**
     * @param  array<string, mixed>  $structured
     * @param  array<int, string>  $uncertainties
     */
    public function __construct(
        public readonly bool $success,
        public readonly array $structured = [],
        public readonly array $uncertainties = [],
        public readonly ?int $promptTokens = null,
        public readonly ?int $completionTokens = null,
        public readonly ?string $error = null,
    ) {}

    public static function unavailable(string $reason): self
    {
        return new self(false, [], [$reason], null, null, $reason);
    }
}
