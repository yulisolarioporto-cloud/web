<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login_view.php");
    exit;
}

$ruta_fpdf = __DIR__ . '/fpdf186/fpdf.php';

if (!file_exists($ruta_fpdf)) {
    die("<h2 style='color:red;font-family:Arial'>Error: Librería FPDF no encontrada.</h2>");
}

require_once __DIR__ . "/conexion.php";
require_once $ruta_fpdf;

// ==========================
// CREAR PDF
// ==========================

$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 15);

// ==========================
// ENCABEZADO
// ==========================

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'REPORTE DE KARDEX - DELATEL', 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 7, 'Fecha de generacion: ' . date('d/m/Y H:i:s'), 0, 1, 'R');

$pdf->Ln(4);

// ==========================
// CABECERA TABLA
// ==========================

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(75, 0, 130);
$pdf->SetTextColor(255, 255, 255);

$pdf->Cell(12, 8, 'ID', 1, 0, 'C', true);
$pdf->Cell(15, 8, 'Prod', 1, 0, 'C', true);
$pdf->Cell(55, 8, 'Producto', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Marca', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Almacen', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Movimiento', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Cant.', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Saldo', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Precio', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Fecha', 1, 1, 'C', true);

// ==========================
// CONSULTA
// ==========================

$sql = "
SELECT
    k.id_kardex,
    p.id_producto,
    p.modelo,
    COALESCE(m.marca,'N/A') AS marca,
    COALESCE(a.nombre_almacen,'Sin almacén') AS nombre_almacen,
    COALESCE(t.descripcion,'N/A') AS movimiento,
    k.cantidad,
    k.saldo_total,
    k.valor_unico_historico,
    DATE_FORMAT(k.create_at,'%d/%m/%Y %H:%i') AS fecha

FROM tb_kardex k

INNER JOIN tb_productos p
    ON k.id_producto = p.id_producto

LEFT JOIN tb_marca m
    ON p.id_marca = m.id_marca

LEFT JOIN tb_almacen a
    ON k.id_almacen = a.id_almacen

LEFT JOIN tb_tipooperacion t
    ON k.id_tipooperacion = t.id_tipooperacion

ORDER BY k.id_kardex DESC
";

$result = $conexion->query($sql);

// ==========================
// DATOS
// ==========================

$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(0, 0, 0);

$fill = false;
$totalRegistros = 0;

while ($row = $result->fetch_assoc()) {

    $pdf->SetFillColor(
        $fill ? 245 : 255,
        $fill ? 245 : 255,
        $fill ? 245 : 255
    );

    $pdf->Cell(
        12,
        7,
        $row['id_kardex'],
        1,
        0,
        'C',
        $fill
    );

    $pdf->Cell(
        15,
        7,
        $row['id_producto'],
        1,
        0,
        'C',
        $fill
    );

    $pdf->Cell(
        55,
        7,
        utf8_decode(substr($row['modelo'], 0, 30)),
        1,
        0,
        'L',
        $fill
    );

    $pdf->Cell(
        30,
        7,
        utf8_decode(substr($row['marca'], 0, 15)),
        1,
        0,
        'L',
        $fill
    );

    $pdf->Cell(
        40,
        7,
        utf8_decode(substr($row['nombre_almacen'], 0, 25)),
        1,
        0,
        'L',
        $fill
    );

    $pdf->Cell(
        35,
        7,
        utf8_decode(substr($row['movimiento'], 0, 20)),
        1,
        0,
        'C',
        $fill
    );

    $pdf->Cell(
        20,
        7,
        $row['cantidad'],
        1,
        0,
        'C',
        $fill
    );

    $pdf->Cell(
        20,
        7,
        $row['saldo_total'],
        1,
        0,
        'C',
        $fill
    );

    $pdf->Cell(
        25,
        7,
        'S/. ' . number_format((float)$row['valor_unico_historico'], 2),
        1,
        0,
        'R',
        $fill
    );

    $pdf->Cell(
        35,
        7,
        $row['fecha'],
        1,
        1,
        'C',
        $fill
    );

    $fill = !$fill;
    $totalRegistros++;
}

// ==========================
// RESUMEN
// ==========================

$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(
    0,
    6,
    'Total de movimientos registrados: ' . $totalRegistros,
    0,
    1,
    'L'
);

$pdf->Ln(3);

$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(120, 120, 120);

$pdf->Cell(
    0,
    5,
    'Generado automaticamente por DELATEL',
    0,
    1,
    'C'
);

// ==========================
// DESCARGAR PDF
// ==========================

$pdf->Output(
    'D',
    'Reporte_Kardex_' . date('Ymd_His') . '.pdf'
);
?>