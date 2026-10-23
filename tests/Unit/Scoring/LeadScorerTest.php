<?php

declare(strict_types=1);

namespace Tests\Unit\Scoring;

use App\Enums\ContactPermission;
use App\Models\Lead;
use App\Services\Scoring\LeadScorer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadScorerTest extends TestCase
{
    use RefreshDatabase;

    public function test_weak_lead_stays_below_qualification_threshold(): void
    {
        $lead = Lead::factory()
            ->for(\App\Models\Company::factory()->create(['city' => 'São Paulo', 'state' => 'SP']))
            ->create(['service_id' => null]);
        $result = app(LeadScorer::class)->score($lead->fresh(['company', 'evidence', 'contacts', 'service']));

        $this->assertSame(0, $result->criteria['design_need']['points']);
        $this->assertSame(0, $result->criteria['evidence_reliability']['points']);
        $this->assertSame(0, $result->criteria['channel_available']['points']);
        $this->assertLessThan(60, $result->total);
        $this->assertSame('precisa_evidencias', $result->qualificationStatus);
        $this->assertNotEmpty($result->criteria);
    }

    public function test_strong_evidence_allowed_channel_and_region_reaches_high_priority(): void
    {
        $lead = Lead::factory()->create();
        $lead->evidence()->createMany([
            ['type' => 'site_ausente', 'description' => 'Sem site', 'reliability' => 'alta', 'collected_by' => 'manual', 'observed_at' => now()],
            ['type' => 'redes_sem_padrao', 'description' => 'Sem padrão visual', 'reliability' => 'alta', 'collected_by' => 'manual', 'observed_at' => now()],
        ]);
        $lead->contacts()->create([
            'channel' => 'email', 'value' => 'a@empresa.com',
            'permission_status' => ContactPermission::Permitida->value, 'verified_at' => now(),
        ]);

        $result = app(LeadScorer::class)->score($lead->fresh(['company', 'evidence', 'contacts', 'service']));

        $this->assertGreaterThanOrEqual(80, $result->total);
        $this->assertSame('alta', $result->priority);
        $this->assertSame('qualificado', $result->qualificationStatus);
    }

    public function test_each_criterion_respects_its_max_weight(): void
    {
        $lead = Lead::factory()->create();
        $result = app(LeadScorer::class)->score($lead->fresh(['company', 'evidence', 'contacts', 'service']));

        foreach ($result->criteria as $criterion) {
            $this->assertLessThanOrEqual($criterion['max'], $criterion['points']);
            $this->assertNotEmpty($criterion['reason']);
        }
    }

    public function test_unknown_permission_scores_lower_than_allowed_permission(): void
    {
        $leadAllowed = Lead::factory()->create();
        $leadAllowed->contacts()->create(['channel' => 'email', 'value' => 'b@empresa.com', 'permission_status' => ContactPermission::Permitida->value]);

        $leadUnknown = Lead::factory()->create();
        $leadUnknown->contacts()->create(['channel' => 'email', 'value' => 'c@empresa.com', 'permission_status' => ContactPermission::Desconhecida->value]);

        $scorer = app(LeadScorer::class);
        $allowed = $scorer->score($leadAllowed->fresh(['company', 'evidence', 'contacts', 'service']));
        $unknown = $scorer->score($leadUnknown->fresh(['company', 'evidence', 'contacts', 'service']));

        $this->assertGreaterThan(
            $unknown->criteria['channel_available']['points'],
            $allowed->criteria['channel_available']['points'],
        );
    }
}
