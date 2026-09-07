<?php

namespace Tests\Unit;

use App\Services\Media\MediaUrl;
use Tests\TestCase;

class MediaUrlTest extends TestCase
{
    public function test_missing_default_logo_falls_back_to_public_brand_asset(): void
    {
        $paths = [
            public_path('storage/logo.png'),
            storage_path('app/public/logo.png'),
        ];

        $backups = [];
        foreach ($paths as $path) {
            if (is_file($path)) {
                $backup = $path.'.media-url-test';
                rename($path, $backup);
                $backups[$path] = $backup;
            }
        }

        try {
            $url = app(MediaUrl::class)->settingUrl('storage/logo.png');
            $this->assertSame(asset('brand/logo.png'), $url);
        } finally {
            foreach ($backups as $path => $backup) {
                rename($backup, $path);
            }
        }
    }

    public function test_missing_default_favicon_falls_back_to_public_brand_asset(): void
    {
        $paths = [
            public_path('storage/favicon.png'),
            storage_path('app/public/favicon.png'),
        ];

        $backups = [];
        foreach ($paths as $path) {
            if (is_file($path)) {
                $backup = $path.'.media-url-test';
                rename($path, $backup);
                $backups[$path] = $backup;
            }
        }

        try {
            $url = app(MediaUrl::class)->settingUrl('storage/favicon.png');
            $this->assertSame(asset('brand/favicon.png'), $url);
        } finally {
            foreach ($backups as $path => $backup) {
                rename($backup, $path);
            }
        }
    }
}
