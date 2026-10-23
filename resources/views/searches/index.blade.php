@extends('layouts.app')
@section('title', 'Histórico de pesquisas')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h2 class="h5 mb-0">Histórico de pesquisas</h2>
    <a href="{{ route('searches.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Nova pesquisa</a>
</div>
<div class="card shadow-sm border-0"><div class="table-responsive">
    <table class="table table-hover table-compact mb-0">
        <thead class="table-light"><tr><th>#</th><th>Fonte</th><th>Municípios</th><th>Segmento</th><th>Meta</th><th>Status</th><th>Encontradas</th><th>Duplicadas</th><th>Rejeitadas</th><th>Qualificadas</th><th></th></tr></thead>
        <tbody>
        @forelse($jobs as $job)
            <tr>
                <td>#{{ $job->id }}</td>
                <td><span class="badge text-bg-light border">{{ $job->provider }}</span></td>
                <td class="small">{{ implode(', ', array_slice($job->cities, 0, 2)) }}{{ count($job->cities) > 2 ? ' +' . (count($job->cities) - 2) : '' }}</td>
                <td class="small">{{ $job->segment }}</td>
                <td>{{ $job->target_quantity }}</td>
                <td><span class="badge @if($job->status->value==='completed') text-bg-success @elseif($job->status->value==='failed') text-bg-danger @elseif($job->status->value==='running') text-bg-primary @else text-bg-secondary @endif">{{ $job->status->label() }}</span></td>
                <td>{{ $job->found_count }}</td><td>{{ $job->duplicate_count }}</td><td>{{ $job->rejected_count }}</td><td>{{ $job->qualified_count }}</td>
                <td class="text-end"><a href="{{ route('searches.show', $job) }}" class="btn btn-sm btn-outline-primary">Abrir</a></td>
            </tr>
        @empty
            <tr><td colspan="11" class="text-center text-secondary py-4">Nenhuma pesquisa executada ainda.</td></tr>
        @endforelse
        </tbody>
    </table>
</div><div class="card-footer">{{ $jobs->links() }}</div></div>
@endsection
