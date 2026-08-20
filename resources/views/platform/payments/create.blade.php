@extends('platform.layout')
@section('title', 'Registrar pago')
@section('content')
<h1>Pago — {{ $tenant->name }}</h1>
<form method="POST" action="{{ route('platform.payments.store', $tenant) }}" enctype="multipart/form-data" class="card card-body">
    @csrf
    <div class="form-group"><label>Monto (Gs., sin puntos)</label><input class="form-control" type="number" name="amount" value="{{ old('amount', $tenant->plan?->price_monthly) }}" required></div>
    <div class="form-group"><label>Medio</label>
        <select class="form-control" name="method">
            <option value="transferencia">Transferencia</option>
            <option value="efectivo">Efectivo</option>
        </select>
    </div>
    <div class="form-group"><label>Referencia / nro. de comprobante</label><input class="form-control" name="reference"></div>
    <div class="form-group"><label>Fecha de pago</label><input class="form-control" type="date" name="paid_at" value="{{ now()->toDateString() }}" required></div>
    <div class="form-group"><label>Renovar</label>
        <select class="form-control" name="interval">
            <option value="monthly">1 mes</option>
            <option value="yearly">1 año</option>
        </select>
    </div>
    <div class="form-group"><label>Notas</label><textarea class="form-control" name="notes"></textarea></div>
    <div class="form-group"><label>Comprobante (opcional)</label><input class="form-control-file" type="file" name="attachment"></div>
    <button class="btn btn-success">Guardar pago y renovar</button>
</form>
@endsection
