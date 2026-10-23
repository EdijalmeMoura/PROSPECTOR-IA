<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContactPermission;
use App\Enums\LeadStage;
use App\Models\Company;
use App\Models\CompanySource;
use App\Models\Lead;
use App\Models\LeadContact;
use App\Models\LeadEvidence;
use App\Models\Service;
use App\Models\User;
use App\Services\Leads\LeadService;
use Illuminate\Database\Seeder;

/**
 * Dados 100% fictícios para demonstração. Tudo recebe is_demo = true e
 * fica separado dos leads reais nos filtros da aplicação.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->admin()->create();
        $service = Service::where('code', 'PACOTE_8_ARTES')->first() ?? Service::factory()->create();
        $leadService = app(LeadService::class);

        $demoCompanies = [
            ['name' => 'Padaria Pão Dourado (FICTÍCIO)', 'segment' => 'Alimentação e bebidas', 'city' => 'São Lourenço da Mata', 'website' => null, 'evidence' => ['site_ausente' => 'Empresa sem site próprio encontrado na demonstração.']],
            ['name' => 'Salão Beleza Real (FICTÍCIO)', 'segment' => 'Moda e beleza', 'city' => 'Jaboatão dos Guararapes', 'website' => 'https://beleza-real-demo.exemplo.com.br', 'evidence' => ['redes_sem_padrao' => 'Redes sociais com artes sem padrão visual (dado fictício).']],
            ['name' => 'Auto Mecânica Forte (FICTÍCIO)', 'segment' => 'Automotivo', 'city' => 'Recife', 'website' => 'https://mecanica-forte-demo.exemplo.com.br', 'evidence' => ['site_desatualizado' => 'Site com aparência desatualizada e conteúdo antigo (dado fictício).']],
        ];

        foreach ($demoCompanies as $item) {
            $lead = $leadService->create(
                data: [
                    'company_name' => $item['name'],
                    'segment' => $item['segment'],
                    'city' => $item['city'],
                    'state' => 'PE',
                    'website' => $item['website'],
                    'service_id' => $service->id,
                    'is_demo' => true,
                ],
                contacts: [[
                    'channel' => 'email',
                    'value' => 'contato-demo@exemplo.com',
                    'permission_status' => ContactPermission::Desconhecida->value,
                ]],
                evidence: array_map(fn ($type, $desc) => [
                    'type' => $type,
                    'description' => $desc,
                    'reliability' => 'media',
                    'collected_by' => 'manual',
                ], array_keys($item['evidence']), array_values($item['evidence'])),
                user: $admin,
                sourceName: 'demo',
            );

            $lead->company->update(['is_demo' => true]);
            $lead->update(['is_demo' => true, 'stage' => LeadStage::Pesquisado->value]);
        }
    }
}
