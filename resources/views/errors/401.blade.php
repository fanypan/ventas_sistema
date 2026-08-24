@php
    $isCentral = in_array(request()->getHost(), config('tenancy.central_domains', []), true);
    $actionUrl = $isCentral
        ? url('/'.config('saas.platform_path').'/login')
        : (Route::has('login') ? route('login') : null);
@endphp

@include('errors.http', [
    'code' => '401',
    'title' => 'No autorizado',
    'message' => 'Tenés que iniciar sesión para continuar.',
    'actionUrl' => $actionUrl,
    'actionLabel' => $actionUrl ? 'Iniciar sesión' : null,
])
