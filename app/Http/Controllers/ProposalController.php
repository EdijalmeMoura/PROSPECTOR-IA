<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProposalStatus;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\Service;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProposalController extends Controller
{
    public function index(): View
    {
        $proposals = Proposal::with(['lead.company', 'service'])->latest()->paginate(15);

        return view('proposals.index', compact('proposals'));
    }

    public function create(Request $request): View
    {
        $lead = Lead::with('company')->findOrFail($request->integer('lead'));

        return view('proposals.create', [
            'lead' => $lead,
            'services' => Service::where('is_active', true)->get(),
            'statuses' => ProposalStatus::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $valid = $request->validate([
            'lead_id' => ['required', 'exists:leads,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'content' => ['required', 'string', 'min:10', 'max:5000'],
            'valid_until' => ['nullable', 'date', 'after:today'],
        ]);

        $proposal = Proposal::create([...$valid, 'status' => ProposalStatus::Rascunho]);
        AuditLogger::record('proposal.created', $proposal);

        return redirect()->route('leads.show', $valid['lead_id'])->with('success', 'Proposta registrada (rascunho).');
    }

    public function show(Proposal $proposal): View
    {
        $proposal->load(['lead.company', 'service']);

        return view('proposals.show', compact('proposal'));
    }

    public function edit(Proposal $proposal): View
    {
        return view('proposals.edit', [
            'proposal' => $proposal,
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Proposal $proposal): RedirectResponse
    {
        $valid = $request->validate([
            'service_id' => ['nullable', 'exists:services,id'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'content' => ['required', 'string', 'min:10', 'max:5000'],
            'valid_until' => ['nullable', 'date'],
            'status' => ['required', 'in:' . implode(',', array_column(ProposalStatus::cases(), 'value'))],
        ]);

        $proposal->update($valid);
        AuditLogger::record('proposal.updated', $proposal);

        return redirect()->route('proposals.show', $proposal)->with('success', 'Proposta atualizada.');
    }

    public function destroy(Proposal $proposal): RedirectResponse
    {
        $leadId = $proposal->lead_id;
        $proposal->delete();

        return redirect()->route('leads.show', $leadId)->with('success', 'Proposta removida.');
    }
}
