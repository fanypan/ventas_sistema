@extends('platform.layout')
@section('title', 'Roles')
@section('content')
<div class="platform-page-head">
    <div>
        <h1>Roles</h1>
        <p class="platform-lead">El admin ve todo. Staff es el día a día. Billing solo cobra. Podés armar uno a medida.</p>
    </div>
    <div class="platform-actions">
        @if (platform_can('users.view'))
            <a class="btn btn-outline-secondary" href="{{ route('platform.users.index') }}">Usuarios</a>
        @endif
        @if (platform_can('roles.create'))
            <a class="btn btn-primary" href="{{ route('platform.roles.create') }}">Nuevo rol</a>
        @endif
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Rol</th>
                    <th>Usuarios</th>
                    <th>Permisos</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($roles as $role)
                <tr>
                    <td>{{ $role->name }}</td>
                    <td>{{ $role->users_count }}</td>
                    <td>{{ $role->permissions->count() }}</td>
                    <td class="text-right">
                        @if (platform_can('roles.update'))
                            <a href="{{ route('platform.roles.edit', $role) }}">Editar</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
