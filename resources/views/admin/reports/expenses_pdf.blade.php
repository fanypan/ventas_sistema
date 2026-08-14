<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; }
        h2 { margin: 0 0 4px; }
        .muted { color: #666; font-size: 9px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #2c3e50; color: #fff; padding: 6px 8px; text-align: left; font-size: 8px; }
        td { padding: 5px 8px; border: 1px solid #eee; }
        .right { text-align: right; }
        .center { text-align: center; }
        .resumen td { text-align: center; font-size: 13px; font-weight: bold; padding: 10px; }
        .badge { color: #fff; font-size: 8px; padding: 1px 4px; }
        .gasto { background: #616161; }
        .insumo { background: #1976d2; }
        .total { color: #d32f2f; }
    </style>
</head>
<body>
    <h2>Reporte de egresos</h2>
    <div class="muted">Periodo: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} · Generado: {{ now()->format('d/m/Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th class="center">Gastos generales</th>
                <th class="center">Insumos</th>
                <th class="center">Total periodo</th>
            </tr>
        </thead>
        <tbody class="resumen">
            <tr>
                <td>{{ money($totalGeneral) }}</td>
                <td>{{ money($totalInsumos) }}</td>
                <td class="total">{{ money($totalEgresos) }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Usuario</th>
                <th class="center">Cant.</th>
                <th class="right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
            <tr>
                <td>{{ \Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                <td>
                    <span class="badge {{ $expense->type === 'insumo' ? 'insumo' : 'gasto' }}">
                        {{ $expense->type === 'insumo' ? 'INSUMO' : 'GRAL' }}
                    </span>
                </td>
                <td>{{ $expense->description }}{{ $expense->insumo ? ' (' . $expense->insumo->name . ')' : '' }}</td>
                <td>{{ $expense->user->name ?? '-' }}</td>
                <td class="center">{{ $expense->quantity ? number_format($expense->quantity, 2) : '-' }}</td>
                <td class="right">{{ money($expense->amount) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="center">Sin egresos en el periodo</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
