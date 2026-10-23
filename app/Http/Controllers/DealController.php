<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DealStatus;
use App\Models\Deal;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DealController extends Controller
{
    public function index(): View
    {
        $deals = Deal::with('lead.company')->latest()->paginate(15);

        return view('deals.index', compact('deals'));
    }

    public function show(Deal $deal): View
    {
        $deal->load('lead.company', 'proposal');

        return view('deals.show', compact('deal'));
    }

    public function update(Request $request, Deal $deal): RedirectResponse
    {
        $valid = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_column(DealStatus::cases(), 'value'))],
            'expected_amount' => ['required', 'numeric', 'min:0'],
            'received_amount' => ['required', 'numeric', 'min:0'],
            'lost_reason' => ['nullable', 'string', 'max:1000', 'required_if:status,perdido'],
        ]);

        $valid['won_at'] = $valid['status'] === DealStatus::Ganho->value ? now() : null;
        $valid['lost_at'] = $valid['status'] === DealStatus::Perdido->value ? now() : null;

        $deal->update($valid);
        AuditLogger::record('deal.updated', $deal, ['status' => $valid['status']]);

        return back()->with('success', 'Negócio atualizado. Receita recebida só conta quando registrada.');
    }
}
