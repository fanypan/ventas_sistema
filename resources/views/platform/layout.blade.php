<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Plataforma') — {{ config('saas.brand') }}</title>
    <link rel="stylesheet" href="{{ asset('template/admin/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/admin/dist/css/adminlte.min.css') }}">
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
        <div class="container">
            <a href="{{ route('platform.dashboard') }}" class="navbar-brand font-weight-bold">{{ config('saas.brand') }}</a>
            @auth('platform')
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="{{ route('platform.dashboard') }}">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('platform.tenants.index') }}">Clientes</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('platform.tenants.create') }}">Alta</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('platform.plans.index') }}">Planes</a></li>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <form method="POST" action="{{ route('platform.logout') }}">
                            @csrf
                            <button class="btn btn-link nav-link">Salir</button>
                        </form>
                    </li>
                </ul>
            @endauth
        </div>
    </nav>
    <div class="content-wrapper">
        <div class="content py-4">
            <div class="container">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('template/admin/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('template/admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('template/admin/dist/js/adminlte.min.js') }}"></script>
</body>
</html>
