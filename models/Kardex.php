<?php
class Kardex {

    public static function obtenerStock($conexion, $id_producto, $id_almacen) {
        $stmt = $conexion->prepare(
            "SELECT COALESCE((
                SELECT saldo_total FROM tb_kardex
                WHERE id_producto = ? AND id_almacen = ?
                ORDER BY id_kardex DESC LIMIT 1
             ), 0) as stock"
        );
        $stmt->bind_param("ii", $id_producto, $id_almacen);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)$data['stock'];
    }

    public static function registrarEntrada($conexion, $id_producto, $id_almacen, $cantidad, $valor) {
        $stmt = $conexion->prepare("CALL sp_entrada(?, ?, ?, ?)");
        $stmt->bind_param("iiid", $id_producto, $id_almacen, $cantidad, $valor);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function registrarSalida($conexion, $id_producto, $id_almacen, $cantidad, $valor) {
        $stmt = $conexion->prepare("CALL sp_salida(?, ?, ?, ?)");
        $stmt->bind_param("iiid", $id_producto, $id_almacen, $cantidad, $valor);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function listarMovimientos($conexion, $offset, $por_pagina) {
        $resultado = [];
        $stmt = $conexion->prepare(
            "SELECT k.id_kardex, p.modelo, t.descripcion as tipo_movimiento,
                    k.id_tipooperacion, k.cantidad, k.saldo_total,
                    k.valor_unico_historico, k.create_at
             FROM tb_kardex k
             INNER JOIN tb_productos p ON k.id_producto = p.id_producto
             LEFT JOIN tb_tipooperacion t ON k.id_tipooperacion = t.id_tipooperacion
             ORDER BY k.id_kardex DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->bind_param("ii", $por_pagina, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $resultado[] = $row;
        }
        $stmt->close();
        return $resultado;
    }

    public static function totalPaginas($conexion, $por_pagina) {
        $result = $conexion->query("SELECT COUNT(*) as total FROM tb_kardex");
        $total = (int)$result->fetch_assoc()['total'];
        return $total > 0 ? ceil($total / $por_pagina) : 1;
    }

    public static function obtenerProductos($conexion) {
        $productos = [];
        $result = $conexion->query(
            "SELECT id_producto, modelo, precio_actual FROM tb_productos WHERE inactive_at IS NULL ORDER BY modelo"
        );
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        return $productos;
    }

    public static function obtenerAlmacenes($conexion) {
        $almacenes = [];
        // Intentar con filtro inactive_at primero
        $result = @$conexion->query("SELECT id_almacen, nombre_almacen FROM tb_almacen WHERE inactive_at IS NULL ORDER BY nombre_almacen");
        if (!$result || $conexion->errno) {
            // Si la columna no existe, traer todos
            $result = $conexion->query("SELECT id_almacen, nombre_almacen FROM tb_almacen ORDER BY nombre_almacen");
        }
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $almacenes[] = $row;
            }
        }
        return $almacenes;
    }

    public static function listarStock($conexion) {
        $resultado = [];
        $result = $conexion->query(
            "SELECT a.nombre_almacen, p.modelo,
             COALESCE((
                 SELECT saldo_total FROM tb_kardex
                 WHERE id_producto = p.id_producto AND id_almacen = a.id_almacen
                 ORDER BY id_kardex DESC LIMIT 1
             ), 0) as stock
             FROM tb_almacen a
             CROSS JOIN tb_productos p
             WHERE p.inactive_at IS NULL AND a.inactive_at IS NULL
             ORDER BY a.nombre_almacen, p.modelo"
        );
        while ($row = $result->fetch_assoc()) {
            $resultado[] = $row;
        }
        return $resultado;
    }
}
