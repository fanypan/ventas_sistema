@extends('admin.layouts.master')

@section('title', 'Arqueo de Caja')

@section('content')
<div class="content-wrapper">
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Arqueo de Caja #{{ $caja->id }}</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Summary -->
            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Resumen del Sistema</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tr class="bg-light"><td colspan="2"><strong>MOVIMIENTOS DE CAJA</strong></td></tr>
                            <tr>
                                <td>Monto Inicial (Efectivo):</td>
                                <td class="text-right"><strong>{{ number_format($caja->opening_amount, 0, ',', '.') }}</strong></td>
                            </tr>
                            <tr>
                                <td>(+) Ventas en Efectivo:</td>
                                <td class="text-right text-success">{{ number_format($salesCash, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>(+) Abonos Recibidos:</td>
                                <td class="text-right text-success">{{ number_format($totalAbonos, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>(-) Gastos / Insumos:</td>
                                <td class="text-right text-danger">{{ number_format($totalExpenses, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="bg-warning text-dark">
                                <th>ESPERADO EN EFECTIVO:</th>
                                <th class="text-right"><h4>{{ number_format($expectedCash, 0, ',', '.') }}</h4></th>
                            </tr>
                            
                            <tr class="bg-light border-top"><td colspan="2"><strong>OTROS MEDIOS (SISTEMA)</strong></td></tr>
                            <tr>
                                <td>Ventas QR:</td>
                                <td class="text-right">{{ number_format($salesQR, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Ventas Tarjeta:</td>
                                <td class="text-right">{{ number_format($salesCard, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Ventas Transferencia:</td>
                                <td class="text-right">{{ number_format($salesTransf, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="bg-info text-white">
                                <th>TOTAL GENERAL SISTEMA:</th>
                                <th class="text-right"><h4>{{ number_format($expectedTotal, 0, ',', '.') }}</h4></th>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card card-info mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Cierre de Caja</h3>
                    </div>
                    <form action="{{ route('financials.cajas.close', $caja->id) }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label>Monto Real en Caja (Efectivo)</label>
                                <input type="number" name="monto_final" id="total_real_input" class="form-control" step="0.01" value="0" readonly>
                            </div>
                            <div class="form-group">
                                <label>Diferencia</label>
                                <input type="text" id="diferencia_label" class="form-control" value="{{ number_format(-$expectedTotal, 2) }}" readonly>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-block btn-info btn-lg" onclick="return confirm('¿Seguro que desea cerrar la caja?')">
                                <i class="fas fa-lock mr-2"></i> Cerrar Caja
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Denominations -->
            <div class="col-md-8">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Conteo de Billetes y Monedas</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $denominations = [
                                    ['label' => '100.000', 'val' => 100000],
                                    ['label' => '50.000', 'val' => 50000],
                                    ['label' => '20.000', 'val' => 20000],
                                    ['label' => '10.000', 'val' => 10000],
                                    ['label' => '5.000', 'val' => 5000],
                                    ['label' => '2.000', 'val' => 2000],
                                    ['label' => '1.000', 'val' => 1000],
                                    ['label' => '500', 'val' => 500],
                                    ['label' => '100', 'val' => 100],
                                    ['label' => '50', 'val' => 50],
                                ];
                            @endphp
                            @foreach($denominations as $den)
                                <div class="col-md-6 mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" style="width: 100px;">{{ $den['label'] }}</span>
                                        </div>
                                        <input type="number" class="form-control count-input" data-val="{{ $den['val'] }}" value="0" min="0">
                                        <div class="input-group-append">
                                            <span class="input-group-text subtotal-den" style="width: 120px;">0.00</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-right mt-3">
                            <h2>CONTEO TOTAL: <span id="span_conteo_total">0.00</span></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    let expected = {{ $expectedCash }};

    $('.count-input').on('input', function() {
        let totalReal = 0;
        $('.count-input').each(function() {
            let qty = parseInt($(this).val()) || 0;
            let val = parseFloat($(this).data('val'));
            let sub = qty * val;
            $(this).parent().find('.subtotal-den').text(sub.toLocaleString('es-PY', { minimumFractionDigits: 2 }));
            totalReal += sub;
        });

        $('#span_conteo_total').text(totalReal.toLocaleString('es-PY', { minimumFractionDigits: 2 }));
        $('#total_real_input').val(totalReal);
        
        let diff = totalReal - expected;
        $('#diferencia_label').val(diff.toLocaleString('es-PY', { minimumFractionDigits: 2 }));
        
        if(diff < 0) {
            $('#diferencia_label').addClass('text-danger').removeClass('text-success');
        } else {
            $('#diferencia_label').addClass('text-success').removeClass('text-danger');
        }
    });
});
</script>
@endpush
