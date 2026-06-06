<?php
require_once __DIR__ . '/../php/roles/permisos.php';
if (!verificarVistaPermiso('historial')) return;

$action = 'listar';
$ventas = include(__DIR__ . "/../php/historial.php");
$ventas = $ventas ?: [];
?>

<div id="alerta-container" class="mb-3"></div>

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="color:var(--primary)">
            <i class="bi bi-clock-history"></i> Historial de Ventas
        </h2>
        <p class="text-muted mb-0">Todas las transacciones registradas</p>
    </div>
    <div class="d-flex gap-2 mt-3 mt-md-0">
        <?php if (puede('historial', 'crear')): ?>
        <a href="php/exportar_excel.php" class="btn btn-success">
            <i class="bi bi-file-earmark-spreadsheet"></i> Excel
        </a>
        <a href="php/exportar_pdf.php" class="btn btn-danger">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>
        <?php endif; ?>
        <?php if (puede('ventas')): ?>
        <a href="?vista=ventas" class="btn btn-primary-custom">
            <i class="bi bi-cart-check"></i> Ventas
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-receipt"></i> Ventas Registradas</span>
        <span class="badge bg-light text-dark"><?= count($ventas) ?> registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Total</th>
                        <th class="text-center">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($ventas)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Sin ventas registradas aún
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ventas as $row): ?>
                    <tr>
                        <td class="ps-4 fw-bold text-secondary">#<?= (int)$row['id_venta'] ?></td>
                        <td>
                            <i class="bi bi-calendar-event text-primary"></i>
                            <?= htmlspecialchars($row['fecha']) ?>
                        </td>
                        <td class="text-truncate" style="max-width:280px">
                            <?= htmlspecialchars($row['titulo'] ?? 'Sin descripción') ?>
                        </td>
                        <td>
                            <span class="badge rounded-pill px-3 py-2" style="background:var(--primary);font-size:.9rem">
                                S/. <?= number_format((float)$row['total'], 2) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-primary-custom" onclick="verDetalle(<?= (int)$row['id_venta'] ?>)">
                                <i class="bi bi-eye-fill"></i> Ver
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt-cutoff"></i> Detalle de Venta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenido-detalle">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>
</div>

<script>
async function verDetalle(id_venta) {
    const modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
    document.getElementById('contenido-detalle').innerHTML =
        '<div class="text-center py-4"><div class="spinner-border" style="color:var(--primary)"></div></div>';
    modal.show();

    try {
        const res  = await fetch('php/historial.php?action=detalle&id_venta=' + id_venta);
        const data = await res.json();

        if (!data.length) {
            document.getElementById('contenido-detalle').innerHTML =
                '<p class="text-center text-muted py-3">Sin productos en esta venta.</p>';
            return;
        }

        let total = 0;
        let html  = `<div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-end">Precio Unit.</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead><tbody>`;

        data.forEach(item => {
            const sub = parseFloat(item.subtotal);
            total += sub;
            html += `<tr>
                <td class="fw-semibold">${item.modelo}</td>
                <td class="text-center"><span class="badge bg-secondary">${item.cantidad}</span></td>
                <td class="text-end">S/. ${parseFloat(item.precio_unitario).toFixed(2)}</td>
                <td class="text-end fw-bold text-success">S/. ${sub.toFixed(2)}</td>
            </tr>`;
        });

        html += `</tbody><tfoot>
            <tr class="fw-bold">
                <td colspan="3" class="text-end">TOTAL:</td>
                <td class="text-end text-success fs-6">S/. ${total.toFixed(2)}</td>
            </tr>
        </tfoot></table></div>`;

        document.getElementById('contenido-detalle').innerHTML = html;
    } catch (e) {
        document.getElementById('contenido-detalle').innerHTML =
            '<p class="text-danger text-center py-3"><i class="bi bi-exclamation-triangle"></i> Error al cargar detalle</p>';
    }
}
</script>
