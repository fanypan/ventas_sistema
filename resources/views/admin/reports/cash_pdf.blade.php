<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Arqueo de Caja</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        .table th { background-color: #f4f4f4; width: 50%; }
        .text-right { text-align: right; }
        .header { margin-bottom: 30px; text-align: center; }
        .summary { font-weight: bold; font-size: 14px; text-align: right; margin-top: 10px; border-top: 2px solid #000; padding-top: 5px; }
        .positive { color: green; }
        .negative { color: red; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Arqueo Diario y Flujo de Caja</h2>
        <p>Fecha de Operación: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
    </div>

    <table class="table">
        <tbody>
            <tr>
                <th>(+) Ingresos por Ventas</th>
                <td class="text-right">{{ money($salesTotal) }}</td>
            </tr>
            <tr>
                <th>(-) Egresos por Compras (Inventario)</th>
                <td class="text-right text-danger">{{ money($purchasesTotal) }}</td>
            </tr>
            <tr>
                <th>(-) Gastos Generales</th>
                <td class="text-right text-danger">{{ money($expensesTotal) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        Balance del Día (Neto): 
        <span class="{{ $net >= 0 ? 'positive' : 'negative' }}">
            {{ money($net) }}
        </span>
    </div>
</body>
</html>
