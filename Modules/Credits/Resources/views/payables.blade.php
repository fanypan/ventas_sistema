@extends('admin.layouts.master')

@section('title', 'Cuentas por Pagar')

@section('content')
<div class="content-wrapper">
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Cuentas por Pagar (Proveedores)</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-outline card-danger">
            <div class="card-body p-0">
                <table class="table table-striped table-hover m-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Compra #</th>
                            <th>Proveedor</th>
                            <th>Total</th>
                            <th>Pagado</th>
                            <th>Pendiente</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->id }}</td>
                            <td>{{ $purchase->supplier->name }}</td>
                            <td>{{ number_format($purchase->total, 2) }}</td>
                            <td class="text-success">{{ number_format($purchase->total_paid(), 2) }}</td>
                            <td class="text-danger"><strong>{{ number_format($purchase->pending_balance(), 2) }}</strong></td>
                            <td>
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalAbonoP{{ $purchase->id }}">
                                    <i class="fas fa-money-bill-wave"></i> Pagar
                                </button>

                                <!-- Modal Abono -->
                                <div class="modal fade" id="modalAbonoP{{ $purchase->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <form action="{{ route('credits.abono.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="abonable_id" value="{{ $purchase->id }}">
                                            <input type="hidden" name="abonable_type" value="Modules\Purchases\Entities\Purchase">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Registrar Pago - Compra #{{ $purchase->id }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Monto a pagar (Máx: {{ number_format($purchase->pending_balance(), 0, ',', '.') }})</label>
                                                        <input type="text" name="amount" class="form-control currency-format" value="{{ $purchase->pending_balance() }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Método de Pago</label>
                                                        <select name="payment_method" class="form-control">
                                                            <option value="Efectivo">Efectivo</option>
                                                            <option value="Transferencia">Transferencia</option>
                                                            <option value="Transferencia">Cheque</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-primary">Registrar Pago</button>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>
</section>
</div>
@endsection
