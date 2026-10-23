<?php

declare(strict_types=1);

namespace App\Services\Search;

use Illuminate\Support\Collection;

/**
 * Provedor padrão: "Conector não configurado".
 * Nunca simula resultados de API. O cadastro manual e a importação CSV
 * permanecem disponíveis mesmo sem provedor externo.
 */
class NullSearchProvider implements SearchProviderInterface
{
    public function key(): string
    {
        return 'null';
    }

    public function label(): string
    {
        return 'Conector não configurado';
    }

    public function isConfigured(): bool
    {
        return false;
    }

    public function configurationHelp(): array
    {
        return [
            'Nenhum provedor de pesquisa está configurado no momento.',
            'Para ativar o Google Places API (New), defina SEARCH_PROVIDER=google e GOOGLE_PLACES_API_KEY no .env.',
            'Enquanto isso, utilize o cadastro manual de empresas e a importação CSV.',
        ];
    }

    public function search(SearchCriteria $criteria): Collection
    {
        return collect();
    }
}
