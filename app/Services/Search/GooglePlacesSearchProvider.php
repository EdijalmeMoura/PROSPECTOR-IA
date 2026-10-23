<?php

declare(strict_types=1);

namespace App\Services\Search;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Conector Google Places API (New) — Text Search.
 *
 * Regras aplicadas:
 *  - apenas os campos necessários são solicitados (field mask);
 *  - paginação com teto documentado (máx. 3 páginas/60 resultados por busca);
 *  - timeouts, retry controlado e erros HTTP/quota tratados;
 *  - place_id é o único conteúdo com retenção permanente; os demais
 *    retornos são marcados como transitórios (ver retention_*);
 *  - nenhuma credencial é registrada em log.
 */
class GooglePlacesSearchProvider implements SearchProviderInterface
{
    private const ENDPOINT = 'https://places.googleapis.com/v1/places:searchText';

    /** A API (New) devolve no máximo 20 resultados por página. */
    private const PAGE_SIZE = 20;
    private const MAX_PAGES = 3;

    private const FIELD_MASK = implode(',', [
        'places.id',
        'places.displayName',
        'places.websiteUri',
        'places.formattedAddress',
        'places.nationalPhoneNumber',
        'places.internationalPhoneNumber',
        'places.primaryType',
        'places.types',
        'places.addressComponents',
        'nextPageToken',
    ]);

    public function key(): string
    {
        return 'google';
    }

    public function label(): string
    {
        return 'Google Places API (New)';
    }

    public function isConfigured(): bool
    {
        return filled(env('GOOGLE_PLACES_API_KEY'));
    }

    public function configurationHelp(): array
    {
        return [
            'Crie um projeto no Google Cloud e ative a Places API (New).',
            'Configure faturamento e cotas de uso antes de rodar pesquisas.',
            'Defina no .env: SEARCH_PROVIDER=google e GOOGLE_PLACES_API_KEY=sua_chave.',
            'Documente os custos por requisição e ajuste GOOGLE_PLACES_MAX_RESULTS_PER_JOB.',
        ];
    }

    public function search(SearchCriteria $criteria): Collection
    {
        if (! $this->isConfigured()) {
            throw new SearchProviderException('Conector Google Places não configurado: GOOGLE_PLACES_API_KEY ausente.');
        }

        $records = collect();
        $pageToken = null;

        foreach ($criteria->cities as $city) {
            if ($records->count() >= $criteria->targetQuantity) {
                break;
            }
            $records = $records->merge($this->searchCity($criteria, $city, $pageToken));
        }

        // Nunca inflar o resultado: devolver exatamente o que a fonte retornou.
        return $records->take($criteria->targetQuantity)->values();
    }

    /**
     * @return Collection<int, ProspectRecord>
     */
    private function searchCity(SearchCriteria $criteria, string $city, ?string $pageToken): Collection
    {
        $records = collect();
        $pages = 0;

        do {
            $payload = [
                'textQuery' => $criteria->textQueryFor($city),
                'pageSize' => min(self::PAGE_SIZE, (int) config('prospector.search.google_page_size', self::PAGE_SIZE)),
                'languageCode' => 'pt-BR',
                'regionCode' => 'BR',
            ];
            if ($pageToken) {
                $payload['pageToken'] = $pageToken;
            }

            $data = $this->request($payload);
            $pages++;

            foreach (($data['places'] ?? []) as $place) {
                $records->push($this->mapPlace($place, $city, $criteria->state));
            }

            $pageToken = $data['nextPageToken'] ?? null;
        } while ($pageToken && $pages < self::MAX_PAGES && $records->count() < $criteria->targetQuantity);

        return $records;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function request(array $payload): array
    {
        try {
            $response = Http::timeout((int) config('prospector.search.google_timeout', 15))
                ->retry((int) config('prospector.search.google_retry', 2), 500, fn ($exception) => $exception instanceof ConnectionException, false)
                ->withHeaders([
                    'X-Goog-Api-Key' => (string) env('GOOGLE_PLACES_API_KEY'),
                    'X-Goog-FieldMask' => self::FIELD_MASK,
                    'Content-Type' => 'application/json',
                ])
                ->post(self::ENDPOINT, $payload);
        } catch (ConnectionException $e) {
            Log::warning('Google Places: falha de conexão', ['error' => $e->getMessage()]);
            throw new SearchProviderException('Falha de conexão com o Google Places. Tente novamente mais tarde.', 0, $e);
        }

        if ($response->status() === 429) {
            Log::warning('Google Places: quota/limite de requisições atingido.');
            throw new SearchProviderException('Quota ou limite de cobrança do Google Places atingido. Verifique o projeto no Google Cloud.');
        }

        if ($response->clientError() || $response->serverError()) {
            // Log sem expor a chave; o corpo do erro do Google não a contém.
            Log::warning('Google Places: erro HTTP', ['status' => $response->status(), 'body' => $response->body()]);
            throw new SearchProviderException("O Google Places retornou erro HTTP {$response->status()}.");
        }

        return $response->json() ?? [];
    }

    /**
     * @param  array<string, mixed>  $place
     */
    private function mapPlace(array $place, string $fallbackCity, string $state): ProspectRecord
    {
        $placeId = $place['id'] ?? null;
        $name = $place['displayName']['text'] ?? 'Empresa sem nome';
        $website = $place['websiteUri'] ?? null;
        $address = $place['formattedAddress'] ?? null;
        $city = $this->extractCity($place['addressComponents'] ?? []) ?? $fallbackCity;
        $segment = $place['primaryType'] ?? null;

        $contacts = [];
        $phone = $place['nationalPhoneNumber'] ?? $place['internationalPhoneNumber'] ?? null;
        if ($phone) {
            $contacts[] = ['channel' => 'telefone', 'value' => $phone];
        }

        // Conteúdo transitório: permitido apenas temporariamente pelas regras.
        $transient = array_filter([
            'formatted_address' => $address,
            'types' => $place['types'] ?? null,
            'phone' => $phone,
        ]);

        return new ProspectRecord(
            sourceName: 'google_places',
            externalId: $placeId,
            companyName: (string) $name,
            website: $website,
            segment: $segment,
            city: $city,
            state: $state,
            sourceUrl: $placeId ? "https://www.google.com/maps/place/?q=place_id:{$placeId}" : 'https://www.google.com/maps',
            contacts: $contacts,
            transientData: $transient,
            permanentRetentionAllowed: filled($placeId),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $components
     */
    private function extractCity(array $components): ?string
    {
        foreach ($components as $component) {
            if (in_array('locality', $component['types'] ?? [], true)) {
                return $component['longText'] ?? null;
            }
        }

        return null;
    }
}
