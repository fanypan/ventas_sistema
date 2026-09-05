<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreManualPaymentRequest;
use App\Models\ManualPayment;
use App\Models\Tenant;
use App\Services\Billing\SubscriptionService;
use App\Services\Media\MediaUrl;
use Illuminate\Support\Facades\Storage;
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
            $path = $request->file('attachment')->store(
                'payment-receipts/'.$tenant->id,
                config('media.payment_disk')
            );
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

    public function attachment(Tenant $tenant, ManualPayment $payment, MediaUrl $urls)
    {
        abort_unless((string) $payment->tenant_id === (string) $tenant->id, 404);
        abort_unless($payment->attachment_path, 404);

        $disk = (string) config('media.payment_disk');

        abort_unless(Storage::disk($disk)->exists($payment->attachment_path), 404);

        if ((config("filesystems.disks.{$disk}.driver") ?? 'local') === 's3') {
            return redirect()->away($urls->temporaryUrl($disk, $payment->attachment_path));
        }

        return Storage::disk($disk)->response($payment->attachment_path);
    }
}
