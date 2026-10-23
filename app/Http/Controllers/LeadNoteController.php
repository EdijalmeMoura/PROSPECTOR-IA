<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadNoteController extends Controller
{
    public function store(Request $request, Lead $lead): RedirectResponse
    {
        $valid = $request->validate([
            'body' => ['required', 'string', 'min:3', 'max:4000'],
            'interaction_type' => ['required', 'in:nota,ligacao,email,reuniao,whatsapp'],
        ]);

        $lead->notes()->create([...$valid, 'user_id' => $request->user()->id]);

        return back()->with('success', 'Interação registrada no histórico.');
    }
}
