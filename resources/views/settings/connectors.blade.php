@extends('layouts.app')
@section('title', 'Conectores e integrações')
@section('content')
<div class="alert alert-secondary small"><i class="bi bi-shield-lock me-1"></i>Credenciais ficam apenas no servidor (variáveis de ambiente). Tokens nunca aparecem na interface nem nos logs.</div>

<div class="card shadow-sm border-0 mb-3"><div class="card-header bg-white"><strong>Pesquisa de empresas</strong></div>
    <div class="card-body">
        <p>Provedor: <strong>{{ $searchProvider->label() }}</strong>
            @if($searchProvider->isConfigured())
                <span class="badge text-bg-success">configurado</span>
            @else
                <span class="badge text-bg-secondary">não configurado</span>
            @endif
        </p>
        <ul class="small text-secondary mb-0">@foreach($searchProvider->configurationHelp() as $help)<li>{{ $help }}</li>@endforeach</ul>
    </div></div>

<div class="card shadow-sm border-0 mb-3"><div class="card-header bg-white"><strong>Inteligência artificial</strong></div>
    <div class="card-body">
        <p>Provedor: <strong>{{ $aiProvider->label() }}</strong>
            @if($aiProvider->isConfigured())
                <span class="badge text-bg-success">configurado</span>
            @else
                <span class="badge text-bg-secondary">não configurado</span>
            @endif
        </p>
        <ul class="small text-secondary mb-0">@foreach($aiProvider->configurationHelp() as $help)<li>{{ $help }}</li>@endforeach</ul>
    </div></div>

<div class="card shadow-sm border-0"><div class="card-header bg-white"><strong>Canais de comunicação</strong></div>
    <div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>Canal</th><th>Status</th><th>O que falta para ativar</th></tr></thead>
        <tbody>
        @foreach($channels as $key => $adapter)
            <tr><td>{{ $adapter->label() }}</td>
                <td>@if($adapter->isConfigured())<span class="badge text-bg-success">configurado</span>@else<span class="badge text-bg-secondary">não configurado</span>@endif</td>
                <td class="small text-secondary">{{ implode(' ', $adapter->configurationHelp()) }}</td></tr>
        @endforeach
        </tbody></table></div></div>
@endsection
