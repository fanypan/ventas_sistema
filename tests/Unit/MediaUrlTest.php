<?php

namespace Tests\Unit;

use App\Services\Media\MediaUrl;
use Tests\TestCase;

class MediaUrlTest extends TestCase
{
    public function test_legacy_default_logo_uses_public_brand_asset(): void
    {
        $url = app(MediaUrl::class)->settingUrl('storage/logo.png');

        $this->assertSame(asset('brand/logo.png'), $url);
    }

    public function test_brand_logo_setting_uses_public_brand_asset(): void
    {
        $url = app(MediaUrl::class)->settingUrl('brand/logo.png');

        $this->assertSame(asset('brand/logo.png'), $url);
    }

    public function test_legacy_default_favicon_uses_public_brand_asset(): void
    {
        $url = app(MediaUrl::class)->settingUrl('storage/favicon.png');

        $this->assertSame(asset('brand/favicon.png'), $url);
    }

    public function test_default_logo_binary_can_be_read_for_reports(): void
    {
        $dataUri = app(MediaUrl::class)->settingDataUri('storage/logo.png');

        $this->assertNotNull($dataUri);
        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);
    }
}
