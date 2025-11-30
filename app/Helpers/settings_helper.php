<?php

use App\Models\SettingModel;

if (!function_exists('get_settings_map')) {
    function get_settings_map(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        try {
            $model = new SettingModel();
            $cache = $model->getAllAsMap();
        } catch (\Throwable $e) {
            $cache = [];
        }
        return $cache;
    }
}

if (!function_exists('get_setting')) {
    function get_setting(string $key, $default = null)
    {
        $map = get_settings_map();
        return array_key_exists($key, $map) ? $map[$key] : $default;
    }
}


