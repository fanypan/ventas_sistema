<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cuenta en pausa</title>
    <link rel="stylesheet" href="{{ asset('template/admin/dist/css/adminlte.min.css') }}">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">La cuenta de <strong>{{ $tenant->name }}</strong> está en pausa.</p>
            <p>Contactá a AranduTech para regularizar el pago (transferencia o efectivo). Después de 7 días de gracia el POS se pausa.</p>
            <a class="btn btn-primary btn-block" href="https://wa.me/{{ config('saas.whatsapp') }}">WhatsApp</a>
        </div>
    </div>
</div>
</body>
</html>
