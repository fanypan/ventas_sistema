<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreManualPaymentRequest;
use App\Models\Tenant;
use App\Services\Billing\SubscriptionService;
use RealRashid\SweetAlert\Facades\Alert;

class PaymentController extends Controller
{
    public function create(Tenant $tenant)
    {
        $tenant->load('plan', 'subscription');

        return view('platform.payments.create', compact('tenant'));
    }

    public function store(StoreManualPaymentRequest $request, Tenant $tenant, SubscriptionService $subscriptions)
    {
        $data = $request->validated();

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('payment-receipts/'.$tenant->id, 'local');
        }

        $subscriptions->registerPayment($tenant, [
            'amount' => $data['amount'],
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'paid_at' => $data['paid_at'],
            'interval' => $data['interval'],
            'notes' => $data['notes'] ?? null,
            'attachment_path' => $path,
        ], $request->user('platform')?->id);

        Alert::success('Pago registrado', 'Se renovó el período de '.$tenant->name)->toToast();

        return redirect()->route('platform.tenants.show', $tenant);
    }
}
