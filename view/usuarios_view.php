<?php
require_once __DIR__ . '/../php/roles/permisos.php';
if (!verificarVistaPermiso('usuarios')) return;

// Solo administradores (nivel 1)
if (($_SESSION['rol_nivel'] ?? 99) > 1) {
    echo renderAccesoDenegado('usuarios');
    return;
}

require_once __DIR__ . '/../php/conexion.php';

// Cargar usuarios con rol
$usuarios = $conexion->query("
    SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.usuario,
           u.estado, u.create_at, r.nombre AS rol_nombre, r.nivel AS rol_nivel, r.id_rol
    FROM tb_usuarios u
    INNER JOIN tb_roles r ON r.id_rol = u.id_rol
    ORDER BY r.nivel, u.nombre
")->fetch_all(MYSQLI_ASSOC) ?? [];

// Cargar roles para el select
$roles_list = $conexion->query("SELECT * FROM tb_roles ORDER BY nivel")->fetch_all(MYSQLI_ASSOC) ?? [];

// Cargar permisos por rol
$permisos_por_rol = [];
$res_p = $conexion->query("SELECT * FROM tb_permisos ORDER BY id_rol, modulo");
while ($p = $res_p->fetch_assoc()) {
    $permisos_por_rol[$p['id_rol']][$p['modulo']] = $p;
}

$modulos = ['productos', 'kardex', 'ventas', 'historial', 'usuarios'];
$modulo_iconos = [
    'productos' => 'bi-box-seam',
    'kardex'    => 'bi-journal-text',
    'ventas'    => 'bi-cart-check',
    'historial' => 'bi-clock-history',
    'usuarios'  => 'bi-people-fill',
];
$rol_colores = [1 => '#f59e0b', 2 => '#22c55e', 3 => '#3b82f6'];
?>

<div id="alerta-container" class="mb-3"></div>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="color:var(--primary)">
            <i class="bi bi-people-fill"></i> Gestión de Usuarios
        </h2>
        <p class="text-muted mb-0">Administración de roles y permisos del sistema</p>
    </div>
</div>

<!-- Tarjetas de roles -->
<div class="row g-3 mb-4">
    <?php foreach ($roles_list as $rol): ?>
    <?php
        $color = $rol_colores[$rol['nivel']] ?? '#94a3b8';
        $usuarios_en_rol = array_filter($usuarios, fn($u) => $u['id_rol'] == $rol['id_rol']);
        $perms = $permisos_por_rol[$rol['id_rol']] ?? [];
    ?>
    <div class="col-md-4">
        <div class="card h-100" style="border-top:4px solid <?= $color ?>">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:44px;height:44px;background:<?= $color ?>22;border-radius:12px;
                                display:flex;align-items:center;justify-content:center;">
                        <i class="bi <?= $rol['nivel'] == 1 ? 'bi-shield-fill-check' : ($rol['nivel'] == 2 ? 'bi-person-badge' : 'bi-boxes') ?>"
                           style="color:<?= $color ?>;font-size:1.2rem"></i>
                    </div>
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($rol['nombre']) ?></div>
                        <div class="text-muted small">Nivel <?= $rol['nivel'] ?> · <?= count($usuarios_en_rol) ?> usuario(s)</div>
                    </div>
                </div>
                <p class="text-muted small mb-3"><?= htmlspecialchars($rol['descripcion'] ?? '') ?></p>

                <!-- Permisos del rol -->
                <div>
                    <div class="fw-semibold small mb-2" style="color:#374151">Permisos:</div>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($modulos as $mod):
                            $p = $perms[$mod] ?? ['puede_ver'=>0,'puede_crear'=>0,'puede_editar'=>0,'puede_eliminar'=>0];
                            $acciones = [];
                            if ($p['puede_ver'])     $acciones[] = 'Ver';
                            if ($p['puede_crear'])   $acciones[] = 'Crear';
                            if ($p['puede_editar'])  $acciones[] = 'Editar';
                            if ($p['puede_eliminar']) $acciones[] = 'Eliminar';
                            $tieneAlgo = !empty($acciones);
                        ?>
                        <span class="badge" style="background:<?= $tieneAlgo ? $color . '22' : '#f1f5f9' ?>;
                              color:<?= $tieneAlgo ? $color : '#94a3b8' ?>;
                              border:1px solid <?= $tieneAlgo ? $color . '44' : '#e2e8f0' ?>;
                              font-size:.7rem;padding:4px 8px">
                            <i class="bi <?= $modulo_iconos[$mod] ?> me-1"></i>
                            <?= ucfirst($mod) ?>
                            <?php if ($tieneAlgo): ?>
                                · <?= implode(', ', $acciones) ?>
                            <?php else: ?>
                                · <s>Bloqueado</s>
                            <?php endif; ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Formulario Registrar Usuario (solo admins) -->
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-person-plus-fill"></i> Registrar Nuevo Usuario</div>
    <div class="card-body">
        <form action="php/usuarios_accion.php?action=crear" method="POST" class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Nombre</label>
                <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Apellido</label>
                <input type="text" name="apellido" class="form-control" placeholder="Apellido" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Usuario</label>
                <input type="text" name="usuario" class="form-control" placeholder="nombre_usuario" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="••••••" required minlength="4">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Rol</label>
                <select name="id_rol" class="form-select" required>
                    <option value="">Seleccionar rol…</option>
                    <?php foreach ($roles_list as $r): ?>
                    <option value="<?= (int)$r['id_rol'] ?>"><?= htmlspecialchars($r['nombre']) ?> (Nivel <?= $r['nivel'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button class="btn btn-primary-custom px-4 py-2">
                    <i class="bi bi-person-plus-fill"></i> Registrar Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de usuarios -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-person-lines-fill"></i> Usuarios del Sistema</span>
        <span class="badge bg-light text-dark"><?= count($usuarios) ?> registrados</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th class="text-center">Estado</th>
                        <th>Registro</th>
                        <th class="text-center">Permisos rápidos</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($usuarios as $u):
                    $color = $rol_colores[$u['rol_nivel']] ?? '#94a3b8';
                    $perms = $permisos_por_rol[$u['id_rol']] ?? [];
                ?>
                <tr>
                    <td class="ps-4 fw-bold text-secondary"><?= (int)$u['id_usuario'] ?></td>
                    <td class="fw-bold">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:34px;height:34px;background:<?= $color ?>22;border-radius:50%;
                                        display:flex;align-items:center;justify-content:center;
                                        color:<?= $color ?>;font-weight:800;font-size:.85rem;flex-shrink:0">
                                <?= strtoupper(substr($u['nombre'], 0, 1)) ?>
                            </div>
                            <?= htmlspecialchars($u['usuario']) ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span style="background:<?= $color ?>18;color:<?= $color ?>;border:1px solid <?= $color ?>44;
                              border-radius:20px;padding:3px 10px;font-size:.78rem;font-weight:700;">
                            <?= htmlspecialchars($u['rol_nombre']) ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if ($u['estado'] === 'ACTIVO'): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= date('d/m/Y', strtotime($u['create_at'])) ?></td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center flex-wrap">
                        <?php foreach ($modulos as $mod):
                            $p = $perms[$mod] ?? ['puede_ver'=>0];
                            $puedeVer = (bool)$p['puede_ver'];
                        ?>
                            <span class="badge <?= $puedeVer ? 'bg-success' : 'bg-light text-muted' ?>"
                                  style="font-size:.65rem" title="<?= ucfirst($mod) ?>: <?= $puedeVer ? 'Acceso' : 'Bloqueado' ?>">
                                <i class="bi <?= $modulo_iconos[$mod] ?>"></i>
                            </span>
                        <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Info de credenciales de prueba -->
<div class="card mt-4" style="border:2px solid #e0e7ff;background:#f0f4ff;">
    <div class="card-body">
        <div class="fw-bold mb-2" style="color:#3730a3"><i class="bi bi-info-circle me-2"></i>Credenciales de prueba (SQL incluido)</div>
        <div class="row g-2">
            <div class="col-md-4">
                <div style="background:#fff;border-radius:10px;padding:12px;border:1px solid #c7d2fe;">
                    <div class="fw-bold text-warning-emphasis"><i class="bi bi-shield-fill-check me-1"></i>Administrador</div>
                    <div class="small text-muted">Usuarios: <code>yuliana</code> / <code>brayan</code></div>
                    <div class="small text-muted">Contraseña: <code>123</code></div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background:#fff;border-radius:10px;padding:12px;border:1px solid #bbf7d0;">
                    <div class="fw-bold text-success"><i class="bi bi-person-badge me-1"></i>Vendedor</div>
                    <div class="small text-muted">Usuario: <code>sandro</code></div>
                    <div class="small text-muted">Contraseña: <code>123</code></div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background:#fff;border-radius:10px;padding:12px;border:1px solid #bfdbfe;">
                    <div class="fw-bold text-primary"><i class="bi bi-boxes me-1"></i>Almacenero</div>
                    <div class="small text-muted">Usuario: <code>daniel</code></div>
                    <div class="small text-muted">Contraseña: <code>123</code></div>
                </div>
            </div>
        </div>
        <div class="text-muted small mt-2">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Ejecuta <code>database/roles_permisos.sql</code> en tu base de datos para crear los roles y usuarios de prueba.
        </div>
    </div>
</div>
