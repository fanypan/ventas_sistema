<?php

namespace App\Helpers;

use App\Models\Setting;
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

    private static function available(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
