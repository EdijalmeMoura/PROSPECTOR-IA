@extends('layouts.app')
@section('title', 'Importar CSV')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white"><strong>Importação de empresas/leads via CSV</strong></div>
        <div class="card-body">
            <p class="small text-secondary">Colunas obrigatórias: <code>company_name</code>, <code>city</code>. Opcionais:
                <code>trade_name, segment, state, website, place_id, contact_channel, contact_value, contact_permission,
                evidence_type, evidence_description, is_demo</code>. Separador vírgula ou ponto e vírgula, codificação UTF-8.</p>
            <a href="{{ route('leads.index') }}" class="btn btn-sm btn-outline-secondary mb-3"
               onclick="event.preventDefault(); document.getElementById('template-form').submit();">
                <i class="bi bi-download me-1"></i>Baixar modelo CSV
            </a>
            <form id="template-form" method="GET" action="{{ route('leads.export') }}" class="d-none"></form>
            <form method="POST" action="{{ route('csv.preview') }}" enctype="multipart/form-data" data-disable-on-submit>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="file">Arquivo CSV (máx. 4 MB)</label>
                    <input type="file" name="file" id="file" accept=".csv,.txt" class="form-control @error('file') is-invalid @enderror" required>
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-primary"><i class="bi bi-eye me-1"></i>Validar e visualizar prévia</button>
            </form>
        </div>
    </div>
</div></div>
@endsection
