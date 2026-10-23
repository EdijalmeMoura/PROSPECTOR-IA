<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel') — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body>
<div class="wrapper d-flex">
    @include('partials.sidebar')
    <div class="main flex-grow-1 d-flex flex-column min-vh-100">
        @include('partials.topbar')
        <main class="p-3 p-md-4 flex-grow-1 bg-body-tertiary">
            @include('partials.flash')
            @yield('content')
        </main>
        <footer class="text-center text-secondary small py-3 bg-white border-top">
            {{ config('app.name') }} — fuso America/Recife · moeda BRL (R$) · dados fictícios claramente marcados
        </footer>
    </div>
</div>
<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
