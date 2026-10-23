@extends('layouts.app')
@section('title', 'Nova pesquisa')
@section('content')
@if(! $provider->isConfigured())
    <div class="alert alert-warning">
        <p class="mb-1"><strong><i class="bi bi-plug me-1"></i>Conector não configurado.</strong></p>
        <ul class="mb-1 small">@foreach($provider->configurationHelp() as $help)<li>{{ $help }}</li>@endforeach</ul>
        <p class="small mb-0">Você ainda pode preparar a pesquisa; sem credenciais ela será encerrada sem resultados, sem simular dados. Use o <a href="{{ route('csv.create') }}">CSV</a> ou o <a href="{{ route('leads.create') }}">cadastro manual</a>.</p>
    </div>
@else
    <div class="alert alert-success small"><i class="bi bi-check-circle me-1"></i>Provedor ativo: <strong>{{ $provider->label() }}</strong>. Respeite cotas, cobrança e regras de retenção do provedor.</div>
@endif
<div class="card shadow-sm border-0"><div class="card-header bg-white"><strong>Parâmetros da pesquisa</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('searches.store') }}" data-disable-on-submit>
            @csrf
            <div class="mb-3">
                <label class="form-label">Municípios (região inicial: RMR/PE) <span class="text-danger">*</span></label>
                <div class="row g-2">
                    @foreach($cities as $city)
                        <div class="col-md-4 col-lg-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cities[]" value="{{ $city }}" id="city-{{ $loop->index }}" @checked($city === config('prospector.region.default_city'))>
                                <label class="form-check-label" for="city-{{ $loop->index }}">{{ $city }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('cities')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Segmento</label>
                    <select name="segment" class="form-select">
                        @foreach($segments as $segment)<option>{{ $segment }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Serviço a oferecer</label>
                    <select name="service_id" class="form-select">
                        <option value="">—</option>
                        @foreach($services as $service)<option value="{{ $service->id }}">{{ $service->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Quantidade pretendida de leads</label>
                    <input type="number" name="target_quantity" class="form-control" min="1" max="{{ config('prospector.search.max_target') }}" value="{{ old('target_quantity', $defaultTarget) }}">
                    <p class="small text-secondary mb-0">Meta padrão 50. O total real depende do que a fonte retornar.</p>
                </div>
            </div>
            <hr>
            <button class="btn btn-primary"><i class="bi bi-play-circle me-1"></i>Iniciar pesquisa</button>
            <a href="{{ route('searches.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div></div>
@endsection
