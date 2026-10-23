@extends('layouts.app')
@section('title', 'Editar proposta')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card shadow-sm border-0"><div class="card-header bg-white"><strong>Editar proposta #{{ $proposal->id }}</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('proposals.update', $proposal) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Serviço</label>
                        <select name="service_id" class="form-select">
                            @foreach($services as $service)<option value="{{ $service->id }}" @selected($proposal->service_id == $service->id)>{{ $service->name }}</option>@endforeach
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Valor</label>
                        <input name="amount" type="number" step="0.01" class="form-control" value="{{ $proposal->amount }}" required></div>
                    <div class="col-md-3"><label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach(\App\Enums\ProposalStatus::cases() as $s)<option value="{{ $s->value }}" @selected($proposal->status === $s)>{{ $s->label() }}</option>@endforeach
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Válida até</label>
                        <input type="date" name="valid_until" class="form-control" value="{{ optional($proposal->valid_until)->format('Y-m-d') }}"></div>
                    <div class="col-12"><label class="form-label">Conteúdo</label>
                        <textarea name="content" rows="6" class="form-control">{{ $proposal->content }}</textarea></div>
                </div>
                <hr><button class="btn btn-primary">Salvar</button>
                <a href="{{ route('proposals.show', $proposal) }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div></div>
</div></div>
@endsection
