<?php

declare(strict_types=1);

namespace App\Services\Csv;

class CsvImportReport
{
    /**
     * @param  array<int, CsvRowReport>  $rows
     */
    public function __construct(
        public readonly array $rows,
        public readonly int $totalLines,
        public readonly int $imported,
        public readonly int $rejected,
        public readonly int $duplicates,
        public readonly array $missingColumns = [],
    ) {}

    public function hasStructureError(): bool
    {
        return $this->missingColumns !== [];
    }
}
