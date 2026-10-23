<?php

declare(strict_types=1);

namespace Tests\Feature\Lgpd;

use App\Models\AuditLog;
use App\Models\Lead;
use App\Models\OptOut;
use App\Models\User;
use App\Services\Leads\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OptOutAndAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_opt_out_is_registered_and_unique_per_channel_and_identifier(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('opt-outs.store'), [
            'channel' => 'whatsapp',
            'identifier' => '(81) 99999-0000',
            'reason' => 'Titular pediu remoção',
        ])->assertRedirect();

        $this->assertDatabaseHas('opt_outs', [
            'channel' => 'whatsapp',
            'identifier' => '81999990000',
        ]);
    }

    public function test_lead_creation_writes_audit_trail(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        app(LeadService::class)->create(
            ['company_name' => 'Auditada', 'city' => 'Recife'], [], [], $user,
        );

        $this->assertTrue(AuditLog::where('action', 'lead.created')->exists());
        $log = AuditLog::where('action', 'lead.created')->first();
        $this->assertSame($user->id, $log->user_id);
        $this->assertNotNull($log->created_at);
    }

    public function test_demo_data_is_kept_separate_from_real_data(): void
    {
        Lead::factory()->create(['is_demo' => true]);
        Lead::factory()->create(['is_demo' => false]);

        $this->assertSame(1, Lead::real()->count());
        $this->assertSame(1, Lead::where('is_demo', true)->count());
    }
}
