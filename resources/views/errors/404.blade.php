@php
    $isCentral = in_array(request()->getHost(), config('tenancy.central_domains', []), true);
@endphp

@include('errors.http', [
    'code' => '404',
    'title' => 'Página no encontrada',
    'message' => 'La página que buscás no existe o ya no está disponible.',
    'actionUrl' => $isCentral ? url('/') : null,
    'actionLabel' => $isCentral ? 'Ir al inicio' : null,
])
