<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Reporte')</title>
    @include('admin.reports.partials.styles')
    @stack('styles')
</head>
<body>
    @include('admin.reports.partials.branding')
    @yield('content')
    @include('admin.reports.partials.footer')
</body>
</html>
