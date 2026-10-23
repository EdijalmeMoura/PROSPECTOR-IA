@extends('layouts.app')
@section('title', 'Editar serviço')
@section('content')
<div class="row justify-content-center"><div class="col-lg-6">
    <div class="card shadow-sm border-0"><div class="card-body">
        <form method="POST" action="{{ route('services.update', $service) }}">@csrf @method('PUT')
            @include('settings.services._form', ['service' => $service])
            <button class="btn btn-primary">Salvar</button>
            <a href="{{ route('services.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div></div>
</div></div>
@endsection
