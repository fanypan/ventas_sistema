<?php
session_start();
if (empty($_SESSION['active'])) {
    header('location: ../');
    exit;
}
include "../conexion.php";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=reporte_inventario_" . date('Ymd_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Capturar filtros
$cat_filter = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$mar_filter = isset($_GET['marca']) ? intval($_GET['marca']) : 0;
$tam_filter = isset($_GET['tamano']) ? intval($_GET['tamano']) : 0;
$stk_filter = isset($_GET['stock']) ? $_GET['stock'] : '';

// Construir la consulta
$where = "p.status = 1";
if ($cat_filter > 0) {
    $where .= " AND p.categoria_id = $cat_filter";
}
if ($mar_filter > 0) {
    $where .= " AND p.marca_id = $mar_filter";
}
if ($tam_filter > 0) {
    $where .= " AND p.tamano_id = $tam_filter";
}
if ($stk_filter === 'con') {
    $where .= " AND p.existencia > 0";
} elseif ($stk_filter === 'sin') {
    $where .= " AND p.existencia <= 0";
}

$query_productos = mysqli_query($conection, "
    SELECT p.codigo, p.descripcion, p.existencia, p.costo, p.precio, 
           c.nombre as categoria, m.nombre as marca, t.nombre as tamano,
           (p.existencia * p.costo) as inversion_total
    FROM producto p
    LEFT JOIN categoria c ON p.categoria_id = c.idcategoria
    LEFT JOIN marca m ON p.marca_id = m.idmarca
    LEFT JOIN tamano t ON p.tamano_id = t.idtamano
    WHERE $where
    ORDER BY p.descripcion ASC
");

// Moneda de configuración
$query_conf = mysqli_query($conection, "SELECT moneda FROM configuracion");
$moneda = "Gs.";
if ($query_conf && mysqli_num_rows($query_conf) > 0) {
    $info_conf = mysqli_fetch_assoc($query_conf);
    $moneda = $info_conf['moneda'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th { background-color: #2e86c1; color: #ffffff; border: 1px solid #000000; padding: 10px; font-weight: bold; text-align: center; }
        td { border: 1px solid #000000; padding: 8px; }
        tr:nth-child(even) { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="9" style="background-color: #2e86c1; color: white; font-size: 20px; text-align: center; font-weight: bold; padding: 15px; border: 1px solid #000;">
                REPORTE FILTRADO DE INVENTARIO - <?php echo date('d/m/Y H:i:s'); ?>
            </td>
        </tr>
        <thead>
            <tr style="background-color: #2e86c1; color: white;">
                <th style="border: 1px solid #000; padding: 10px; font-weight: bold; background-color: #2e86c1; color: white;">Código</th>
                <th style="border: 1px solid #000; padding: 10px; font-weight: bold; background-color: #2e86c1; color: white;">Descripción</th>
                <th style="border: 1px solid #000; padding: 10px; font-weight: bold; background-color: #2e86c1; color: white;">Categoría</th>
                <th style="border: 1px solid #000; padding: 10px; font-weight: bold; background-color: #2e86c1; color: white;">Marca</th>
                <th style="border: 1px solid #000; padding: 10px; font-weight: bold; background-color: #2e86c1; color: white;">Tamaño</th>
                <th style="border: 1px solid #000; padding: 10px; font-weight: bold; background-color: #2e86c1; color: white; text-align: center;">Stock</th>
                <th style="border: 1px solid #000; padding: 10px; font-weight: bold; background-color: #2e86c1; color: white; text-align: right;">Costo Unit. (<?php echo $moneda; ?>)</th>
                <th style="border: 1px solid #000; padding: 10px; font-weight: bold; background-color: #2e86c1; color: white; text-align: right;">Precio Unit. (<?php echo $moneda; ?>)</th>
                <th style="border: 1px solid #000; padding: 10px; font-weight: bold; background-color: #2e86c1; color: white; text-align: right;">Val. Inversión (<?php echo $moneda; ?>)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_costo = 0;
            if ($query_productos && mysqli_num_rows($query_productos) > 0) {
                while ($prod = mysqli_fetch_assoc($query_productos)) {
                    $total_costo += $prod['inversion_total'];
                    ?>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px;"><?php echo $prod['codigo']; ?></td>
                        <td style="border: 1px solid #000; padding: 5px;"><?php echo $prod['descripcion']; ?></td>
                        <td style="border: 1px solid #000; padding: 5px;"><?php echo $prod['categoria'] ?? 'Sin categoría'; ?></td>
                        <td style="border: 1px solid #000; padding: 5px;"><?php echo $prod['marca'] ?? 'Sin marca'; ?></td>
                        <td style="border: 1px solid #000; padding: 5px;"><?php echo $prod['tamano'] ?? 'Sin tamaño'; ?></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"><?php echo $prod['existencia']; ?></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: right;"><?php echo formatCant($prod['costo']); ?></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: right;"><?php echo formatCant($prod['precio']); ?></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: right; font-weight: bold;"><?php echo formatCant($prod['inversion_total']); ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
            <tr style="background-color: #eaeaea; font-weight: bold;">
                <td colspan="8" style="border: 1px solid #000; padding: 10px; text-align: right; font-weight: bold;">Inversión Total de los Productos Listados:</td>
                <td style="border: 1px solid #000; padding: 10px; text-align: right; font-weight: bold;"><?php echo formatCant($total_costo); ?></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
