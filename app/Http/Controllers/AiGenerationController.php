<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AiKind;
use App\Models\Lead;
use App\Services\Ai\LeadAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AiGenerationController extends Controller
{
    public function generate(Request $request, Lead $lead, LeadAiService $ai): RedirectResponse
    {
        $valid = $request->validate([
            'kind' => ['required', Rule::enum(AiKind::class)],
        ]);

        $generation = $ai->generate($lead, AiKind::from($valid['kind']), $request->user());

        return back()->with(
            $generation->status->value === 'sucesso' ? 'success' : 'warning',
            $generation->status->value === 'sucesso'
                ? 'Geração de IA concluída e vinculada às evidências.'
                : 'IA indisponível: ' . ($generation->error_message ?? 'sem credenciais configuradas.')
        );
    }
}
