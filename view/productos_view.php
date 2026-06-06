<?php
require_once __DIR__ . '/../php/roles/permisos.php';
if (!verificarVistaPermiso('productos')) return;

$action = 'listar';
$data   = include(__DIR__ . "/../php/productos.php");
$activos   = $data['activos']   ?? [];
$inactivos = $data['inactivos'] ?? [];
require_once __DIR__ . '/../php/conexion.php';
$almacenes = $conexion->query("SELECT id_almacen, nombre_almacen FROM tb_almacen");
?>

<div id="alerta-container" class="mb-3"></div>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="color:var(--primary)">
            <i class="bi bi-box-seam"></i> Productos
        </h2>
        <p class="text-muted mb-0">Gestión de catálogo de productos</p>
    </div>
</div>

<!-- FORMULARIO -->
<?php if (puede('productos', 'crear')): ?>
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-plus-circle"></i> Registrar Producto</div>
    <div class="card-body">

        <form action="php/productos.php?action=insertar" method="POST" enctype="multipart/form-data" class="row g-3">

            <div class="col-md-4">
                <label class="form-label fw-semibold">Modelo</label>
                <input type="text" name="modelo" class="form-control" required>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Precio</label>
                <input type="number" name="precio" class="form-control" step="0.01" required>
            </div>

            <!-- 🔥 ALMACÉN -->
            <div class="col-md-3">
                <label class="form-label fw-semibold">Almacén</label>
                <select name="id_almacen" class="form-control" required>
                    <option value="">Seleccione</option>
                    <?php while($a = $almacenes->fetch_assoc()): ?>
                        <option value="<?= $a['id_almacen'] ?>">
                            <?= $a['nombre_almacen'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Imagen</label>
                <input type="file" name="imagen" class="form-control">
            </div>

            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-primary-custom w-100">
                    <i class="bi bi-save2"></i>
                </button>
            </div>

        </form>

    </div>
</div>
<?php endif; ?>

<!-- MODAL EDITAR -->
<?php if (puede('productos', 'editar')): ?>
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Editar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form action="php/productos.php?action=actualizar" method="POST">

                    <input type="hidden" name="id" id="edit_id">

                    <div class="mb-3">
                        <label class="form-label">Modelo</label>
                        <input type="text" name="modelo" id="edit_modelo" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Precio</label>
                        <input type="number" name="precio" id="edit_precio" class="form-control" step="0.01" required>
                    </div>

                    <button class="btn btn-primary w-100">
                        Actualizar
                    </button>

                </form>

            </div>

        </div>
    </div>
</div>
<?php endif; ?>

<!-- TABLA -->
<div class="card mt-3">

    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Productos</span>

        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#activos">Activos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#inactivos">Inactivos</a>
            </li>
        </ul>
    </div>

    <div class="card-body tab-content">

        <!-- ===================== ACTIVOS ===================== -->
        <div class="tab-pane fade show active" id="activos">

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Imagen</th>
                        <th>Modelo</th>
                        <th>Almacén</th>
                        <th>Precio</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($activos as $row): ?>
                    <tr>

                        <td>#<?= (int)$row['id_producto'] ?></td>

                        <td>
                            <?php if ($row['imagen']): ?>
                                <img src="img/<?= htmlspecialchars($row['imagen']) ?>" width="50">
                            <?php endif; ?>
                        </td>

                        <td><?= htmlspecialchars($row['modelo']) ?></td>

                        <td><?= htmlspecialchars($row['nombre_almacen'] ?? 'Sin almacén') ?></td>

                        <td>S/. <?= number_format((float)$row['precio_actual'], 2) ?></td>

                        <td><?= date('d/m/Y', strtotime($row['create_at'])) ?></td>

                        <td class="d-flex gap-2">

                            <!-- EDITAR -->
                            <?php if (puede('productos','editar')): ?>
                                <button class="btn btn-warning btn-sm"
                                    onclick="editar(
                                        <?= (int)$row['id_producto'] ?>,
                                        '<?= htmlspecialchars($row['modelo'], ENT_QUOTES) ?>',
                                        <?= (float)$row['precio_actual'] ?>
                                    )">
                                    Editar
                                </button>
                            <?php endif; ?>

                            <!-- DESACTIVAR -->
                            <?php if (puede('productos','eliminar')): ?>
                                <a href="php/productos.php?action=desactivar&id=<?= (int)$row['id_producto'] ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('¿Desactivar este producto?')">
                                    Desactivar
                                </a>
                            <?php endif; ?>

                        </td>

                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        </div>

        <!-- ===================== INACTIVOS ===================== -->
        <div class="tab-pane fade" id="inactivos">

            <table class="table table-dark table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Modelo</th>
                        <th>Almacén</th>
                        <th>Precio</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($inactivos as $row): ?>
                    <tr>

                        <td>#<?= (int)$row['id_producto'] ?></td>

                        <td><?= htmlspecialchars($row['modelo']) ?></td>

                        <td><?= htmlspecialchars($row['nombre_almacen'] ?? 'Sin almacén') ?></td>

                        <td>S/. <?= number_format((float)$row['precio_actual'], 2) ?></td>

                        <td><?= date('d/m/Y', strtotime($row['create_at'])) ?></td>

                        <td>

                            <?php if (puede('productos','editar')): ?>
                                <a href="php/productos.php?action=reactivar&id=<?= (int)$row['id_producto'] ?>"
                                   class="btn btn-success btn-sm">
                                    Reactivar
                                </a>
                            <?php endif; ?>

                        </td>

                    </tr>
                <?php endforeach; ?>
                </tbody>

            </table>

        </div>

    </div>
</div>

<!-- JS -->
<script>
function editar(id, modelo, precio) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_modelo').value = modelo;
    document.getElementById('edit_precio').value = precio;

    new bootstrap.Modal(document.getElementById('modalEditar')).show();
}
</script>