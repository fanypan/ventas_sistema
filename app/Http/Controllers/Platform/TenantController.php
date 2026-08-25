<?php

namespace App\Http\Controllers\Platform;

use App\Actions\Platform\CreateTenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreTenantRequest;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Billing\SubscriptionService;
use RealRashid\SweetAlert\Facades\Alert;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with(['plan', 'domains', 'subscription'])->latest()->paginate(20);

        return view('platform.tenants.index', compact('tenants'));
    }

    public function create()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return view('platform.tenants.create', compact('plans'));
    }

    public function store(StoreTenantRequest $request, CreateTenant $createTenant)
    {
        try {
            [$tenant, $password] = $createTenant->execute($request->validated());
        } catch (\Throwable $e) {
            report($e);

            Alert::error(
                'No se pudo crear el cliente',
                'Falló el aprovisionamiento. Revisá los logs o probá de nuevo con el mismo slug.'
            )->toToast();

            return back()->withInput();
        }

        Alert::success('Cliente creado', 'Se aprovisionó '.$tenant->url())->toToast();

        return redirect()->route('platform.tenants.show', $tenant)->with('plain_password', $password);
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['plan', 'domains', 'subscription.plan', 'payments.staff', 'subscriptions.events']);

        return view('platform.tenants.show', compact('tenant'));
    }

    public function suspend(Tenant $tenant, SubscriptionService $subscriptions)
    {
        $subscriptions->suspend($tenant);
        Alert::warning('Suspendido', $tenant->name)->toToast();

        return back();
    }

    public function reactivate(Tenant $tenant, SubscriptionService $subscriptions)
    {
        $subscriptions->reactivate($tenant);
        Alert::success('Reactivado', $tenant->name)->toToast();

        return back();
    }

    public function cancel(Tenant $tenant, SubscriptionService $subscriptions)
    {
        $subscriptions->cancel($tenant);
        Alert::info('Dado de baja', $tenant->name)->toToast();

        return back();
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        Alert::success('Eliminado', 'Se borró el tenant y su base.')->toToast();

        return redirect()->route('platform.tenants.index');
    }
}
