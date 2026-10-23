@extends('layouts.app')
@section('title', 'Novo usuário')
@section('content')
<div class="row justify-content-center"><div class="col-lg-6">
    <div class="card shadow-sm border-0"><div class="card-body">
        <form method="POST" action="{{ route('users.store') }}">@csrf
            @include('settings.users._form', ['user' => null])
            <button class="btn btn-primary">Criar usuário</button>
        </form>
    </div></div>
</div></div>
@endsection
