<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Enums\AiKind;
use App\Enums\AiStatus;
use App\Models\AiGeneration;
use App\Models\Lead;
use App\Models\User;
use App\Services\Ai\LeadAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiUnavailableTest extends TestCase
{
    use RefreshDatabase;

    public function test_generation_without_credentials_is_marked_unavailable_not_faked(): void
    {
        config(['prospector.providers.ai' => 'null']);
        $user = User::factory()->create();
        $lead = Lead::factory()->create();

        $generation = app(LeadAiService::class)->generate($lead, AiKind::Resumo, $user);

        $this->assertSame(AiStatus::Indisponivel, $generation->status);
        $this->assertNull($generation->structured_output);
        $this->assertStringContainsString('não configurado', (string) $generation->error_message);
        $this->assertSame(1, AiGeneration::count());
    }

    public function test_manual_review_still_works_without_ai(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->create();

        $this->actingAs($user)->patch(route('leads.review', $lead), [
            'decision' => 'qualificado',
            'notes' => 'Revisão humana manual.',
        ])->assertRedirect();

        $this->assertSame('qualificado', $lead->fresh()->qualification_status->value);
    }
}
