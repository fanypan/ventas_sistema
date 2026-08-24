<?php

namespace Tests\Unit;

use App\Models\ManualPayment;
use App\Models\Tenant;
use Tests\TestCase;

class TenantStatusLabelTest extends TestCase
{
    public function test_status_labels_are_spanish(): void
    {
        $tenant = new Tenant(['status' => Tenant::STATUS_GRACE]);

        $this->assertSame('En gracia', $tenant->statusLabel());
        $this->assertSame('warn', $tenant->statusTone());
    }

    public function test_pending_status_uses_info_tone(): void
    {
        $tenant = new Tenant(['status' => Tenant::STATUS_PENDING]);

        $this->assertSame('Pendiente', $tenant->statusLabel());
        $this->assertSame('info', $tenant->statusTone());
    }

    public function test_payment_method_label(): void
    {
        $payment = new ManualPayment(['method' => ManualPayment::METHOD_CASH]);

        $this->assertSame('Efectivo', $payment->methodLabel());
    }
}
