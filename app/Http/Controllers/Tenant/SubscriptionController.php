<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function show(): View
    {
        $tenant = Tenant::with(['plan', 'subscription', 'payments'])->find(tenant('id'));

        return view('tenant.subscription', [
            'tenant' => $tenant,
            'whatsapp' => config('saas.whatsapp'),
        ]);
    }
}
