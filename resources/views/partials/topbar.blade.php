<header class="bg-white border-bottom px-3 px-md-4 py-2 d-flex justify-content-between align-items-center">
    <h1 class="h5 mb-0">@yield('title', 'Painel')</h1>
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }} ({{ auth()->user()->role->label() }})
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><span class="dropdown-item-text small text-secondary">{{ auth()->user()->email }}</span></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-1"></i>Sair</button>
                </form>
            </li>
        </ul>
    </div>
</header>
