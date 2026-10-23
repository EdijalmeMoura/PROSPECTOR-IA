@extends('layouts.app')
@section('title', 'Negócio #' . $deal->id)
@section('content')
<a href="{{ route('deals.index') }}" class="btn btn-sm btn-outline-secondary mb-3">Voltar</a>
<div class="card shadow-sm border-0"><div class="card-header bg-white"><strong>{{ $deal->lead->company->name }}</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('deals.update', $deal) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(\App\Enums\DealStatus::cases() as $s)<option value="{{ $s->value }}" @selected($deal->status === $s)>{{ $s->label() }}</option>@endforeach
                    </select></div>
                <div class="col-md-4"><label class="form-label">Valor previsto</label>
                    <input name="expected_amount" type="number" step="0.01" class="form-control" value="{{ $deal->expected_amount }}" required></div>
                <div class="col-md-4"><label class="form-label">Receita recebida</label>
                    <input name="received_amount" type="number" step="0.01" class="form-control" value="{{ $deal->received_amount }}" required></div>
                <div class="col-12"><label class="form-label">Motivo da perda (obrigatório se perdido)</label>
                    <textarea name="lost_reason" rows="2" class="form-control">{{ $deal->lost_reason }}</textarea></div>
            </div>
            <hr><button class="btn btn-primary">Salvar</button>
        </form>
    </div></div>
@endsection
