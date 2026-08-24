<?php

namespace Tests\Unit;

use App\Support\RucParaguay;
use Tests\TestCase;

class RucParaguayTest extends TestCase
{
    public function test_accepts_consumidor_final(): void
    {
        $this->assertTrue(RucParaguay::isValid('0'));
        $this->assertSame('0', RucParaguay::format('0'));
    }

    public function test_validates_known_ruc_with_dv(): void
    {
        $this->assertTrue(RucParaguay::isValid('1946520-3'));
        $this->assertTrue(RucParaguay::isValid('80009735-1'));
        $this->assertSame('80009735-1', RucParaguay::format('800097351'));
    }

    public function test_rejects_invalid_dv(): void
    {
        $this->assertFalse(RucParaguay::isValid('80009735-9'));
        $this->assertFalse(RucParaguay::isValid('1946520-0'));
    }

    public function test_rejects_consumidor_final_when_disabled(): void
    {
        $this->assertFalse(RucParaguay::isValid('0', allowConsumidorFinal: false));
    }

    public function test_calculate_dv_matches_dnit_algorithm(): void
    {
        $this->assertSame(3, RucParaguay::calculateDv('1946520'));
        $this->assertSame(1, RucParaguay::calculateDv('80009735'));
    }
}
