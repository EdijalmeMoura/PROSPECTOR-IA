<?php

declare(strict_types=1);

namespace App\Services\Csv;

use App\Models\Company;
use App\Models\User;
use App\Services\Dedup\CompanyDeduplicator;
use App\Services\Leads\LeadService;
use App\Support\DomainNormalizer;
use Illuminate\Support\Str;

/**
 * Importação CSV de empresas/leads.
 * - valida cabeçalhos obrigatórios;
 * - valida cada linha e rejeita inválidas sem interromper o lote;
 * - detecta duplicidades por place_id, domínio normalizado e dentro do arquivo;
 * - nomes parecidos NÃO são tratados como duplicidade.
 */
class LeadCsvImporter
{
    public const REQUIRED_COLUMNS = ['company_name', 'city'];

    public const OPTIONAL_COLUMNS = [
        'trade_name', 'segment', 'state', 'website', 'place_id',
        'contact_channel', 'contact_value', 'contact_permission',
        'evidence_type', 'evidence_description', 'is_demo',
    ];

    public function __construct(
        private readonly CompanyDeduplicator $deduplicator,
        private readonly LeadService $leadService,
    ) {}

    /**
     * Prévia sem escrita: parseia, valida e aponta duplicidades.
     */
    public function preview(string $absolutePath): CsvImportReport
    {
        [$headers, $rows] = $this->read($absolutePath);
        $missing = array_values(array_diff(self::REQUIRED_COLUMNS, $headers));
        if ($missing !== []) {
            return new CsvImportReport([], 0, 0, 0, 0, $missing);
        }

        $reports = [];
        $seenDomains = [];
        $seenPlaceIds = [];

        foreach ($rows as $index => $values) {
            $line = $index + 2; // cabeçalho é a linha 1
            $data = $this->combine($headers, $values);
            $errors = $this->validateRow($data);

            $domain = DomainNormalizer::normalize($data['website'] ?? null);
            $placeId = $data['place_id'] ?? null;
            $duplicate = false;

            if ($placeId && isset($seenPlaceIds[$placeId])) {
                $duplicate = true;
            } elseif ($domain && isset($seenDomains[$domain])) {
                $duplicate = true;
            } elseif ($this->deduplicator->findExisting($placeId ?: null, $data['website'] ?? null) !== null) {
                $duplicate = true;
            }

            if ($placeId) {
                $seenPlaceIds[$placeId] = true;
            }
            if ($domain) {
                $seenDomains[$domain] = true;
            }

            $reports[] = new CsvRowReport($line, $data, $errors, $duplicate);
        }

        return $this->summarize($reports, $missing);
    }

    /**
     * Importa somente linhas válidas e não duplicadas.
     */
    public function import(string $absolutePath, User $user): CsvImportReport
    {
        $report = $this->preview($absolutePath);
        if ($report->hasStructureError()) {
            return $report;
        }

        $imported = 0;
        $rows = [];

        foreach ($report->rows as $row) {
            if (! $row->isValid()) {
                $rows[] = $row;
                continue;
            }

            $data = $row->data;
            $contacts = [];
            if (! empty($data['contact_value'])) {
                $contacts[] = [
                    'channel' => $data['contact_channel'] ?? 'email',
                    'value' => trim($data['contact_value']),
                    'permission_status' => in_array(($data['contact_permission'] ?? ''), ['permitida', 'negada'], true)
                        ? $data['contact_permission']
                        : 'desconhecida',
                ];
            }
            $evidence = [];
            if (! empty($data['evidence_description'])) {
                $evidence[] = [
                    'type' => $data['evidence_type'] ?? 'observacao_manual',
                    'description' => $data['evidence_description'],
                    'reliability' => 'media',
                    'collected_by' => 'manual',
                ];
            }

            $lead = $this->leadService->create(
                data: [
                    'company_name' => $data['company_name'],
                    'trade_name' => $data['trade_name'] ?? null,
                    'segment' => $data['segment'] ?? null,
                    'city' => $data['city'],
                    'state' => $data['state'] ?? config('prospector.region.state'),
                    'website' => $data['website'] ?? null,
                    'place_id' => $data['place_id'] ?? null,
                    'is_demo' => Str::lower((string) ($data['is_demo'] ?? '0')) === 'true' || ($data['is_demo'] ?? '0') === '1',
                ],
                contacts: $contacts,
                evidence: $evidence,
                user: $user,
                sourceName: 'csv',
            );

            $row->leadId = $lead->id;
            $imported++;
            $rows[] = $row;
        }

        return $this->summarize($rows, [], $imported);
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, string>>}
     */
    private function read(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [[], []];
        }

        // Remove BOM e detecta delimitador (, ou ;).
        $first = fgets($handle);
        rewind($handle);
        if ($first !== false && str_starts_with($first, "\xEF\xBB\xBF")) {
            fseek($handle, 3);
        }
        $delimiter = ($first !== null && substr_count($first, ';') > substr_count($first, ',')) ? ';' : ',';

        $headerLine = fgetcsv($handle, 0, $delimiter);
        $headers = array_map(fn ($h) => Str::snake(trim((string) $h)), $headerLine ?: []);

        $rows = [];
        while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($values) === 1 && ($values[0] === null || trim((string) $values[0]) === '')) {
                continue; // ignora linhas em branco
            }
            $rows[] = array_map(fn ($v) => trim((string) $v), $values);
        }
        fclose($handle);

        return [$headers, $rows];
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, string>  $values
     * @return array<string, string>
     */
    private function combine(array $headers, array $values): array
    {
        $data = [];
        foreach ($headers as $i => $header) {
            $data[$header] = $values[$i] ?? '';
        }

        return $data;
    }

    /**
     * @param  array<string, string>  $data
     * @return array<int, string>
     */
    private function validateRow(array $data): array
    {
        $errors = [];

        if (empty($data['company_name'])) {
            $errors[] = 'Nome da empresa é obrigatório.';
        }
        if (empty($data['city'])) {
            $errors[] = 'Cidade é obrigatória.';
        }
        if (! empty($data['website']) && DomainNormalizer::normalize($data['website']) === null) {
            $errors[] = 'Site inválido.';
        }
        if (! empty($data['contact_value']) && ($data['contact_channel'] ?? '') === 'email'
            && ! filter_var($data['contact_value'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'E-mail de contato inválido.';
        }
        if (isset($data['state']) && $data['state'] !== '' && ! preg_match('/^[A-Za-z]{2}$/', $data['state'])) {
            $errors[] = 'Estado deve ser a UF com 2 letras.';
        }

        return $errors;
    }

    /**
     * @param  array<int, CsvRowReport>  $rows
     * @param  array<int, string>  $missing
     */
    private function summarize(array $rows, array $missing, ?int $forceImported = null): CsvImportReport
    {
        $rejected = count(array_filter($rows, fn (CsvRowReport $r) => $r->errors !== []));
        $duplicates = count(array_filter($rows, fn (CsvRowReport $r) => $r->duplicate));
        $imported = $forceImported ?? count(array_filter($rows, fn (CsvRowReport $r) => $r->leadId !== null));

        return new CsvImportReport(
            rows: $rows,
            totalLines: count($rows),
            imported: $imported,
            rejected: $rejected,
            duplicates: $duplicates,
            missingColumns: $missing,
        );
    }
}
