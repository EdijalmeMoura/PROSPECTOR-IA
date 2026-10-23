<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\SearchJob;
use App\Services\Search\SearchOrchestrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunSearchJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public function __construct(public readonly SearchJob $searchJob)
    {
        // Funciona com database (hospedagem comum) e redis quando disponível.
        $this->onQueue('searches');
    }

    public function handle(SearchOrchestrator $orchestrator): void
    {
        $orchestrator->run($this->searchJob);
    }
}
