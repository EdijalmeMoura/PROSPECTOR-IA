@extends('layouts.app')
@section('title', 'Pesquisa #' . $job->id)
@section('content')
<a href="{{ route('searches.index') }}" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Voltar</a>
<div class="card shadow-sm border-0 mb-3"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <strong>Pesquisa #{{ $job->id }}</strong>
            <span class="badge @if($job->status->value==='completed') text-bg-success @elseif($job->status->value==='failed') text-bg-danger @else text-bg-secondary @endif ms-2">{{ $job->status->label() }}</span>
        </div>
        @if($job->status->isFinished())
            <form method="POST" action="{{ route('searches.reprocess', $job) }}">
                @csrf <button class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-repeat me-1"></i>Reprocessar com os mesmos parâmetros</button>
            </form>
        @endif
    </div>
    <div class="progress my-3" role="progressbar" aria-valuenow="{{ $job->progress }}" aria-valuemin="0" aria-valuemax="100" style="height: 22px;">
        <div class="progress-bar progress-bar-striped @if($job->status->value==='running') progress-bar-animated @endif" style="width: {{ $job->progress }}%">{{ $job->progress }}%</div>
    </div>
    <div class="row text-center">
        @foreach(['Meta' => $job->target_quantity, 'Encontradas' => $job->found_count, 'Duplicadas' => $job->duplicate_count, 'Rejeitadas' => $job->rejected_count, 'Qualificadas' => $job->qualified_count] as $label => $value)
            <div class="col"><div class="fs-5 fw-bold">{{ $value }}</div><div class="small text-secondary">{{ $label }}</div></div>
        @endforeach
    </div>
    @if($job->error_message)<div class="alert alert-warning mt-3 mb-0 small">{{ $job->error_message }}</div>@endif
    <p class="small text-secondary mt-2 mb-0">Fonte: {{ $job->provider }} · municípios: {{ implode(', ', $job->cities) }} · segmento: {{ $job->segment }}</p>
</div></div>
<div class="card shadow-sm border-0"><div class="card-header bg-white"><strong>Leads gerados</strong></div>
    <div class="table-responsive"><table class="table table-sm table-compact mb-0">
        <thead><tr><th>Empresa</th><th>Cidade</th><th>Pontuação</th><th>Qualificação</th><th></th></tr></thead>
        <tbody>
        @forelse($job->leads as $lead)
            <tr><td>{{ $lead->company->name }}</td><td>{{ $lead->company->city }}</td>
                <td>@include('partials.score-badge', ['score' => $lead->score])</td>
                <td>{{ $lead->qualification_status->label() }}</td>
                <td><a href="{{ route('leads.show', $lead) }}" class="btn btn-sm btn-outline-primary">Abrir</a></td></tr>
        @empty
            <tr><td colspan="5" class="text-center text-secondary py-3">Nenhum lead gerado por esta pesquisa.</td></tr>
        @endforelse
        </tbody></table></div></div>
@endsection
