<?php

namespace Tests\Unit;

use App\Support\TenantAssignableRole;
use Tests\TestCase;

class TenantAssignableRoleTest extends TestCase
{
    public function test_superadmin_is_protected_regardless_of_case(): void
    {
        $this->assertTrue(TenantAssignableRole::isProtected('superadmin'));
        $this->assertTrue(TenantAssignableRole::isProtected(' SuperAdmin '));
        $this->assertFalse(TenantAssignableRole::isProtected('admin'));
        $this->assertFalse(TenantAssignableRole::isProtected('cajero'));
        $this->assertFalse(TenantAssignableRole::isProtected(null));
    }
}
