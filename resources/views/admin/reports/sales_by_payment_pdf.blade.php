<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a2e; }
        h2 { color: #7c3aed; font-size: 18px; margin-bottom: 4px; }
        .subtitle { color: #555; font-size: 10px; margin-bottom: 12px; }
        .summary { display: table; width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .summary-box { display: table-cell; padding: 12px; text-align: center; border-radius: 6px; border: 2px solid #e5e7eb; min-width: 80px; }
        .summary-box .label { font-size: 9px; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; }
        .summary-box .value { font-size: 16px; font-weight: bold; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { padding: 7px 10px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: .5px; color: white; }
        td { padding: 6px 10px; border-bottom: 1px solid #f0f0f0; font-size: 10px; }
        tr:nth-child(even) td { background: #faf5ff; }
        .th-efectivo { background: #059669; }
        .th-tarjeta  { background: #1d4ed8; }
        .th-qr       { background: #0d9488; }
        .th-transf   { background: #d97706; }
        .th-other    { background: #6b7280; }
        .section-header { padding: 8px 10px; font-weight: bold; font-size: 10px; letter-spacing: .3px; color: white; margin-top: 14px; }
        .grand-total { font-weight: bold; background: #f5f3ff; border-top: 2px solid #7c3aed; }
    </style>
</head>
<body>
    <h2>💳 Reporte de Ventas por Tipo de Pago</h2>
    <div class="subtitle">
        Período: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        &nbsp;|&nbsp; Total ventas: {{ $sales->count() }}
        &nbsp;|&nbsp; Generado: {{ now()->format('d/m/Y H:i') }}
    </div>

    <!-- Summary por método -->
    @php
    $methodLabels = ['efectivo'=>'Efectivo', 'qr'=>'QR/Digital', 'tarjeta'=>'Tarjeta', 'transferencia'=>'Transferencia'];
    $methodColors = ['efectivo'=>'#d1fae5', 'qr'=>'#cffafe', 'tarjeta'=>'#dbeafe', 'transferencia'=>'#fef3c7'];
    @endphp

    <table>
        <thead>
            <tr>
                <th style="background:#7c3aed;">Método de Pago</th>
                <th style="background:#7c3aed;">N° Transacciones</th>
                <th style="background:#7c3aed;">Total Recaudado</th>
                <th style="background:#7c3aed;">% del Total</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = $byMethod->sum('total'); @endphp
            @foreach($byMethod as $method => $data)
            <tr>
                <td><strong>{{ $methodLabels[$method] ?? ucfirst($method) }}</strong></td>
                <td>{{ $data['count'] }}</td>
                <td><strong>{{ money($data['total']) }}</strong></td>
                <td>{{ $grandTotal > 0 ? number_format(($data['total'] / $grandTotal) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
            <tr class="grand-total">
                <td>TOTAL</td>
                <td>{{ $sales->count() }}</td>
                <td>{{ money($grandTotal) }}</td>
                <td>100%</td>
            </tr>
        </tbody>
    </table>

    <!-- Detalle de ventas -->
    <h4 style="margin-top:20px; color:#7c3aed;">Detalle de Transacciones</h4>
    <table>
        <thead>
            <tr>
                <th style="background:#4c1d95;">#Venta</th>
                <th style="background:#4c1d95;">Fecha</th>
                <th style="background:#4c1d95;">Cliente</th>
                <th style="background:#4c1d95;">Tipo de Pago</th>
                <th style="background:#4c1d95;">Total</th>
                <th style="background:#4c1d95;">Cobrado</th>
                <th style="background:#4c1d95;">Vuelto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>#{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $sale->customer->name ?? 'Público General' }}</td>
                <td>{{ $methodLabels[$sale->payment_type] ?? ucfirst($sale->payment_type) }}</td>
                <td>{{ money($sale->total) }}</td>
                <td>{{ money($sale->payment_with) }}</td>
                <td>{{ money($sale->change) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
