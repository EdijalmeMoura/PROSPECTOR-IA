<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OptOut;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OptOutController extends Controller
{
    public function index(): View
    {
        $optOuts = OptOut::with('lead.company')->latest('requested_at')->paginate(15);

        return view('opt-outs.index', compact('optOuts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $valid = $request->validate([
            'channel' => ['required', 'in:email,whatsapp,instagram,telefone'],
            'identifier' => ['required', 'string', 'max:180'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'lead_id' => ['nullable', 'exists:leads,id'],
        ]);

        $identifier = $valid['channel'] === 'whatsapp'
            ? preg_replace('/\D+/', '', $valid['identifier'])
            : mb_strtolower(trim($valid['identifier']));

        $optOut = OptOut::updateOrCreate(
            ['channel' => $valid['channel'], 'identifier' => $identifier],
            ['reason' => $valid['reason'] ?? null, 'lead_id' => $valid['lead_id'] ?? null, 'source' => 'manual', 'requested_at' => now()],
        );

        AuditLogger::record('optout.registered', $optOut);

        return back()->with('success', 'Pedido de não contato registrado; novos envios serão bloqueados.');
    }
}
