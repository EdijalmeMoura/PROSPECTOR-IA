<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acesso — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body class="bg-body-tertiary">
<div class="container" style="max-width: 420px;">
    <div class="card shadow-sm mt-5">
        <div class="card-body p-4">
            <h1 class="h4 text-center mb-1"><i class="bi bi-radar me-1"></i>{{ config('app.name') }}</h1>
            <p class="text-secondary text-center small mb-4">Prospecção de empresas para serviços de design e web</p>
            <form method="POST" action="{{ route('login.store') }}" autocomplete="off">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required autofocus aria-describedby="email-error">
                    @error('email')<div class="invalid-feedback" id="email-error">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input">
                    <label for="remember" class="form-check-label">Manter sessão</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>
    </div>
    <p class="text-center text-secondary small mt-3">Ambiente restrito a usuários autorizados.</p>
</div>
</body>
</html>
