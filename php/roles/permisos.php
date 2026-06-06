<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: " . (defined('ROOT_PATH') ? ROOT_PATH : '') . "login_view.php");
    exit;
}

if (!isset($_SESSION['rol']) || !isset($_SESSION['permisos'])) {
    require_once __DIR__ . '/../conexion.php';

    $uid  = (int)$_SESSION['id_usuario'];
    $stmt = $conexion->prepare("
        SELECT u.id_usuario, u.nombre, u.apellido, u.email,
               r.id_rol, r.nombre AS rol_nombre, r.nivel AS rol_nivel
        FROM tb_usuarios u
        INNER JOIN tb_roles r ON r.id_rol = u.id_rol
        WHERE u.id_usuario = ? AND u.estado = 'ACTIVO'
        LIMIT 1
    ");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$usuario) {
        session_destroy();
        header("Location: login_view.php");
        exit;
    }

    $stmtP = $conexion->prepare("
        SELECT modulo, puede_ver, puede_crear, puede_editar, puede_eliminar
        FROM tb_permisos
        WHERE id_rol = ?
    ");
    $stmtP->bind_param("i", $usuario['id_rol']);
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

    $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
    $_SESSION['usuario_email']  = $usuario['email'];
    $_SESSION['rol']            = $usuario['rol_nombre'];
    $_SESSION['rol_nivel']      = (int)$usuario['rol_nivel'];
    $_SESSION['id_rol']         = (int)$usuario['id_rol'];
    $_SESSION['permisos']       = $permisos;
}

function puede(string $modulo, string $accion = 'ver'): bool {
    return !empty($_SESSION['permisos'][$modulo][$accion]);
}

function requierePermiso(string $modulo, string $accion = 'ver'): void {
    if (!puede($modulo, $accion)) {
        if (!headers_sent()) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['error' => true, 'mensaje' => 'No tienes permiso para realizar esta acción.', 'codigo' => 403]);
        exit;
    }
}

function verificarVistaPermiso(string $modulo): bool {
    if (!puede($modulo)) {
        echo renderAccesoDenegado($modulo);
        return false;
    }
    return true;
}

function renderAccesoDenegado(string $modulo): string {
    $iconos = [
        'productos' => 'bi-box-seam',
        'kardex'    => 'bi-journal-text',
        'ventas'    => 'bi-cart-check',
        'historial' => 'bi-clock-history',
        'usuarios'  => 'bi-people',
    ];
    $icono = $iconos[$modulo] ?? 'bi-lock';
    $rol   = htmlspecialchars($_SESSION['rol'] ?? 'desconocido');
    $mod   = ucfirst($modulo);
    return <<<HTML
    <div class="access-denied-wrapper d-flex flex-column align-items-center justify-content-center" style="min-height:340px;gap:16px;">
        <div class="access-denied-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <div class="text-center">
            <h4 class="fw-bold mb-1" style="color:#dc3545">Acceso Restringido</h4>
            <p class="text-muted mb-3">El módulo <strong>{$mod}</strong> no está disponible para tu rol: <span class="badge-rol">{$rol}</span></p>
            <p class="text-muted small">Contacta al administrador si necesitas acceso a esta sección.</p>
        </div>
        <div class="access-denied-modules">
            <i class="bi {$icono} me-2"></i>Módulo {$mod} bloqueado
        </div>
    </div>
    HTML;
}

function permisosComoJSON(): string {
    return json_encode($_SESSION['permisos'] ?? []);
}

function badgeRol(): string {
    $rol   = $_SESSION['rol']        ?? 'Sin rol';
    $nivel = $_SESSION['rol_nivel']  ?? 99;
    $clases = [
        1 => 'badge-admin',
        2 => 'badge-vendedor',
        3 => 'badge-almacenero',
    ];
    $clase = $clases[$nivel] ?? 'badge-default';
    $iconos = [
        1 => 'bi-shield-fill-check',
        2 => 'bi-person-badge',
        3 => 'bi-boxes',
    ];
    $icono = $iconos[$nivel] ?? 'bi-person';
    $rolEsc = htmlspecialchars($rol);
    return "<span class=\"rol-badge {$clase}\"><i class=\"bi {$icono}\"></i> {$rolEsc}</span>";
}
