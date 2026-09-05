<?php

namespace App\Services\Pwa;

use App\Helpers\SettingHelper;
use App\Services\Media\ImageOptimizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

class TenantPwa
{
    public const ICON_SIZES = [32, 192, 512];

    public function __construct(private ImageOptimizer $optimizer) {}

    /**
     * @return array<string, mixed>
     */
    public function manifest(): array
    {
        $name = (string) (SettingHelper::getValue('app_name') ?: config('app.name'));
        $slug = (string) (tenant('slug') ?: 'pos');
        $icon192 = route('pwa.icon', ['size' => 192], false);
        $icon512 = route('pwa.icon', ['size' => 512], false);

        return [
            'id' => '/?pwa='.$slug,
            'name' => $name,
            'short_name' => $this->shortName($name),
            'description' => 'Caja, stock y créditos de '.$name,
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'display_override' => ['standalone', 'minimal-ui'],
            'background_color' => '#f1f5f9',
            'theme_color' => '#4f46e5',
            'lang' => 'es-PY',
            'dir' => 'ltr',
            'orientation' => 'any',
            'icons' => [
                ['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
                ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'shortcuts' => [
                [
                    'name' => 'Nueva venta',
                    'short_name' => 'Vender',
                    'url' => '/admin/pos',
                    'icons' => [['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png']],
                ],
                [
                    'name' => 'Clientes',
                    'short_name' => 'Clientes',
                    'url' => '/admin/customers',
                    'icons' => [['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png']],
                ],
            ],
        ];
    }

    public function iconVersion(): string
    {
        return $this->fingerprint();
    }

    public function iconPng(int $size): string
    {
        $size = in_array($size, self::ICON_SIZES, true) ? $size : 192;
        $key = 'pwa-icon:'.(tenant('id') ?? 'none').':'.$size.':'.$this->fingerprint();

        return Cache::remember($key, 3600, fn () => $this->optimizer->squarePng(
            $this->sourceBinary(),
            $size,
            [79, 70, 229],
        ));
    }

    public function cacheName(): string
    {
        return 'tenant-pwa-v1-'.(tenant('id') ?? 'pos');
    }

    private function shortName(string $name): string
    {
        if (mb_strlen($name) <= 12) {
            return $name;
        }

        return mb_substr($name, 0, 11).'…';
    }

    private function fingerprint(): string
    {
        $logo = (string) (SettingHelper::getValue('app_logo') ?? '');
        $appName = (string) (SettingHelper::getValue('app_name') ?? '');

        return substr(hash('sha256', $logo.'|'.$appName), 0, 16);
    }

    private function sourceBinary(): ?string
    {
        foreach ([SettingHelper::getValue('app_logo'), SettingHelper::getValue('app_favicon')] as $value) {
            $binary = $this->readLocal($value);
            if ($binary !== null) {
                return $binary;
            }
        }

        return null;
    }

    private function readLocal(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return null;
        }

        $legacy = ltrim($value, '/');
        $public = (string) config('media.public_disk', 'public');

        try {
            if (Storage::disk($public)->exists($legacy)) {
                return Storage::disk($public)->get($legacy);
            }
        } catch (Throwable) {
        }

        $candidates = [
            public_path($legacy),
            storage_path('app/public/'.$legacy),
        ];

        if (str_starts_with($legacy, 'storage/')) {
            $candidates[] = storage_path('app/public/'.substr($legacy, strlen('storage/')));
        }

        foreach ($candidates as $path) {
            if (is_file($path)) {
                $contents = file_get_contents($path);
                if ($contents !== false) {
                    return $contents;
                }
            }
        }

        return null;
    }
}
