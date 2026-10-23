@extends('layouts.app')
@section('title', 'Editar usuário')
@section('content')
<div class="row justify-content-center"><div class="col-lg-6">
    <div class="card shadow-sm border-0"><div class="card-body">
        <form method="POST" action="{{ route('users.update', $user) }}">@csrf @method('PUT')
            @include('settings.users._form', ['user' => $user])
            <button class="btn btn-primary">Salvar</button>
        </form>
    </div></div>
</div></div>
@endsection
