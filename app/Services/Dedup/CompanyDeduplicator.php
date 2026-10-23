<?php

declare(strict_types=1);

namespace App\Services\Dedup;

use App\Models\Company;
use App\Support\DomainNormalizer;

/**
 * Deduplicação por identificadores confiáveis:
 *  - place_id (Google) ou outro external_id por fonte;
 *  - domínio normalizado.
 * Nomes semelhantes NÃO são considerados prova de duplicidade.
 */
class CompanyDeduplicator
{
    public function findExisting(?string $placeId, ?string $website): ?Company
    {
        if ($placeId !== null && $placeId !== '') {
            $company = Company::where('place_id', $placeId)->first();
            if ($company !== null) {
                return $company;
            }
        }

        $domain = DomainNormalizer::normalize($website);
        if ($domain !== null) {
            return Company::where('normalized_domain', $domain)->first();
        }

        return null;
    }

    /**
     * Verifica também colisão de external_id em uma fonte específica.
     */
    public function existsExternalId(string $sourceName, string $externalId): bool
    {
        return \App\Models\CompanySource::query()
            ->where('source_name', $sourceName)
            ->where('external_id', $externalId)
            ->exists();
    }
}
