<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

    public function store(Request $request, SubscriptionService $subscriptions)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:63', 'unique:tenants,slug'],
            'ruc' => ['nullable', 'string', 'max:30'],
            'plan_id' => ['required', 'exists:plans,id'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email'],
            'interval' => ['required', 'in:monthly,yearly'],
            'brand_color' => ['nullable', 'string', 'max:20'],
        ]);

        $password = Str::random(12);
        $plan = Plan::findOrFail($data['plan_id']);

        $tenant = Tenant::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'ruc' => $data['ruc'] ?? null,
            'status' => Tenant::STATUS_PENDING,
            'plan_id' => $plan->id,
            'admin_name' => $data['admin_name'],
            'admin_email' => $data['admin_email'],
            'admin_password_hash' => Hash::make($password),
            'brand_color' => $data['brand_color'] ?? null,
            'setup_password' => $password,
        ]);

        $subscriptions->start($tenant, $plan, $data['interval']);

        Alert::success('Cliente creado', 'Se está aprovisionando '.$tenant->url())->toToast();

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
