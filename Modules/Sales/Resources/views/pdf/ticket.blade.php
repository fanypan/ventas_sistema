<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket #{{ $sale->id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            width: 200px; /* Adjust based on printer width */
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
        }
        .info {
            margin-bottom: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .table th, .table td {
            text-align: left;
            padding: 2px 0;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 10px;
        }
        .divider {
            border-top: 1px dotted #000;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $settings['empresa_nombre'] ?? 'Mi Empresa' }}</h2>
        <p>{{ $settings['empresa_direccion'] ?? '' }}<br>
        Tel: {{ $settings['empresa_telefono'] ?? '' }}<br>
        RUC: {{ $settings['empresa_ruc'] ?? '' }}</p>
    </div>

    <div class="info">
        Ticket: #{{ $sale->id }}<br>
        Fecha: {{ $sale->created_at->format('d/m/Y H:i') }}<br>
        Cliente: {{ $sale->customer->name ?? 'Consumidor Final' }}<br>
        Vendedor: {{ $sale->creator->name ?? 'Admin' }}
    </div>

    <div class="divider"></div>

    <table class="table">
        <thead>
            <tr>
                <th>PROD</th>
                <th class="text-right">CANT</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->details as $detail)
            <tr>
                <td colspan="3">{{ $detail->product->name }}</td>
            </tr>
            <tr>
                <td></td>
                <td class="text-right">{{ $detail->quantity }} x {{ number_format($detail->price, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($detail->quantity * $detail->price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table class="table">
        <tr>
            <td>SUBTOTAL:</td>
            <td class="text-right">{{ number_format($sale->total + $sale->discount, 0, ',', '.') }} Gs.</td>
        </tr>
        @if($sale->discount > 0)
        <tr>
            <td>DESCUENTO:</td>
            <td class="text-right">-{{ number_format($sale->discount, 0, ',', '.') }} Gs.</td>
        </tr>
        @endif
        <tr style="font-weight: bold; font-size: 14px;">
            <td>TOTAL:</td>
            <td class="text-right">{{ number_format($sale->total, 0, ',', '.') }} Gs.</td>
        </tr>
        @php
            $paymentLabels = [
                'efectivo' => 'Efectivo',
                'tarjeta'  => 'Tarjeta',
                'transferencia' => 'Transferencia',
                'credito'  => 'Crédito',
            ];
        @endphp
        <tr>
            <td>FORMA DE PAGO:</td>
            <td class="text-right">{{ $paymentLabels[$sale->payment_type] ?? ucfirst($sale->payment_type ?? 'N/A') }}</td>
        </tr>
        @if($sale->payment_type === 'efectivo' && $sale->payment_with > 0)
        <tr>
            <td>PAGA CON:</td>
            <td class="text-right">{{ number_format($sale->payment_with, 0, ',', '.') }} Gs.</td>
        </tr>
        <tr style="font-weight: bold;">
            <td>VUELTO:</td>
            <td class="text-right">{{ number_format($sale->change, 0, ',', '.') }} Gs.</td>
        </tr>
        @endif
    </table>

    <div class="footer">
        <p>¡Gracias por su compra!<br>
        {{ $settings['company_website'] ?? '' }}</p>
    </div>
</body>
</html>
