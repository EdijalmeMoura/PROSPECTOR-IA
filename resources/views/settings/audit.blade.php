@extends('layouts.app')
@section('title', 'Trilha de auditoria')
@section('content')
<form method="GET" class="mb-3"><div class="input-group input-group-sm" style="max-width: 360px;">
    <input name="action" class="form-control" placeholder="Filtrar ação (ex.: lead.created)" value="{{ request('action') }}">
    <button class="btn btn-outline-secondary">Filtrar</button>
</div></form>
<div class="card shadow-sm border-0"><div class="table-responsive">
    <table class="table table-sm table-compact mb-0">
        <thead class="table-light"><tr><th>Data</th><th>Usuário</th><th>Ação</th><th>Alvo</th><th>IP</th></tr></thead>
        <tbody>
        @forelse($logs as $log)
            <tr><td class="small">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                <td class="small">{{ $log->user?->name ?? 'sistema' }}</td>
                <td><code class="small">{{ $log->action }}</code></td>
                <td class="small">{{ $log->auditable_type }} #{{ $log->auditable_id }}</td>
                <td class="small">{{ $log->ip_address }}</td></tr>
        @empty
            <tr><td colspan="5" class="text-center text-secondary py-4">Sem registros.</td></tr>
        @endforelse
        </tbody>
    </table>
</div><div class="card-footer">{{ $logs->links() }}</div></div>
@endsection
