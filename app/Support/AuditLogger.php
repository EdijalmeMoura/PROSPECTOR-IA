<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Registra uma ação relevante para conformidade/LGPD.
     * Nenhum token ou segredo deve ser incluído em $changes.
     *
     * @param  array<string, mixed>|null  $changes
     */
    public static function record(string $action, ?Model $auditable = null, ?array $changes = null): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'changes' => $changes,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent() ? mb_substr((string) Request::userAgent(), 0, 500) : null,
            'created_at' => now(),
        ]);
    }
}
