<?php

namespace Tests\Unit;

use App\Support\PosHelp;
use PHPUnit\Framework\TestCase;

class PosHelpTest extends TestCase
{
    public function test_shortcuts_do_not_bind_browser_chords(): void
    {
        foreach (['sale', 'purchase'] as $context) {
            foreach (PosHelp::boundChords($context) as $chord) {
                $this->assertNotContains(
                    $chord,
                    PosHelp::BROWSER_RESERVED,
                    "El atajo {$chord} de {$context} choca con el navegador"
                );
            }
        }
    }

    public function test_sale_and_purchase_share_safe_function_keys(): void
    {
        $sale = PosHelp::boundChords('sale');
        $purchase = PosHelp::boundChords('purchase');

        $this->assertContains('F2', $sale);
        $this->assertContains('F8', $sale);
        $this->assertContains('F9', $sale);
        $this->assertContains('F2', $purchase);
        $this->assertContains('F8', $purchase);
        $this->assertNotContains('F9', $purchase);
        $this->assertNotContains('F5', $sale);
        $this->assertNotContains('F12', $purchase);
    }
}
