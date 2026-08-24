@extends('platform.layout')
@section('title', $tenant->name)
@section('content')
@if (session('plain_password'))
    <div class="alert alert-success" role="status">
        Contraseña inicial del admin: <strong class="platform-password">{{ session('plain_password') }}</strong>
        (también se envía por mail).
    </div>
@endif

<div class="platform-page-head">
    <div>
        <h1>{{ $tenant->name }}</h1>
        <p class="platform-lead mb-2">
            <a href="{{ $tenant->url() }}" target="_blank" rel="noopener noreferrer">{{ $tenant->primaryDomain() }}</a>
            · {{ $tenant->plan?->name ?: 'Sin plan' }}
        </p>
        @include('platform.partials.status-badge', ['tenant' => $tenant])
    </div>
    <div class="platform-actions">
        <a class="btn btn-success" href="{{ route('platform.payments.create', $tenant) }}">Registrar pago</a>
        @if ($tenant->status !== 'suspended')
            <form method="POST" action="{{ route('platform.tenants.suspend', $tenant) }}" onsubmit="return confirm(@json('¿Pausar el POS de '.$tenant->name.'? El comercio no va a poder cobrar.'))">
                @csrf
                <button class="btn btn-warning" type="submit">Suspender</button>
            </form>
        @else
            <form method="POST" action="{{ route('platform.tenants.reactivate', $tenant) }}">
                @csrf
                <button class="btn btn-info" type="submit">Reactivar</button>
            </form>
        @endif
        @if (auth('platform')->user()->isAdmin())
            <form method="POST" action="{{ route('platform.tenants.cancel', $tenant) }}" onsubmit="return confirm(@json('¿Dar de baja a '.$tenant->name.'? No se borra la base.'))">
                @csrf
                <button class="btn btn-secondary" type="submit">Baja</button>
            </form>
            <form method="POST" action="{{ route('platform.tenants.destroy', $tenant) }}" onsubmit="return confirm(@json('¿Borrar '.$tenant->name.' y su base? Esto no se puede deshacer.'))">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">Eliminar</button>
            </form>
        @endif
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <dl class="platform-meta">
            <div>
                <dt>RUC</dt>
                <dd>{{ $tenant->ruc ?: '—' }}</dd>
            </div>
            <div>
                <dt>Admin</dt>
                <dd>{{ $tenant->admin_name }} &lt;{{ $tenant->admin_email }}&gt;</dd>
            </div>
            <div>
                <dt>Vence</dt>
                <dd>{{ optional($tenant->subscription?->ends_at)->format('d/m/Y H:i') ?: '—' }}</dd>
            </div>
            <div>
                <dt>Aprovisionado</dt>
                <dd>{{ optional($tenant->provisioned_at)->format('d/m/Y H:i') ?: 'Pendiente' }}</dd>
            </div>
        </dl>
    </div>
</div>

<div class="card">
    <div class="card-header">Pagos</div>
    @if ($tenant->payments->isEmpty())
        @include('platform.partials.empty', [
            'title' => 'Sin pagos',
            'body' => 'Registrá el cobro para renovar el período.',
            'actionUrl' => route('platform.payments.create', $tenant),
            'actionLabel' => 'Registrar pago',
        ])
    @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Medio</th>
                        <th>Ref</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($tenant->payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at?->format('d/m/Y') }}</td>
                        <td>{{ money($payment->amount) }}</td>
                        <td>{{ $payment->methodLabel() }}</td>
                        <td>{{ $payment->reference ?: '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
