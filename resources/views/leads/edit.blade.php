@extends('layouts.app')
@section('title', 'Editar lead')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white"><strong>Editar lead #{{ $lead->id }}</strong> — {{ $lead->company->name }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('leads.update', $lead) }}" data-disable-on-submit>
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Serviço recomendado</label>
                    <select name="service_id" class="form-select">
                        <option value="">—</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" @selected($lead->service_id == $service->id)>{{ $service->name }} — {{ $service->formattedPrice() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Justificativa do serviço</label>
                    <textarea name="recommended_service_reason" rows="2" class="form-control">{{ old('recommended_service_reason', $lead->recommended_service_reason) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Resumo da oportunidade</label>
                    <textarea name="opportunity_evidence_summary" rows="3" class="form-control">{{ old('opportunity_evidence_summary', $lead->opportunity_evidence_summary) }}</textarea>
                </div>
                <hr>
                <p class="small text-secondary">Adicionar canal de contato:</p>
                <div class="row g-2">
                    <div class="col-4">
                        <select name="contact_channel" class="form-select form-select-sm">
                            @foreach(['email','whatsapp','instagram','telefone','site'] as $c)<option>{{ $c }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-4"><input name="contact_value" class="form-control form-control-sm" placeholder="Valor do contato"></div>
                    <div class="col-4">
                        <select name="contact_permission" class="form-select form-select-sm">
                            @foreach($permissions as $p)<option value="{{ $p->value }}">{{ $p->label() }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary">Salvar e recalcular pontuação</button>
                    <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div></div>
@endsection
