<?php
class Producto {

    public static function existeModelo($conexion, $modelo, $excluir_id = 0) {
        $stmt = $conexion->prepare(
            "SELECT COUNT(*) as total FROM tb_productos WHERE modelo = ? AND id_producto != ? AND inactive_at IS NULL"
        );
        $stmt->bind_param("si", $modelo, $excluir_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)$row['total'] > 0;
    }

    public static function insertar($conexion, $id_marca, $id_tipo, $id_unidad, $modelo, $precio, $imagen) {
    $stmt = $conexion->prepare(
        "INSERT INTO tb_productos (id_marca, id_tipo, id_unidad, modelo, precio_actual, SeVende, iduser_create, imagen)
         VALUES (?, ?, ?, ?, ?, '1', 1, ?)"
    );

    $stmt->bind_param("iiisds", $id_marca, $id_tipo, $id_unidad, $modelo, $precio, $imagen);

    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

    public static function actualizar($conexion, $id, $modelo, $precio) {
        $stmt = $conexion->prepare(
            "UPDATE tb_productos SET modelo = ?, precio_actual = ? WHERE id_producto = ?"
        );
        $stmt->bind_param("sdi", $modelo, $precio, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function desactivar($conexion, $id) {
        $stmt = $conexion->prepare(
            "UPDATE tb_productos SET inactive_at = NOW() WHERE id_producto = ?"
        );
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function reactivar($conexion, $id) {
        $stmt = $conexion->prepare(
            "UPDATE tb_productos SET inactive_at = NULL WHERE id_producto = ?"
        );
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public static function listarActivos($conexion) {
    $data = [];

    $sql = "
        SELECT 
            p.id_producto,
            p.modelo,
            p.precio_actual,
            p.imagen,
            p.create_at,
            a.nombre_almacen
        FROM tb_productos p
        LEFT JOIN tb_kardex k 
            ON p.id_producto = k.id_producto
        LEFT JOIN tb_almacen a 
            ON k.id_almacen = a.id_almacen
        WHERE p.inactive_at IS NULL
        GROUP BY p.id_producto
        ORDER BY p.create_at DESC
    ";

    $result = $conexion->query($sql);

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

    public static function listarInactivos($conexion) {
    $data = [];

    $sql = "
        SELECT 
            p.id_producto,
            p.modelo,
            p.precio_actual,
            p.imagen,
            p.create_at,
            a.nombre_almacen
        FROM tb_productos p
        LEFT JOIN tb_kardex k 
            ON p.id_producto = k.id_producto
        LEFT JOIN tb_almacen a 
            ON k.id_almacen = a.id_almacen
        WHERE p.inactive_at IS NOT NULL
        GROUP BY p.id_producto
        ORDER BY p.inactive_at DESC
    ";

    $result = $conexion->query($sql);

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}
}
