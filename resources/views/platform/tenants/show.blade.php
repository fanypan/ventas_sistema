@extends('platform.layout')
@section('title', $tenant->name)
@section('content')
@if (session('plain_password'))
    <div class="alert alert-success">
        Contraseña inicial del admin: <strong>{{ session('plain_password') }}</strong> (también se envía por mail).
    </div>
@endif
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="mb-0">{{ $tenant->name }}</h1>
        <p class="text-muted mb-0">{{ $tenant->url() }} · {{ $tenant->status }} · {{ $tenant->plan?->name }}</p>
    </div>
    <div>
        <a class="btn btn-success" href="{{ route('platform.payments.create', $tenant) }}">Registrar pago</a>
        @if ($tenant->status !== 'suspended')
            <form class="d-inline" method="POST" action="{{ route('platform.tenants.suspend', $tenant) }}">@csrf<button class="btn btn-warning">Suspender</button></form>
        @else
            <form class="d-inline" method="POST" action="{{ route('platform.tenants.reactivate', $tenant) }}">@csrf<button class="btn btn-info">Reactivar</button></form>
        @endif
        <form class="d-inline" method="POST" action="{{ route('platform.tenants.cancel', $tenant) }}">@csrf<button class="btn btn-secondary">Baja</button></form>
        <form class="d-inline" method="POST" action="{{ route('platform.tenants.destroy', $tenant) }}" onsubmit="return confirm('¿Borrar tenant y su base?')">@csrf @method('DELETE')<button class="btn btn-danger">Eliminar</button></form>
    </div>
</div>
<div class="card mb-3">
    <div class="card-body">
        <p>RUC: {{ $tenant->ruc ?: '—' }}</p>
        <p>Admin: {{ $tenant->admin_name }} &lt;{{ $tenant->admin_email }}&gt;</p>
        <p>Vence: {{ optional($tenant->subscription?->ends_at)->format('d/m/Y H:i') ?: '—' }}</p>
        <p>Aprovisionado: {{ optional($tenant->provisioned_at)->format('d/m/Y H:i') ?: 'pendiente' }}</p>
    </div>
</div>
<h4>Pagos</h4>
<table class="table">
    <thead><tr><th>Fecha</th><th>Monto</th><th>Medio</th><th>Ref</th></tr></thead>
    <tbody>
    @forelse ($tenant->payments as $payment)
        <tr>
            <td>{{ $payment->paid_at?->format('d/m/Y') }}</td>
            <td>Gs. {{ number_format($payment->amount, 0, ',', '.') }}</td>
            <td>{{ $payment->method }}</td>
            <td>{{ $payment->reference }}</td>
        </tr>
    @empty
        <tr><td colspan="4">Sin pagos.</td></tr>
    @endforelse
    </tbody>
</table>
@endsection
