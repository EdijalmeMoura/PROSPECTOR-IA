<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SearchJobStatus;
use App\Http\Requests\StoreSearchJobRequest;
use App\Jobs\RunSearchJob;
use App\Models\SearchJob;
use App\Models\Service;
use App\Services\Search\SearchOrchestrator;
use App\Services\Search\SearchProviderManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SearchJobController extends Controller
{
    public function index(): View
    {
        $jobs = SearchJob::with(['user', 'service'])
            ->latest()
            ->paginate(15);

        return view('searches.index', compact('jobs'));
    }

    public function create(SearchProviderManager $manager): View
    {
        $provider = $manager->make();

        return view('searches.create', [
            'services' => Service::where('is_active', true)->orderBy('name')->get(),
            'cities' => config('prospector.region.cities'),
            'segments' => config('prospector.segments'),
            'defaultTarget' => config('prospector.search.default_target'),
            'provider' => $provider,
        ]);
    }

    public function store(StoreSearchJobRequest $request, SearchOrchestrator $orchestrator): RedirectResponse
    {
        $job = $orchestrator->createJob($request->user(), $request->validated());
        RunSearchJob::dispatch($job);

        return redirect()->route('searches.show', $job)
            ->with('success', 'Pesquisa enfileirada. O progresso é atualizado nesta página.');
    }

    public function show(SearchJob $search): View
    {
        $search->load(['user', 'service', 'leads.company', 'leads.service']);

        return view('searches.show', ['job' => $search]);
    }

    /**
     * Reprocessamento seguro: cria um novo job com os mesmos parâmetros.
     */
    public function reprocess(SearchJob $search): RedirectResponse
    {
        if ($search->status === SearchJobStatus::Running) {
            return back()->with('warning', 'A pesquisa ainda está em execução.');
        }

        $newJob = SearchJob::create([
            'user_id' => $search->user_id,
            'service_id' => $search->service_id,
            'provider' => $search->provider,
            'status' => SearchJobStatus::Queued,
            'segment' => $search->segment,
            'cities' => $search->cities,
            'state' => $search->state,
            'target_quantity' => $search->target_quantity,
        ]);

        RunSearchJob::dispatch($newJob);

        return redirect()->route('searches.show', $newJob)->with('success', 'Novo processamento enfileirado com os mesmos parâmetros.');
    }
}
