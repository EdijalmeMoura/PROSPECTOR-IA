<?php

declare(strict_types=1);

namespace App\Services\Search;

class SearchProviderManager
{
    public function make(?string $key = null): SearchProviderInterface
    {
        $key ??= (string) config('prospector.providers.search', 'null');

        return match ($key) {
            'google' => app(GooglePlacesSearchProvider::class),
            default => app(NullSearchProvider::class),
        };
    }
}
