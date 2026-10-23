<div class="mb-3"><label class="form-label">Código</label>
    <input name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $service?->code) }}" required>
    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label class="form-label">Nome</label>
    <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $service?->name) }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label class="form-label">Descrição</label>
    <textarea name="description" rows="2" class="form-control">{{ old('description', $service?->description) }}</textarea></div>
<div class="mb-3"><label class="form-label">Preço (R$)</label>
    <input name="price" type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $service?->price) }}" required>
    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="form-check mb-3">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $service?->is_active ?? true))>
    <label for="is_active" class="form-check-label">Ativo</label>
</div>
