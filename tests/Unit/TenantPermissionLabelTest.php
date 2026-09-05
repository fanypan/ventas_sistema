<?php

namespace Tests\Unit;

use App\Support\TenantPermissionLabel;
use Tests\TestCase;

class TenantPermissionLabelTest extends TestCase
{
    public function test_groups_and_labels_crud_permissions(): void
    {
        $this->assertSame('sale', TenantPermissionLabel::groupKey('create sale'));
        $this->assertSame('Ventas', TenantPermissionLabel::groupLabel('void sale'));
        $this->assertSame('Crear', TenantPermissionLabel::actionLabel('create sale'));
        $this->assertSame('Anular', TenantPermissionLabel::actionLabel('void sale'));
        $this->assertSame('Archivos', TenantPermissionLabel::actionLabel('filemanager'));
        $this->assertSame('filemanager', TenantPermissionLabel::groupKey('filemanager'));
    }
}
