@extends('admin.layouts.master')

@section('title', 'Editar venta #' . $sale->id)

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-edit mr-2"></i>Editar venta #{{ $sale->id }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Volver</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary" style="max-width: 640px;">
                <form method="POST" action="{{ route('sales.update', $sale->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <p class="text-muted">Solo se modifica la cabecera. Los productos y el stock no cambian. Para anular use el botón de anular en el historial.</p>
                        <div class="form-group">
                            <label>Fecha y hora</label>
                            <input type="datetime-local" name="fecha" class="form-control"
                                   value="{{ old('fecha', $sale->created_at->format('Y-m-d\TH:i')) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Cliente</label>
                            <select name="customer_id" class="form-control" required>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ (int) old('customer_id', $sale->customer_id) === $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} {{ $customer->nit ? '(' . $customer->nit . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Método de pago</label>
                            <select name="payment_type" class="form-control" required>
                                @foreach(['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'credito' => 'Crédito', 'qr' => 'QR', 'tarjeta' => 'Tarjeta'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('payment_type', $sale->payment_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="status" class="form-control" required>
                                <option value="1" {{ (int) old('status', $sale->status) === 1 ? 'selected' : '' }}>Pagada</option>
                                <option value="2" {{ (int) old('status', $sale->status) === 2 ? 'selected' : '' }}>Crédito</option>
                                <option value="3" {{ (int) old('status', $sale->status) === 3 ? 'selected' : '' }}>Abono</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Actualizar venta</button>
                        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-default">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
