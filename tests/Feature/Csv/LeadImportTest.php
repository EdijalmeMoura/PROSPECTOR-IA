<?php

declare(strict_types=1);

namespace Tests\Feature\Csv;

use App\Models\Lead;
use App\Models\User;
use App\Services\Csv\LeadCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeadImportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->user = User::factory()->create();
    }

    private function makeCsv(string $content): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('empresas.csv', $content);
    }

    public function test_preview_validates_columns_and_flags_invalid_rows(): void
    {
        $csv = implode("\n", [
            'company_name;city;website;contact_channel;contact_value',
            'Empresa Boa;Recife;https://boa.com.br;email;contato@boa.com.br',
            ';Recife;https://sem-nome.com.br',
            'Empresa Ruim;Recife;nao-e-url',
        ]);

        $report = app(LeadCsvImporter::class)->preview($this->store($csv));

        $this->assertSame(3, $report->totalLines);
        $this->assertSame(2, $report->rejected);
        $this->assertEmpty($report->missingColumns);
    }

    public function test_missing_required_columns_are_reported(): void
    {
        $csv = "nome;cidade\nAlguma;Recife\n";
        $report = app(LeadCsvImporter::class)->preview($this->store($csv));

        $this->assertTrue($report->hasStructureError());
        $this->assertContains('company_name', $report->missingColumns);
        $this->assertContains('city', $report->missingColumns);
    }

    public function test_duplicate_domains_are_detected_within_file_and_database(): void
    {
        $csv = implode("\n", [
            'company_name,city,website',
            'Empresa A,Recife,https://dupla.com.br',
            'Empresa B,Olinda,http://dupla.com.br',
        ]);

        $report = app(LeadCsvImporter::class)->preview($this->store($csv));

        $this->assertSame(1, $report->duplicates);
    }

    public function test_import_persists_only_valid_rows_and_creates_leads(): void
    {
        $csv = implode("\n", [
            'company_name;city;website;evidence_type;evidence_description',
            'Empresa Válida;São Lourenço da Mata;https://valida.com.br;site_ausente;Sem site',
            ';Recife;',
        ]);

        $report = app(LeadCsvImporter::class)->import($this->store($csv), $this->user);

        $this->assertSame(1, $report->imported);
        $this->assertSame(1, $report->rejected);
        $this->assertSame(1, Lead::count());
        $this->assertSame('valida.com.br', Lead::first()->company->normalized_domain);
    }

    public function test_http_preview_then_import_flow_works_with_uploaded_file(): void
    {
        $csv = "company_name,city\nEmpresa Web,Recife\n";
        $this->actingAs($this->user)
            ->post(route('csv.preview'), ['file' => $this->makeCsv($csv)])
            ->assertOk()
            ->assertSee('Empresa Web');
    }

    private function store(string $content): string
    {
        $path = 'csv-imports/test.csv';
        Storage::disk('local')->put($path, $content);

        return Storage::disk('local')->path($path);
    }
}
