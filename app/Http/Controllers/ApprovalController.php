<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ChannelType;
use App\Enums\ContactPermission;
use App\Models\Approval;
use App\Models\Lead;
use App\Services\Approval\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(private readonly ApprovalService $approvals) {}

    public function index(Request $request): View
    {
        $approvals = Approval::with('lead.company')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('approvals.index', ['approvals' => $approvals]);
    }

    public function store(Request $request, Lead $lead): RedirectResponse
    {
        $valid = $request->validate([
            'channel' => ['required', Rule::enum(ChannelType::class)],
            'recipient' => ['required', 'string', 'max:180'],
            'full_text' => ['required', 'string', 'min:20', 'max:4000'],
            'ai_generation_id' => ['nullable', 'integer', 'exists:ai_generations,id'],
        ], [
            'full_text.min' => 'A mensagem precisa ter ao menos 20 caracteres.',
        ]);

        $approval = $this->approvals->createDraft(
            $lead,
            ChannelType::from($valid['channel']),
            $valid['recipient'],
            $valid['full_text'],
            $valid['ai_generation_id'] ?? null,
        );

        return redirect()->route('approvals.show', $approval)->with('success', 'Versão criada e enviada para a central de aprovação.');
    }

    public function show(Approval $approval): View
    {
        $approval->load(['lead.company', 'lead.contacts', 'lead.evidence', 'approver', 'aiGeneration']);

        return view('approvals.show', [
            'approval' => $approval,
            'channels' => ChannelType::cases(),
            'permissions' => ContactPermission::cases(),
        ]);
    }

    public function approve(Approval $approval, Request $request): RedirectResponse
    {
        $this->approvals->approve($approval, $request->user());

        return back()->with('success', 'Conteúdo aprovado. O envio ainda exige a ação explícita de envio.');
    }

    public function reject(Approval $approval, Request $request): RedirectResponse
    {
        $valid = $request->validate(['decision_note' => ['required', 'string', 'min:3', 'max:1000']],
            ['decision_note.required' => 'Informe o motivo da rejeição.']);

        $this->approvals->reject($approval, $request->user(), $valid['decision_note']);

        return back()->with('success', 'Aprovação rejeitada.');
    }

    public function cancel(Approval $approval, Request $request): RedirectResponse
    {
        $this->approvals->cancel($approval, $request->user());

        return back()->with('success', 'Solicitação cancelada.');
    }
}
