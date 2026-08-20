<?php
session_start();
if (empty($_SESSION['active'])) {
    header('location: ../');
    exit;
}

$code = isset($_GET['code']) ? $_GET['code'] : '';
$desc = isset($_GET['desc']) ? $_GET['desc'] : '';
$price = isset($_GET['price']) ? $_GET['price'] : '';

if (empty($code)) {
    echo "Código de producto no especificado.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Código de Barras - <?php echo htmlspecialchars($desc); ?></title>
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- JsBarcode Library -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-color: #f8fafc;
            --accent-color: #6366f1;
            --accent-hover: #4f46e5;
            --border-color: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            width: 100%;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            margin-bottom: 30px;
        }

        h1 {
            font-size: 1.8rem;
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .info-card label {
            display: block;
            font-size: 0.85rem;
            color: #94a3b8;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .info-card span {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .controls {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .form-group {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
        }

        .form-control {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-control label {
            font-size: 0.9rem;
            font-weight: 600;
        }

        input[type="number"] {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-color);
            padding: 10px 15px;
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            width: 100px;
            outline: none;
            transition: border-color 0.3s;
        }

        input[type="number"]:focus {
            border-color: var(--accent-color);
        }

        .checkbox-group {
            display: flex;
            gap: 20px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }

        .btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s, transform 0.1s;
            margin-left: auto;
        }

        .btn:hover {
            background-color: var(--accent-hover);
        }

        .btn:active {
            transform: scale(0.98);
        }

        /* Preview area */
        .preview-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #94a3b8;
        }

        .preview-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 200px;
            border: 2px dashed var(--border-color);
        }

        /* Printable labels container */
        #print-area {
            display: none;
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                color: black;
                padding: 0;
                margin: 0;
                display: block;
                min-height: auto;
            }

            .no-print {
                display: none !important;
            }

            #print-area {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
                padding: 10px;
                width: 100%;
                box-sizing: border-box;
            }

            .label-item {
                border: 1px solid #ccc;
                padding: 10px;
                text-align: center;
                background: white;
                page-break-inside: avoid;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                box-sizing: border-box;
                height: 120px; /* Standard label height */
            }

            .label-title {
                font-size: 10px;
                font-weight: bold;
                margin-bottom: 2px;
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                color: #000;
            }

            .label-price {
                font-size: 11px;
                font-weight: bold;
                margin-top: 2px;
                color: #000;
            }

            .label-barcode {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="container no-print">
        <h1><i class="fas fa-barcode"></i> Imprimir Etiquetas de Código de Barras</h1>

        <div class="info-grid">
            <div class="info-card">
                <label>Producto / Variante</label>
                <span><?php echo htmlspecialchars($desc); ?></span>
            </div>
            <div class="info-card">
                <label>Código Registrado</label>
                <span><?php echo htmlspecialchars($code); ?></span>
            </div>
        </div>

        <div class="controls">
            <div class="form-group">
                <div class="form-control">
                    <label for="quantity">Cantidad de Copias</label>
                    <input type="number" id="quantity" min="1" max="100" value="12">
                </div>

                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="show-desc" checked> Mostrar Descripción
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" id="show-price" checked> Mostrar Precio (<?php echo htmlspecialchars($price); ?>)
                    </label>
                </div>

                <button class="btn" id="btn-print">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>

        <div class="preview-title">Vista Previa de la Etiqueta</div>
        <div class="preview-box">
            <div style="text-align: center; color: black;" id="preview-label">
                <div class="label-title-preview" style="font-size: 12px; font-weight: bold; margin-bottom: 5px;"><?php echo htmlspecialchars($desc); ?></div>
                <svg id="barcode-preview"></svg>
                <div class="label-price-preview" style="font-size: 13px; font-weight: bold; margin-top: 5px;"><?php echo htmlspecialchars($price); ?></div>
            </div>
        </div>
    </div>

    <!-- Container for printing -->
    <div id="print-area"></div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const codeVal = "<?php echo addslashes($code); ?>";
            const descVal = "<?php echo addslashes($desc); ?>";
            const priceVal = "<?php echo addslashes($price); ?>";

            // Generate barcode preview
            function updatePreview() {
                const showDesc = document.getElementById('show-desc').checked;
                const showPrice = document.getElementById('show-price').checked;

                document.querySelector('.label-title-preview').style.display = showDesc ? 'block' : 'none';
                document.querySelector('.label-price-preview').style.display = showPrice ? 'block' : 'none';

                try {
                    JsBarcode("#barcode-preview", codeVal, {
                        format: "CODE128",
                        lineColor: "#000",
                        width: 2,
                        height: 50,
                        displayValue: true,
                        fontSize: 14
                    });
                } catch (e) {
                    console.error("Error generating barcode preview:", e);
                }
            }

            // Generate full printable grid
            function generatePrintGrid() {
                const qty = parseInt(document.getElementById('quantity').value) || 1;
                const showDesc = document.getElementById('show-desc').checked;
                const showPrice = document.getElementById('show-price').checked;
                const printArea = document.getElementById('print-area');

                printArea.innerHTML = '';

                for (let i = 0; i < qty; i++) {
                    const item = document.createElement('div');
                    item.className = 'label-item';

                    if (showDesc) {
                        const title = document.createElement('div');
                        title.className = 'label-title';
                        title.innerText = descVal;
                        item.appendChild(title);
                    }

                    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                    svg.className = 'label-barcode';
                    svg.id = `barcode-print-${i}`;
                    item.appendChild(svg);

                    if (showPrice) {
                        const price = document.createElement('div');
                        price.className = 'label-price';
                        price.innerText = priceVal;
                        item.appendChild(price);
                    }

                    printArea.appendChild(item);

                    // Render code on new SVG
                    JsBarcode(`#barcode-print-${i}`, codeVal, {
                        format: "CODE128",
                        lineColor: "#000",
                        width: 1.5,
                        height: 40,
                        displayValue: true,
                        fontSize: 11
                    });
                }
            }

            // Listeners
            document.getElementById('show-desc').addEventListener('change', updatePreview);
            document.getElementById('show-price').addEventListener('change', updatePreview);

            document.getElementById('btn-print').addEventListener('click', function() {
                generatePrintGrid();
                window.print();
            });

            // Init preview
            updatePreview();
        });
    </script>
</body>
</html>
