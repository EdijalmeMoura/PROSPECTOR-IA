@extends('layouts.app')
@section('title', 'Proposta #' . $proposal->id)
@section('content')
<a href="{{ route('proposals.index') }}" class="btn btn-sm btn-outline-secondary mb-3">Voltar</a>
<div class="card shadow-sm border-0"><div class="card-header bg-white d-flex justify-content-between">
    <strong>Proposta #{{ $proposal->id }} — {{ $proposal->lead->company->name }}</strong>
    <span class="badge text-bg-light border">{{ $proposal->status->label() }}</span></div>
    <div class="card-body">
        <dl class="row small">
            <dt class="col-sm-3">Serviço</dt><dd class="col-sm-9">{{ $proposal->service?->name ?? '—' }}</dd>
            <dt class="col-sm-3">Valor</dt><dd class="col-sm-9">R$ {{ number_format((float)$proposal->amount, 2, ',', '.') }}</dd>
            <dt class="col-sm-3">Válida até</dt><dd class="col-sm-9">{{ optional($proposal->valid_until)?->format('d/m/Y') ?? '—' }}</dd>
            <dt class="col-sm-3">Enviada em</dt><dd class="col-sm-9">{{ optional($proposal->sent_at)?->format('d/m/Y H:i') ?? 'rascunho' }}</dd>
        </dl>
        <div class="border rounded p-3 bg-body-tertiary" style="white-space: pre-wrap;">{{ $proposal->content }}</div>
        <a href="{{ route('proposals.edit', $proposal) }}" class="btn btn-sm btn-outline-primary mt-3">Editar</a>
    </div></div>
@endsection
