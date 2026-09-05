<link rel="manifest" href="{{ route('pwa.manifest') }}">
<meta name="theme-color" content="#4f46e5">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ \Illuminate\Support\Str::limit((string) (Setting::getValue('app_name') ?: config('app.name')), 12, '') }}">
<link rel="apple-touch-icon" href="{{ route('pwa.icon', ['size' => 192]) }}?v={{ app(\App\Services\Pwa\TenantPwa::class)->iconVersion() }}">
