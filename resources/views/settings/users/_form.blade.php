<div class="mb-3"><label class="form-label">Nome</label>
    <input name="name" class="form-control" value="{{ old('name', $user?->name) }}" required></div>
<div class="mb-3"><label class="form-label">E-mail</label>
    <input type="email" name="email" class="form-control" value="{{ old('email', $user?->email) }}" required></div>
<div class="mb-3"><label class="form-label">Perfil</label>
    <select name="role" class="form-select">
        @foreach($roles as $role)<option value="{{ $role->value }}" @selected(old('role', $user?->role?->value) === $role->value)>{{ $role->label() }}</option>@endforeach
    </select></div>
<div class="mb-3"><label class="form-label">Senha {{ $user ? '(deixe em branco para manter)' : '' }}</label>
    <input type="password" name="password" class="form-control" {{ $user ? '' : 'required' }}></div>
<div class="mb-3"><label class="form-label">Confirmar senha</label>
    <input type="password" name="password_confirmation" class="form-control" {{ $user ? '' : 'required' }}></div>
<div class="form-check mb-3">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="u-active" @checked(old('is_active', $user?->is_active ?? true))>
    <label for="u-active" class="form-check-label">Conta ativa</label>
</div>
