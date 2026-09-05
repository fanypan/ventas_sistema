<?php

namespace Tests\Feature;

use Tests\TenantTestCase;

class HelpPageTest extends TenantTestCase
{
    public function test_guest_is_redirected_from_help(): void
    {
        $this->tenantGet('/admin/ayuda')->assertRedirect();
    }

    public function test_authenticated_user_can_open_help(): void
    {
        $this->actingAs($this->tenantUser)
            ->tenantGet('/admin/ayuda')
            ->assertOk()
            ->assertSee('Ayuda del POS')
            ->assertSee('Punto de venta')
            ->assertSee('Cobrar en el mostrador')
            ->assertSee('Registrar una compra')
            ->assertSee('F8')
            ->assertSee('F9')
            ->assertSee('Evitamos Ctrl+T');
    }

    public function test_pos_exposes_shortcuts_overlay_and_help_link(): void
    {
        $this->actingAs($this->tenantUser)
            ->tenantGet('/admin/pos')
            ->assertOk()
            ->assertSee('id="modalPosShortcuts"', false)
            ->assertSee('initPosShortcuts')
            ->assertSee('aria-keyshortcuts="F8 Enter"', false)
            ->assertSee('/admin/ayuda')
            ->assertSee('Atajos de esta pantalla');
    }

    public function test_purchase_create_exposes_shortcuts_overlay(): void
    {
        $this->actingAs($this->tenantUser)
            ->tenantGet('/admin/purchases/create')
            ->assertOk()
            ->assertSee('id="modalPosShortcuts"', false)
            ->assertSee('initPosShortcuts')
            ->assertSee('F8');
    }
}
