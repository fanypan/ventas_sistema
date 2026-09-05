<?php

namespace Tests\Feature;

use App\Actions\SetAdminPassword;
use App\Http\Middleware\PreventRequestForgery;
use App\Mail\TenantAdminInviteMail;
use App\Models\Plan;
use App\Models\PlatformUser;
use App\Models\Tenant;
use App\Models\User;
use App\Support\AdminInvite;
use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\CleansTenantArtifacts;
use Tests\TestCase;

class AdminPasswordSetupTest extends TestCase
{
    use CleansTenantArtifacts;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanupTenantArtifacts();

        config([
            'tenancy.central_domains' => ['localhost', '127.0.0.1'],
            'saas.tenant_base_domain' => 'localhost',
            'queue.default' => 'sync',
        ]);

        $this->seed([
            PlanSeeder::class,
            PlatformUserSeeder::class,
        ]);
    }

    public function test_provisioning_sends_invite_mail_without_plaintext_password(): void
    {
        Mail::fake();

        $tenant = $this->provisionTenant('inviteshop', 'admin@invite.test');

        Mail::assertSent(TenantAdminInviteMail::class, function (TenantAdminInviteMail $mail) use ($tenant) {
            $html = $mail->render();

            return $mail->hasTo('admin@invite.test')
                && str_contains($html, '/activar')
                && str_contains($html, 'signature=')
                && str_contains($html, $tenant->url())
                && ! str_contains($html, 'Contraseña:');
        });

        $this->assertNull($tenant->admin_password_set_at);
        $this->assertTrue($tenant->adminNeedsPassword());

        $tenant->run(function () {
            $user = User::where('email', 'admin@invite.test')->firstOrFail();
            $this->assertTrue($user->must_change_password);
        });
    }

    public function test_platform_store_does_not_flash_a_password(): void
    {
        Mail::fake();

        $response = $this->actingAs(PlatformUser::first(), 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post('http://localhost/'.config('saas.platform_path').'/clientes', [
                'name' => 'Alta HTTP',
                'slug' => 'altahttp',
                'plan_id' => Plan::first()->id,
                'admin_name' => 'Ada Admin',
                'admin_email' => 'ada@alta.test',
                'interval' => 'monthly',
            ]);

        $tenant = Tenant::where('slug', 'altahttp')->firstOrFail();
        $this->rememberTenantArtifact($tenant->getTenantKey());

        $response->assertRedirect(route('platform.tenants.show', $tenant));
        $response->assertSessionMissing('plain_password');

        $this->actingAs(PlatformUser::first(), 'platform')
            ->get('http://localhost/'.config('saas.platform_path').'/clientes/'.$tenant->id)
            ->assertOk()
            ->assertSee('Reenviar invitación')
            ->assertDontSee('Contraseña inicial');
    }

    public function test_admin_cannot_login_until_password_is_set(): void
    {
        Mail::fake();
        $this->provisionTenant('nologinshop', 'admin@nologin.test');

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->from('http://nologinshop.localhost/')
            ->post('http://nologinshop.localhost/', [
                'email' => 'admin@nologin.test',
                'password' => 'password',
            ])
            ->assertRedirect('http://nologinshop.localhost/')
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    public function test_signed_invite_lets_admin_set_password_and_enter(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant('activarshop', 'admin@activar.test');
        $url = AdminInvite::url($tenant);

        $this->get($url)
            ->assertOk()
            ->assertSee('Definí tu contraseña');

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->post($url, [
                'password' => 'ClaveNueva99',
                'password_confirmation' => 'ClaveNueva99',
            ])
            ->assertRedirect();

        $this->assertAuthenticated('web');

        $tenant->refresh();
        $this->assertNotNull($tenant->admin_password_set_at);
        $this->assertFalse($tenant->adminNeedsPassword());

        $this->post('http://activarshop.localhost/logout');
        $this->assertGuest('web');

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->post('http://activarshop.localhost/', [
                'email' => 'admin@activar.test',
                'password' => 'ClaveNueva99',
            ])
            ->assertRedirect();

        $this->assertAuthenticated('web');
    }

    public function test_invalid_invite_signature_shows_expired_page(): void
    {
        Mail::fake();
        $this->provisionTenant('vencidoshop', 'admin@vencido.test');

        $this->get('http://vencidoshop.localhost/activar?expires=1&signature=invalida')
            ->assertForbidden()
            ->assertSee('Este enlace ya no vale');
    }

    public function test_used_invite_redirects_to_login(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant('usadoshop', 'admin@usado.test');

        $tenant->run(function () use ($tenant) {
            $user = User::where('email', $tenant->admin_email)->firstOrFail();
            app(SetAdminPassword::class)->execute($user, 'ClaveNueva99');
        });

        $this->get(AdminInvite::url($tenant->fresh()))
            ->assertRedirect($tenant->url());
    }

    public function test_staff_can_resend_invite_until_password_is_set(): void
    {
        Mail::fake();
        $tenant = $this->provisionTenant('reenvioshop', 'admin@reenvio.test');
        $path = config('saas.platform_path');

        $this->actingAs(PlatformUser::first(), 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post("http://localhost/{$path}/clientes/{$tenant->id}/invitar")
            ->assertRedirect();

        Mail::assertSent(TenantAdminInviteMail::class, 2);

        $tenant->run(function () use ($tenant) {
            $user = User::where('email', $tenant->admin_email)->firstOrFail();
            app(SetAdminPassword::class)->execute($user, 'ClaveNueva99');
        });

        $this->actingAs(PlatformUser::first(), 'platform')
            ->withoutMiddleware(PreventRequestForgery::class)
            ->post("http://localhost/{$path}/clientes/{$tenant->id}/invitar")
            ->assertRedirect();

        Mail::assertSent(TenantAdminInviteMail::class, 2);
    }

    private function provisionTenant(string $slug, string $email): Tenant
    {
        $tenant = Tenant::create([
            'name' => 'Comercio '.$slug,
            'slug' => $slug,
            'status' => Tenant::STATUS_PENDING,
            'plan_id' => Plan::first()->id,
            'admin_name' => 'Ada Admin',
            'admin_email' => $email,
        ]);
        $this->rememberTenantArtifact($tenant->getTenantKey());

        return $tenant->fresh();
    }

    protected function tearDown(): void
    {
        $this->cleanupTenantArtifacts();

        parent::tearDown();
    }
}
