<?php
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/../models/Kardex.php";

$action = $action ?? $_GET['action'] ?? '';

if ($action === 'registrar') {
    $tipo        = $_POST['tipo'] ?? '';
    $id_producto = (int)($_POST['id_producto'] ?? 0);
    $id_almacen  = (int)($_POST['id_almacen'] ?? 0);
    $cantidad    = (int)($_POST['cantidad'] ?? 0);
    $valor       = (float)($_POST['valor'] ?? 0);

    if (!$id_producto || !$id_almacen || $cantidad < 1 || $valor <= 0) {
        header("Location: ../index.php?vista=kardex&msg=error&detalle=datos_invalidos");
        exit;
    }

    try {
        if ($tipo === 'entrada') {
            Kardex::registrarEntrada($conexion, $id_producto, $id_almacen, $cantidad, $valor);
            $msg = 'entrada';
        } else {
            $stock = Kardex::obtenerStock($conexion, $id_producto, $id_almacen);
            if ($cantidad > $stock) {
                header("Location: ../index.php?vista=kardex&msg=sin_stock");
                exit;
            }
            Kardex::registrarSalida($conexion, $id_producto, $id_almacen, $cantidad, $valor);
            $msg = 'salida';
        }
        header("Location: ../index.php?vista=kardex&msg=$msg");
    } catch (mysqli_sql_exception $e) {
        header("Location: ../index.php?vista=kardex&msg=error&detalle=db_error");
    }
    exit;

} elseif ($action === 'listar') {
    $por_pagina    = 10;
    $pagina_actual = max(1, (int)($pagina_actual ?? $_GET['pagina'] ?? 1));
    $offset        = ($pagina_actual - 1) * $por_pagina;

    return [
        'movimientos'   => Kardex::listarMovimientos($conexion, $offset, $por_pagina),
        'total_paginas' => Kardex::totalPaginas($conexion, $por_pagina),
    ];

} elseif ($action === 'formulario') {
    return [
        'productos' => Kardex::obtenerProductos($conexion),
        'almacenes' => Kardex::obtenerAlmacenes($conexion),
    ];

} elseif ($action === 'stock') {
    return Kardex::listarStock($conexion);
}
