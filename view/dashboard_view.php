<?php
// Dashboard básico - muestra resúmenes
require_once __DIR__ . '/../php/conexion.php';

$total_productos = $conexion->query("SELECT COUNT(*) as c FROM tb_productos WHERE inactive_at IS NULL")->fetch_assoc()['c'] ?? 0;
$total_ventas    = $conexion->query("SELECT COUNT(*) as c FROM tb_ventas")->fetch_assoc()['c'] ?? 0;
$ingresos        = $conexion->query("SELECT COALESCE(SUM(total),0) as s FROM tb_ventas")->fetch_assoc()['s'] ?? 0;
$movimientos     = $conexion->query("SELECT COUNT(*) as c FROM tb_kardex")->fetch_assoc()['c'] ?? 0;
?>
<div class="row g-4 mb-4">
    <?php
    $stats = [
        ['icon'=>'bi-box-seam',      'color'=>'#4b0082', 'label'=>'Productos Activos', 'valor'=>$total_productos],
        ['icon'=>'bi-cart-check',    'color'=>'#198754', 'label'=>'Ventas Realizadas', 'valor'=>$total_ventas],
        ['icon'=>'bi-cash-coin',     'color'=>'#0d6efd', 'label'=>'Ingresos Totales',  'valor'=>'S/. ' . number_format($ingresos, 2)],
        ['icon'=>'bi-journal-text',  'color'=>'#fd7e14', 'label'=>'Movimientos Kardex','valor'=>$movimientos],
    ];
    foreach ($stats as $s): ?>
    <div class="col-sm-6 col-xl-3 fade-in">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div style="width:54px;height:54px;background:<?= $s['color'] ?>22;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="bi <?= $s['icon'] ?> fs-4" style="color:<?= $s['color'] ?>"></i>
                </div>
                <div>
                    <div class="text-muted small"><?= $s['label'] ?></div>
                    <div class="fw-bold fs-4"><?= $s['valor'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<p class="text-muted text-center">Selecciona una sección en el menú para comenzar.</p>
