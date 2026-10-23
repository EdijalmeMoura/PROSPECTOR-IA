<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::with('user')
            ->when($request->filled('action'), fn ($q) => $q->where('action', 'like', '%' . $request->string('action') . '%'))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('settings.audit', compact('logs'));
    }
}
