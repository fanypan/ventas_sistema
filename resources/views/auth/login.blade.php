<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <title>Ingresar — {{ Setting::getValue('app_name') ?? ENV('APP_NAME') }}</title>
        @include('pwa.favicon')
        @include('pwa.meta')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('template/admin/plugins/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('template/admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('template/admin/dist/css/adminlte.min.css') }}">
        @include('auth.partials.screen-styles')
    </head>
    <body class="auth-screen">
        @php
            if (!$errors->isEmpty()) {
                alert()->error('Revisá el formulario', implode(' · ', $errors->all()))->toToast();
            }
            if (session('error')) {
                alert()->error('No se pudo ingresar', session('error'))->toToast();
            }
            if (session('status')) {
                alert()->info('Listo', session('status'))->toToast();
            }
            $appName = Setting::getValue('app_name') ?? 'SISVEN';
            $logo = Setting::getValue('app_logo');
        @endphp
        <div class="auth-split">
            <aside class="auth-brand">
                <div class="auth-brand-copy">
                    @if($logo)
                        <img src="{{ setting_file_url($logo) }}" alt="" class="auth-brand-logo">
                    @endif
                    <h1>{{ $appName }}</h1>
                    <p>Caja, stock y créditos en un solo lugar. Entrá para seguir con el día.</p>
                </div>
                <div class="auth-brand-foot">{{ $appName }}</div>
            </aside>
            <main class="auth-main">
                <div class="auth-form">
                    <h2>Iniciar sesión</h2>
                    <p class="auth-lead">Usá tu correo y contraseña para continuar. Si es tu primer acceso, revisá el mail para definir la contraseña.</p>
                    <form method="POST" id="login-form" action="{{ route('login') }}">
                        @csrf
                        <div class="auth-field">
                            <label for="email">Correo electrónico</label>
                            <div class="input-group">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="nina.v@example.com" aria-label="Correo electrónico">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-envelope" aria-hidden="true"></span>
                                    </div>
                                </div>
                            </div>
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="auth-field">
                            <label for="password">Contraseña</label>
                            <div class="input-group">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••" aria-label="Contraseña">
                                <div class="input-group-append">
                                    @include('auth.partials.password-toggle-btn', ['target' => '#password'])
                                </div>
                            </div>
                            @error('password')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="auth-row">
                            <div class="icheck-primary">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">Recordarme</label>
                            </div>
                        </div>
                        <button type="submit" class="auth-submit">Iniciar sesión</button>
                    </form>
                    <p class="auth-lead d-none" data-pwa-install style="margin-top:1rem">
                        <button type="button" class="btn btn-link p-0 font-weight-bold">Instalá el sistema en este dispositivo</button>
                    </p>
                    <p class="auth-lead d-none" data-pwa-ios-hint style="margin-top:1rem">
                        En el iPhone: tocá <strong>Compartir</strong> y después <strong>Agregar a inicio</strong> para usarlo como app.
                    </p>
                </div>
            </main>
        </div>
        @include('sweetalert::alert')
        <script src="{{ asset('template/admin/plugins/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('template/admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        @include('auth.partials.password-toggle-script')
        <script src="{{ asset('js/pwa.js') }}"></script>
    </body>
</html>
