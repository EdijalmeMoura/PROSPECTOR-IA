@extends('layouts.app')
@section('title', 'Serviços e preços')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <p class="text-secondary small mb-0">Catálogo comercial editável. Moeda BRL (R$).</p>
    <a href="{{ route('services.create') }}" class="btn btn-primary btn-sm">Novo serviço</a>
</div>
<div class="card shadow-sm border-0"><div class="table-responsive">
    <table class="table table-compact mb-0">
        <thead class="table-light"><tr><th>Código</th><th>Nome</th><th>Descrição</th><th>Preço</th><th>Ativo</th><th></th></tr></thead>
        <tbody>
        @foreach($services as $service)
            <tr><td><code>{{ $service->code }}</code></td><td>{{ $service->name }}</td>
                <td class="small">{{ $service->description }}</td><td>{{ $service->formattedPrice() }}</td>
                <td>{{ $service->is_active ? 'Sim' : 'Não' }}</td>
                <td><a href="{{ route('services.edit', $service) }}" class="btn btn-sm btn-outline-secondary">Editar</a></td></tr>
        @endforeach
        </tbody>
    </table>
</div><div class="card-footer">{{ $services->links() }}</div></div>
@endsection
