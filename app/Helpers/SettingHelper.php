<?php

namespace App\Helpers;

use App\Models\Setting;
use App\Services\Media\MediaUrl;
use Illuminate\Support\Facades\Schema;

class SettingHelper
{
    public static function getValue(string $key)
    {
        if (! static::available()) {
            return $key === 'app_name' ? config('app.name') : null;
        }

        $setting = Setting::where(['key' => $key])->first();

        return $setting ? $setting->value : null;
    }

    public static function getName(string $key)
    {
        if (! static::available()) {
            return null;
        }

        $setting = Setting::where(['key' => $key])->first();

        return $setting ? $setting->name : null;
    }

    public static function getType(string $key)
    {
        if (! static::available()) {
            return null;
        }

        $setting = Setting::where(['key' => $key])->first();

        return $setting ? $setting->type : null;
    }

    public static function getExt(string $key)
    {
        if (! static::available()) {
            return null;
        }

        $setting = Setting::where(['key' => $key])->first();

        return $setting ? $setting->ext : null;
    }

    public static function fileUrl(?string $key = null, ?string $value = null): string
    {
        if ($key !== null && $value === null) {
            $value = static::getValue($key);
        }

        return app(MediaUrl::class)->settingUrl($value);
    }

    private static function available(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
