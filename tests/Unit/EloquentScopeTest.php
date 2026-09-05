<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\SifenDocument;
use App\Models\Tenant;
use Modules\Financials\Entities\Caja;
use Modules\Products\Entities\Product;
use Modules\Sales\Entities\Sale;
use Modules\Sales\Entities\SaleInstallment;
use Tests\TestCase;

class EloquentScopeTest extends TestCase
{
    public function test_plan_scopes(): void
    {
        $this->assertStringContainsString('is_active', Plan::active()->toSql());
        $this->assertStringContainsString('is_public', Plan::listedOnLanding()->toSql());
    }

    public function test_catalog_and_sale_scopes(): void
    {
        $this->assertStringContainsString('status', Product::active()->toSql());
        $this->assertStringContainsString('stock', Product::active()->lowStock(3)->toSql());
        $this->assertStringContainsString('status', Sale::paid()->toSql());
        $this->assertStringContainsString('status', Sale::credit()->toSql());
        $this->assertStringContainsString('status', Caja::open()->toSql());
        $this->assertStringContainsString('status', SaleInstallment::pending()->toSql());
    }

    public function test_tenant_and_sifen_scopes(): void
    {
        $this->assertStringContainsString('status', Tenant::active()->toSql());
        $this->assertStringContainsString('status', Tenant::billable()->toSql());
        $this->assertStringContainsString('status', SifenDocument::countsTowardQuota()->toSql());
    }
}
