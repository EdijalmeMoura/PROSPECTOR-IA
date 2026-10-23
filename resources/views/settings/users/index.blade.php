@extends('layouts.app')
@section('title', 'Usuários')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h2 class="h5 mb-0">Controle de acesso por função</h2>
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">Novo usuário</a>
</div>
<div class="card shadow-sm border-0"><div class="table-responsive">
    <table class="table table-compact mb-0">
        <thead class="table-light"><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Ativo</th><th>Último login</th><th></th></tr></thead>
        <tbody>
        @foreach($users as $user)
            <tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td>
                <td><span class="badge text-bg-dark">{{ $user->role->label() }}</span></td>
                <td>@if($user->is_active)<span class="badge text-bg-success">sim</span>@else<span class="badge text-bg-danger">não</span>@endif</td>
                <td class="small">{{ optional($user->last_login_at)?->format('d/m/Y H:i') ?? '—' }}</td>
                <td class="text-end">
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                    <form method="POST" action="{{ route('users.toggle', $user) }}" class="d-inline">@csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-warning">{{ $user->is_active ? 'Desativar' : 'Ativar' }}</button></form>
                </td></tr>
        @endforeach
        </tbody>
    </table>
</div><div class="card-footer">{{ $users->links() }}</div></div>
@endsection
