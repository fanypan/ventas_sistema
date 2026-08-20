
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ ($title ?? 'Admin') . " - " . Setting::getValue('app_name') }}</title>
        <link rel="icon" href="{{ asset(Setting::getValue('app_favicon')) }}" type="image/png" />
        <!-- Google Font: Outfit -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{ asset('template/admin/plugins/fontawesome-free/css/all.min.css') }}">
        <!-- Ionicons -->
        <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{ asset('template/admin/dist/css/adminlte.min.css') }}">
        <!-- icheck bootstrap -->
        <link rel="stylesheet" href="{{ asset('template/admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
        <!-- overlayScrollbars -->
        <link rel="stylesheet" href="{{ asset('template/admin/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
        <!-- DataTables -->
        <link rel="stylesheet" href="{{ asset('template/admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
        <!-- Premium UI CSS -->
        <link rel="stylesheet" href="{{ asset('css/custom-premium.css') }}?v=20260817e">
        <script>
            (function () {
                var pref = 'system';
                try {
                    pref = localStorage.getItem('theme-preference') || localStorage.getItem('theme') || 'system';
                } catch (e) {}
                if (pref === 'dark-mode') pref = 'dark';
                if (pref !== 'light' && pref !== 'dark' && pref !== 'system') pref = 'system';
                var dark = pref === 'dark' || (pref === 'system' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark-mode', dark);
                document.documentElement.setAttribute('data-theme-preference', pref);
                document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
            })();
        </script>
        @stack('style')
    </head>
    <body class="hold-transition layout-top-nav layout-navbar-fixed">
        <script>
            document.body.classList.toggle('dark-mode', document.documentElement.classList.contains('dark-mode'));
            document.body.classList.toggle('light', !document.documentElement.classList.contains('dark-mode'));
        </script>
        @php
            if (!$errors->isEmpty()) {
                alert()->error('Notificación', implode('<br>', $errors->all()))->toToast()->toHtml();
            }
        @endphp
        <div class="wrapper">
            <!-- Preloader -->
            <div class="preloader flex-column justify-content-center align-items-center">
                <img class="animation__shake" src="{{ asset(Setting::getValue('app_logo')) }}" alt="{{ Setting::getName('app_name') }}" height="60" width="60">
            </div>

            <!-- Navbar -->
            @include('admin.layouts.navbar')
            @if (!empty($subscriptionBanner))
                <div class="alert alert-{{ $subscriptionBanner['level'] }} mb-0 rounded-0 text-center">
                    {{ $subscriptionBanner['text'] }}
                    <a href="{{ route('tenant.plan') }}">Ver mi plan</a>
                </div>
            @endif
            <!-- /.navbar -->

            <!-- Content Wrapper. Contains page content -->
            @yield('content')
            <!-- /.content-wrapper -->
            @yield('modal')
            @include('admin.layouts.modal')
            @include('sweetalert::alert')
            @include('admin.layouts.footer')
        </div>
        <!-- ./wrapper -->
        <!-- jQuery -->
        <script src="{{ asset('js/theme.js') }}"></script>
        <script src="{{ asset('template/admin/plugins/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('js/product-grid-filter.js') }}"></script>
        @yield('js')
        @include('admin.layouts.script')
        <!-- jQuery UI 1.11.4 -->
        <script src="{{ asset('template/admin/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
        <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
        <!-- DataTables  & Plugins -->
        <script src="{{ asset('template/admin/plugins/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('template/admin/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script>
            $.widget.bridge('uibutton', $.ui.button)
        </script>
        <!-- Bootstrap 4 -->
        <script src="{{ asset('template/admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <!-- overlayScrollbars -->
        <script src="{{ asset('template/admin/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
        <!-- AdminLTE App -->
        <script src="{{ asset('template/admin/dist/js/adminlte.js') }}"></script>
        @stack('script')
    </body>
</html>