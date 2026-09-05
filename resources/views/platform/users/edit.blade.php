@extends('platform.layout')
@section('title', 'Editar usuario')
@section('content')
<div class="platform-page-head">
    <div>
        <h1>{{ $user->name }}</h1>
        <p class="platform-lead">Cambiá el rol si esta persona no tiene que ver todo el panel.</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('platform.users.index') }}">Volver</a>
</div>

<form method="POST" action="{{ route('platform.users.update', $user) }}" class="card card-body">
    @csrf
    @method('PUT')
    <div class="platform-form-grid">
        <div class="form-group">
            <label for="name">Nombre</label>
            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="form-group">
            <label for="email">Correo</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña nueva</label>
            <div class="input-group">
                <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" minlength="8">
                <div class="input-group-append">
                    @include('auth.partials.password-toggle-btn', ['target' => '#password'])
                </div>
            </div>
            <small class="form-text">Dejá vacío para no cambiarla.</small>
        </div>
        <div class="form-group span-2">
            <label>Roles</label>
            <div class="platform-check-grid">
                @foreach ($roles as $role)
                    <label class="platform-check">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(in_array($role->name, old('roles', $user->roles->pluck('name')->all()), true))>
                        <span>{{ $role->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="platform-form-actions">
            <button class="btn btn-primary" type="submit">Guardar</button>
            @if (platform_can('users.delete') && $user->id !== auth('platform')->id())
                <button class="btn btn-outline-danger" type="submit" form="delete-user-form" onclick="return confirm('¿Sacar a {{ $user->name }} del equipo?')">Eliminar</button>
            @endif
        </div>
    </div>
</form>

@if (platform_can('users.delete') && $user->id !== auth('platform')->id())
    <form id="delete-user-form" method="POST" action="{{ route('platform.users.destroy', $user) }}" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endif
@endsection

@push('scripts')
@include('auth.partials.password-toggle-script')
@endpush
