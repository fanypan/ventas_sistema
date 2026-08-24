<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Error' }} — {{ config('saas.brand') }}</title>
    <link rel="icon" href="{{ asset('brand/favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('brand/favicon-128.png') }}" type="image/png" sizes="128x128">
    <link rel="stylesheet" href="{{ asset('template/admin/dist/css/adminlte.min.css') }}">
    <style>
        .error-code {
            font-size: 4rem;
            font-weight: 700;
            line-height: 1;
            color: #6c757d;
        }

        .error-card {
            max-width: 480px;
        }
    </style>
</head>
<body class="hold-transition login-page bg-light">
<div class="login-box error-card">
    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-body login-card-body text-center py-5">
            <p class="error-code mb-3">{{ $code }}</p>
            <h1 class="h4 mb-3">{{ $title }}</h1>
            <p class="text-muted mb-4">{{ $message }}</p>
            @if (! empty($actionUrl) && ! empty($actionLabel))
                <a href="{{ $actionUrl }}" class="btn btn-primary">{{ $actionLabel }}</a>
            @endif
        </div>
    </div>
    <p class="mt-3 text-center text-muted small mb-0">{{ config('saas.brand') }}</p>
</div>
</body>
</html>
