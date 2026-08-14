<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h2 { margin: 0 0 4px; }
        .muted { color: #555; font-size: 10px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3a5f; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; }
        .right { text-align: right; }
        .total { font-weight: bold; background: #f1f5f9; }
    </style>
</head>
<body>
    <h2>Estado de cuenta — {{ $customer->name }}</h2>
    <div class="muted">
        {{ $settings['company_name'] ?? '' }}
        @if($from || $to)
            · Periodo: {{ $from ?: 'inicio' }} al {{ $to ?: 'hoy' }}
        @endif
        · Generado: {{ now()->format('d/m/Y H:i') }}
    </div>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Descripción</th>
                <th class="right">Cargo</th>
                <th class="right">Abono</th>
                <th class="right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                <td>{{ $row['description'] }}</td>
                <td class="right">{{ $row['cargo'] ? money($row['cargo']) : '' }}</td>
                <td class="right">{{ $row['abono'] ? money($row['abono']) : '' }}</td>
                <td class="right">{{ money($row['saldo']) }}</td>
            </tr>
            @endforeach
            <tr class="total">
                <td colspan="4">Saldo</td>
                <td class="right">{{ money($saldo) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
