<?php
require_once __DIR__ . "/conexion.php";

$action = $action ?? $_GET['action'] ?? '';

if ($action === 'listar') {
    $ventas = [];
    $result = $conexion->query(
        "SELECT id_venta, fecha, titulo, total FROM tb_ventas ORDER BY id_venta DESC"
    );
    while ($row = $result->fetch_assoc()) {
        $ventas[] = $row;
    }
    return $ventas;

} elseif ($action === 'detalle') {
    header('Content-Type: application/json; charset=utf-8');
    $id_venta = (int)($_GET['id_venta'] ?? 0);

    if (!$id_venta) {
        echo json_encode([]);
        exit;
    }

    $stmt = $conexion->prepare(
        "SELECT d.cantidad, d.precio_unitario, d.subtotal, p.modelo
         FROM tb_detalle_venta d
         INNER JOIN tb_productos p ON d.id_producto = p.id_producto
         WHERE d.id_venta = ?"
    );
    $stmt->bind_param("i", $id_venta);
    $stmt->execute();
    $result = $stmt->get_result();
    $detalle = [];
    while ($row = $result->fetch_assoc()) {
        $detalle[] = $row;
    }
    $stmt->close();
    echo json_encode($detalle);
    exit;
}
