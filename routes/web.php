<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\Platform\AuthController;
use App\Http\Controllers\Platform\DashboardController;
use App\Http\Controllers\Platform\PaymentController;
use App\Http\Controllers\Platform\PlanController;
use App\Http\Controllers\Platform\RoleController;
use App\Http\Controllers\Platform\TenantController;
use App\Http\Controllers\Platform\UserController;
use Illuminate\Support\Facades\Route;

foreach (array_values(config('tenancy.central_domains', [])) as $index => $domain) {
    Route::domain($domain)->get('/', [LandingController::class, 'index'])
        ->name($index === 0 ? 'landing' : 'landing.'.$domain);
}

Route::middleware(['central', 'platform.access'])->group(function () {

    Route::prefix(config('saas.platform_path'))->name('platform.')->group(function () {
        Route::middleware(['guest:platform', 'throttle:platform-login'])->group(function () {
            Route::get('login', [AuthController::class, 'showLogin'])->name('login');
            Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
        });

        Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:platform');

        Route::middleware('auth:platform')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

            Route::middleware('platform.permission:tenants.view')->group(function () {
                Route::get('clientes', [TenantController::class, 'index'])->name('tenants.index');
            });

            Route::middleware('platform.permission:tenants.create')->group(function () {
                Route::get('clientes/nuevo', [TenantController::class, 'create'])->name('tenants.create');
                Route::post('clientes', [TenantController::class, 'store'])->name('tenants.store');
            });

            Route::middleware('platform.permission:tenants.view')->group(function () {
                Route::get('clientes/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
            });

            Route::post('clientes/{tenant}/invitar', [TenantController::class, 'invite'])
                ->middleware('platform.permission:tenants.update')
                ->name('tenants.invite');
            Route::get('clientes/{tenant}/logo', [TenantController::class, 'logo'])
                ->middleware('platform.permission:tenants.view')
                ->name('tenants.logo');
            Route::post('clientes/{tenant}/logo', [TenantController::class, 'updateLogo'])
                ->middleware('platform.permission:tenants.update')
                ->name('tenants.logo.update');
            Route::delete('clientes/{tenant}/logo', [TenantController::class, 'destroyLogo'])
                ->middleware('platform.permission:tenants.update')
                ->name('tenants.logo.destroy');
            Route::post('clientes/{tenant}/catalogo', [TenantController::class, 'cloneCatalog'])
                ->middleware('platform.permission:tenants.catalog')
                ->name('tenants.catalog');
            Route::post('clientes/{tenant}/suspender', [TenantController::class, 'suspend'])
                ->middleware('platform.permission:tenants.update')
                ->name('tenants.suspend');
            Route::post('clientes/{tenant}/reactivar', [TenantController::class, 'reactivate'])
                ->middleware('platform.permission:tenants.update')
                ->name('tenants.reactivate');
            Route::post('clientes/{tenant}/baja', [TenantController::class, 'cancel'])
                ->middleware('platform.permission:tenants.cancel')
                ->name('tenants.cancel');
            Route::delete('clientes/{tenant}', [TenantController::class, 'destroy'])
                ->middleware('platform.permission:tenants.delete')
                ->name('tenants.destroy');

            Route::middleware('platform.permission:payments.create')->group(function () {
                Route::get('clientes/{tenant}/pago', [PaymentController::class, 'create'])->name('payments.create');
                Route::post('clientes/{tenant}/pago', [PaymentController::class, 'store'])->name('payments.store');
            });

            Route::get('clientes/{tenant}/pagos/{payment}/comprobante', [PaymentController::class, 'attachment'])
                ->middleware('platform.permission:tenants.view')
                ->name('payments.attachment');

            Route::get('planes', [PlanController::class, 'index'])
                ->middleware('platform.permission:plans.view')
                ->name('plans.index');
            Route::middleware('platform.permission:plans.update')->group(function () {
                Route::get('planes/{plan}/editar', [PlanController::class, 'edit'])->name('plans.edit');
                Route::put('planes/{plan}', [PlanController::class, 'update'])->name('plans.update');
            });

            Route::middleware('platform.permission:users.view')->group(function () {
                Route::get('equipo', [UserController::class, 'index'])->name('users.index');
            });
            Route::middleware('platform.permission:users.create')->group(function () {
                Route::get('equipo/nuevo', [UserController::class, 'create'])->name('users.create');
                Route::post('equipo', [UserController::class, 'store'])->name('users.store');
            });

            Route::middleware('platform.permission:roles.view')->group(function () {
                Route::get('equipo/roles', [RoleController::class, 'index'])->name('roles.index');
            });
            Route::middleware('platform.permission:roles.create')->group(function () {
                Route::get('equipo/roles/nuevo', [RoleController::class, 'create'])->name('roles.create');
                Route::post('equipo/roles', [RoleController::class, 'store'])->name('roles.store');
            });
            Route::middleware('platform.permission:roles.update')->group(function () {
                Route::get('equipo/roles/{role}/editar', [RoleController::class, 'edit'])->name('roles.edit');
                Route::put('equipo/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
            });
            Route::delete('equipo/roles/{role}', [RoleController::class, 'destroy'])
                ->middleware('platform.permission:roles.delete')
                ->name('roles.destroy');

            Route::middleware('platform.permission:users.update')->group(function () {
                Route::get('equipo/{platformUser}/editar', [UserController::class, 'edit'])->name('users.edit');
                Route::put('equipo/{platformUser}', [UserController::class, 'update'])->name('users.update');
            });
            Route::delete('equipo/{platformUser}', [UserController::class, 'destroy'])
                ->middleware('platform.permission:users.delete')
                ->name('users.destroy');
        });
    });
});
