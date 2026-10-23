<?php

declare(strict_types=1);

namespace App\Services\Search;

class SearchCriteria
{
    /**
     * @param  array<int, string>  $cities
     */
    public function __construct(
        public readonly array $cities,
        public readonly string $state,
        public readonly ?string $segment,
        public readonly ?int $serviceId,
        public readonly int $targetQuantity,
        public readonly ?string $serviceName = null,
    ) {}

    /**
     * Texto de busca por município (categoria + serviço quando aplicável).
     */
    public function textQueryFor(string $city): string
    {
        $parts = [];
        if ($this->segment && $this->segment !== 'Todos') {
            $parts[] = $this->segment;
        } else {
            $parts[] = 'empresas';
        }
        if ($this->serviceName) {
            $parts[] = "{$this->serviceName} perto de";
        }
        $parts[] = $city;
        $parts[] = $this->state;
        $parts[] = 'Brasil';

        return implode(' ', $parts);
    }
}
