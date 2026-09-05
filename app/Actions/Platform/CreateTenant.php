<?php

namespace App\Actions\Platform;

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Billing\SubscriptionService;
use App\Services\Media\TenantLogoService;
use App\Support\RucParaguay as RucParaguaySupport;
use Illuminate\Http\UploadedFile;

class CreateTenant
{
    public function __construct(
        private SubscriptionService $subscriptions,
        private TenantLogoService $logos,
    ) {}

    public function execute(array $data): Tenant
    {
        $plan = Plan::findOrFail($data['plan_id']);

        $payload = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'ruc' => ! empty($data['ruc']) ? RucParaguaySupport::format($data['ruc']) : null,
            'status' => Tenant::STATUS_PENDING,
            'plan_id' => $plan->id,
            'admin_name' => $data['admin_name'],
            'admin_email' => $data['admin_email'],
            'brand_color' => $data['brand_color'] ?? null,
        ];

        if (! empty($data['catalog_source_id'])) {
            $payload['catalog_source_id'] = $data['catalog_source_id'];
        }

        if (($data['logo'] ?? null) instanceof UploadedFile) {
            $payload['pending_logo_path'] = $this->logos->storePending($data['logo']);
        }

        $tenant = Tenant::create($payload)->fresh();

        $this->subscriptions->start($tenant, $plan, $data['interval']);

        return $tenant;
    }
}
