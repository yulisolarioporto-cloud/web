<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'mensaje' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/php/conexion.php';

$data = json_decode(file_get_contents("php://input"), true);
$user = trim($data['usuario'] ?? '');
$pass = trim($data['password'] ?? '');

if (!$user || !$pass) {
    echo json_encode(['error' => true, 'mensaje' => 'Campos requeridos']);
    exit;
}

$stmt = $conexion->prepare("
    SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.password,
           r.id_rol, r.nombre AS rol_nombre, r.nivel AS rol_nivel
    FROM tb_usuarios u
    INNER JOIN tb_roles r ON r.id_rol = u.id_rol
    WHERE u.usuario = ? AND u.estado = 'ACTIVO'
    LIMIT 1
");
$stmt->bind_param("s", $user);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($res && $pass === $res['password']) {
    session_regenerate_id(true);
    $_SESSION['id_usuario']     = $res['id_usuario'];
    $_SESSION['usuario_nombre'] = $res['nombre'] . ' ' . $res['apellido'];
    $_SESSION['usuario_email']  = $res['email'];
    $_SESSION['id_rol']         = $res['id_rol'];
    $_SESSION['rol']            = $res['rol_nombre'];
    $_SESSION['rol_nivel']      = $res['rol_nivel'];

    $stmtP = $conexion->prepare("
        SELECT modulo, puede_ver, puede_crear, puede_editar, puede_eliminar
        FROM tb_permisos WHERE id_rol = ?
    ");
    $stmtP->bind_param("i", $res['id_rol']);
    $stmtP->execute();
    $resP = $stmtP->get_result();
    $permisos = [];
    while ($p = $resP->fetch_assoc()) {
        $permisos[$p['modulo']] = [
            'ver'      => (bool)$p['puede_ver'],
            'crear'    => (bool)$p['puede_crear'],
            'editar'   => (bool)$p['puede_editar'],
            'eliminar' => (bool)$p['puede_eliminar'],
        ];
    }
    $stmtP->close();
    $_SESSION['permisos'] = $permisos;

    echo json_encode(['error' => false, 'mensaje' => 'Bienvenido, ' . $res['nombre'], 'rol' => $res['rol_nombre']]);
} else {
    echo json_encode(['error' => true, 'mensaje' => 'Usuario o contraseña incorrectos']);
}
