<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\ManualPayment;
use App\Models\Tenant;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('platform.dashboard', [
            'tenants' => Tenant::count(),
            'active' => Tenant::active()->count(),
            'grace' => Tenant::grace()->count(),
            'suspended' => Tenant::restricted()->count(),
            'recentPayments' => ManualPayment::with('tenant')->latest('paid_at')->limit(8)->get(),
            'recentTenants' => Tenant::with('plan')->latest()->limit(8)->get(),
        ]);
    }
}
