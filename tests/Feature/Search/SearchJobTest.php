<?php

declare(strict_types=1);

namespace Tests\Feature\Search;

use App\Enums\SearchJobStatus;
use App\Jobs\RunSearchJob;
use App\Models\Lead;
use App\Models\SearchJob;
use App\Models\User;
use App\Services\Search\SearchOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SearchJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_search_dispatches_queued_job(): void
    {
        Queue::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('searches.store'), [
            'cities' => ['Recife'],
            'segment' => 'Todos',
            'target_quantity' => 50,
        ])->assertRedirect();

        Queue::assertPushed(RunSearchJob::class);
    }

    public function test_unconfigured_provider_ends_job_without_fabricating_results(): void
    {
        config(['prospector.providers.search' => 'null']);
        $job = SearchJob::factory()->create(['provider' => 'null']);

        $result = app(SearchOrchestrator::class)->run($job);

        $this->assertSame(SearchJobStatus::Failed, $result->status);
        $this->assertSame(0, $result->found_count);
        $this->assertStringContainsString('Conector não configurado', (string) $result->error_message);
        $this->assertSame(0, Lead::count());
    }

    public function test_reprocess_creates_a_new_job_with_same_parameters(): void
    {
        Queue::fake();
        $job = SearchJob::factory()->create(['status' => SearchJobStatus::Completed]);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('searches.reprocess', $job))->assertRedirect();
        $this->assertSame(2, SearchJob::count());
        $this->assertSame(SearchJobStatus::Queued, SearchJob::latest('id')->first()->status);
    }

    public function test_target_quantity_is_validated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('searches.store'), [
            'cities' => ['Recife'],
            'target_quantity' => 9999,
        ])->assertSessionHasErrors('target_quantity');
    }

    public function test_invalid_city_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('searches.store'), [
            'cities' => ['Município Inexistente'],
            'target_quantity' => 10,
        ])->assertSessionHasErrors('cities.*');
    }
}
