<?php

declare(strict_types=1);

namespace App\Services\Search;

use Illuminate\Support\Collection;

interface SearchProviderInterface
{
    /** Identificador técnico (null, google, ...). */
    public function key(): string;

    /** Nome de exibição. */
    public function label(): string;

    /** True apenas quando as credenciais necessárias estão configuradas. */
    public function isConfigured(): bool;

    /**
     * Instruções do que falta para ativar o conector.
     *
     * @return array<int, string>
     */
    public function configurationHelp(): array;

    /**
     * Executa a pesquisa e retorna uma coleção de ProspectRecord.
     *
     * @return Collection<int, ProspectRecord>
     */
    public function search(SearchCriteria $criteria): Collection;
}
