@extends('layouts.app')
@section('title', 'Prévia da importação')
@section('content')
<div class="card shadow-sm border-0 mb-3"><div class="card-body">
    <div class="row text-center">
        <div class="col"><div class="fs-4 fw-bold">{{ $report->totalLines }}</div><div class="small text-secondary">Linhas</div></div>
        <div class="col"><div class="fs-4 fw-bold text-success">{{ collect($report->rows)->filter(fn($r) => $r->isValid())->count() }}</div><div class="small text-secondary">Válidas</div></div>
        <div class="col"><div class="fs-4 fw-bold text-warning">{{ $report->duplicates }}</div><div class="small text-secondary">Duplicadas</div></div>
        <div class="col"><div class="fs-4 fw-bold text-danger">{{ $report->rejected }}</div><div class="small text-secondary">Rejeitadas</div></div>
    </div>
    <form method="POST" action="{{ route('csv.import') }}" class="mt-3">
        @csrf
        <input type="hidden" name="path" value="{{ $path }}">
        <button class="btn btn-primary" @if(collect($report->rows)->every(fn($r) => !$r->isValid())) disabled @endif>
            <i class="bi bi-check2-circle me-1"></i>Importar apenas linhas válidas
        </button>
        <a href="{{ route('csv.create') }}" class="btn btn-outline-secondary">Cancelar</a>
    </form>
</div></div>
<div class="card shadow-sm border-0"><div class="table-responsive">
    <table class="table table-sm table-compact mb-0">
        <thead class="table-light"><tr><th>Linha</th><th>Empresa</th><th>Cidade</th><th>Site</th><th>Status</th><th>Motivo</th></tr></thead>
        <tbody>
        @foreach($report->rows as $row)
            <tr @class(['table-danger' => $row->errors, 'table-warning' => $row->duplicate])>
                <td>{{ $row->line }}</td>
                <td>{{ $row->data['company_name'] ?? '' }}</td>
                <td>{{ $row->data['city'] ?? '' }}</td>
                <td class="small">{{ $row->data['website'] ?? '' }}</td>
                <td>
                    @if($row->errors)<span class="badge text-bg-danger">rejeitada</span>
                    @elseif($row->duplicate)<span class="badge text-bg-warning">duplicada</span>
                    @else<span class="badge text-bg-success">válida</span>@endif
                </td>
                <td class="small text-danger">{{ implode(' ', $row->errors) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div></div>
@endsection
