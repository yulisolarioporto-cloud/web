<?php
require_once __DIR__ . '/../php/roles/permisos.php';
if (!verificarVistaPermiso('ventas')) return;

$pagina_actual  = max(1, (int)($_GET['p'] ?? 1));
$_GET['pagina'] = $pagina_actual;
$action         = 'listar';
$data           = include(__DIR__ . "/../php/ventas.php");
$productos      = $data['productos']     ?? [];
$total_paginas  = $data['total_paginas'] ?? 1;
?>
<style>
.card-producto { border:none; border-radius:16px; overflow:hidden; transition:.3s; box-shadow:0 4px 15px rgba(0,0,0,0.08); background:#fff; }
.card-producto:hover { transform:translateY(-5px); box-shadow:0 10px 25px rgba(0,0,0,0.15); }
.img-producto  { height:160px; object-fit:contain; background:#f8f9fa; border-radius:10px; padding:8px; width:100%; }
.precio-tag    { color:var(--primary); font-size:1.15rem; font-weight:800; }
.carrito-panel { position:sticky; top:20px; border-radius:18px; box-shadow:0 6px 24px rgba(0,0,0,0.1); background:#fff; }
#items-carrito { min-height:110px; max-height:380px; overflow-y:auto; }
#items-carrito::-webkit-scrollbar { width:5px; }
#items-carrito::-webkit-scrollbar-thumb { background:#ccc; border-radius:10px; }
</style>

<div id="alerta-container" class="mb-3"></div>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="color:var(--primary)">
            <i class="bi bi-cart-check"></i> Ventas
        </h2>
        <p class="text-muted mb-0">Selecciona productos y procesa la venta</p>
    </div>
    <div class="mt-3 mt-md-0">
        <input type="text" id="buscador" class="form-control shadow-sm"
               placeholder="&#xF52A; Buscar producto..." style="max-width:260px">
    </div>
</div>

<?php if (!puede('ventas', 'crear')): ?>
<!-- Solo pueden ver historial, no crear ventas -->
<div class="alert d-flex align-items-center gap-3" style="background:#fef3c7;border:1px solid #fcd34d;border-radius:14px;padding:16px 20px;">
    <i class="bi bi-eye-fill fs-5" style="color:#d97706"></i>
    <span>Tu rol (<strong><?= htmlspecialchars($_SESSION['rol'] ?? '') ?></strong>) solo puede <strong>consultar</strong> el módulo de ventas, no registrar nuevas.</span>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Grilla de productos -->
    <div class="col-lg-<?= puede('ventas', 'crear') ? '8' : '12' ?>">
        <div class="row g-3" id="contenedor-productos">
            <?php if (empty($productos)): ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                    Sin productos disponibles
                </div>
            <?php else: ?>
                <?php foreach ($productos as $row):
                    $modelo = htmlspecialchars($row['modelo'], ENT_QUOTES, 'UTF-8');
                    $stock  = (int)$row['stock'];
                    $precio = (float)$row['precio_actual'];
                ?>
                <div class="col-sm-6 col-xl-<?= puede('ventas', 'crear') ? '4' : '3' ?> tarjeta-producto fade-in"
                     data-modelo="<?= strtolower($modelo) ?>">
                    <div class="card-producto h-100 p-3 text-center">
                        <img src="img/<?= htmlspecialchars($row['imagen'] ?? '') ?>"
                             class="img-producto mb-3"
                             onerror="this.src='https://via.placeholder.com/160x160/ede7f6/4b0082?text=Sin+Imagen'">
                        <h6 class="fw-bold text-truncate mb-1" title="<?= $modelo ?>"><?= $modelo ?></h6>
                        <div class="precio-tag mb-2">S/. <?= number_format($precio, 2) ?></div>
                        <div class="mb-3">
                            <?php if ($stock > 0): ?>
                                <span class="badge bg-success px-3 py-2">Stock: <?= $stock ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger px-3 py-2">Sin stock</span>
                            <?php endif; ?>
                        </div>
                        <?php if (puede('ventas', 'crear')): ?>
                        <button class="btn btn-primary-custom w-100 mt-auto"
                            <?= $stock === 0 ? 'disabled' : '' ?>
                            onclick="agregarAlCarrito(<?= (int)$row['id_producto'] ?>, '<?= addslashes($modelo) ?>', <?= $precio ?>, <?= $stock ?>)">
                            <i class="bi bi-cart-plus"></i>
                            <?= $stock === 0 ? 'Agotado' : 'Agregar' ?>
                        </button>
                        <?php else: ?>
                        <button class="btn btn-outline-secondary w-100 mt-auto" disabled>
                            <i class="bi bi-lock"></i> Solo lectura
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Paginación -->
        <?php if ($total_paginas > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?= $pagina_actual == $i ? 'active' : '' ?>">
                    <a class="page-link" href="?vista=ventas&p=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Carrito (solo si puede crear ventas) -->
    <?php if (puede('ventas', 'crear')): ?>
    <div class="col-lg-4">
        <div class="carrito-panel p-4">
            <h5 class="fw-bold mb-3 pb-2 border-bottom" style="color:var(--primary)">
                <i class="bi bi-cart3"></i> Carrito
            </h5>
            <div id="items-carrito">
                <div class="text-center text-muted py-4">
                    <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                    Carrito vacío
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-2">
                <span class="fw-bold fs-5">Total:</span>
                <span class="fw-bold fs-5 text-success">S/. <span id="total-carrito">0.00</span></span>
            </div>
            <button class="btn btn-success w-100 mt-3 py-3 fw-bold rounded-3"
                    id="btn-procesar-venta"
                    onclick="procesarVenta()"
                    disabled>
                <i class="bi bi-bag-check-fill"></i> Realizar Venta
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>
