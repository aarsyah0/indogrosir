<?php

use App\Support\Settings;
use Illuminate\Support\Carbon;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Settings::get($key, $default);
    }
}

if (! function_exists('format_date')) {
    function format_date(null|string|Carbon $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $date = $value instanceof Carbon ? $value : Carbon::parse($value);

        return $date->format((string) setting('date_format', 'd/m/Y'));
    }
}

if (! function_exists('format_money')) {
    function format_money(null|int|float|string $amount): string
    {
        $value = (float) ($amount ?? 0);
        $symbol = (string) setting('currency_symbol', 'Rp');

        return $symbol.' '.number_format($value, 0, ',', '.');
    }
}

if (! function_exists('status_label')) {
    function status_label(string $status): string
    {
        return \App\Enums\RequisitionStatus::tryFrom($status)?->label() ?? $status;
    }
}

if (! function_exists('badge_class')) {
    function badge_class(string $tone): string
    {
        return match ($tone) {
            'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            'red' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
            'amber' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
            'orange' => 'bg-orange-50 text-orange-800 ring-orange-600/20',
            default => 'bg-slate-100 text-slate-700 ring-slate-500/20',
        };
    }
}
