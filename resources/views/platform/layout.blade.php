<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Plataforma') — {{ config('saas.brand') }}</title>
    <link rel="icon" href="{{ asset('brand/favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('brand/favicon-128.png') }}" type="image/png" sizes="128x128">
    <link rel="apple-touch-icon" href="{{ asset('brand/apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('template/admin/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/admin/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/platform.css') }}?v=20260901a">
</head>
<body class="hold-transition layout-top-nav platform-app @auth('platform') layout-navbar-fixed @else platform-auth @endauth">
<a class="skip-link" href="#contenido">Saltar al contenido</a>
<div class="wrapper">
    <nav class="main-header navbar navbar-expand-md navbar-dark">
        <div class="platform-wrap d-flex flex-wrap align-items-center w-100">
            <a href="{{ auth('platform')->check() ? route('platform.dashboard') : route('platform.login') }}" class="navbar-brand">{{ config('saas.brand') }}</a>
            @auth('platform')
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#platformNav" aria-controls="platformNav" aria-expanded="false" aria-label="Abrir menú">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse flex-grow-1" id="platformNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('platform.dashboard') ? 'active' : '' }}" href="{{ route('platform.dashboard') }}">Inicio</a>
                        </li>
                        @if (platform_can('tenants.view'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('platform.tenants.*') && ! request()->routeIs('platform.tenants.create') ? 'active' : '' }}" href="{{ route('platform.tenants.index') }}">Clientes</a>
                        </li>
                        @endif
                        @if (platform_can('tenants.create'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('platform.tenants.create') ? 'active' : '' }}" href="{{ route('platform.tenants.create') }}">Alta</a>
                        </li>
                        @endif
                        @if (platform_can('plans.view'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('platform.plans.*') ? 'active' : '' }}" href="{{ route('platform.plans.index') }}">Planes</a>
                        </li>
                        @endif
                        @if (platform_can('users.view') || platform_can('roles.view'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('platform.users.*') || request()->routeIs('platform.roles.*') ? 'active' : '' }}" href="{{ platform_can('users.view') ? route('platform.users.index') : route('platform.roles.index') }}">Equipo</a>
                        </li>
                        @endif
                        @if (config('observability.horizon_enabled') && auth('platform')->user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url(config('horizon.path')) }}">Colas</a>
                            </li>
                        @endif
                    </ul>
                    <ul class="navbar-nav ml-auto align-items-md-center">
                        <li class="nav-item d-none d-md-block">
                            <span class="navbar-staff">{{ auth('platform')->user()->name }}</span>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('platform.logout') }}">
                                @csrf
                                <button class="btn-logout" type="submit">Salir</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endauth
        </div>
    </nav>
    <div class="content-wrapper">
        <div class="content">
            <div class="platform-shell" id="contenido">
                @if (session('success'))
                    <div class="alert alert-success" role="status">{{ session('success') }}</div>
                @endif
                @if ($errors->any() && ! request()->routeIs('platform.login'))
                    <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('template/admin/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('template/admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('template/admin/dist/js/adminlte.min.js') }}"></script>
@include('sweetalert::alert')
@stack('scripts')
</body>
</html>
