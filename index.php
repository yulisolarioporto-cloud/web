<?php
session_start();

require_once __DIR__ . '/php/roles/permisos.php';

$vistas_permitidas = ['productos', 'kardex', 'ventas', 'historial', 'usuarios'];
$vista = $_GET['vista'] ?? null;

// Vista por defecto según permisos
if (!$vista || !in_array($vista, $vistas_permitidas)) {
    if (puede('ventas'))        $vista = 'ventas';
    elseif (puede('kardex'))    $vista = 'kardex';
    elseif (puede('productos')) $vista = 'productos';
    elseif (puede('historial')) $vista = 'historial';
    else $vista = 'sin_acceso';
}

// Bloqueo de acceso
if ($vista !== 'sin_acceso' && !puede($vista)) {
    $vista = 'acceso_denegado';
}

$nombre_usuario = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
$rol            = htmlspecialchars($_SESSION['rol'] ?? '');
$rol_nivel      = (int)($_SESSION['rol_nivel'] ?? 99);
$permisos_json  = permisosComoJSON();

$nav_items = [
    'productos' => ['icono' => 'bi-box-seam',      'label' => 'Productos'],
    'kardex'    => ['icono' => 'bi-journal-text',  'label' => 'Kardex'],
    'ventas'    => ['icono' => 'bi-cart-check',    'label' => 'Ventas'],
    'historial' => ['icono' => 'bi-clock-history', 'label' => 'Historial'],
    'usuarios'  => ['icono' => 'bi-people-fill',   'label' => 'Usuarios'],
];

$rol_clases = [
    1 => 'admin',
    2 => 'vendedor',
    3 => 'almacenero'
];

$rol_clase = $rol_clases[$rol_nivel] ?? 'default';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DELATEL — <?= ucfirst($vista) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/sistema.css" rel="stylesheet">

    <style>
        .nav-role-link {
            display: flex;
            gap: 6px;
            padding: 8px 14px;
            text-decoration: none;
            color: white;
        }

        .nav-role-link.active {
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
        }

        .nav-role-link.locked {
            opacity: 0.4;
            pointer-events: none;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark navbar-custom px-3">

    <a class="navbar-brand" href="index.php">DELATEL</a>

    <div class="d-flex gap-2">

        <?php foreach ($nav_items as $modulo => $item): ?>
            <?php $tieneAcceso = puede($modulo); ?>

            <a class="nav-role-link <?= $vista === $modulo ? 'active' : '' ?> <?= !$tieneAcceso ? 'locked' : '' ?>"
               href="<?= $tieneAcceso ? '?vista=' . $modulo : '#' ?>">

                <i class="bi <?= $item['icono'] ?>"></i>
                <?= $item['label'] ?>
            </a>

        <?php endforeach; ?>

    </div>

    <div class="text-white">
        <?= $nombre_usuario ?> | <?= $rol ?>
    </div>

</nav>

<div class="container mt-4">

<?php
if ($vista === 'acceso_denegado' || $vista === 'sin_acceso') {
    echo "<h3>Acceso denegado</h3>";

} else {
    $archivo_vista = __DIR__ . "/view/{$vista}_view.php";

    if (file_exists($archivo_vista)) {
        include $archivo_vista;
    } else {
        echo "<h3>Vista no encontrada</h3>";
    }
}
?>

</div>

<script>
    window.PERMISOS = <?= $permisos_json ?>;
    window.VISTA_ACTUAL = "<?= $vista ?>";
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/apps.js"></script>

</body>
</html>