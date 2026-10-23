<?php

declare(strict_types=1);

namespace App\Services\Communication;

class SendResult
{
    /**
     * @param  array<string, mixed>|null  $payload
     */
    public function __construct(
        public readonly bool $success,
        public readonly string $status,
        public readonly ?string $providerMessageId = null,
        public readonly ?array $payload = null,
        public readonly ?string $error = null,
    ) {}
}
