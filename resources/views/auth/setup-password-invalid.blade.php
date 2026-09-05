<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Enlace vencido</title>
    <link rel="stylesheet" href="{{ asset('template/admin/dist/css/adminlte.min.css') }}">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Este enlace ya no vale.</p>
            <p>Pedile a AranduTech que te reenvíe la invitación, o ingresá si ya definiste tu contraseña.</p>
            <p class="mb-0"><a href="{{ route('login') }}">Ir al ingreso</a></p>
        </div>
    </div>
</div>
</body>
</html>
