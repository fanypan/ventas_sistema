<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a2e; }
        h2 { color: #1d4ed8; font-size: 18px; margin-bottom: 4px; }
        .subtitle { color: #555; font-size: 10px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #1d4ed8; color: white; padding: 7px 10px; text-align: left; font-size: 9px; text-transform: uppercase; }
        td { padding: 6px 10px; border-bottom: 1px solid #f0f0f0; font-size: 10px; }
        tr:nth-child(even) td { background: #eff6ff; }
        .grand-total td { font-weight: bold; background: #dbeafe; border-top: 2px solid #1d4ed8; }
    </style>
</head>
<body>
    <h2>📦 Reporte de Ventas por Producto</h2>
    <div class="subtitle">
        Período: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        &nbsp;|&nbsp; Generado: {{ now()->format('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Código</th>
                <th>Unidades Vendidas</th>
                <th>Precio Unitario</th>
                <th>Total Recaudado</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; $grandUnits = 0; $i = 1; @endphp
            @foreach($details as $productId => $group)
            @php
                $prod    = $group->first()->product;
                $units   = $group->sum('quantity');
                $revenue = $group->sum(fn($d) => $d->quantity * $d->price);
                $grandTotal += $revenue;
                $grandUnits += $units;
            @endphp
            <tr>
                <td>{{ $i++ }}</td>
                <td><strong>{{ $prod->description ?? 'N/A' }}</strong><br><small>{{ $prod->brand ?? '' }}</small></td>
                <td>{{ $prod->code ?? '-' }}</td>
                <td><strong>{{ $units }}</strong></td>
                <td>{{ money($group->first()->price) }}</td>
                <td><strong>{{ money($revenue) }}</strong></td>
            </tr>
            @endforeach
            <tr class="grand-total">
                <td colspan="3">TOTALES</td>
                <td>{{ $grandUnits }}</td>
                <td>—</td>
                <td>{{ money($grandTotal) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
