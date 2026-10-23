<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Services\Communication\CommunicationService;
use Illuminate\Http\RedirectResponse;

class MessageController extends Controller
{
    public function send(Approval $approval, CommunicationService $communication): RedirectResponse
    {
        $result = $communication->send($approval->fresh());

        return $result['ok']
            ? back()->with('success', 'Mensagem enviada e confirmada pelo provedor.')
            : back()->with('warning', 'Envio bloqueado: ' . ($result['reason'] ?? 'falha desconhecida'));
    }
}
