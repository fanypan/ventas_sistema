@extends('admin.layouts.master')

@section('title', 'Detalle de Compra #' . $purchase->id)

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Detalle de Compra #{{ $purchase->id }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Listado
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">Información de la Compra</h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item border-top-0">
                                    <b>Fecha</b> <span class="float-right">{{ $purchase->created_at->format('d/m/Y H:i') }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Proveedor</b> <span class="float-right font-weight-bold text-primary">{{ $purchase->supplier->name ?? '-' }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>RUC</b> <span class="float-right">{{ $purchase->supplier->nit ?? '-' }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Estado</b>
                                    <span class="float-right">
                                        @if($purchase->status == 1)
                                            <span class="badge badge-success">ACTIVA</span>
                                        @else
                                            <span class="badge badge-danger">ANULADA</span>
                                        @endif
                                    </span>
                                </li>
                                <li class="list-group-item border-bottom-0">
                                    <b>Registrado por</b> <span class="float-right">{{ $purchase->creator->name ?? 'N/A' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-body p-0">
                            <table class="table m-0">
                                <tr>
                                    <th class="p-3">Total Compra</th>
                                    <td class="text-right p-3 font-weight-bold" style="font-size: 1.2rem;">{{ money($purchase->total) }}</td>
                                </tr>
                                <tr class="bg-light">
                                    <th class="p-3">Total Pagado</th>
                                    <td class="text-right p-3 text-success font-weight-bold">{{ money($purchase->total_paid()) }}</td>
                                </tr>
                                <tr>
                                    <th class="p-3">Saldo Pendiente</th>
                                    <td class="text-right p-3 text-danger font-weight-bold">{{ money($purchase->pending_balance()) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark">
                            <h3 class="card-title text-white">Artículos Comprados</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-valign-middle m-0">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cant.</th>
                                        <th class="text-right">Costo Unit.</th>
                                        <th class="text-center">Lote</th>
                                        <th class="text-center">Vencimiento</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase->details as $detail)
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold">{{ $detail->product->description ?? 'Producto eliminado' }}</span>
                                        </td>
                                        <td class="text-center">{{ $detail->quantity }}</td>
                                        <td class="text-right">{{ money($detail->price) }}</td>
                                        <td class="text-center">{{ $detail->lot_number ?: '-' }}</td>
                                        <td class="text-center">
                                            {{ $detail->expiration_date ? \Carbon\Carbon::parse($detail->expiration_date)->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="text-right font-weight-bold">{{ money($detail->quantity * $detail->price) }}</td>
                                    </tr>
                                    @endforeach
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
