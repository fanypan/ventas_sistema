<?php

namespace App\Observability;

use Sentry\Event;
use Sentry\EventHint;

class SentryScrubber
{
    /** @var list<string> */
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'api_key',
        'apikey',
        'authorization',
        'cookie',
        'secret',
        'sifen_partner_token',
        'recaptcha',
        'recaptcha_secret_key',
        'recaptcha_site_key',
    ];

    public function __invoke(Event $event, ?EventHint $hint): ?Event
    {
        $request = $event->getRequest();
        if (is_array($request) && isset($request['data']) && is_array($request['data'])) {
            $request['data'] = $this->scrub($request['data']);
            $event->setRequest($request);
        }

        $extra = $event->getExtra();
        if ($extra !== []) {
            $event->setExtra($this->scrub($extra));
        }

        $event->setUser(null);

        return $event;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function scrub(array $payload): array
    {
        $clean = [];

        foreach ($payload as $key => $value) {
            if ($this->isSensitive((string) $key)) {
                $clean[$key] = '[filtered]';

                continue;
            }

            $clean[$key] = is_array($value) ? $this->scrub($value) : $value;
        }

        return $clean;
    }

    private function isSensitive(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (self::SENSITIVE_KEYS as $needle) {
            if ($normalized === $needle || str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }
}
