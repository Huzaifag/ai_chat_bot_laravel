<?php

use App\Services\SystemSettingService;

if (!function_exists('system_setting')) {
    /**
     * Get a system setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function system_setting(string $key, $default = null)
    {
        $service = app(SystemSettingService::class);
        return $service->get($key, $default);
    }
}

if (!function_exists('set_system_setting')) {
    /**
     * Set a system setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @return void
     */
    function set_system_setting(string $key, $value, string $type = 'text'): void
    {
        $service = app(SystemSettingService::class);
        $service->set($key, $value, $type);
    }
}
