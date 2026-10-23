@extends('layouts.app')
@section('title', 'Nova proposta')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card shadow-sm border-0"><div class="card-header bg-white"><strong>Proposta para {{ $lead->company->name }}</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('proposals.store') }}">
                @csrf
                <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Serviço</label>
                        <select name="service_id" class="form-select">
                            @foreach($services as $service)<option value="{{ $service->id }}">{{ $service->name }} — {{ $service->formattedPrice() }}</option>@endforeach
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Valor (R$)</label>
                        <input type="number" step="0.01" min="0" name="amount" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Válida até</label>
                        <input type="date" name="valid_until" class="form-control"></div>
                    <div class="col-12"><label class="form-label">Conteúdo</label>
                        <textarea name="content" rows="6" class="form-control" minlength="10" required></textarea></div>
                </div>
                <hr><button class="btn btn-primary">Salvar rascunho</button>
                <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div></div>
</div></div>
@endsection
