<?php
$conexion = new mysqli("127.0.0.1", "root", "", "TESTDELATEL1", 3306);

if ($conexion->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => true, 'mensaje' => 'Error de conexión con la base de datos']));
}

$conexion->set_charset("utf8mb4");
