<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir código de barras - {{ $product->description }}</title>
    <link rel="stylesheet" href="{{ asset('template/admin/plugins/fontawesome-free/css/all.min.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f6f9; color: #1f2937; margin: 0; padding: 24px; }
        .card { max-width: 720px; margin: 0 auto 24px; background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        h1 { font-size: 1.4rem; margin: 0 0 16px; }
        .info { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
        .info div { background: #f8fafc; padding: 12px; border-radius: 6px; }
        .info label { display: block; font-size: .75rem; color: #64748b; text-transform: uppercase; }
        .controls { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; margin: 16px 0; }
        .preview { text-align: center; padding: 16px; background: #fff; border: 1px dashed #cbd5e1; }
        #print-area { display: none; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            #print-area { display: flex; flex-wrap: wrap; }
            .label-item { width: 50mm; padding: 4mm; text-align: center; page-break-inside: avoid; }
            .label-title { font-size: 10px; font-weight: 700; }
            .label-price { font-size: 11px; font-weight: 700; }
        }
    </style>
</head>
<body>
    <div class="card no-print">
        <h1><i class="fas fa-barcode"></i> Imprimir etiquetas</h1>
        <div class="info">
            <div>
                <label>Producto</label>
                <strong>{{ $product->description }}</strong>
            </div>
            <div>
                <label>Código</label>
                <strong>{{ $product->code }}</strong>
            </div>
        </div>
        <div class="controls">
            <div>
                <label for="quantity">Cantidad</label><br>
                <input type="number" id="quantity" min="1" max="100" value="12" class="form-control" style="padding:6px 10px;">
            </div>
            <label><input type="checkbox" id="show-desc" checked> Descripción</label>
            <label><input type="checkbox" id="show-price" checked> Precio ({{ money($product->price) }})</label>
            <button type="button" id="btn-print" style="background:#007bff;color:#fff;border:0;padding:8px 16px;border-radius:4px;cursor:pointer;">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="{{ route('products.index') }}">Volver</a>
        </div>
        <div class="preview">
            <div id="preview-label">
                <div class="label-title-preview" style="font-size:12px;font-weight:700;margin-bottom:6px;">{{ $product->description }}</div>
                <svg id="barcode-preview"></svg>
                <div class="label-price-preview" style="font-size:13px;font-weight:700;margin-top:6px;">{{ money($product->price) }}</div>
            </div>
        </div>
    </div>
    <div id="print-area"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const codeVal = @json($product->code);
            const descVal = @json($product->description);
            const priceVal = @json(money($product->price));

            function updatePreview() {
                document.querySelector('.label-title-preview').style.display =
                    document.getElementById('show-desc').checked ? 'block' : 'none';
                document.querySelector('.label-price-preview').style.display =
                    document.getElementById('show-price').checked ? 'block' : 'none';
                JsBarcode('#barcode-preview', codeVal, {
                    format: 'CODE128', lineColor: '#000', width: 2, height: 50, displayValue: true, fontSize: 14
                });
            }

            document.getElementById('btn-print').addEventListener('click', function () {
                const qty = parseInt(document.getElementById('quantity').value, 10) || 1;
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
                    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    svg.id = 'barcode-print-' + i;
                    item.appendChild(svg);
                    if (showPrice) {
                        const price = document.createElement('div');
                        price.className = 'label-price';
                        price.innerText = priceVal;
                        item.appendChild(price);
                    }
                    printArea.appendChild(item);
                    JsBarcode('#barcode-print-' + i, codeVal, {
                        format: 'CODE128', lineColor: '#000', width: 1.5, height: 40, displayValue: true, fontSize: 11
                    });
                }
                window.print();
            });

            document.getElementById('show-desc').addEventListener('change', updatePreview);
            document.getElementById('show-price').addEventListener('change', updatePreview);
            updatePreview();
        });
    </script>
</body>
</html>
