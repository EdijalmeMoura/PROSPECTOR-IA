<?php

declare(strict_types=1);

namespace Tests\Feature\Leads;

use App\Enums\LeadStage;
use App\Models\Lead;
use App\Models\Service;
use App\Models\User;
use App\Services\Leads\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Service::factory()->create();
    }

    public function test_manual_lead_is_created_with_company_contacts_evidence_and_score(): void
    {
        $response = $this->actingAs($this->user)->post(route('leads.store'), [
            'company_name' => 'Padaria Teste Ltda',
            'city' => 'São Lourenço da Mata',
            'state' => 'PE',
            'segment' => 'Alimentação e bebidas',
            'website' => 'https://padaria-teste.com.br',
            'service_id' => Service::first()->id,
            'contact_channel' => 'email',
            'contact_value' => 'contato@padaria-teste.com.br',
            'contact_permission' => 'permitida',
            'evidence_type' => 'site_ausente',
            'evidence_description' => 'Sem site próprio na data da consulta.',
            'evidence_reliability' => 'alta',
        ]);

        $lead = Lead::first();
        $response->assertRedirect(route('leads.show', $lead));
        $this->assertGreaterThan(0, $lead->score);
        $this->assertSame('padaria-teste.com.br', $lead->company->normalized_domain);
        $this->assertCount(1, $lead->contacts);
        $this->assertCount(1, $lead->evidence);
        $this->assertNotNull($lead->score_justification);
    }

    public function test_validation_rejects_lead_without_company_name_or_city(): void
    {
        $this->actingAs($this->user)
            ->post(route('leads.store'), ['company_name' => '', 'city' => ''])
            ->assertSessionHasErrors(['company_name', 'city']);

        $this->assertSame(0, Lead::count());
    }

    public function test_invalid_website_is_rejected(): void
    {
        $this->actingAs($this->user)->post(route('leads.store'), [
            'company_name' => 'Empresa X',
            'city' => 'Recife',
            'website' => 'nao-e-uma-url',
        ])->assertSessionHasErrors('website');
    }

    public function test_lead_listing_filters_by_city(): void
    {
        $leadService = app(LeadService::class);
        $leadService->create(['company_name' => 'Empresa Recife', 'city' => 'Recife'], [], [], $this->user);
        $leadService->create(['company_name' => 'Empresa SLM', 'city' => 'São Lourenço da Mata'], [], [], $this->user);

        $this->actingAs($this->user)
            ->get(route('leads.index', ['city' => 'Recife']))
            ->assertOk()
            ->assertSee('Empresa Recife')
            ->assertDontSee('Empresa SLM');
    }

    public function test_stage_change_is_recorded_in_history(): void
    {
        $lead = Lead::factory()->create();

        $this->actingAs($this->user)->patch(route('leads.stage', $lead), [
            'stage' => LeadStage::Contatado->value,
        ])->assertRedirect();

        $this->assertSame(LeadStage::Contatado, $lead->fresh()->stage);
        $this->assertDatabaseHas('lead_stage_history', [
            'lead_id' => $lead->id,
            'from_stage' => 'novo',
            'to_stage' => LeadStage::Contatado->value,
        ]);
    }

    public function test_manual_review_updates_qualification_status(): void
    {
        $lead = Lead::factory()->create();

        $this->actingAs($this->user)->patch(route('leads.review', $lead), [
            'decision' => 'rejeitado',
            'notes' => 'Sem evidências suficientes.',
        ])->assertRedirect();

        $this->assertSame('rejeitado', $lead->fresh()->qualification_status->value);
        $this->assertNotNull($lead->fresh()->reviewed_at);
    }

    public function test_export_returns_csv_stream(): void
    {
        app(LeadService::class)->create(['company_name' => 'Exportável', 'city' => 'Recife'], [], [], $this->user);

        $this->actingAs($this->user)->get(route('leads.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
