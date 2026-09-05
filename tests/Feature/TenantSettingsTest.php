<?php

namespace Tests\Feature;

use App\Http\Middleware\PreventRequestForgery;
use App\Models\Setting;
use Tests\TenantTestCase;

class TenantSettingsTest extends TenantTestCase
{
    public function test_guest_is_redirected_from_settings(): void
    {
        $this->tenantGet('/admin/setting')->assertRedirect();
    }

    public function test_authenticated_user_sees_settings_layout(): void
    {
        $this->actingAs($this->tenantUser)
            ->tenantGet('/admin/setting')
            ->assertOk()
            ->assertSee('Configuración')
            ->assertSee('Empresa')
            ->assertSee('Identidad')
            ->assertSee('Contacto')
            ->assertSee('Nombre de la empresa')
            ->assertSee('Nombre en pantalla')
            ->assertSee('Guardar cambios')
            ->assertSee('Cambiar archivo')
            ->assertSee('id="settings-company"', false);
    }

    public function test_settings_update_persists_and_keeps_tab(): void
    {
        $this->actingAs($this->tenantUser)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->put('http://demo.localhost/admin/setting', [
                'key' => ['company_name'],
                'value' => ['Almacén Central'],
                'tab' => 'company',
            ])
            ->assertRedirect()
            ->assertRedirectContains('#settings-company');

        $this->tenant->run(function () {
            $this->assertSame('Almacén Central', Setting::where('key', 'company_name')->value('value'));
        });
    }
}
