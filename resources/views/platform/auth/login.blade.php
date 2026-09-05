@extends('platform.layout')
@section('title', 'Ingresar')
@section('content')
<div class="platform-login">
    <div class="card">
        <div class="card-body p-4">
            <h1>Ingresá a la plataforma</h1>
            <p class="platform-lead mb-4">Usá tu correo de staff.</p>
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('platform.login.attempt') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Correo</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-group">
                        <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" autocomplete="current-password" required>
                        <div class="input-group-append">
                            @include('auth.partials.password-toggle-btn', ['target' => '#password'])
                        </div>
                    </div>
                </div>
                <div class="form-group form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))>
                    <label class="form-check-label font-weight-normal" for="remember">Recordarme</label>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Ingresá</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('auth.partials.password-toggle-script')
@endpush
