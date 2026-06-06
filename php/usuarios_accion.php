<?php
require_once __DIR__ . '/roles/permisos.php';
require_once __DIR__ . '/conexion.php';

// Solo administradores (nivel 1)
if (($_SESSION['rol_nivel'] ?? 99) > 1) {
    header("Location: ../index.php?vista=usuarios&msg=error&detalle=sin_permiso");
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'crear') {
    $nombre   = trim($_POST['nombre']   ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $usuario  = trim($_POST['usuario']  ?? '');
    $password = $_POST['password'] ?? '';
    $id_rol   = (int)($_POST['id_rol']  ?? 0);

    if (!$nombre || !$apellido || !$email || !$usuario || !$password || !$id_rol) {
        header("Location: ../index.php?vista=usuarios&msg=error&detalle=datos_invalidos");
        exit;
    }

    // Verificar que el usuario o email no exista ya
    $check = $conexion->prepare("SELECT COUNT(*) as total FROM tb_usuarios WHERE usuario = ? OR email = ?");
    $check->bind_param("ss", $usuario, $email);
    $check->execute();
    $existe = (int)$check->get_result()->fetch_assoc()['total'];
    $check->close();

    if ($existe > 0) {
        header("Location: ../index.php?vista=usuarios&msg=error&detalle=usuario_duplicado");
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conexion->prepare(
        "INSERT INTO tb_usuarios (nombre, apellido, email, usuario, password, id_rol, estado)
         VALUES (?, ?, ?, ?, ?, ?, 'ACTIVO')"
    );
    $stmt->bind_param("sssssi", $nombre, $apellido, $email, $usuario, $hash, $id_rol);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        header("Location: ../index.php?vista=usuarios&msg=usuario_registrado");
    } else {
        header("Location: ../index.php?vista=usuarios&msg=error&detalle=db_error");
    }
    exit;
}

header("Location: ../index.php?vista=usuarios");
exit;
