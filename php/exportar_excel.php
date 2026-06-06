<?php
require_once __DIR__ . '/conexion.php';

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=reporte_kardex.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1'>";
echo "<tr>
<th>ID</th>
<th>Producto</th>
<th>Tipo</th>
<th>Cantidad</th>
<th>Saldo</th>
<th>Precio</th>
<th>Almacén</th>
<th>Fecha</th>
</tr>";

$sql = "SELECT k.*, p.modelo, a.nombre_almacen
        FROM tb_kardex k
        INNER JOIN tb_productos p ON p.id_producto = k.id_producto
        INNER JOIN tb_almacenes a ON a.id_almacen = k.id_almacen";

$result = $conexion->query($sql);

while ($row = $result->fetch_assoc()) {

    $tipo = ($row['id_tipooperacion'] == 1) ? "Entrada" : "Salida";

    echo "<tr>
        <td>{$row['id_kardex']}</td>
        <td>{$row['modelo']}</td>
        <td>{$tipo}</td>
        <td>{$row['cantidad']}</td>
        <td>{$row['saldo_total']}</td>
        <td>{$row['valor_unico_historico']}</td>
        <td>{$row['nombre_almacen']}</td>
        <td>{$row['create_at']}</td>
    </tr>";
}

echo "</table>";
exit;