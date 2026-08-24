@extends('platform.layout')
@section('title', 'Registrar pago')
@section('content')
<div class="platform-page-head">
    <div>
        <h1>Pago — {{ $tenant->name }}</h1>
        <p class="platform-lead">Registrá el cobro y se renueva el período desde el vencimiento actual.</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('platform.tenants.show', $tenant) }}">Volver</a>
</div>

<form method="POST" action="{{ route('platform.payments.store', $tenant) }}" enctype="multipart/form-data" class="card card-body">
    @csrf
    <div class="platform-form-grid">
        <div class="form-group">
            <label for="amount">Monto (Gs., sin puntos)</label>
            <input class="form-control @error('amount') is-invalid @enderror" id="amount" type="number" name="amount" value="{{ old('amount', $tenant->plan?->price_monthly) }}" min="1" required>
        </div>
        <div class="form-group">
            <label for="method">Medio</label>
            <select class="form-control" id="method" name="method">
                <option value="transferencia" @selected(old('method', 'transferencia') === 'transferencia')>Transferencia</option>
                <option value="efectivo" @selected(old('method') === 'efectivo')>Efectivo</option>
            </select>
        </div>
        <div class="form-group">
            <label for="reference">Referencia / nro. de comprobante</label>
            <input class="form-control @error('reference') is-invalid @enderror" id="reference" name="reference" value="{{ old('reference') }}">
        </div>
        <div class="form-group">
            <label for="paid_at">Fecha de pago</label>
            <input class="form-control @error('paid_at') is-invalid @enderror" id="paid_at" type="date" name="paid_at" value="{{ old('paid_at', now()->toDateString()) }}" required>
        </div>
        <div class="form-group">
            <label for="interval">Renovar</label>
            <select class="form-control" id="interval" name="interval">
                <option value="monthly" @selected(old('interval', 'monthly') === 'monthly')>1 mes</option>
                <option value="yearly" @selected(old('interval') === 'yearly')>1 año</option>
            </select>
        </div>
        <div class="form-group">
            <label for="attachment">Comprobante (opcional)</label>
            <input class="form-control-file" id="attachment" type="file" name="attachment">
        </div>
        <div class="form-group span-2">
            <label for="notes">Notas</label>
            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
        </div>
        <div class="platform-form-actions">
            <button class="btn btn-success" type="submit">Guardar pago y renovar</button>
        </div>
    </div>
</form>
@endsection
