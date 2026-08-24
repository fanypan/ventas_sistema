@extends('platform.layout')
@section('title', 'Panel')
@section('content')
<div class="platform-page-head">
    <div>
        <h1>Panel</h1>
        <p class="platform-lead">Clientes, vencimientos y los últimos cobros registrados.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('platform.tenants.create') }}">Nuevo cliente</a>
</div>

<div class="platform-stats">
    <div class="platform-stat">
        <p class="platform-stat__value">{{ $tenants }}</p>
        <p class="platform-stat__label">Clientes</p>
    </div>
    <div class="platform-stat platform-stat--ok">
        <p class="platform-stat__value">{{ $active }}</p>
        <p class="platform-stat__label">Activos</p>
    </div>
    <div class="platform-stat platform-stat--warn">
        <p class="platform-stat__value">{{ $grace }}</p>
        <p class="platform-stat__label">En gracia</p>
    </div>
    <div class="platform-stat platform-stat--bad">
        <p class="platform-stat__value">{{ $suspended }}</p>
        <p class="platform-stat__label">Pausados</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Últimos clientes</span>
                <a href="{{ route('platform.tenants.index') }}">Ver todos</a>
            </div>
            @forelse ($recentTenants as $tenant)
                @if ($loop->first)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Comercio</th>
                                    <th>Estado</th>
                                    <th>Plan</th>
                                </tr>
                            </thead>
                            <tbody>
                @endif
                            <tr>
                                <td><a href="{{ route('platform.tenants.show', $tenant) }}">{{ $tenant->name }}</a></td>
                                <td>@include('platform.partials.status-badge', ['tenant' => $tenant])</td>
                                <td>{{ $tenant->plan?->name ?: '—' }}</td>
                            </tr>
                @if ($loop->last)
                            </tbody>
                        </table>
                    </div>
                @endif
            @empty
                @include('platform.partials.empty', [
                    'title' => 'Todavía no hay clientes',
                    'body' => 'Cuando cobres el primer plan, dales de alta acá.',
                    'actionUrl' => route('platform.tenants.create'),
                    'actionLabel' => 'Nuevo cliente',
                ])
            @endforelse
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-header">Últimos pagos</div>
            @forelse ($recentPayments as $payment)
                @if ($loop->first)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Monto</th>
                                    <th>Medio</th>
                                </tr>
                            </thead>
                            <tbody>
                @endif
                            <tr>
                                <td>
                                    @if ($payment->tenant)
                                        <a href="{{ route('platform.tenants.show', $payment->tenant) }}">{{ $payment->tenant->name }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ money($payment->amount) }}</td>
                                <td>{{ $payment->methodLabel() }}</td>
                            </tr>
                @if ($loop->last)
                            </tbody>
                        </table>
                    </div>
                @endif
            @empty
                @include('platform.partials.empty', [
                    'title' => 'Sin pagos todavía',
                    'body' => 'Los cobros que registres van a aparecer acá.',
                    'actionUrl' => null,
                    'actionLabel' => null,
                ])
            @endforelse
        </div>
    </div>
</div>
@endsection
