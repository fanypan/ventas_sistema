<?php

namespace App\Http\Controllers\Platform;

use App\Actions\Platform\CloneCatalog;
use App\Actions\Platform\CreateTenant;
use App\Actions\Platform\DeleteTenant;
use App\Actions\Platform\SendAdminInvite;
use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CloneCatalogRequest;
use App\Http\Requests\Platform\DestroyTenantRequest;
use App\Http\Requests\Platform\StoreTenantRequest;
use App\Http\Requests\Platform\UpdateTenantLogoRequest;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Billing\SubscriptionService;
use App\Services\Media\TenantLogoService;
use Illuminate\Support\Facades\Storage;
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
        $plans = Plan::active()->orderBy('sort_order')->get();
        $catalogSources = Tenant::catalogSources();

        return view('platform.tenants.create', compact('plans', 'catalogSources'));
    }

    public function store(StoreTenantRequest $request, CreateTenant $createTenant)
    {
        try {
            $tenant = $createTenant->execute($request->validated());
        } catch (\Throwable $e) {
            report($e);

            Alert::error(
                'No se pudo crear el cliente',
                'Falló el aprovisionamiento. Revisá los logs o probá de nuevo con el mismo slug.'
            )->toToast();

            return back()->withInput();
        }

        Alert::success(
            'Cliente creado',
            'Mandamos un enlace a '.$tenant->admin_email.' para definir la contraseña.'
        )->toToast();

        return redirect()->route('platform.tenants.show', $tenant);
    }

    public function show(Tenant $tenant, TenantLogoService $logos)
    {
        $tenant->load(['plan', 'domains', 'subscription.plan', 'payments.staff', 'subscriptions.events']);
        $catalogSources = Tenant::catalogSources($tenant);
        $logoPath = $logos->currentPath($tenant);
        $hasCustomLogo = TenantLogoService::isCustomPath($logoPath);

        return view('platform.tenants.show', compact('tenant', 'catalogSources', 'hasCustomLogo'));
    }

    public function updateLogo(UpdateTenantLogoRequest $request, Tenant $tenant, TenantLogoService $logos)
    {
        try {
            $logos->put($tenant, $request->file('logo'));
        } catch (BusinessRuleException $e) {
            Alert::error('No se pudo guardar el logo', $e->getMessage())->toToast();

            return back();
        }

        Alert::success('Logo actualizado', 'Va a verse en el login y el menú de '.$tenant->name.'.')->toToast();

        return back();
    }

    public function destroyLogo(Tenant $tenant, TenantLogoService $logos)
    {
        try {
            $logos->reset($tenant);
        } catch (BusinessRuleException $e) {
            Alert::error('No se pudo restablecer el logo', $e->getMessage())->toToast();

            return back();
        }

        Alert::success('Logo restablecido', 'Volvió el logo por defecto de '.$tenant->name.'.')->toToast();

        return back();
    }

    public function logo(Tenant $tenant, TenantLogoService $logos)
    {
        abort_unless($tenant->provisioned_at, 404);

        $path = $logos->currentPath($tenant);
        abort_unless(TenantLogoService::isCustomPath($path), 404);

        return $tenant->run(function () use ($path) {
            $disk = Storage::disk((string) config('media.public_disk', 'public'));
            abort_unless($disk->exists($path), 404);

            return $disk->response($path);
        });
    }

    public function cloneCatalog(CloneCatalogRequest $request, Tenant $tenant, CloneCatalog $cloneCatalog)
    {
        try {
            $source = Tenant::findOrFail($request->validated('source_id'));
            $result = $cloneCatalog->execute($source, $tenant, $request->boolean('copy_prices'));
        } catch (BusinessRuleException $e) {
            Alert::error('No se pudo copiar el catálogo', $e->getMessage())->toToast();

            return back();
        }

        Alert::success('Catálogo copiado', $result->message())->toToast();

        return back();
    }

    public function invite(Tenant $tenant, SendAdminInvite $sendAdminInvite)
    {
        try {
            $sendAdminInvite->execute($tenant);
        } catch (BusinessRuleException $e) {
            Alert::error('No se pudo reenviar', $e->getMessage())->toToast();

            return back();
        }

        Alert::success('Invitación enviada', 'Mandamos un enlace a '.$tenant->admin_email.'.')->toToast();

        return back();
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

    public function destroy(DestroyTenantRequest $request, Tenant $tenant, DeleteTenant $deleteTenant)
    {
        try {
            $deleteTenant->execute($tenant);
        } catch (\Throwable $e) {
            report($e);

            Alert::error(
                'No se pudo eliminar',
                'No se pudo borrar la base '.$tenant->database()->getName().'. Reintentá; si sigue, avisá a sistemas.'
            )->toToast();

            return back();
        }

        Alert::success('Eliminado', 'Se borró el cliente y su base.')->toToast();

        return redirect()->route('platform.tenants.index');
    }
}
