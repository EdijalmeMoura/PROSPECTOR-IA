@extends('layouts.app')
@section('title', 'Lead #' . $lead->id)
@section('content')
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('leads.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    <a href="{{ route('leads.edit', $lead) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Editar</a>
    <a href="{{ route('leads.export-show', $lead) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-download me-1"></i>CSV</a>
    <a href="{{ route('proposals.create', ['lead' => $lead->id]) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-file-earmark-text me-1"></i>Nova proposta</a>
</div>

@if($lead->is_demo)
    <div class="alert alert-info"><i class="bi bi-info-circle me-1"></i>Registro de demonstração: dados fictícios, separados dos leads reais.</div>
@endif

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white d-flex justify-content-between">
                <strong>{{ $lead->company->name }}</strong>
                @include('partials.score-badge', ['score' => $lead->score])
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-3">Cidade/UF</dt><dd class="col-sm-9">{{ $lead->company->city }}/{{ $lead->company->state }}</dd>
                    <dt class="col-sm-3">Segmento</dt><dd class="col-sm-9">{{ $lead->company->segment ?: '—' }}</dd>
                    <dt class="col-sm-3">Site</dt><dd class="col-sm-9">@if($lead->company->website)<a href="{{ $lead->company->website }}" target="_blank" rel="noopener">{{ $lead->company->website }}</a>@else — @endif</dd>
                    <dt class="col-sm-3">Serviço recomendado</dt><dd class="col-sm-9">{{ $lead->service?->name ?? '—' }}</dd>
                    <dt class="col-sm-3">Verificado em</dt><dd class="col-sm-9">{{ optional($lead->last_verified_at)->format('d/m/Y H:i') }}</dd>
                    <dt class="col-sm-3">Base legal</dt><dd class="col-sm-9">{{ $lead->company->legal_basis }} · {{ $lead->company->processing_purpose }}</dd>
                </dl>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white"><strong>Pontuação explicável</strong></div>
            <div class="card-body">
                @if($lead->score_breakdown)
                    @foreach($lead->score_breakdown as $key => $criterion)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small">
                                <span class="text-capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                <strong>{{ $criterion['points'] }}/{{ $criterion['max'] }}</strong>
                            </div>
                            <div class="progress" style="height: 6px;"><div class="progress-bar" style="width: {{ ($criterion['points'] / max(1, $criterion['max'])) * 100 }}%"></div></div>
                            <p class="small text-secondary mb-0">{{ $criterion['reason'] }}</p>
                        </div>
                    @endforeach
                @else
                    <p class="text-secondary small mb-0">Sem evidências suficientes para pontuar.</p>
                @endif
                <hr>
                <p class="small mb-0"><strong>Qualificação:</strong> {{ $lead->qualification_status->label() }} · <strong>Prioridade:</strong> {{ $lead->priority->label() }}</p>
                <p class="small text-secondary mb-0">{{ $lead->score_justification }}</p>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white"><strong>Evidências verificáveis</strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Tipo</th><th>Descrição</th><th>Fonte</th><th>Confiabilidade</th><th>Observada em</th></tr></thead>
                    <tbody>
                    @forelse($lead->evidence as $evidence)
                        <tr>
                            <td><span class="badge text-bg-light border">{{ str_replace('_',' ',$evidence->type) }}</span></td>
                            <td class="small">{{ $evidence->description }}</td>
                            <td class="small">@if($evidence->source_url)<a href="{{ $evidence->source_url }}" target="_blank" rel="noopener">abrir</a>@else — @endif</td>
                            <td class="small">{{ $evidence->reliability->label() }}</td>
                            <td class="small">{{ optional($evidence->observed_at)->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-3">Nenhuma evidência registrada.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white"><strong>Canais comerciais</strong></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Canal</th><th>Valor</th><th>Permissão</th><th>Verificado</th></tr></thead>
                    <tbody>
                    @forelse($lead->contacts as $contact)
                        <tr><td>{{ $contact->channel }}</td><td>{{ $contact->value }}</td>
                            <td><span class="badge @if($contact->permission_status->value==='permitida') text-bg-success @elseif($contact->permission_status->value==='negada') text-bg-danger @else text-bg-secondary @endif">{{ $contact->permission_status->label() }}</span></td>
                            <td class="small">{{ optional($contact->verified_at)->format('d/m/Y') }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-secondary py-3">Sem canais cadastrados.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white"><strong>Etapa do funil</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('leads.stage', $lead) }}" class="d-flex gap-2">
                    @csrf @method('PATCH')
                    <select name="stage" class="form-select form-select-sm">
                        @foreach($stages as $stage)
                            <option value="{{ $stage->value }}" @selected($lead->stage === $stage)>{{ $stage->label() }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-primary">Mover</button>
                </form>
                <p class="small text-secondary mt-2 mb-0">Etapa atual: <strong>{{ $lead->stage->label() }}</strong></p>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white"><strong>Revisão manual</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('leads.review', $lead) }}">
                    @csrf @method('PATCH')
                    <select name="decision" class="form-select form-select-sm mb-2">
                        <option value="qualificado">Confirmar qualificado</option>
                        <option value="precisa_evidencias">Precisa de mais evidências</option>
                        <option value="rejeitado">Rejeitar lead</option>
                    </select>
                    <textarea name="notes" rows="2" class="form-control form-control-sm mb-2" placeholder="Justificativa (registrada como nota)"></textarea>
                    <button class="btn btn-sm btn-outline-dark w-100">Registrar revisão</button>
                </form>
                @if($lead->reviewed_at)
                    <p class="small text-secondary mt-2 mb-0">Revisado por {{ $lead->reviewer?->name }} em {{ $lead->reviewed_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white"><strong>Inteligência artificial</strong></div>
            <div class="card-body">
                <p class="small text-secondary">A IA usa só evidências verificadas e sinaliza incertezas. Sem credenciais, a revisão manual continua.</p>
                @foreach(\App\Enums\AiKind::cases() as $kind)
                    <form method="POST" action="{{ route('leads.ai.generate', $lead) }}" class="d-inline">
                        @csrf <input type="hidden" name="kind" value="{{ $kind->value }}">
                        <button class="btn btn-sm btn-outline-primary mb-1">{{ $kind->label() }}</button>
                    </form>
                @endforeach
                @php($gens = \App\Models\AiGeneration::where('lead_id', $lead->id)->latest()->take(5)->get())
                @forelse($gens as $gen)
                    <hr><p class="small mb-1"><strong>{{ $gen->kind->label() }}</strong> · {{ $gen->status->label() }} <span class="text-secondary">({{ $gen->provider }})</span></p>
                    @if($gen->structured_output)<pre class="small bg-body-tertiary p-2 mb-1" style="white-space: pre-wrap;">{{ json_encode($gen->structured_output, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) }}</pre>@endif
                    @if($gen->error_message)<p class="small text-warning mb-1">{{ $gen->error_message }}</p>@endif
                    @if($gen->uncertainties)<p class="small text-secondary mb-0">Incertezas: {{ implode('; ', (array) $gen->uncertainties) }}</p>@endif
                @empty
                    <p class="small text-secondary mb-0 mt-2">Nenhuma geração ainda.</p>
                @endforelse
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white"><strong>Nova mensagem (vai para aprovação)</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('approvals.store', $lead) }}">
                    @csrf
                    <select name="channel" class="form-select form-select-sm mb-2" required>
                        @foreach(\App\Enums\ChannelType::cases() as $channel)
                            <option value="{{ $channel->value }}">{{ $channel->label() }}</option>
                        @endforeach
                    </select>
                    <input name="recipient" class="form-control form-control-sm mb-2" placeholder="Destinatário (e-mail/telefone/handle)" required>
                    <textarea name="full_text" rows="4" class="form-control form-control-sm mb-2" minlength="20" placeholder="Texto integral da mensagem" required></textarea>
                    <button class="btn btn-sm btn-warning w-100"><i class="bi bi-check2-square me-1"></i>Enviar para aprovação humana</button>
                </form>
                <p class="small text-secondary mt-2 mb-0">Nenhuma mensagem é enviada automaticamente. Toda edição cria uma nova versão.</p>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white"><strong>Notas e interações</strong></div>
            <div class="card-body">
                @foreach($lead->notes->take(5) as $note)
                    <p class="small mb-2"><span class="badge text-bg-light border">{{ $note->interaction_type }}</span>
                        {{ $note->body }}<br><span class="text-secondary">{{ $note->user->name }} · {{ $note->created_at->format('d/m/Y H:i') }}</span></p>
                @endforeach
                <form method="POST" action="{{ route('leads.notes.store', $lead) }}">
                    @csrf
                    <select name="interaction_type" class="form-select form-select-sm mb-2">
                        @foreach(['nota','ligacao','email','reuniao','whatsapp'] as $type)<option>{{ $type }}</option>@endforeach
                    </select>
                    <textarea name="body" rows="2" class="form-control form-control-sm mb-2" required></textarea>
                    <button class="btn btn-sm btn-outline-primary w-100">Adicionar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
