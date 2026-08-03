<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a2e; }
        h2 { color: #e63946; font-size: 18px; margin-bottom: 4px; }
        .subtitle { color: #555; font-size: 10px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #e63946; color: white; padding: 7px 10px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; }
        td { padding: 6px 10px; border-bottom: 1px solid #f0f0f0; }
        tr:nth-child(even) td { background: #fff5f5; }
        .badge-red    { color: #dc2626; font-weight: bold; }
        .badge-orange { color: #d97706; font-weight: bold; }
        .total-row td { font-weight: bold; background: #fee2e2; border-top: 2px solid #e63946; }
        .warning-box { background: #fef2f2; border: 1px solid #fca5a5; border-radius: 5px; padding: 8px 12px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <h2>⚠ Reporte de Stock Mínimo</h2>
    <div class="subtitle">Productos con stock ≤ {{ $threshold }} unidades &nbsp;|&nbsp; Generado: {{ now()->format('d/m/Y H:i') }}</div>

    @if($products->isEmpty())
    <div class="warning-box">
        ✅ No hay productos con stock menor o igual a {{ $threshold }} unidades.
    </div>
    @else
    <div class="warning-box">
        ⚠ Se encontraron <strong>{{ $products->count() }}</strong> producto(s) con inventario crítico.
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>P.Costo</th>
                <th>P.Venta</th>
                <th>Stock Actual</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->code }}</td>
                <td><strong>{{ $p->description }}</strong><br><small>{{ $p->brand }}</small></td>
                <td>{{ $p->category->name ?? '-' }}</td>
                <td>{{ money($p->cost) }}</td>
                <td>{{ money($p->price) }}</td>
                <td class="{{ $p->stock <= 0 ? 'badge-red' : 'badge-orange' }}">{{ $p->stock }}</td>
                <td>{{ $p->stock <= 0 ? 'AGOTADO' : 'CRÍTICO' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>
