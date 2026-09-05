@extends('platform.layout')
@section('title', 'Planes')
@section('content')
<div class="platform-page-head">
    <div>
        <h1>Planes</h1>
        <p class="platform-lead">Precios y límites que ves en la landing y en el alta de clientes.</p>
    </div>
</div>

<div class="card">
    @if ($plans->isEmpty())
        @include('platform.partials.empty', [
            'title' => 'No hay planes',
            'body' => 'No hay planes cargados en la base.',
            'actionUrl' => null,
            'actionLabel' => null,
        ])
    @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Mensual</th>
                        <th>Usuarios</th>
                        <th>Cajas</th>
                        <th>SIFEN</th>
                        <th>Landing</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($plans as $plan)
                    <tr>
                        <td>{{ $plan->name }}</td>
                        <td>{{ money($plan->price_monthly) }}</td>
                        <td>{{ $plan->max_users ?: 'Sin tope' }}</td>
                        <td>{{ $plan->max_cajas ?: 'Sin tope' }}</td>
                        <td>{{ $plan->sifen_documents_monthly ?: 'Sin SIFEN' }}</td>
                        <td>
                            <span class="platform-badge platform-badge--{{ $plan->is_public ? 'ok' : 'neutral' }}">
                                {{ $plan->is_public ? 'Pública' : 'Interno' }}
                            </span>
                        </td>
                        <td>
                            <span class="platform-badge platform-badge--{{ $plan->is_active ? 'ok' : 'neutral' }}">
                                {{ $plan->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-right">
                            @if (platform_can('plans.update'))
                                <a href="{{ route('platform.plans.edit', $plan) }}">Editar</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
