<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TenantTestCase;

class UserPasswordAndToastTest extends TenantTestCase
{
    public function test_user_password_must_be_at_least_eight_characters(): void
    {
        $this->actingAs($this->tenantUser)
            ->from('http://demo.localhost/admin/user')
            ->tenantPost('/admin/user', [
                'name' => 'Corto',
                'email' => 'corto@demo.test',
                'password' => '1234567',
                'role' => 'operator',
            ])
            ->assertRedirect('http://demo.localhost/admin/user')
            ->assertSessionHasErrors('password');

        $this->tenant->run(function () {
            $this->assertDatabaseMissing('users', ['email' => 'corto@demo.test']);
        });
    }

    public function test_user_create_toast_does_not_render_html(): void
    {
        $this->actingAs($this->tenantUser)
            ->tenantPost('/admin/user', [
                'name' => '<img src=x onerror=alert(1)>',
                'email' => 'xss-user@demo.test',
                'password' => '12345678',
                'role' => 'operator',
            ])
            ->assertRedirect();

        $this->tenant->run(function () {
            $this->assertTrue(User::where('email', 'xss-user@demo.test')->exists());
        });

        $config = json_decode((string) session('alert.config'), true);
        $this->assertIsArray($config);
        $this->assertArrayNotHasKey('html', $config);
        $this->assertArrayHasKey('text', $config);
        $this->assertStringContainsString('<img src=x onerror=alert(1)>', $config['text']);
    }
}
