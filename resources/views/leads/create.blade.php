@extends('layouts.app')
@section('title', 'Novo lead')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><strong>Cadastro manual de lead</strong>
                <span class="small text-secondary ms-2">dados reais e verificáveis; nada de empresa inventada</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('leads.store') }}" data-disable-on-submit>
                    @csrf
                    @include('leads._form_fields', ['lead' => null])
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Salvar e qualificar</button>
                        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
