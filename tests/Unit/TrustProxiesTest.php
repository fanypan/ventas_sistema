<?php

namespace Tests\Unit;

use App\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Tests\TestCase;

class TrustProxiesTest extends TestCase
{
    protected function tearDown(): void
    {
        Request::setTrustedProxies([], Request::HEADER_X_FORWARDED_FOR);

        parent::tearDown();
    }

    public function test_empty_config_ignores_forwarded_for(): void
    {
        config(['trustedproxy.proxies' => '']);

        $request = $this->forwardedRequest('203.0.113.10', '1.2.3.4');
        $this->app->make(TrustProxies::class)->handle($request, fn ($r) => $r);

        $this->assertSame('203.0.113.10', $request->ip());
    }

    public function test_private_trusts_docker_bridge(): void
    {
        config(['trustedproxy.proxies' => 'private']);

        $request = $this->forwardedRequest('172.18.0.5', '1.2.3.4');
        $this->app->make(TrustProxies::class)->handle($request, fn ($r) => $r);

        $this->assertSame('1.2.3.4', $request->ip());
    }

    public function test_private_does_not_trust_public_remote(): void
    {
        config(['trustedproxy.proxies' => 'private']);

        $request = $this->forwardedRequest('203.0.113.10', '1.2.3.4');
        $this->app->make(TrustProxies::class)->handle($request, fn ($r) => $r);

        $this->assertSame('203.0.113.10', $request->ip());
    }

    public function test_star_trusts_any_remote(): void
    {
        config(['trustedproxy.proxies' => '*']);

        $request = $this->forwardedRequest('203.0.113.10', '1.2.3.4');
        $this->app->make(TrustProxies::class)->handle($request, fn ($r) => $r);

        $this->assertSame('1.2.3.4', $request->ip());
    }

    public function test_configured_proxies_parses_csv_and_aliases(): void
    {
        $middleware = $this->app->make(TrustProxies::class);

        config(['trustedproxy.proxies' => '10.0.0.1, 10.0.0.2']);
        $this->assertSame(['10.0.0.1', '10.0.0.2'], $middleware->configuredProxies());

        config(['trustedproxy.proxies' => 'docker']);
        $this->assertSame([
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
        ], $middleware->configuredProxies());

        config(['trustedproxy.proxies' => '**']);
        $this->assertSame('*', $middleware->configuredProxies());
    }

    private function forwardedRequest(string $remoteAddr, string $forwardedFor): Request
    {
        return Request::create('/', 'GET', [], [], [], [
            'REMOTE_ADDR' => $remoteAddr,
            'HTTP_X_FORWARDED_FOR' => $forwardedFor,
        ]);
    }
}
