<?php
class Venta {

    public static function obtenerStock($conexion, $id_producto) {
        $stmt = $conexion->prepare(
            "SELECT saldo_total FROM tb_kardex WHERE id_producto = ? ORDER BY id_kardex DESC LIMIT 1"
        );
        $stmt->bind_param("i", $id_producto);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res ? (int)$res['saldo_total'] : 0;
    }

    public static function insertarVenta($conexion, $titulo, $total) {
        $stmt = $conexion->prepare(
            "INSERT INTO tb_ventas (fecha, titulo, total, iduser_create) VALUES (NOW(), ?, ?, 1)"
        );
        $stmt->bind_param("sd", $titulo, $total);
        $stmt->execute();
        $id = $conexion->insert_id;
        $stmt->close();
        return $id;
    }

    public static function actualizarTotal($conexion, $id_venta, $total) {
        $stmt = $conexion->prepare("UPDATE tb_ventas SET total = ? WHERE id_venta = ?");
        $stmt->bind_param("di", $total, $id_venta);
        $stmt->execute();
        $stmt->close();
    }

    public static function insertarDetalle($conexion, $id_venta, $id_producto, $cantidad, $precio) {
        $subtotal = $cantidad * $precio;
        $stmt = $conexion->prepare(
            "INSERT INTO tb_detalle_venta (id_venta, id_producto, cantidad, precio_unitario, subtotal, precio_final, iduser_create)
             VALUES (?, ?, ?, ?, ?, ?, 1)"
        );
        $stmt->bind_param("iiiddd", $id_venta, $id_producto, $cantidad, $precio, $subtotal, $subtotal);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function salidaKardex($conexion, $id_producto, $cantidad, $precio) {
        $stmt = $conexion->prepare("CALL sp_salida(?, 1, ?, ?)");
        $stmt->bind_param("iid", $id_producto, $cantidad, $precio);
        $stmt->execute();
        $stmt->close();
        // Limpiar resultados del SP
        while ($conexion->more_results()) {
            $conexion->next_result();
        }
    }

    public static function listarProductos($conexion, $limit, $offset) {
        $productos = [];
        $stmt = $conexion->prepare(
            "SELECT p.id_producto, p.modelo, p.precio_actual, p.imagen,
             COALESCE(k.saldo_total, 0) as stock
             FROM tb_productos p
             LEFT JOIN tb_kardex k ON k.id_kardex = (
                 SELECT MAX(id_kardex) FROM tb_kardex WHERE id_producto = p.id_producto
             )
             WHERE p.inactive_at IS NULL
             LIMIT ? OFFSET ?"
        );
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        $stmt->close();
        return $productos;
    }

    public static function contarProductos($conexion) {
        $result = $conexion->query("SELECT COUNT(*) as total FROM tb_productos WHERE inactive_at IS NULL");
        return (int)$result->fetch_assoc()['total'];
    }
}
