@extends('platform.layout')
@section('title', 'Clientes')
@section('content')
<div class="platform-page-head">
    <div>
        <h1>Clientes</h1>
        <p class="platform-lead">Comercios, plan y vencimiento de la suscripción.</p>
    </div>
    @if (platform_can('tenants.create'))
        <a class="btn btn-primary" href="{{ route('platform.tenants.create') }}">Nuevo cliente</a>
    @endif
</div>

<div class="card">
    @if ($tenants->isEmpty())
        @include('platform.partials.empty', [
            'title' => 'Todavía no hay clientes',
            'body' => 'Después del pago, completá el alta. El sistema crea la base y manda las credenciales.',
            'actionUrl' => platform_can('tenants.create') ? route('platform.tenants.create') : null,
            'actionLabel' => platform_can('tenants.create') ? 'Nuevo cliente' : null,
        ])
    @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Comercio</th>
                        <th>Subdominio</th>
                        <th>Plan</th>
                        <th>Estado</th>
                        <th>Vence</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($tenants as $tenant)
                    <tr>
                        <td><a href="{{ route('platform.tenants.show', $tenant) }}">{{ $tenant->name }}</a></td>
                        <td>{{ $tenant->primaryDomain() }}</td>
                        <td>{{ $tenant->plan?->name ?: '—' }}</td>
                        <td>@include('platform.partials.status-badge', ['tenant' => $tenant])</td>
                        <td>{{ $tenant->subscription?->endsLabel() ?: '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@if ($tenants->hasPages())
    <div class="mt-3">{{ $tenants->links() }}</div>
@endif
@endsection
