<?php

namespace App\Http\Middleware;

use App\Http\Responses\JsonEnvelope;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class EnsureTenantSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = tenant();
        if (! $tenant instanceof Tenant) {
            return $next($request);
        }

        if ($this->isAuthRoute($request)) {
            return $next($request);
        }

        if ($tenant->status === Tenant::STATUS_PENDING) {
            return response()->view('tenant.pending', ['tenant' => $tenant], 503);
        }

        if ($tenant->isBlocked()) {
            if (JsonEnvelope::wantsJson($request)) {
                return JsonEnvelope::error('La cuenta está en pausa. Contactá a AranduTech.', null, 403);
            }

            return response()->view('tenant.suspended', ['tenant' => $tenant], 403);
        }

        if ($tenant->isReadOnly() && $this->isMutating($request)) {
            if (JsonEnvelope::wantsJson($request)) {
                return JsonEnvelope::error('La cuenta está en solo lectura hasta regularizar el pago.', null, 403);
            }

            return back()->with('error', 'Tu cuenta está en solo lectura. Regularizá el pago para volver a vender.');
        }

        view()->share('subscriptionTenant', $tenant);
        view()->share('subscriptionBanner', $this->banner($tenant));

        return $next($request);
    }

    private function isAuthRoute(Request $request): bool
    {
        return $request->routeIs('login', 'logout', 'index', 'password.setup.show', 'password.setup.store')
            || $request->is('login', 'logout', 'activar');
    }

    private function isMutating(Request $request): bool
    {
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && ! $request->routeIs('logout');
    }

    private function banner(Tenant $tenant): ?array
    {
        $subscription = $tenant->subscription;
        $ends = $subscription?->ends_at;

        if ($subscription?->isLifetime()) {
            return null;
        }

        if ($tenant->status === Tenant::STATUS_GRACE) {
            return [
                'level' => 'warning',
                'text' => 'Tu plan venció. Tenés hasta '.optional($subscription?->grace_ends_at)->format('d/m/Y').' de gracia para pagar.',
            ];
        }

        if ($tenant->status === Tenant::STATUS_READONLY) {
            return [
                'level' => 'danger',
                'text' => 'Cuenta en solo lectura. El POS está pausado hasta registrar el pago.',
            ];
        }

        if ($ends && now()->diffInDays($ends, false) <= config('saas.reminder_days_before') && $ends->isFuture()) {
            return [
                'level' => 'info',
                'text' => 'Tu plan vence el '.$ends->format('d/m/Y').'. Pagá por transferencia o efectivo a AranduTech.',
            ];
        }

        return null;
    }
}
