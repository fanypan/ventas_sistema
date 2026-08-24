@include('errors.http', [
    'code' => '419',
    'title' => 'Sesión expirada',
    'message' => 'La sesión venció por inactividad. Recargá la página e intentá de nuevo.',
    'actionUrl' => url()->current(),
    'actionLabel' => 'Recargar',
])
