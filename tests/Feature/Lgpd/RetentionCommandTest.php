<?php

declare(strict_types=1);

namespace Tests\Feature\Lgpd;

use App\Enums\RetentionCondition;
use App\Models\Approval;
use App\Models\CompanySource;
use App\Models\Lead;
use App\Models\MessageLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetentionCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_purge_removes_transient_metadata_but_keeps_place_identifier(): void
    {
        $source = CompanySource::factory()->create([
            'retention_condition' => RetentionCondition::Transitoria->value,
            'retention_expires_at' => now()->subDay(),
            'metadata' => ['formatted_address' => 'Rua X, 100'],
            'external_id' => 'PLACE_KEEP_1',
        ]);

        $this->artisan('prospector:purge-retention')->assertSuccessful();

        $source->refresh();
        $this->assertNull($source->metadata);
        $this->assertSame('PLACE_KEEP_1', $source->external_id);
    }

    public function test_dry_run_does_not_modify_data(): void
    {
        $source = CompanySource::factory()->create([
            'retention_condition' => RetentionCondition::Transitoria->value,
            'retention_expires_at' => now()->subDay(),
            'metadata' => ['x' => 1],
        ]);

        $this->artisan('prospector:purge-retention', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame(['x' => 1], $source->fresh()->metadata);
    }
}
