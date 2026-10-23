@extends('layouts.app')
@section('title', 'Leads')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Novo lead</a>
        <a href="{{ route('csv.create') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-filetype-csv me-1"></i>Importar CSV</a>
    </div>
    <a href="{{ route('leads.export', request()->query()) }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-download me-1"></i>Exportar CSV</a>
</div>

<form method="GET" class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-2">
                <label class="form-label small" for="q">Busca por empresa</label>
                <input type="search" name="q" id="q" class="form-control form-control-sm" value="{{ $filters['q'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small" for="city">Cidade</label>
                <select name="city" id="city" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($cities as $city)
                        <option @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small" for="segment">Segmento</label>
                <select name="segment" id="segment" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($segments as $segment)
                        <option @selected(($filters['segment'] ?? '') === $segment)>{{ $segment }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small" for="service_id">Serviço</label>
                <select name="service_id" id="service_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected((int) ($filters['service_id'] ?? 0) === $service->id)>{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small" for="priority">Prioridade</label>
                <select name="priority" id="priority" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($priorities as $priority)
                        <option value="{{ $priority->value }}" @selected(($filters['priority'] ?? '') === $priority->value)>{{ $priority->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small" for="stage">Etapa</label>
                <select name="stage" id="stage" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($stages as $stage)
                        <option value="{{ $stage->value }}" @selected(($filters['stage'] ?? '') === $stage->value)>{{ $stage->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2 align-items-center">
            <button class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>Filtrar</button>
            <a href="{{ route('leads.index') }}" class="btn btn-sm btn-light">Limpar</a>
            <div class="form-check form-check-inline ms-auto">
                <input class="form-check-input" type="radio" name="scope" id="scopeReal" value="real" @checked(($filters['scope'] ?? 'real') !== 'demo')>
                <label class="form-check-label small" for="scopeReal">Reais</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="scope" id="scopeDemo" value="demo" @checked(($filters['scope'] ?? '') === 'demo')>
                <label class="form-check-label small" for="scopeDemo">Demonstrativos (fictícios)</label>
            </div>
        </div>
    </div>
</form>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover table-compact mb-0 align-middle">
            <thead class="table-light">
            <tr>
                <th>Empresa</th><th>Cidade/UF</th><th>Segmento</th><th>Serviço</th>
                <th>Pontuação</th><th>Qualificação</th><th>Etapa</th><th>Verificado</th><th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($leads as $lead)
                <tr @class(['table-warning' => $lead->is_demo])>
                    <td>
                        <a href="{{ route('leads.show', $lead) }}" class="fw-semibold text-decoration-none">{{ $lead->company->name }}</a>
                        @if($lead->is_demo)<span class="badge text-bg-info ms-1">fictício</span>@endif
                    </td>
                    <td>{{ $lead->company->city }}/{{ $lead->company->state }}</td>
                    <td>{{ $lead->company->segment ?: '—' }}</td>
                    <td>{{ $lead->service?->name ?: '—' }}</td>
                    <td>@include('partials.score-badge', ['score' => $lead->score])</td>
                    <td><span class="badge text-bg-light border">{{ $lead->qualification_status->label() }}</span></td>
                    <td><span class="badge text-bg-dark">{{ $lead->stage->label() }}</span></td>
                    <td class="small">{{ optional($lead->last_verified_at)->format('d/m/Y') }}</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('leads.show', $lead) }}">Detalhes</a>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('leads.edit', $lead) }}">Editar</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-secondary py-4">Nenhum lead encontrado. Cadastre manualmente, importe um CSV ou inicie uma pesquisa.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $leads->links() }}</div>
</div>
@endsection
