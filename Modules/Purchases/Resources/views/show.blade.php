@extends('admin.layouts.master')

@section('title', 'Detalle de Compra')

@section('content')
<div class="content-wrapper">
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Detalle de Compra #{{ $purchase->id }}</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Información General</h3>
                <div class="card-tools">
                    <a href="{{ route('purchases.index') }}" class="btn btn-default btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Proveedor:</strong> {{ $purchase->supplier->name }}<br>
                        <strong>NIT/RUC:</strong> {{ $purchase->supplier->nit }}
                    </div>
                    <div class="col-md-4 text-center">
                        <strong>Fecha:</strong> {{ $purchase->created_at->format('d/m/Y H:i') }}<br>
                        <strong>Registrado por:</strong> {{ $purchase->creator->name }}
                    </div>
                    <div class="col-md-4 text-right">
                        <strong>Estado:</strong> 
                        @if($purchase->status == 1)
                            <span class="badge badge-success">Completado</span>
                        @else
                            <span class="badge badge-warning">Crédito</span>
                        @endif
                        <br>
                        <h3>TOTAL: {{ number_format($purchase->total, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Productos Comprados</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-right">Precio Costo</th>
                            <th class="text-center">Lote</th>
                            <th class="text-center">Vencimiento</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->details as $detail)
                        <tr>
                            <td>{{ $detail->product->description }}</td>
                            <td class="text-center">{{ $detail->quantity }}</td>
                            <td class="text-right">{{ number_format($detail->price, 2) }}</td>
                            <td class="text-center">{{ $detail->lot_number ?: '-' }}</td>
                            <td class="text-center">
                                {{ $detail->expiration_date ? \Carbon\Carbon::parse($detail->expiration_date)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="text-right">{{ number_format($detail->quantity * $detail->price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
</div>
@endsection
