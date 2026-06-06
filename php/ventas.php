<?php
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/../models/Venta.php";

$action = $action ?? $_GET['action'] ?? '';

if ($action === 'vender') {
    $id_producto = (int)($_POST['id_producto'] ?? 0);
    $precio      = (float)($_POST['precio'] ?? 0);
    $modelo      = trim($_POST['modelo'] ?? '');

    if (!$id_producto || !$precio) {
        header("Location: ../index.php?vista=ventas&msg=error&detalle=datos_invalidos");
        exit;
    }

    $stock_actual = Venta::obtenerStock($conexion, $id_producto);
    if ($stock_actual < 1) {
        header("Location: ../index.php?vista=ventas&msg=sin_stock");
        exit;
    }

    $titulo   = "Venta: " . substr($modelo, 0, 50) . " (x1)";
    $id_venta = Venta::insertarVenta($conexion, $titulo, $precio);
    Venta::insertarDetalle($conexion, $id_venta, $id_producto, 1, $precio);
    Venta::salidaKardex($conexion, $id_producto, 1, $precio);

    header("Location: ../index.php?vista=ventas&msg=vendido");
    exit;

} elseif ($action === 'vender_carrito') {
    header('Content-Type: application/json; charset=utf-8');
    $carrito = json_decode(file_get_contents('php://input'), true);

    if (empty($carrito) || !is_array($carrito)) {
        echo json_encode(['status' => 'error', 'message' => 'Carrito vacío']);
        exit;
    }

    foreach ($carrito as &$item) {
        $item['id']       = (int)($item['id'] ?? 0);
        $item['cantidad'] = (int)($item['cantidad'] ?? 0);
        $item['precio']   = (float)($item['precio'] ?? 0);
        $item['modelo']   = substr(trim($item['modelo'] ?? ''), 0, 70);

        if (!$item['id'] || $item['cantidad'] < 1 || $item['precio'] <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Datos de producto inválidos']);
            exit;
        }

        $stock = Venta::obtenerStock($conexion, $item['id']);
        if ($item['cantidad'] > $stock) {
            echo json_encode(['status' => 'error', 'message' => 'Stock insuficiente para: ' . $item['modelo']]);
            exit;
        }
    }
    unset($item);

    $titulo_items = array_map(fn($i) => $i['modelo'] . " (x" . $i['cantidad'] . ")", $carrito);
    $titulo       = "Venta: " . substr(implode(", ", $titulo_items), 0, 60);
    $total        = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $carrito));

    $id_venta = Venta::insertarVenta($conexion, $titulo, $total);

    foreach ($carrito as $item) {
        Venta::insertarDetalle($conexion, $id_venta, $item['id'], $item['cantidad'], $item['precio']);
        Venta::salidaKardex($conexion, $item['id'], $item['cantidad'], $item['precio']);
    }

    echo json_encode(['status' => 'success']);
    exit;

} elseif ($action === 'listar') {
    $limit  = 8;
    $pagina = max(1, (int)($_GET['pagina'] ?? 1));
    $offset = ($pagina - 1) * $limit;

    return [
        'productos'     => Venta::listarProductos($conexion, $limit, $offset),
        'total_paginas' => ceil(Venta::contarProductos($conexion) / $limit) ?: 1,
    ];
}
