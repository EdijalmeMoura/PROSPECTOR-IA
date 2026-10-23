<?php

declare(strict_types=1);

namespace App\Services\Csv;

class CsvRowReport
{
    /**
     * @param  array<string, string>  $data
     * @param  array<int, string>  $errors
     */
    public function __construct(
        public readonly int $line,
        public readonly array $data,
        public array $errors = [],
        public bool $duplicate = false,
        public ?int $leadId = null,
    ) {}

    public function isValid(): bool
    {
        return $this->errors === [] && ! $this->duplicate;
    }
}
