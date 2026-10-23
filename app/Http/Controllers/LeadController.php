<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ContactPermission;
use App\Enums\LeadStage;
use App\Enums\Priority;
use App\Enums\QualificationStatus;
use App\Http\Requests\ReviewLeadRequest;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\Service;
use App\Services\Leads\LeadService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function __construct(private readonly LeadService $leadService) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'city' => ['nullable', 'string'],
            'segment' => ['nullable', 'string'],
            'service_id' => ['nullable', 'integer'],
            'priority' => ['nullable', 'string'],
            'stage' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'q' => ['nullable', 'string', 'max:120'],
            'scope' => ['nullable', 'in:real,demo'],
        ]);

        $leads = $this->filterQuery($request)
            ->with(['company', 'service', 'assignedUser'])
            ->latest('leads.created_at')
            ->paginate(15)
            ->withQueryString();

        return view('leads.index', [
            'leads' => $leads,
            'services' => Service::orderBy('name')->get(),
            'cities' => config('prospector.region.cities'),
            'segments' => config('prospector.segments'),
            'stages' => LeadStage::cases(),
            'priorities' => Priority::cases(),
            'statuses' => QualificationStatus::cases(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('leads.create', [
            'services' => Service::where('is_active', true)->orderBy('name')->get(),
            'cities' => config('prospector.region.cities'),
            'segments' => config('prospector.segments'),
            'permissions' => ContactPermission::cases(),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $contacts = [];
        if (! empty($data['contact_value'])) {
            $contacts[] = [
                'channel' => $data['contact_channel'] ?? 'email',
                'value' => $data['contact_value'],
                'permission_status' => $data['contact_permission'] ?? ContactPermission::Desconhecida->value,
            ];
        }

        $evidence = [];
        if (! empty($data['evidence_description'])) {
            $evidence[] = [
                'type' => $data['evidence_type'] ?? 'observacao_manual',
                'description' => $data['evidence_description'],
                'reliability' => $data['evidence_reliability'] ?? 'media',
                'collected_by' => 'manual',
            ];
        }

        $lead = $this->leadService->create(
            data: $data,
            contacts: $contacts,
            evidence: $evidence,
            user: $request->user(),
        );

        return redirect()->route('leads.show', $lead)->with('success', 'Lead cadastrado e pontuado com sucesso.');
    }

    public function show(Lead $lead): View
    {
        $lead->load([
            'company.sources', 'service', 'contacts', 'evidence', 'notes.user',
            'tasks', 'stageHistory.user', 'approvals' => fn ($q) => $q->latest('id'),
            'proposals', 'deal', 'searchJob', 'reviewer',
        ]);

        return view('leads.show', [
            'lead' => $lead,
            'services' => Service::where('is_active', true)->get(),
            'stages' => LeadStage::cases(),
            'permissions' => ContactPermission::cases(),
        ]);
    }

    public function edit(Lead $lead): View
    {
        $lead->load(['company', 'contacts', 'service']);

        return view('leads.edit', [
            'lead' => $lead,
            'services' => Service::orderBy('name')->get(),
            'permissions' => ContactPermission::cases(),
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $data = $request->validated();

        $lead->update(array_filter([
            'service_id' => $data['service_id'] ?? null,
            'recommended_service_reason' => $data['recommended_service_reason'] ?? null,
            'opportunity_evidence_summary' => $data['opportunity_evidence_summary'] ?? null,
        ], fn ($v) => $v !== null));

        if (! empty($data['contact_value'])) {
            $lead->contacts()->create([
                'channel' => $data['contact_channel'] ?? 'email',
                'value' => $data['contact_value'],
                'permission_status' => $data['contact_permission'] ?? ContactPermission::Desconhecida->value,
                'verified_at' => now(),
            ]);
        }

        $this->leadService->applyScore($lead->fresh());

        return redirect()->route('leads.show', $lead)->with('success', 'Lead atualizado; pontuação recalculada.');
    }

    public function changeStage(Request $request, Lead $lead): RedirectResponse
    {
        $valid = $request->validate([
            'stage' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $stage = LeadStage::tryFrom($valid['stage']);
        if (! $stage) {
            return back()->withErrors(['stage' => 'Etapa inválida.']);
        }

        $this->leadService->changeStage($lead, $stage, $request->user(), $valid['reason'] ?? null);

        return back()->with('success', "Etapa alterada para {$stage->label()}.");
    }

    public function review(ReviewLeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->leadService->review($lead, $request->user(), $request->validated('decision'), $request->validated('notes'));

        return back()->with('success', 'Revisão manual registrada.');
    }

    public function exportShow(Lead $lead): StreamedResponse
    {
        return $this->streamCsv(collect([$lead->load(['company', 'service'])]), "lead-{$lead->id}.csv");
    }

    public function export(Request $request): StreamedResponse
    {
        $leads = $this->filterQuery($request)->with(['company', 'service'])->get();

        return $this->streamCsv($leads, 'leads-prospector.csv');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Lead>
     */
    private function filterQuery(Request $request)
    {
        return Lead::query()
            ->join('companies', 'companies.id', '=', 'leads.company_id')
            ->when($request->filled('city'), fn ($q) => $q->where('companies.city', $request->string('city')))
            ->when($request->filled('segment'), fn ($q) => $q->where('companies.segment', $request->string('segment')))
            ->when($request->filled('service_id'), fn ($q) => $q->where('leads.service_id', $request->integer('service_id')))
            ->when($request->filled('priority'), fn ($q) => $q->where('leads.priority', $request->string('priority')))
            ->when($request->filled('stage'), fn ($q) => $q->where('leads.stage', $request->string('stage')))
            ->when($request->filled('status'), fn ($q) => $q->where('leads.qualification_status', $request->string('status')))
            ->when($request->filled('q'), fn ($q) => $q->where('companies.name', 'like', '%' . $request->string('q') . '%'))
            ->when($request->string('scope')->value() === 'demo', fn ($q) => $q->where('leads.is_demo', true))
            ->when(! $request->boolean('include_demo') && $request->string('scope')->value() !== 'demo', fn ($q) => $q->where('leads.is_demo', false))
            ->select('leads.*');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Lead>  $leads
     */
    private function streamCsv($leads, string $filename): StreamedResponse
    {
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => "attachment; filename=\"$filename\""];

        return response()->stream(function () use ($leads) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para Excel
            fputcsv($out, ['ID', 'Empresa', 'Cidade', 'Segmento', 'Site', 'Etapa', 'Status', 'Prioridade', 'Pontuação', 'Serviço', 'Verificado em', 'Fonte']);
            foreach ($leads as $lead) {
                fputcsv($out, [
                    $lead->id,
                    $lead->company?->name,
                    $lead->company?->city,
                    $lead->company?->segment,
                    $lead->company?->website,
                    $lead->stage?->label(),
                    $lead->qualification_status?->label(),
                    $lead->priority?->label(),
                    $lead->score,
                    $lead->service?->name,
                    optional($lead->last_verified_at)?->format('d/m/Y H:i'),
                    $lead->searchJob?->provider ?? 'manual',
                ]);
            }
            fclose($out);
        }, Response::HTTP_OK, $headers);
    }
}
