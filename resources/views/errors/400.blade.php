@include('errors.http', [
    'code' => '400',
    'title' => 'Solicitud inválida',
    'message' => 'La solicitud no se pudo procesar. Revisá los datos e intentá de nuevo.',
])
