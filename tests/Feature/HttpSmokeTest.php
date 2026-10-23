<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ChannelType;
use App\Models\Approval;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\SearchJob;
use App\Models\Service;
use App\Models\User;
use App\Services\Approval\ApprovalService;
use App\Services\Leads\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HttpSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_main_pages_render_for_authenticated_admin(): void
    {
        $admin = User::factory()->admin()->create();
        Service::factory()->create();
        $lead = app(LeadService::class)->create(
            ['company_name' => 'Empresa Smoke', 'city' => 'Recife', 'website' => 'https://smoke.com.br'],
            [['channel' => 'email', 'value' => 'a@smoke.com.br']],
            [['type' => 'site_ausente', 'description' => 'Sem site', 'reliability' => 'alta']],
            $admin,
        );
        SearchJob::factory()->create(['user_id' => $admin->id]);
        app(ApprovalService::class)->createDraft($lead, ChannelType::Email, 'a@smoke.com.br', 'Texto da mensagem de smoke test com vinte caracteres.');
        Proposal::factory()->create(['lead_id' => $lead->id]);
        Deal::factory()->create(['lead_id' => $lead->id]);

        $routes = [
            route('dashboard'), route('leads.index'), route('leads.create'),
            route('leads.show', $lead), route('leads.edit', $lead),
            route('companies.index'), route('companies.create'),
            route('companies.show', $lead->company), route('companies.edit', $lead->company),
            route('searches.index'), route('searches.create'),
            route('searches.show', SearchJob::first()),
            route('csv.create'), route('approvals.index'),
            route('approvals.show', Approval::first()),
            route('proposals.index'), route('proposals.create', ['lead' => $lead->id]),
            route('proposals.show', Proposal::first()), route('proposals.edit', Proposal::first()),
            route('deals.index'), route('deals.show', Deal::first()),
            route('opt-outs.index'), route('services.index'), route('services.create'),
            route('services.edit', Service::first()), route('users.index'), route('users.create'),
            route('users.edit', $admin), route('connectors.show'), route('audit-logs.index'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_demo_filter_and_csv_pages_render(): void
    {
        $admin = User::factory()->admin()->create();
        Lead::factory()->demo()->create();

        $this->actingAs($admin)->get(route('leads.index', ['scope' => 'demo']))->assertOk()->assertSee('fictício');
        $this->actingAs($admin)->get(route('leads.export'))->assertOk();
    }
}
