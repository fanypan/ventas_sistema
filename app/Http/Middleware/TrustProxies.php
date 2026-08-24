<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    public function handle($request, \Closure $next)
    {
        $this->proxies = $this->configuredProxies();

        return parent::handle($request, $next);
    }

    /**
     * @return array<int, string>|string
     */
    public function configuredProxies(): array|string
    {
        $value = config('trustedproxy.proxies');

        if ($value === '*' || $value === '**') {
            return '*';
        }

        if ($value === 'private' || $value === 'docker') {
            return [
                '10.0.0.0/8',
                '172.16.0.0/12',
                '192.168.0.0/16',
            ];
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
