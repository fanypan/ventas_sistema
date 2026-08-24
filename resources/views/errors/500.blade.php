@include('errors.http', [
    'code' => '500',
    'title' => 'Error interno',
    'message' => 'Algo salió mal de nuestro lado. Si el problema persiste, contactá al soporte.',
])
