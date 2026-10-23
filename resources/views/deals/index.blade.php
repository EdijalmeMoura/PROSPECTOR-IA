@extends('layouts.app')
@section('title', 'Negócios')
@section('content')
<div class="card shadow-sm border-0"><div class="table-responsive">
    <table class="table table-hover table-compact mb-0">
        <thead class="table-light"><tr><th>#</th><th>Empresa</th><th>Status</th><th>Valor previsto</th><th>Recebido</th><th>Motivo perda</th><th></th></tr></thead>
        <tbody>
        @forelse($deals as $deal)
            <tr><td>#{{ $deal->id }}</td><td>{{ $deal->lead->company->name }}</td>
                <td><span class="badge @if($deal->status->value==='ganho') text-bg-success @elseif($deal->status->value==='perdido') text-bg-danger @else text-bg-secondary @endif">{{ $deal->status->label() }}</span></td>
                <td>R$ {{ number_format((float)$deal->expected_amount, 2, ',', '.') }}</td>
                <td class="text-success">R$ {{ number_format((float)$deal->received_amount, 2, ',', '.') }}</td>
                <td class="small">{{ $deal->lost_reason ?? '—' }}</td>
                <td><a href="{{ route('deals.show', $deal) }}" class="btn btn-sm btn-outline-primary">Abrir</a></td></tr>
        @empty
            <tr><td colspan="7" class="text-center text-secondary py-4">Nenhum negócio registrado.</td></tr>
        @endforelse
        </tbody>
    </table>
</div><div class="card-footer">{{ $deals->links() }}</div></div>
@endsection
