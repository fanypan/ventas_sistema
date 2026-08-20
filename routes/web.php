<?php

use App\Http\Controllers\Platform\AuthController;
use App\Http\Controllers\Platform\DashboardController;
use App\Http\Controllers\Platform\PaymentController;
use App\Http\Controllers\Platform\PlanController;
use App\Http\Controllers\Platform\TenantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if (in_array($request->getHost(), config('tenancy.central_domains', []), true)) {
        return app(\App\Http\Controllers\LandingController::class)->index();
    }

    return redirect('/login');
})->name('landing');

Route::middleware('central')->group(function () {

    Route::prefix('plataforma')->name('platform.')->group(function () {
        Route::middleware('guest:platform')->group(function () {
            Route::get('login', [AuthController::class, 'showLogin'])->name('login');
            Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
        });

        Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:platform');

        Route::middleware('auth:platform')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('clientes', [TenantController::class, 'index'])->name('tenants.index');
            Route::get('clientes/nuevo', [TenantController::class, 'create'])->name('tenants.create');
            Route::post('clientes', [TenantController::class, 'store'])->name('tenants.store');
            Route::get('clientes/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
            Route::post('clientes/{tenant}/suspender', [TenantController::class, 'suspend'])->name('tenants.suspend');
            Route::post('clientes/{tenant}/reactivar', [TenantController::class, 'reactivate'])->name('tenants.reactivate');
            Route::post('clientes/{tenant}/baja', [TenantController::class, 'cancel'])->name('tenants.cancel');
            Route::delete('clientes/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
            Route::get('clientes/{tenant}/pago', [PaymentController::class, 'create'])->name('payments.create');
            Route::post('clientes/{tenant}/pago', [PaymentController::class, 'store'])->name('payments.store');
            Route::get('planes', [PlanController::class, 'index'])->name('plans.index');
            Route::get('planes/{plan}/editar', [PlanController::class, 'edit'])->name('plans.edit');
            Route::put('planes/{plan}', [PlanController::class, 'update'])->name('plans.update');
        });
    });
});
