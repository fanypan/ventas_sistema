<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Compras</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        .table th { background-color: #f4f4f4; }
        .text-right { text-align: right; }
        .header { margin-bottom: 30px; text-align: center; }
        .summary { font-weight: bold; font-size: 14px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reporte de Compras</h2>
        <p>Desde: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - Hasta: {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Referencia</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Estado</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($purchases as $purchase)
            @php $grandTotal += $purchase->grand_total; @endphp
            <tr>
                <td>{{ $purchase->reference_no ?? 'C-' . $purchase->id }}</td>
                <td>{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $purchase->supplier->supplier_name ?? 'N/A' }}</td>
                <td>{{ $purchase->status }}</td>
                <td class="text-right">{{ money($purchase->total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        Total Compras Registradas: {{ money($grandTotal) }}
    </div>
</body>
</html>
