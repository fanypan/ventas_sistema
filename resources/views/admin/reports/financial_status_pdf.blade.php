<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estado de Resultados</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #333; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h2 { margin-bottom: 5px; color: #000; text-transform: uppercase; }
        .header p { margin-top: 0; color: #555; }
        .report-box { width: 80%; margin: 0 auto; border: 1px solid #ccc; padding: 20px; border-radius: 5px; }
        .line-item { display: table; width: 100%; margin-bottom: 10px; border-bottom: 1px dashed #eee; padding-bottom: 5px; }
        .label { display: table-cell; text-align: left; font-weight: bold; width: 70%; }
        .amount { display: table-cell; text-align: right; width: 30%; }
        .subtotal { border-top: 1px solid #999; margin-top: 15px; padding-top: 10px; font-size: 14px; }
        .total { border-top: 2px solid #000; margin-top: 20px; padding-top: 10px; font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .total-amount { font-size: 18px; color: #000; }
        .text-success { color: green; }
        .text-danger { color: red; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Estado de Resultados y Rentabilidad Global</h2>
        <p>Período: {{ \Carbon\Carbon::parse($startDate)->format('d - M - Y') }} al {{ \Carbon\Carbon::parse($endDate)->format('d - M - Y') }}</p>
    </div>

    <div class="report-box">
        <!-- Ingresos -->
        <h3 style="margin-top:0; color:#333; border-bottom:1px solid #ccc; padding-bottom:5px;">Ingresos Operativos</h3>
        <div class="line-item">
            <div class="label">Ventas Netas Realizadas</div>
            <div class="amount">{{ money($salesTotal) }}</div>
        </div>

        <!-- Costos -->
        <h3 style="margin-top:30px; color:#333; border-bottom:1px solid #ccc; padding-bottom:5px;">Costos Directos</h3>
        <div class="line-item">
            <div class="label">Costo de Mercadería (Compras)</div>
            <div class="amount text-danger">-{{ money($purchasesTotal) }}</div>
        </div>

        <!-- Utilidad Bruta -->
        <div class="line-item subtotal">
            <div class="label">Utilidad Bruta</div>
            <div class="amount {{ $utilidadBruta >= 0 ? 'text-success' : 'text-danger' }}">
                {{ money($utilidadBruta) }}
            </div>
        </div>

        <!-- Gastos -->
        <h3 style="margin-top:30px; color:#333; border-bottom:1px solid #ccc; padding-bottom:5px;">Gastos Operativos</h3>
        <div class="line-item">
            <div class="label">Gastos Generales / Administrativos</div>
            <div class="amount text-danger">-{{ money($expensesTotal) }}</div>
        </div>

        <!-- Utilidad Neta -->
        <div class="line-item total">
            <div class="label">Utilidad Neta del Ejercicio</div>
            <div class="amount total-amount {{ $utilidadNeta >= 0 ? 'text-success' : 'text-danger' }}">
                {{ money($utilidadNeta) }}
            </div>
        </div>
        
        <div style="margin-top: 30px; text-align: center; color: #777; font-size: 11px;">
            <p>Margen de Utilidad Neta Estimado: {{ $salesTotal > 0 ? number_format(($utilidadNeta / $salesTotal) * 100, 2) : 0 }}%</p>
            <p>Documento generado el {{ date('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
