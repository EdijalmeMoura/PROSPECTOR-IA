@extends('layouts.app')
@section('title', 'Opt-outs')
@section('content')
<div class="card shadow-sm border-0 mb-3"><div class="card-body">
    <form method="POST" action="{{ route('opt-outs.store') }}" class="row g-2">
        @csrf
        <div class="col-md-3"><select name="channel" class="form-select form-select-sm" required>
            @foreach(['email','whatsapp','instagram','telefone'] as $c)<option>{{ $c }}</option>@endforeach
        </select></div>
        <div class="col-md-4"><input name="identifier" class="form-control form-control-sm" placeholder="E-mail/telefone/handle" required></div>
        <div class="col-md-3"><input name="reason" class="form-control form-control-sm" placeholder="Motivo (opcional)"></div>
        <div class="col-md-2"><button class="btn btn-sm btn-danger w-100">Bloquear contato</button></div>
    </form>
</div></div>
<div class="card shadow-sm border-0"><div class="table-responsive">
    <table class="table table-sm table-compact mb-0">
        <thead class="table-light"><tr><th>Canal</th><th>Identificador</th><th>Origem</th><th>Motivo</th><th>Solicitado em</th></tr></thead>
        <tbody>
        @forelse($optOuts as $opt)
            <tr><td>{{ $opt->channel }}</td><td>{{ $opt->identifier }}</td><td>{{ $opt->source }}</td>
                <td class="small">{{ $opt->reason ?? '—' }}</td><td class="small">{{ $opt->requested_at->format('d/m/Y H:i') }}</td></tr>
        @empty
            <tr><td colspan="5" class="text-center text-secondary py-4">Nenhum pedido de não contato registrado.</td></tr>
        @endforelse
        </tbody>
    </table>
</div><div class="card-footer">{{ $optOuts->links() }}</div></div>
@endsection
