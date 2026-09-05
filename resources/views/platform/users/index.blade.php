@extends('platform.layout')
@section('title', 'Equipo')
@section('content')
<div class="platform-page-head">
    <div>
        <h1>Equipo</h1>
        <p class="platform-lead">Quién entra al panel y con qué rol.</p>
    </div>
    <div class="platform-actions">
        @if (platform_can('roles.view'))
            <a class="btn btn-outline-secondary" href="{{ route('platform.roles.index') }}">Roles</a>
        @endif
        @if (platform_can('users.create'))
            <a class="btn btn-primary" href="{{ route('platform.users.create') }}">Nuevo usuario</a>
        @endif
    </div>
</div>

<div class="card">
    @if ($users->isEmpty())
        @include('platform.partials.empty', [
            'title' => 'No hay usuarios',
            'body' => 'Creá al menos un admin para no quedar afuera.',
            'actionUrl' => platform_can('users.create') ? route('platform.users.create') : null,
            'actionLabel' => platform_can('users.create') ? 'Nuevo usuario' : null,
        ])
    @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Roles</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="text-right">
                            @if (platform_can('users.update'))
                                <a href="{{ route('platform.users.edit', $user) }}">Editar</a>
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
