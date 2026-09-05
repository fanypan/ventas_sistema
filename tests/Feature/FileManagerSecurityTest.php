<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TenantTestCase;

class FileManagerSecurityTest extends TenantTestCase
{
    public function test_guest_cannot_initialize_file_manager_on_tenant_host(): void
    {
        $this->getJson('http://demo.localhost/file-manager/initialize')
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.'])
            ->assertJsonMissingPath('status');
    }

    public function test_guest_cannot_list_file_manager_content_on_tenant_host(): void
    {
        $this->getJson('http://demo.localhost/file-manager/content?disk=filemanager')
            ->assertUnauthorized();
    }

    public function test_file_manager_is_not_reachable_on_central_domain(): void
    {
        $this->getJson('http://localhost/file-manager/initialize')
            ->assertNotFound();
    }

    public function test_operator_cannot_use_file_manager(): void
    {
        $operator = $this->tenant->run(function () {
            $user = User::create([
                'name' => 'Cajero',
                'email' => 'cajero@demo.test',
                'password' => Hash::make('password'),
            ]);
            $user->assignRole('operator');

            return $user;
        });

        $this->actingAs($operator)
            ->getJson('http://demo.localhost/file-manager/initialize')
            ->assertForbidden();
    }

    public function test_admin_can_initialize_file_manager_on_dedicated_disk_only(): void
    {
        $response = $this->actingAs($this->tenantUser)
            ->getJson('http://demo.localhost/file-manager/initialize');

        $response->assertOk()
            ->assertJsonPath('config.leftDisk', 'filemanager');

        $disks = $response->json('config.disks');
        $this->assertIsArray($disks);
        $this->assertArrayHasKey('filemanager', $disks);
        $this->assertArrayNotHasKey('local', $disks);
        $this->assertArrayNotHasKey('public', $disks);

        $this->actingAs($this->tenantUser)
            ->json('GET', 'http://demo.localhost/file-manager/content', ['disk' => 'local'])
            ->assertOk()
            ->assertJsonPath('result.status', 'error')
            ->assertJsonPath('result.message', 'aclError');

        $this->actingAs($this->tenantUser)
            ->json('GET', 'http://demo.localhost/file-manager/content', ['disk' => 'filemanager'])
            ->assertOk();
    }
}
