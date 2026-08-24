<?php

namespace Tests\Feature;

use Tests\TenantTestCase;

class PosXssEscapeTest extends TenantTestCase
{
    public function test_pos_view_escapes_dynamic_names(): void
    {
        $html = $this->actingAs($this->tenantUser)
            ->tenantGet('/admin/pos')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('function escapeHtml(', $html);
        $this->assertStringContainsString('escapeHtml(d.product', $html);
        $this->assertStringContainsString('escapeHtml(c.name)', $html);
        $this->assertStringNotContainsString('${d.product.description}', $html);
        $this->assertStringNotContainsString('${c.name}', $html);
    }
}
