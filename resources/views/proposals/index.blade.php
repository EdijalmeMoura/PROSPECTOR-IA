@extends('layouts.app')
@section('title', 'Propostas')
@section('content')
<div class="card shadow-sm border-0"><div class="table-responsive">
    <table class="table table-hover table-compact mb-0">
        <thead class="table-light"><tr><th>#</th><th>Empresa</th><th>Serviço</th><th>Valor (R$)</th><th>Status</th><th>Enviada em</th><th></th></tr></thead>
        <tbody>
        @forelse($proposals as $proposal)
            <tr><td>#{{ $proposal->id }}</td><td>{{ $proposal->lead->company->name }}</td>
                <td>{{ $proposal->service?->name ?? '—' }}</td>
                <td>{{ number_format((float)$proposal->amount, 2, ',', '.') }}</td>
                <td><span class="badge text-bg-light border">{{ $proposal->status->label() }}</span></td>
                <td class="small">{{ optional($proposal->sent_at)?->format('d/m/Y') ?? '—' }}</td>
                <td><a href="{{ route('proposals.show', $proposal) }}" class="btn btn-sm btn-outline-primary">Abrir</a></td></tr>
        @empty
            <tr><td colspan="7" class="text-center text-secondary py-4">Nenhuma proposta registrada.</td></tr>
        @endforelse
        </tbody>
    </table>
</div><div class="card-footer">{{ $proposals->links() }}</div></div>
@endsection
