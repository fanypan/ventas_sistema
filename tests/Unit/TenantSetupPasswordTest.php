<?php

namespace Tests\Unit;

use App\Support\TenantSetupPassword;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class TenantSetupPasswordTest extends TestCase
{
    public function test_stores_encrypted_payload_and_pulls_once(): void
    {
        TenantSetupPassword::store('tid-1', 'ClavePlana-9x');

        $cached = cache()->get(TenantSetupPassword::cacheKey('tid-1'));
        $this->assertIsString($cached);
        $this->assertNotSame('ClavePlana-9x', $cached);
        $this->assertSame('ClavePlana-9x', Crypt::decryptString($cached));

        $this->assertSame('ClavePlana-9x', TenantSetupPassword::pull('tid-1'));
        $this->assertNull(TenantSetupPassword::pull('tid-1'));
    }
}
