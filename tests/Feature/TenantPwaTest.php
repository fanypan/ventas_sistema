<?php

namespace Tests\Feature;

use Tests\TenantTestCase;

class TenantPwaTest extends TenantTestCase
{
    public function test_manifest_is_installable(): void
    {
        $response = $this->tenantGet('/manifest.webmanifest');

        $response->assertOk()
            ->assertJsonPath('display', 'standalone')
            ->assertJsonPath('start_url', '/')
            ->assertJsonPath('theme_color', '#4f46e5')
            ->assertJsonPath('name', 'Comercio Demo')
            ->assertJsonFragment(['url' => '/admin/customers']);

        $this->assertStringContainsString(
            'application/manifest+json',
            (string) $response->headers->get('content-type')
        );
    }

    public function test_service_worker_is_public(): void
    {
        $this->tenantGet('/sw.js')
            ->assertOk()
            ->assertHeader('Service-Worker-Allowed', '/')
            ->assertSee('tenant-pwa-v1', false)
            ->assertSee('/offline', false);
    }

    public function test_icons_are_square_pngs(): void
    {
        foreach ([32, 192, 512] as $size) {
            $response = $this->tenantGet("/pwa/icon-{$size}.png");

            $response->assertOk();
            $this->assertSame('image/png', $response->headers->get('content-type'));

            $info = getimagesizefromstring($response->getContent());
            $this->assertIsArray($info);
            $this->assertSame($size, $info[0]);
            $this->assertSame($size, $info[1]);
        }

        $this->tenantGet('/pwa/icon-64.png')->assertNotFound();
    }

    public function test_favicon_is_served_on_tenant_domain(): void
    {
        $response = $this->tenantGet('/favicon.ico');

        $response->assertOk();
        $this->assertSame('image/png', $response->headers->get('content-type'));

        $info = getimagesizefromstring($response->getContent());
        $this->assertIsArray($info);
        $this->assertSame(32, $info[0]);
        $this->assertSame(32, $info[1]);
    }

    public function test_login_and_app_link_the_manifest(): void
    {
        $this->tenantGet('/')
            ->assertOk()
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('js/pwa.js', false)
            ->assertSee('apple-mobile-web-app-capable', false);

        $this->actingAs($this->tenantUser)
            ->tenantGet('/admin/dashboard')
            ->assertOk()
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('data-pwa-install', false);
    }

    public function test_central_domain_does_not_serve_tenant_pwa(): void
    {
        $this->get('http://localhost/manifest.webmanifest')->assertNotFound();
        $this->get('http://localhost/sw.js')->assertNotFound();
    }
}
