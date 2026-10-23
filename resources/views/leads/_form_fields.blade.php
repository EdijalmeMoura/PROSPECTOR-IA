<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label" for="company_name">Nome da empresa <span class="text-danger">*</span></label>
        <input type="text" name="company_name" id="company_name" class="form-control @error('company_name') is-invalid @enderror"
               value="{{ old('company_name', $lead?->company?->name) }}" required>
        @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="trade_name">Nome fantasia</label>
        <input type="text" name="trade_name" id="trade_name" class="form-control" value="{{ old('trade_name', $lead?->company?->trade_name) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="city">Cidade <span class="text-danger">*</span></label>
        <input list="cities-list" name="city" id="city" class="form-control @error('city') is-invalid @enderror"
               value="{{ old('city', $lead?->company?->city) }}" required>
        <datalist id="cities-list">
            @foreach(config('prospector.region.cities') as $city)<option value="{{ $city }}">@endforeach
        </datalist>
        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2">
        <label class="form-label" for="state">UF</label>
        <input type="text" name="state" id="state" maxlength="2" class="form-control text-uppercase" value="{{ old('state', $lead?->company?->state ?? 'PE') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="segment">Segmento</label>
        <select name="segment" id="segment" class="form-select">
            @foreach(($segments ?? config('prospector.segments')) as $segment)
                <option @selected(old('segment', $lead?->company?->segment) === $segment)>{{ $segment }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="website">Site</label>
        <input type="url" name="website" id="website" class="form-control @error('website') is-invalid @enderror"
               placeholder="https://exemplo.com.br" value="{{ old('website', $lead?->company?->website) }}">
        @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="place_id">Place ID (Google, quando houver)</label>
        <input type="text" name="place_id" id="place_id" class="form-control" value="{{ old('place_id', $lead?->company?->place_id) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="service_id">Serviço a oferecer</label>
        <select name="service_id" id="service_id" class="form-select">
            <option value="">— selecione —</option>
            @foreach($services as $service)
                <option value="{{ $service->id }}" @selected(old('service_id', $lead?->service_id) == $service->id)>
                    {{ $service->name }} — {{ $service->formattedPrice() }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<hr class="my-4">
<h2 class="h6">Canal comercial (opcional)</h2>
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label" for="contact_channel">Canal</label>
        <select name="contact_channel" id="contact_channel" class="form-select">
            @foreach(['email' => 'E-mail', 'whatsapp' => 'WhatsApp', 'instagram' => 'Instagram', 'telefone' => 'Telefone', 'site' => 'Site'] as $val => $label)
                <option value="{{ $val }}" @selected(old('contact_channel') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-5">
        <label class="form-label" for="contact_value">Contato</label>
        <input type="text" name="contact_value" id="contact_value" class="form-control @error('contact_value') is-invalid @enderror" value="{{ old('contact_value') }}">
        @error('contact_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="contact_permission">Permissão do canal</label>
        <select name="contact_permission" id="contact_permission" class="form-select">
            @foreach(($permissions ?? \App\Enums\ContactPermission::cases()) as $permission)
                <option value="{{ $permission->value }}" @selected(old('contact_permission') === $permission->value)>{{ $permission->label() }}</option>
            @endforeach
        </select>
    </div>
</div>

<hr class="my-4">
<h2 class="h6">Evidência da oportunidade (opcional, verificável)</h2>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label" for="evidence_type">Tipo</label>
        <select name="evidence_type" id="evidence_type" class="form-select">
            @foreach(['site_ausente' => 'Sem site', 'site_desatualizado' => 'Site desatualizado', 'redes_sem_padrao' => 'Redes sem padrão', 'wordpress_lento' => 'WordPress lento', 'wordpress_erro' => 'Erro no WordPress', 'identidade_visual_fraca' => 'Identidade visual fraca', 'observacao_manual' => 'Observação manual'] as $val => $label)
                <option value="{{ $val }}" @selected(old('evidence_type') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-5">
        <label class="form-label" for="evidence_description">Descrição da evidência</label>
        <input type="text" name="evidence_description" id="evidence_description" maxlength="1000" class="form-control" value="{{ old('evidence_description') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="evidence_reliability">Confiabilidade</label>
        <select name="evidence_reliability" id="evidence_reliability" class="form-select">
            <option value="baixa">Baixa</option>
            <option value="media" selected>Média</option>
            <option value="alta">Alta</option>
        </select>
    </div>
</div>
