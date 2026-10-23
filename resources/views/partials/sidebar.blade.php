@php
    $nav = [
        ['route' => 'dashboard', 'icon' => 'speedometer2', 'label' => 'Painel'],
        ['route' => 'leads.index', 'icon' => 'people', 'label' => 'Leads'],
        ['route' => 'companies.index', 'icon' => 'building', 'label' => 'Empresas'],
        ['route' => 'searches.index', 'icon' => 'search', 'label' => 'Pesquisas'],
        ['route' => 'csv.create', 'icon' => 'filetype-csv', 'label' => 'Importar CSV'],
        ['route' => 'approvals.index', 'icon' => 'check2-square', 'label' => 'Aprovações'],
        ['route' => 'proposals.index', 'icon' => 'file-earmark-text', 'label' => 'Propostas'],
        ['route' => 'deals.index', 'icon' => 'trophy', 'label' => 'Negócios'],
        ['route' => 'opt-outs.index', 'icon' => 'bell-slash', 'label' => 'Opt-outs'],
    ];
    $adminNav = [
        ['route' => 'services.index', 'icon' => 'tags', 'label' => 'Serviços e preços'],
        ['route' => 'users.index', 'icon' => 'person-badge', 'label' => 'Usuários'],
        ['route' => 'connectors.show', 'icon' => 'plug', 'label' => 'Conectores'],
        ['route' => 'audit-logs.index', 'icon' => 'journal-text', 'label' => 'Auditoria'],
    ];
@endphp
<aside class="sidebar bg-dark text-white" aria-label="Navegação principal">
    <div class="px-3 py-3 border-bottom border-secondary">
        <a href="{{ route('dashboard') }}" class="text-white text-decoration-none fw-bold fs-5">
            <i class="bi bi-radar me-1"></i> Prospector IA
        </a>
    </div>
    <ul class="nav nav-pills flex-column px-2 py-2">
        @foreach($nav as $item)
            <li class="nav-item">
                <a class="nav-link text-white @if(request()->routeIs($item['route'])) active bg-primary @endif" href="{{ route($item['route']) }}">
                    <i class="bi bi-{{ $item['icon'] }} me-2"></i>{{ $item['label'] }}
                </a>
            </li>
        @endforeach
        @if(auth()->user()?->isAdmin())
            <li class="nav-item mt-3 text-uppercase small text-secondary px-2">Administração</li>
            @foreach($adminNav as $item)
                <li class="nav-item">
                    <a class="nav-link text-white @if(request()->routeIs($item['route'])) active bg-primary @endif" href="{{ route($item['route']) }}">
                        <i class="bi bi-{{ $item['icon'] }} me-2"></i>{{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        @endif
    </ul>
</aside>
