@php
    $isEdit = (bool) $role;
    $isProtected = $isEdit && \App\Support\PlatformAccess::isProtectedRole($role->name);
@endphp
@extends('platform.layout')
@section('title', $isEdit ? 'Editar rol' : 'Nuevo rol')
@section('content')
<div class="platform-page-head">
    <div>
        <h1>{{ $isEdit ? $role->name : 'Nuevo rol' }}</h1>
        <p class="platform-lead">
            @if ($isProtected)
                El rol admin siempre tiene todos los permisos. No se puede borrar ni recortar.
            @else
                Marcá solo lo que esta persona necesita para laburar.
            @endif
        </p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('platform.roles.index') }}">Volver</a>
</div>

<form method="POST" action="{{ $isEdit ? route('platform.roles.update', $role) : route('platform.roles.store') }}" class="card card-body">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif
    <div class="platform-form-grid">
        <div class="form-group">
            <label for="name">Nombre</label>
            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $role->name ?? '') }}" pattern="[a-z0-9-]+" {{ $isProtected ? 'readonly' : 'required' }}>
            <small class="form-text">Minúsculas, números y guiones.</small>
        </div>
        <div class="form-group span-2">
            <label>Permisos</label>
            @if ($isProtected)
                <p class="platform-lead mb-0">Todos ({{ count($permissions) }}).</p>
            @else
                <div class="platform-check-grid">
                    @foreach ($permissions as $key => $label)
                        <label class="platform-check">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key, $selected, true))>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="platform-form-actions">
            <button class="btn btn-primary" type="submit">{{ $isEdit ? 'Guardar' : 'Crear rol' }}</button>
            @if ($isEdit && ! $isProtected && platform_can('roles.delete'))
                <button class="btn btn-outline-danger" type="submit" form="delete-role-form" onclick="return confirm('¿Borrar el rol {{ $role->name }}?')">Eliminar</button>
            @endif
        </div>
    </div>
</form>

@if ($isEdit && ! $isProtected && platform_can('roles.delete'))
    <form id="delete-role-form" method="POST" action="{{ route('platform.roles.destroy', $role) }}" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endif
@endsection
