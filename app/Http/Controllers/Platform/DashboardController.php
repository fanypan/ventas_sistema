<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\ManualPayment;
use App\Models\Tenant;

class DashboardController extends Controller
{
    public function index()
    {
        return view('platform.dashboard', [
            'tenants' => Tenant::count(),
            'active' => Tenant::where('status', Tenant::STATUS_ACTIVE)->count(),
            'grace' => Tenant::where('status', Tenant::STATUS_GRACE)->count(),
            'suspended' => Tenant::whereIn('status', [Tenant::STATUS_SUSPENDED, Tenant::STATUS_READONLY])->count(),
            'recentPayments' => ManualPayment::with('tenant')->latest('paid_at')->limit(8)->get(),
            'recentTenants' => Tenant::with('plan')->latest()->limit(8)->get(),
        ]);
    }
}
