<?php
session_start();
if (empty($_SESSION['active'])) {
    header('location: ../');
    exit;
}
include "../conexion.php";

// Obtener datos para los filtros
$query_cat = mysqli_query($conection, "SELECT idcategoria, nombre FROM categoria WHERE status = 1 ORDER BY nombre ASC");
$query_mar = mysqli_query($conection, "SELECT idmarca, nombre FROM marca WHERE status = 1 ORDER BY nombre ASC");
$query_tam = mysqli_query($conection, "SELECT idtamano, nombre FROM tamano WHERE status = 1 ORDER BY nombre ASC");

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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php include "includes/scripts.php"; ?>
    <title>Reporte de Inventarios</title>
    <style>
        .filter-box {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto;
            gap: 15px;
            align-items: flex-end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            font-weight: 600;
            color: #4a5568;
            font-size: 0.9rem;
        }
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            background: #fff;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .filter-group select:focus {
            border-color: #3182ce;
        }
        .btn-actions {
            display: flex;
            gap: 10px;
        }
        .btn-report {
            padding: 9px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .btn-report:hover {
            opacity: 0.9;
        }
        .btn-search { background-color: #3182ce; color: white; }
        .btn-excel { background-color: #276749; color: white; }
        .btn-print { background-color: #4a5568; color: white; }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .summary-card {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #3182ce;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .summary-card.inversion { border-left-color: #276749; }
        .summary-card.price { border-left-color: #dd6b20; }
        
        .summary-card label {
            display: block;
            font-size: 0.8rem;
            color: #718096;
            text-transform: uppercase;
            font-weight: bold;
        }
        .summary-card span {
            font-size: 1.3rem;
            font-weight: bold;
            color: #2d3748;
        }

        .table-responsive {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        .table-responsive table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-responsive th {
            background-color: #f7fafc;
            color: #4a5568;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            padding: 12px 15px;
            border-bottom: 2px solid #e2e8f0;
            text-align: left;
        }
        .table-responsive td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #2d3748;
            font-size: 0.95rem;
        }
        .table-responsive tr:last-child td {
            border-bottom: none;
        }

        @media print {
            header, nav, .filter-box, .btn-actions, #btn-print-action {
                display: none !important;
            }
            body {
                background: white;
                color: black;
                padding: 0;
            }
            #container {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .table-responsive {
                box-shadow: none;
                border: none;
            }
            .table-responsive th {
                background-color: #eee !important;
                color: #000 !important;
                border-bottom: 2px solid #000 !important;
            }
            .table-responsive td {
                border-bottom: 1px solid #ccc !important;
            }
            .print-title {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
            }
        }
        .print-title {
            display: none;
        }
    </style>
</head>
<body>
    <?php include "includes/header.php"; ?>
    <section id="container">
        
        <div class="print-title">
            <h2>REPORTE DE INVENTARIO - <?php echo date('d/m/Y'); ?></h2>
        </div>

        <h1 class="no-print"><i class="fas fa-file-invoice"></i> Reporte de Inventario</h1>
        
        <!-- Filter Panel -->
        <div class="filter-box no-print">
            <form method="GET" action="" class="filter-form">
                <div class="filter-group">
                    <label for="categoria">Categoría</label>
                    <select name="categoria" id="categoria">
                        <option value="0">-- Todos --</option>
                        <?php while ($cat = mysqli_fetch_assoc($query_cat)) { ?>
                            <option value="<?php echo $cat['idcategoria']; ?>" <?php echo $cat_filter == $cat['idcategoria'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="marca">Marca</label>
                    <select name="marca" id="marca">
                        <option value="0">-- Todos --</option>
                        <?php while ($mar = mysqli_fetch_assoc($query_mar)) { ?>
                            <option value="<?php echo $mar['idmarca']; ?>" <?php echo $mar_filter == $mar['idmarca'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($mar['nombre']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="tamano">Tamaño</label>
                    <select name="tamano" id="tamano">
                        <option value="0">-- Todos --</option>
                        <?php while ($tam = mysqli_fetch_assoc($query_tam)) { ?>
                            <option value="<?php echo $tam['idtamano']; ?>" <?php echo $tam_filter == $tam['idtamano'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tam['nombre']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="stock">Existencia</label>
                    <select name="stock" id="stock">
                        <option value="" <?php echo $stk_filter == '' ? 'selected' : ''; ?>>-- Todos --</option>
                        <option value="con" <?php echo $stk_filter == 'con' ? 'selected' : ''; ?>>Con Existencia</option>
                        <option value="sin" <?php echo $stk_filter == 'sin' ? 'selected' : ''; ?>>Stock Cero (Agotado)</option>
                    </select>
                </div>

                <div class="btn-actions">
                    <button type="submit" class="btn-report btn-search"><i class="fas fa-filter"></i> Filtrar</button>
                    
                    <?php
                    // Query string for exports
                    $q_str = "categoria=$cat_filter&marca=$mar_filter&tamano=$tam_filter&stock=$stk_filter";
                    ?>
                    <a href="descargarExcelInventario.php?<?php echo $q_str; ?>" class="btn-report btn-excel"><i class="far fa-file-excel"></i> Excel</a>
                    <button type="button" onclick="window.print();" class="btn-report btn-print"><i class="fas fa-print"></i> Imprimir</button>
                </div>
            </form>
        </div>

        <?php
        // Calulate Totals
        $total_productos = 0;
        $total_items = 0;
        $total_inversion = 0;
        $productos_list = [];
        
        if ($query_productos && mysqli_num_rows($query_productos) > 0) {
            while ($row = mysqli_fetch_assoc($query_productos)) {
                $total_productos++;
                $total_items += $row['existencia'];
                $total_inversion += $row['inversion_total'];
                $productos_list[] = $row;
            }
        }
        ?>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <label>Total de Productos Distintos</label>
                <span><?php echo $total_productos; ?></span>
            </div>
            <div class="summary-card price">
                <label>Total Unidades en Stock</label>
                <span><?php echo $total_items; ?></span>
            </div>
            <div class="summary-card inversion">
                <label>Inversión Total Estimada</label>
                <span><?php echo $moneda . ' ' . formatCant($total_inversion); ?></span>
            </div>
        </div>

        <!-- Result Table -->
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th>Marca</th>
                        <th>Tamaño</th>
                        <th style="text-align: center;">Stock</th>
                        <th style="text-align: right;">Costo Unit.</th>
                        <th style="text-align: right;">Precio Unit.</th>
                        <th style="text-align: right;">Val. Inversión</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($productos_list) > 0) { ?>
                        <?php foreach ($productos_list as $prod) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prod['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($prod['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($prod['categoria'] ?? 'Sin categoría'); ?></td>
                                <td><?php echo htmlspecialchars($prod['marca'] ?? 'Sin marca'); ?></td>
                                <td><?php echo htmlspecialchars($prod['tamano'] ?? 'Sin tamaño'); ?></td>
                                <td style="text-align: center; font-weight: bold; <?php echo $prod['existencia'] <= 0 ? 'color: red;' : ''; ?>">
                                    <?php echo $prod['existencia']; ?>
                                </td>
                                <td style="text-align: right;"><?php echo $moneda . ' ' . formatCant($prod['costo']); ?></td>
                                <td style="text-align: right;"><?php echo $moneda . ' ' . formatCant($prod['precio']); ?></td>
                                <td style="text-align: right; font-weight: 600;"><?php echo $moneda . ' ' . formatCant($prod['inversion_total']); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: #718096; padding: 20px;">
                                No se encontraron productos que coincidan con los filtros aplicados.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </section>
    <?php include "includes/footer.php"; ?>
</body>
</html>
