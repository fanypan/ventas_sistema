<?php

namespace App\Helpers;

use App\Models\Setting;

class SettingHelper
{
    public static function getValue(string $key)
    {
        $setting = Setting::where(['key' => $key])->first();
        return $setting ? $setting->value : null;
    }

    public static function getName(string $key)
    {
        $setting = Setting::where(['key' => $key])->first();
        return $setting ? $setting->name : null;
    }

    public static function getType(string $key)
    {
        $setting = Setting::where(['key' => $key])->first();
        return $setting ? $setting->type : null;
    }

    public static function getExt(string $key)
    {
        $setting = Setting::where(['key' => $key])->first();
        return $setting ? $setting->ext : null;
    }
}
