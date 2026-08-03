<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recibo de Pago #{{ $abono->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .receipt-box {
            border: 2px solid #333;
            padding: 15px;
            width: 100%;
        }
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
        }
        .receipt-title {
            float: right;
            font-size: 20px;
            font-weight: bold;
            color: #555;
        }
        .content {
            line-height: 1.6;
        }
        .amount-words {
            font-style: italic;
            margin-top: 10px;
            border-bottom: 1px dotted #000;
        }
        .footer {
            margin-top: 40px;
        }
        .signature {
            border-top: 1px solid #000;
            width: 200px;
            text-align: center;
            float: right;
            padding-top: 5px;
        }
        .text-right { text-align: right; }
        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="receipt-box">
        <div class="header">
            <span class="receipt-title">RECIBO DE DINERO</span>
            <div class="company-name">{{ $settings['empresa_nombre'] ?? 'Mi Empresa' }}</div>
            <div>{{ $settings['empresa_direccion'] ?? '' }}</div>
            <div>RUC: {{ $settings['empresa_ruc'] ?? '' }}</div>
        </div>

        <div class="content">
            <table width="100%">
                <tr>
                    <td><strong>N° Recibo:</strong> {{ str_pad($abono->id, 8, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-right"><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($abono->payment_date)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td><strong>Valor:</strong> {{ number_format($abono->amount, 0, ',', '.') }} Gs.</td>
                    <td class="text-right"><strong>Venta Ref:</strong> #{{ $abono->abonable_id }}</td>
                </tr>
            </table>

            <div style="margin-top: 15px;">
                Recibimos de <strong>{{ $abono->abonable->customer->name ?? 'Cliente' }}</strong><br>
                La cantidad de: <strong>{{ number_format($abono->amount, 0, ',', '.') }} Guaraníes</strong>
            </div>
            
            <div style="margin-top: 10px;">
                En concepto de: <strong>
                    @if($abono->installment_number)
                        Pago de cuota #{{ $abono->installment_number }}
                    @else
                        Abono a cuenta
                    @endif
                </strong>
            </div>

            <div style="margin-top: 10px;">
                Método de Pago: <strong>{{ strtoupper($abono->payment_method) }}</strong>
                @if($abono->reference)
                    <br>Nro. Referencia: <strong>{{ $abono->reference }}</strong>
                @endif
                @if($abono->received_amount && $abono->received_amount > $abono->amount)
                    <br>Monto Recibido: <strong>{{ number_format($abono->received_amount, 0, ',', '.') }} Gs.</strong>
                    <br>Vuelto: <strong>{{ number_format($abono->received_amount - $abono->amount, 0, ',', '.') }} Gs.</strong>
                @endif
            </div>
        </div>

        <div class="footer">
            <div class="signature">
                Firma Autorizada<br>
                Usuario: {{ $abono->user->name ?? 'Admin' }}
            </div>
            <div class="clear"></div>
        </div>
    </div>
</body>
</html>
