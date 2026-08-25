<?php

namespace App\Actions\Platform;

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Billing\SubscriptionService;
use App\Support\RucParaguay as RucParaguaySupport;
use App\Support\TenantSetupPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTenant
{
    public function __construct(private SubscriptionService $subscriptions)
    {
    }

    /**
     * @return array{0: Tenant, 1: string}
     */
    public function execute(array $data): array
    {
        $password = Str::random(12);
        $plan = Plan::findOrFail($data['plan_id']);

        app()->instance(TenantSetupPassword::PENDING, $password);

        try {
            $tenant = Tenant::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'ruc' => $data['ruc'] ? RucParaguaySupport::format($data['ruc']) : null,
                'status' => Tenant::STATUS_PENDING,
                'plan_id' => $plan->id,
                'admin_name' => $data['admin_name'],
                'admin_email' => $data['admin_email'],
                'admin_password_hash' => Hash::make($password),
                'brand_color' => $data['brand_color'] ?? null,
            ]);
        } finally {
            app()->forgetInstance(TenantSetupPassword::PENDING);
        }

        $this->subscriptions->start($tenant, $plan, $data['interval']);

        return [$tenant, $password];
    }
}
