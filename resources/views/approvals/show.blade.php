@extends('layouts.app')
@section('title', 'Aprovação #' . $approval->id)
@section('content')
<a href="{{ route('approvals.index') }}" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left"></i> Voltar</a>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white d-flex justify-content-between">
                <strong>Mensagem — versão {{ $approval->content_version }}</strong>
                <span class="badge @if($approval->status->value==='aprovada') text-bg-success @elseif($approval->status->value==='pendente') text-bg-warning @else text-bg-secondary @endif">{{ $approval->status->label() }}</span>
            </div>
            <div class="card-body">
                <p class="small mb-2"><strong>Empresa:</strong> {{ $approval->lead->company->name }} ·
                    <strong>Canal:</strong> {{ $approval->channel->label() }} ·
                    <strong>Destinatário:</strong> {{ $approval->recipient }}</p>
                <div class="border rounded p-3 bg-body-tertiary" style="white-space: pre-wrap;">{{ $approval->full_text }}</div>
                <p class="small text-secondary mt-2 mb-0"><i class="bi bi-shield-lock me-1"></i>Hash de conteúdo: <code>{{ substr($approval->content_hash, 0, 16) }}…</code>. Qualquer edição invalida esta aprovação e cria nova versão.</p>
            </div>
        </div>
        @if($approval->status->value === 'pendente')
            <div class="card shadow-sm border-0 mb-3"><div class="card-body d-flex flex-wrap gap-2">
                <form method="POST" action="{{ route('approvals.approve', $approval) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Aprovar</button>
                </form>
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="bi bi-x-lg me-1"></i>Rejeitar</button>
                <form method="POST" action="{{ route('approvals.cancel', $approval) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-outline-secondary">Cancelar</button>
                </form>
            </div></div>
        @endif
        @if($approval->status->value === 'aprovada')
            <div class="card shadow-sm border-0 mb-3"><div class="card-body">
                <p class="small text-secondary mb-2">A aprovação está vinculada a este destinatário, canal e texto exatos. O envio ainda exige ação explícita e passa por novas verificações (opt-out, limite diário, estado do canal).</p>
                <form method="POST" action="{{ route('approvals.send', $approval) }}" data-confirm="Confirma o envio externo desta mensagem?">
                    @csrf
                    <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Enviar agora</button>
                </form>
            </div></div>
        @endif
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white"><strong>Evidências que justificam a abordagem</strong></div>
            <ul class="list-group list-group-flush">
                @forelse(($approval->evidence_snapshot ?? []) as $evidence)
                    <li class="list-group-item small">
                        <strong class="text-capitalize">{{ str_replace('_', ' ', $evidence['type']) }}</strong> — {{ $evidence['description'] }}
                        @if(!empty($evidence['source_url']))<a href="{{ $evidence['source_url'] }}" target="_blank" rel="noopener" class="ms-1">fonte</a>@endif
                    </li>
                @empty
                    <li class="list-group-item small text-secondary">Nenhuma evidência vinculada no momento da versão.</li>
                @endforelse
            </ul>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><strong>Condição do canal</strong></div>
            <div class="card-body small">
                <p>Permissão registrada: <strong>{{ $approval->channel_permission->label() }}</strong></p>
                <p class="text-secondary mb-0">A aprovação humana não substitui consentimento nem requisitos legais/contratuais do canal.</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="{{ route('approvals.reject', $approval) }}">
            @csrf @method('PATCH')
            <div class="modal-header"><h5 class="modal-title">Motivo da rejeição</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><textarea name="decision_note" rows="3" class="form-control" required minlength="3"></textarea></div>
            <div class="modal-footer"><button class="btn btn-danger">Confirmar rejeição</button></div>
        </form>
    </div></div>
</div>
@endsection
