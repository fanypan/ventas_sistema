<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <title>Definí tu contraseña — {{ Setting::getValue('app_name') ?? config('app.name') }}</title>
        @include('pwa.favicon')
        @include('pwa.meta')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('template/admin/plugins/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('template/admin/dist/css/adminlte.min.css') }}">
        @include('auth.partials.screen-styles')
    </head>
    <body class="auth-screen">
        @php
            if (!$errors->isEmpty()) {
                alert()->error('Revisá el formulario', implode(' · ', $errors->all()))->toToast();
            }
            $appName = Setting::getValue('app_name') ?? config('app.name');
            $logo = Setting::getValue('app_logo');
        @endphp
        <div class="auth-split">
            <aside class="auth-brand">
                <div class="auth-brand-copy">
                    @if($logo)
                        <img src="{{ setting_file_url($logo) }}" alt="" class="auth-brand-logo">
                    @endif
                    <h1>{{ $appName }}</h1>
                    <p>Elegí una contraseña tuya. Nadie de AranduTech la va a conocer.</p>
                </div>
                <div class="auth-brand-foot">{{ $appName }}</div>
            </aside>
            <main class="auth-main">
                <div class="auth-form">
                    <h2>Definí tu contraseña</h2>
                    <p class="auth-lead">Va a ser la clave de {{ $user->email }} para entrar al sistema.</p>
                    <form method="POST" action="{{ $actionUrl }}">
                        @csrf
                        <div class="auth-field">
                            <label for="password">Contraseña</label>
                            <div class="input-group">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" minlength="8" maxlength="72" aria-label="Contraseña" autofocus>
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
                        <div class="auth-field">
                            <label for="password-confirm">Repetí la contraseña</label>
                            <div class="input-group">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" minlength="8" maxlength="72" aria-label="Repetí la contraseña">
                                <div class="input-group-append">
                                    @include('auth.partials.password-toggle-btn', ['target' => '#password-confirm'])
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="auth-submit">Guardar e ingresar</button>
                    </form>
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
