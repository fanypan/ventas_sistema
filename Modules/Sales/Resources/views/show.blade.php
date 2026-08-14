@extends('admin.layouts.master')

@section('title', 'Detalle de Venta #' . $sale->id)

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Detalle de Venta #{{ $sale->id }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Listado
                    </a>
                    @if($sale->status != 0)
                    @can('update sale')
                    <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    @endcan
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Información General -->
                <div class="col-md-4">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">Información de la Venta</h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item border-top-0">
                                    <b>Fecha</b> <span class="float-right">{{ $sale->created_at->format('d/m/Y H:i') }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Cliente</b> <span class="float-right font-weight-bold text-primary">{{ $sale->customer->name ?? 'Consumidor Final' }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Método de Pago</b> 
                                    <span class="float-right">
                                        @if($sale->payment_type == 'credit')
                                            <span class="badge badge-warning"><i class="fas fa-credit-card"></i> CRÉDITO</span>
                                        @else
                                            <span class="badge badge-success"><i class="fas fa-money-bill"></i> {{ strtoupper($sale->payment_type) }}</span>
                                        @endif
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Estado</b> 
                                    <span class="float-right">
                                        @if($sale->status == 1)
                                            <span class="badge badge-success">PAGADO / FINALIZADO</span>
                                        @else
                                            <span class="badge badge-danger">PENDIENTE / CRÉDITO</span>
                                        @endif
                                    </span>
                                </li>
                                <li class="list-group-item border-bottom-0">
                                    <b>Vendedor</b> <span class="float-right">{{ $sale->creator->name ?? 'N/A' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Resumen Financiero -->
                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-body p-0">
                            <table class="table m-0">
                                <tr>
                                    <th class="p-3">Total Venta</th>
                                    <td class="text-right p-3 font-weight-bold" style="font-size: 1.2rem;">Gs. {{ number_format($sale->total, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="bg-light">
                                    <th class="p-3">Total Pagado</th>
                                    <td class="text-right p-3 text-success font-weight-bold">Gs. {{ number_format($sale->total_paid(), 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th class="p-3">Saldo Pendiente</th>
                                    <td class="text-right p-3 text-danger font-weight-bold">Gs. {{ number_format($sale->pending_balance(), 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Detalle de Productos -->
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark">
                            <h3 class="card-title">Artículos Vendidos</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-valign-middle m-0">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cant.</th>
                                        <th class="text-right">Precio Unit.</th>
                                        <th class="text-right">Ajustes</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $subtotalBase = 0; @endphp
                                    @foreach($sale->details as $detail)
                                    @php 
                                        $baseItemTotal = $detail->quantity * $detail->price;
                                        $finalItemTotal = $baseItemTotal - $detail->discount + $detail->interest_amount;
                                        $subtotalBase += $finalItemTotal;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold">{{ $detail->product->description }}</span>
                                            <br><small class="text-muted">ID: {{ $detail->product->id }}</small>
                                        </td>
                                        <td class="text-center">{{ $detail->quantity }}</td>
                                        <td class="text-right">Gs. {{ number_format($detail->price, 0, ',', '.') }}</td>
                                        <td class="text-right">
                                            @if($detail->discount > 0)
                                                <small class="text-danger">Dto: -{{ number_format($detail->discount, 0, ',', '.') }}</small><br>
                                            @endif
                                            @if($detail->interest_amount > 0)
                                                <small class="text-success">Rec: +{{ number_format($detail->interest_amount, 0, ',', '.') }}</small>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold">Gs. {{ number_format($finalItemTotal, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($sale->payment_type == 'credit')
                    <!-- Cronograma de Cuotas -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-header bg-info">
                            <h3 class="card-title">Cronograma de Pagos (Cuotas)</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered table-sm m-0 text-center">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Vencimiento</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Pagado el</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->installments as $inst)
                                    <tr>
                                        <td>{{ $inst->installment_number }}</td>
                                        <td>{{ \Carbon\Carbon::parse($inst->due_date)->format('d/m/Y') }}</td>
                                        <td class="font-weight-bold">Gs. {{ number_format($inst->amount, 0, ',', '.') }}</td>
                                        <td>
                                            @if($inst->status == 1)
                                                <span class="badge badge-success">PAGADO</span>
                                            @else
                                                <span class="badge badge-warning">PENDIENTE</span>
                                            @endif
                                        </td>
                                        <td>{{ $inst->paid_at ? \Carbon\Carbon::parse($inst->paid_at)->format('d/m/Y H:i') : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Historial de Abonos -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-header bg-secondary">
                            <h3 class="card-title">Historial de Pagos / Abonos</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm m-0">
                                <thead>
                                    <tr>
                                        <th>Fecha Pago</th>
                                        <th>Monto</th>
                                        <th>Método</th>
                                        <th>Registrado por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sale->abonos as $abono)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($abono->payment_date)->format('d/m/Y H:i') }}</td>
                                        <td class="font-weight-bold text-success">Gs. {{ number_format($abono->amount, 0, ',', '.') }}</td>
                                        <td>{{ $abono->payment_method }}</td>
                                        <td>{{ $abono->user->name ?? 'N/A' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">No se registran abonos aún.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
