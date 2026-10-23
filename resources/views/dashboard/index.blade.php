@extends('layouts.app')
@section('title', 'Painel')
@section('content')
<div class="row g-3 mb-3">
    @php
        $cards = [
            ['Pesquisas iniciadas', $metrics['searches_started'], 'search', 'secondary'],
            ['Pesquisas concluídas', $metrics['searches_completed'], 'check-circle', 'success'],
            ['Empresas (reais)', $metrics['companies_found'], 'building', 'primary'],
            ['Leads qualificados', $metrics['leads_qualified'], 'award', 'primary'],
            ['Aprovações pendentes', $metrics['approvals_pending'], 'hourglass-split', 'warning'],
            ['Mensagens enviadas', $metrics['messages']['sent'], 'send', 'dark'],
            ['Propostas enviadas', $metrics['proposals_sent'], 'file-earmark-text', 'info'],
            ['Negócios ganhos', $metrics['deals_won'], 'trophy', 'success'],
        ];
    @endphp
    @foreach($cards as [$label, $value, $icon, $color])
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-secondary small">{{ $label }}</div>
                            <div class="fs-4 fw-bold">{{ $value }}</div>
                        </div>
                        <i class="bi bi-{{ $icon }} fs-3 text-{{ $color }}"></i>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><strong>Valores comerciais</strong></div>
            <div class="card-body">
                <p class="small text-secondary mb-2">Previsão não é receita: propostas e valores previstos ficam separados do que efetivamente entrou.</p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between"><span>Valor previsto (negócios abertos)</span><strong>R$ {{ number_format($metrics['expected_amount'], 2, ',', '.') }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>Receita efetivamente recebida</span><strong class="text-success">R$ {{ number_format($metrics['received_amount'], 2, ',', '.') }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>Negócios perdidos</span><strong class="text-danger">{{ $metrics['deals_lost'] }}</strong></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><strong>Mensagens (eventos confirmados pelo provedor)</strong></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between"><span>Aprovadas</span><span>{{ $metrics['messages']['approved'] }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span>Enviadas</span><span>{{ $metrics['messages']['sent'] }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span>Entregues</span><span>{{ $metrics['messages']['delivered'] }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span>Respondidas</span><span>{{ $metrics['messages']['replied'] }}</span></li>
                    <li class="list-group-item d-flex justify-content-between"><span>Falhas</span><span class="text-danger">{{ $metrics['messages']['failed'] }}</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100"><div class="card-header bg-white"><strong>Leads por cidade</strong></div>
            <div class="card-body">@include('partials.metric-list', ['rows' => $metrics['by_city']])</div></div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100"><div class="card-header bg-white"><strong>Leads por prioridade</strong></div>
            <div class="card-body">@include('partials.metric-list', ['rows' => $metrics['by_priority']])</div></div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100"><div class="card-header bg-white"><strong>Leads por etapa</strong></div>
            <div class="card-body">@include('partials.metric-list', ['rows' => $metrics['by_stage']])</div></div>
    </div>
</div>
@endsection
