@extends('layouts.app')
@section('title', 'Nova empresa')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card shadow-sm border-0"><div class="card-header bg-white"><strong>Cadastro manual de empresa</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('companies.store') }}">
                @csrf
                @include('companies._form', ['company' => null])
                <button class="btn btn-primary">Salvar empresa</button>
                <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div></div>
</div></div>
@endsection
