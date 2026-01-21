<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingService
{
    public function get(string $key, $default = null)
    {
        return Cache::remember(
            "system_setting_{$key}",
            now()->addHour(),
            fn () => optional(SystemSetting::where('key', $key)->first())->value ?? $default
        );
    }

    public function set(string $key, $value, string $type = 'text'): void
    {
        SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );

        Cache::forget("system_setting_{$key}");
    }
}
