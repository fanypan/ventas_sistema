<?php
$host = 'localhost';
$user = 'root';
$password = '';
$db = 'ventas_bd';

$conection = mysqli_connect($host, $user, $password, $db);

if (!$conection) {
    die("Error en la conexión: " . mysqli_connect_error());
}

function formatCant($cantidad) {
    if ($cantidad == "" || $cantidad == null) return "0";
    return number_format((float)$cantidad, 0, ',', '.');
}
