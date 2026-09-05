@extends('platform.layout')
@section('title', 'Nuevo usuario')
@section('content')
<div class="platform-page-head">
    <div>
        <h1>Nuevo usuario</h1>
        <p class="platform-lead">Entra al panel con el rol que le asignes. Staff cobra y da de alta; billing solo registra pagos.</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('platform.users.index') }}">Volver</a>
</div>

<form method="POST" action="{{ route('platform.users.store') }}" class="card card-body">
    @csrf
    <div class="platform-form-grid">
        <div class="form-group">
            <label for="name">Nombre</label>
            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="form-group">
            <label for="email">Correo</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <div class="input-group">
                <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" minlength="8" required>
                <div class="input-group-append">
                    @include('auth.partials.password-toggle-btn', ['target' => '#password'])
                </div>
            </div>
        </div>
        <div class="form-group span-2">
            <label>Roles</label>
            <div class="platform-check-grid">
                @foreach ($roles as $role)
                    <label class="platform-check">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(in_array($role->name, old('roles', ['staff']), true))>
                        <span>{{ $role->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="platform-form-actions">
            <button class="btn btn-primary" type="submit">Crear usuario</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
@include('auth.partials.password-toggle-script')
@endpush
