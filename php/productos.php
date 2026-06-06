<?php
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/../models/Producto.php";

$action = $action ?? $_GET['action'] ?? '';

if ($action === 'insertar') {

    $modelo = trim($_POST['modelo'] ?? '');
    $precio = (float)($_POST['precio'] ?? 0);
    $id_almacen = (int)($_POST['id_almacen'] ?? 0);

    if (!$modelo || $precio <= 0) {
        header("Location: ../index.php?vista=productos&msg=error&detalle=datos_invalidos");
        exit;
    }

    if (Producto::existeModelo($conexion, $modelo)) {
        header("Location: ../index.php?vista=productos&msg=error&detalle=producto_duplicado");
        exit;
    }

    $id_marca = 1;
    $id_tipo = 1;
    $id_unidad = 1;
    $imagen = null;

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {

        $extension_permitida = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));

        if (in_array($extension, $extension_permitida)) {

            $nombre = strtolower(
                preg_replace('/[^a-z0-9_\-]/i', '_', $modelo)
            ) . '_' . time() . '.' . $extension;

            if (move_uploaded_file(
                $_FILES['imagen']['tmp_name'],
                __DIR__ . '/../img/' . $nombre
            )) {
                $imagen = $nombre;
            }
        }
    }

    // Registrar producto
    Producto::insertar(
        $conexion,
        $id_marca,
        $id_tipo,
        $id_unidad,
        $modelo,
        $precio,
        $imagen
    );

    // Obtener ID del producto recién creado
    $id_producto = $conexion->insert_id;

    // Registrar almacén en kardex
    if ($id_almacen > 0) {

        $stmt = $conexion->prepare("
            INSERT INTO tb_kardex
            (
                id_producto,
                id_almacen,
                id_tipooperacion,
                fecha,
                cantidad,
                saldo_total,
                valor_unico_historico,
                iduser_create
            )
            VALUES (?, ?, 1, CURDATE(), 1, 1, ?, 1)
        ");

        $stmt->bind_param(
            "iid",
            $id_producto,
            $id_almacen,
            $precio
        );

        $stmt->execute();
        $stmt->close();
    }

    header("Location: ../index.php?vista=productos&msg=registrado");
    exit;
} elseif ($action === 'desactivar') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) Producto::desactivar($conexion, $id);
    header("Location: ../index.php?vista=productos&msg=desactivado");
    exit;

} elseif ($action === 'reactivar') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) Producto::reactivar($conexion, $id);
    header("Location: ../index.php?vista=productos&msg=reactivado");
    exit;

} elseif ($action === 'listar') {
    return [
        'activos'   => Producto::listarActivos($conexion),
        'inactivos' => Producto::listarInactivos($conexion),
    ];
}
