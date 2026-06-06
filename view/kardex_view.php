<?php
require_once __DIR__ . '/../php/roles/permisos.php';
if (!verificarVistaPermiso('kardex')) return;

require_once __DIR__ . '/../php/conexion.php';
require_once __DIR__ . '/../models/Kardex.php';

// Listar movimientos
$pagina_actual = max(1, (int)($_GET['p'] ?? 1));
$por_pagina    = 10;
$offset        = ($pagina_actual - 1) * $por_pagina;

$movimientos   = Kardex::listarMovimientos($conexion, $offset, $por_pagina);
$total_rows    = $conexion->query("SELECT COUNT(*) as total FROM tb_kardex")->fetch_assoc()['total'];
$total_paginas = $total_rows > 0 ? ceil($total_rows / $por_pagina) : 1;

// Datos
$productos = Kardex::obtenerProductos($conexion);
$almacenes = Kardex::obtenerAlmacenes($conexion);
?>

<div id="alerta-container" class="mb-3"></div>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="color:var(--primary)">
            <i class="bi bi-journal-text"></i> Kardex
        </h2>
        <p class="text-muted mb-0">Control de entradas y salidas de inventario</p>
    </div>

    <div class="d-flex gap-2 mt-3 mt-md-0">
        <?php if (puede('kardex', 'ver')): ?>
        <a href="php/exportar_excel.php" class="btn btn-success" data-accion="exportar-kardex">
            <i class="bi bi-file-earmark-spreadsheet"></i> Excel
        </a>
        <a href="php/exportar_pdf.php" class="btn btn-danger" data-accion="exportar-kardex">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- FORMULARIO -->
<?php if (puede('kardex', 'crear')): ?>
<div class="card mb-4" data-perm-section="kardex-crear">
    <div class="card-header">
        <i class="bi bi-plus-circle"></i> Registrar Movimiento
    </div>

    <div class="card-body">
        <form id="form-kardex" action="php/kardex.php?action=registrar" method="POST" class="row g-3">

            <div class="col-md-3">
                <label class="form-label fw-semibold">Tipo</label>
                <select name="tipo" class="form-select" required>
                    <option value="entrada">📥 Entrada</option>
                    <option value="salida">📤 Salida</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Producto</label>
                <select name="id_producto" id="producto-select" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    <?php foreach ($productos as $p): ?>
                    <option 
                        value="<?= (int)$p['id_producto'] ?>"
                        data-precio="<?= (float)$p['precio_actual'] ?>"
                    >
                        <?= htmlspecialchars($p['modelo']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Almacén</label>
                <select name="id_almacen" class="form-select" required>
                    <option value="">Seleccionar…</option>
                    <?php foreach ($almacenes as $a): ?>
                    <option value="<?= (int)$a['id_almacen'] ?>">
                        <?= htmlspecialchars($a['nombre_almacen']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Cantidad</label>
                <input type="number" name="cantidad" class="form-control" min="1" required>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Precio Unit.</label>
                <input type="number" name="valor" id="precio-unitario" class="form-control" step="0.01" required>
            </div>

            <div class="col-12 text-end">
                <button class="btn btn-primary-custom px-4 py-2">
                    <i class="bi bi-save2"></i> Registrar
                </button>
            </div>

        </form>
    </div>
</div>
<?php endif; ?>

<!-- HISTORIAL -->
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <span><i class="bi bi-list-ul"></i> Historial de Movimientos</span>
        <span class="badge bg-light text-dark"><?= count($movimientos) ?> registros</span>
    </div>

    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>Movimiento</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-center">Saldo</th>
                    <th class="text-end">Precio</th>
                    <th>Fecha</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($movimientos as $row): ?>
                <tr>
                    <td>#<?= (int)$row['id_kardex'] ?></td>
                    <td><?= htmlspecialchars($row['modelo']) ?></td>
                    <td>
                        <?= ((int)$row['id_tipooperacion'] === 1) ? '📥 Entrada' : '📤 Salida' ?>
                    </td>
                    <td class="text-center fw-bold"><?= (int)$row['cantidad'] ?></td>
                    <td class="text-center"><?= (int)$row['saldo_total'] ?></td>
                    <td class="text-end">S/. <?= number_format((float)$row['valor_unico_historico'], 2) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['create_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
    </div>
</div>

<!-- PAGINACIÓN -->
<?php if ($total_paginas > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
        <li class="page-item <?= $pagina_actual == $i ? 'active' : '' ?>">
            <a class="page-link" href="?vista=kardex&p=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<!-- JS (PRECIO AUTOMÁTICO + PREVENCIÓN DE RECARGA) -->
<script>
document.getElementById('producto-select')?.addEventListener('change', function () {
    const precio = this.options[this.selectedIndex].dataset.precio;
    document.getElementById('precio-unitario').value = precio || '';
});

document.getElementById('form-kardex')?.addEventListener('submit', function (e) {
    e.preventDefault(); // evita recarga (como pediste)
    this.submit(); // luego puedes cambiar a fetch si quieres AJAX
});
</script>