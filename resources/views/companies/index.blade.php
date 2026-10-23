@extends('layouts.app')
@section('title', 'Empresas')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('companies.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Nova empresa</a>
</div>
<form method="GET" class="card shadow-sm border-0 mb-3"><div class="card-body row g-2">
    <div class="col-md-4"><input name="q" class="form-control form-control-sm" placeholder="Buscar por nome" value="{{ request('q') }}"></div>
    <div class="col-md-3"><input list="cities-list" name="city" class="form-control form-control-sm" placeholder="Cidade" value="{{ request('city') }}">
        <datalist id="cities-list">@foreach($cities as $c)<option value="{{ $c }}">@endforeach</datalist></div>
    <div class="col-md-2"><button class="btn btn-sm btn-primary">Filtrar</button>
        <a href="{{ route('companies.index') }}" class="btn btn-sm btn-light">Limpar</a></div>
</div></form>
<div class="card shadow-sm border-0"><div class="table-responsive">
    <table class="table table-hover table-compact mb-0">
        <thead class="table-light"><tr><th>Empresa</th><th>Cidade</th><th>Segmento</th><th>Site</th><th>Leads</th><th></th></tr></thead>
        <tbody>
        @forelse($companies as $company)
            <tr @class(['table-warning' => $company->is_demo])>
                <td><a href="{{ route('companies.show', $company) }}" class="fw-semibold text-decoration-none">{{ $company->name }}</a>
                    @if($company->place_id)<span class="badge text-bg-light border" title="place_id retido conforme regras Google">place_id</span>@endif</td>
                <td>{{ $company->city }}/{{ $company->state }}</td>
                <td>{{ $company->segment ?: '—' }}</td>
                <td class="small">@if($company->website)<a href="{{ $company->website }}" target="_blank" rel="noopener">{{ $company->normalized_domain }}</a>@else — @endif</td>
                <td>{{ $company->leads_count }}</td>
                <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('companies.edit', $company) }}">Editar</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-secondary py-4">Nenhuma empresa cadastrada.</td></tr>
        @endforelse
        </tbody>
    </table>
</div><div class="card-footer">{{ $companies->links() }}</div></div>
@endsection
