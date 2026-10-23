<div class="row g-3">
    <div class="col-md-8"><label class="form-label">Nome <span class="text-danger">*</span></label>
        <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $company?->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4"><label class="form-label">Nome fantasia</label>
        <input name="trade_name" class="form-control" value="{{ old('trade_name', $company?->trade_name) }}"></div>
    <div class="col-md-4"><label class="form-label">Cidade <span class="text-danger">*</span></label>
        <input list="cities" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $company?->city) }}" required>
        <datalist id="cities">@foreach($cities as $c)<option value="{{ $c }}">@endforeach</datalist></div>
    <div class="col-md-2"><label class="form-label">UF</label>
        <input name="state" maxlength="2" class="form-control text-uppercase" value="{{ old('state', $company?->state ?? 'PE') }}"></div>
    <div class="col-md-6"><label class="form-label">Segmento</label>
        <select name="segment" class="form-select">
            @foreach($segments as $s)<option @selected(old('segment', $company?->segment) === $s)>{{ $s }}</option>@endforeach
        </select></div>
    <div class="col-md-6"><label class="form-label">Site</label>
        <input type="url" name="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $company?->website) }}">
        @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6"><label class="form-label">Place ID (Google)</label>
        <input name="place_id" class="form-control" value="{{ old('place_id', $company?->place_id) }}"></div>
    <div class="col-md-6"><label class="form-label">Base legal</label>
        <select name="legal_basis" class="form-select">
            @foreach($legalBases as $b)<option @selected(old('legal_basis', $company?->legal_basis ?? 'legitimo_interesse') === $b)>{{ $b }}</option>@endforeach
        </select></div>
</div>
<hr>
