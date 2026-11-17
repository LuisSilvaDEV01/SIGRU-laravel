<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIGRU — {{ $title ?? 'Sistema' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.10.0/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
@php
    $nav = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'ti-layout-dashboard', 'match' => ['dashboard']],
        ['route' => 'fila.index', 'label' => 'Fila virtual', 'icon' => 'ti-list-numbers', 'match' => ['fila.*', 'refeicao.*']],
        ['route' => 'carteiras.index', 'label' => 'Carteiras', 'icon' => 'ti-wallet', 'match' => ['carteiras.*']],
        ['route' => 'estoque.index', 'label' => 'Estoque', 'icon' => 'ti-package', 'match' => ['estoque.*']],
    ];
@endphp
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="ti ti-building-community" aria-hidden="true"></i>
            <div>
                <span class="brand-name">SIGRU</span>
                <span class="brand-sub">RU · Unimontes</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}"
                   class="nav-item {{ request()->routeIs(...$item['match']) ? 'active' : '' }}">
                    <i class="ti {{ $item['icon'] }}" aria-hidden="true"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
        <div class="sidebar-footer">Laravel · Fase 3</div>
    </aside>
    <div class="main-wrapper">
        <header class="topbar">
            <h1 class="topbar-title">{{ $title ?? 'SIGRU' }}</h1>
            <span class="topbar-badge">
                <i class="ti ti-calendar" aria-hidden="true"></i>
                {{ now()->format('d/m/Y') }}
            </span>
        </header>
        <main class="content">
            @if (session('success'))
                <div class="alert alert-success"><i class="ti ti-check"></i> {{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error"><i class="ti ti-alert-circle"></i> {{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
