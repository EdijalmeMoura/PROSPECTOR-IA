@extends('layouts.app')
@section('title', 'Empresa')
@section('content')
<a href="{{ route('companies.index') }}" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Voltar</a>
<div class="card shadow-sm border-0 mb-3"><div class="card-header bg-white d-flex justify-content-between">
    <strong>{{ $company->name }}</strong>
    <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-outline-primary">Editar</a></div>
    <div class="card-body">
        <dl class="row small mb-0">
            <dt class="col-sm-3">Cidade/UF</dt><dd class="col-sm-9">{{ $company->city }}/{{ $company->state }}</dd>
            <dt class="col-sm-3">Segmento</dt><dd class="col-sm-9">{{ $company->segment ?: '—' }}</dd>
            <dt class="col-sm-3">Site</dt><dd class="col-sm-9">{{ $company->website ?: '—' }}</dd>
            <dt class="col-sm-3">Domínio normalizado</dt><dd class="col-sm-9">{{ $company->normalized_domain ?: '—' }}</dd>
            <dt class="col-sm-3">Place ID</dt><dd class="col-sm-9"><code>{{ $company->place_id ?: '—' }}</code></dd>
            <dt class="col-sm-3">Finalidade</dt><dd class="col-sm-9">{{ $company->processing_purpose }}</dd>
            <dt class="col-sm-3">Última verificação</dt><dd class="col-sm-9">{{ optional($company->last_verified_at)->format('d/m/Y H:i') }}</dd>
        </dl>
    </div></div>
<div class="card shadow-sm border-0"><div class="card-header bg-white"><strong>Fontes e retenção</strong></div>
    <div class="table-responsive"><table class="table table-sm mb-0">
        <thead><tr><th>Fonte</th><th>ID externo</th><th>Retenção</th><th>Coleta</th><th>Expira retenção</th></tr></thead>
        <tbody>
        @foreach($company->sources as $source)
            <tr><td>{{ $source->source_name }}</td><td><code class="small">{{ $source->external_id }}</code></td>
                <td>{{ $source->retention_condition->label() }}</td>
                <td class="small">{{ optional($source->collected_at)->format('d/m/Y') }}</td>
                <td class="small">{{ optional($source->retention_expires_at)->format('d/m/Y') ?: 'permanente' }}</td></tr>
        @endforeach
        </tbody></table></div></div>
<div class="card shadow-sm border-0 mt-3"><div class="card-header bg-white"><strong>Leads vinculados</strong></div>
    <div class="list-group list-group-flush">
        @forelse($company->leads as $lead)
            <a href="{{ route('leads.show', $lead) }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                <span>Lead #{{ $lead->id }} — {{ $lead->stage->label() }}</span>
                @include('partials.score-badge', ['score' => $lead->score])
            </a>
        @empty <p class="text-secondary small p-3 mb-0">Nenhum lead vinculado.</p> @endforelse
    </div></div>
@endsection
