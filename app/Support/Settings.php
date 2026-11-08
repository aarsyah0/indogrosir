<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Settings
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $fallback = config('atkabt.'.$key, $default);

        try {
            if (! Schema::hasTable('system_settings')) {
                return $fallback;
            }
        } catch (\Throwable) {
            return $fallback;
        }

        $settings = Cache::remember('system_settings', 60, function () {
            return SystemSetting::query()->pluck('value', 'key')->all();
        });

        if (! array_key_exists($key, $settings)) {
            return $fallback;
        }

        $value = $settings[$key];

        if (in_array($value, ['true', 'false', '1', '0'], true)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $value;
    }

    public static function put(string $key, mixed $value): void
    {
        SystemSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? 'true' : 'false') : (string) $value]
        );

        Cache::forget('system_settings');
    }
}
