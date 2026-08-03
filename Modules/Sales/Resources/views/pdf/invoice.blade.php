<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura #{{ $sale->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .container {
            padding: 20px;
        }
        .header {
            width: 100%;
            margin-bottom: 20px;
        }
        .header td {
            vertical-align: top;
        }
        .company-info h1 {
            margin: 0;
            color: #007bff;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info h2 {
            margin: 0;
            color: #444;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .details-table th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
        }
        .details-table td {
            border: 1px solid #dee2e6;
            padding: 10px;
        }
        .totals-table {
            width: 300px;
            float: right;
            margin-top: 20px;
        }
        .totals-table td {
            padding: 5px;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <table class="header">
            <tr>
                <td class="company-info">
                    <h1>{{ $settings['empresa_nombre'] ?? 'Mi Empresa' }}</h1>
                    <p>
                        RUC: {{ $settings['empresa_ruc'] ?? '' }}<br>
                        {{ $settings['empresa_direccion'] ?? '' }}<br>
                        Tel: {{ $settings['empresa_telefono'] ?? '' }}<br>
                        Email: {{ $settings['empresa_email'] ?? '' }}
                    </p>
                </td>
                <td class="invoice-info">
                    <h2>FACTURA</h2>
                    <p>
                        N°: 001-001-{{ str_pad($sale->id, 7, '0', STR_PAD_LEFT) }}<br>
                        Fecha: {{ $sale->created_at->format('d/m/Y') }}<br>
                        Condición: {{ $sale->payment_type == 'credit' ? 'Crédito' : 'Contado' }}
                    </p>
                </td>
            </tr>
        </table>

        <div style="border: 1px solid #dee2e6; padding: 10px; margin-bottom: 20px;">
            <strong>Cliente:</strong> {{ $sale->customer->name ?? 'Consumidor Final' }}<br>
            <strong>RUC/CI:</strong> {{ $sale->customer->tax_id ?? 'N/A' }}<br>
            <strong>Dirección:</strong> {{ $sale->customer->address ?? 'N/A' }}
        </div>

        <table class="details-table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th class="text-right">Cant.</th>
                    <th class="text-right">Precio Unit.</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->details as $detail)
                <tr>
                    <td>{{ $detail->product->name }}</td>
                    <td class="text-right">{{ $detail->quantity }}</td>
                    <td class="text-right">{{ number_format($detail->price, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detail->quantity * $detail->price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td><strong>SUBTOTAL:</strong></td>
                <td class="text-right">{{ number_format($sale->total + $sale->discount, 0, ',', '.') }} Gs.</td>
            </tr>
            @if($sale->discount > 0)
            <tr>
                <td><strong>DESCUENTO:</strong></td>
                <td class="text-right">-{{ number_format($sale->discount, 0, ',', '.') }} Gs.</td>
            </tr>
            @endif
            <tr style="font-size: 16px; background-color: #f8f9fa;">
                <td><strong>TOTAL:</strong></td>
                <td class="text-right"><strong>{{ number_format($sale->total, 0, ',', '.') }} Gs.</strong></td>
            </tr>
        </table>

        <div style="clear: both;"></div>

        <div class="footer">
            <p>Original: Cliente | Duplicado: Archivo Tributario</p>
            <p>{{ $settings['company_invoice_footer'] ?? 'Gracias por su preferencia' }}</p>
        </div>
    </div>
</body>
</html>
