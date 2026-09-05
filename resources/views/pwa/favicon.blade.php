@php
    $iconVersion = app(\App\Services\Pwa\TenantPwa::class)->iconVersion();
@endphp
<link rel="icon" href="{{ route('pwa.favicon') }}?v={{ $iconVersion }}" type="image/png" sizes="32x32">
<link rel="icon" href="{{ route('pwa.icon', ['size' => 192]) }}?v={{ $iconVersion }}" type="image/png" sizes="192x192">
