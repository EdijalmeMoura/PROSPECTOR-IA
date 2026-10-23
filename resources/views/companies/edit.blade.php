@extends('layouts.app')
@section('title', 'Editar empresa')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card shadow-sm border-0"><div class="card-header bg-white"><strong>Editar empresa</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('companies.update', $company) }}">
                @csrf @method('PUT')
                @include('companies._form', ['company' => $company])
                <button class="btn btn-primary">Salvar</button>
                <a href="{{ route('companies.show', $company) }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div></div>
</div></div>
@endsection
