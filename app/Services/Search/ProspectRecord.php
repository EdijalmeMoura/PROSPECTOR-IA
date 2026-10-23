<?php

declare(strict_types=1);

namespace App\Services\Search;

/**
 * Registro normalizado retornado por qualquer provedor de pesquisa.
 * O campo $transientData carrega conteúdo que o provedor não permite
 * reter permanentemente (ex.: dados do Google Places que não o place_id).
 */
class ProspectRecord
{
    /**
     * @param  array<string, mixed>  $transientData
     * @param  array<int, array{channel:string, value:string}>  $contacts
     */
    public function __construct(
        public readonly string $sourceName,
        public readonly ?string $externalId,
        public readonly string $companyName,
        public readonly ?string $website,
        public readonly ?string $segment,
        public readonly string $city,
        public readonly string $state,
        public readonly string $sourceUrl,
        public readonly array $contacts = [],
        public readonly array $transientData = [],
        public readonly bool $permanentRetentionAllowed = false,
    ) {}
}
