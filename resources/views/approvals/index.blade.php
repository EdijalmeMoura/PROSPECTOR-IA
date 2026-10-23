@extends('layouts.app')
@section('title', 'Aprovações')
@section('content')
<div class="card shadow-sm border-0"><div class="table-responsive">
    <table class="table table-hover table-compact mb-0">
        <thead class="table-light"><tr><th>#</th><th>Empresa</th><th>Canal</th><th>Destinatário</th><th>Versão</th><th>Status</th><th>Permissão</th><th>Criada em</th><th></th></tr></thead>
        <tbody>
        @forelse($approvals as $approval)
            <tr>
                <td>#{{ $approval->id }}</td>
                <td>{{ $approval->lead?->company?->name }}</td>
                <td>{{ $approval->channel->label() }}</td>
                <td class="small">{{ $approval->recipient }}</td>
                <td>v{{ $approval->content_version }}</td>
                <td><span class="badge @if($approval->status->value==='aprovada') text-bg-success @elseif($approval->status->value==='pendente') text-bg-warning @else text-bg-secondary @endif">{{ $approval->status->label() }}</span></td>
                <td class="small">{{ $approval->channel_permission->label() }}</td>
                <td class="small">{{ $approval->created_at->format('d/m/Y H:i') }}</td>
                <td><a href="{{ route('approvals.show', $approval) }}" class="btn btn-sm btn-outline-primary">Revisar</a></td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-secondary py-4">Nenhuma mensagem aguardando aprovação.</td></tr>
        @endforelse
        </tbody>
    </table>
</div><div class="card-footer">{{ $approvals->links() }}</div></div>
@endsection
