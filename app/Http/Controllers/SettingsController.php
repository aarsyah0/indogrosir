<?php

namespace App\Http\Controllers;

use App\Support\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        $keys = [
            'currency_code', 'currency_symbol', 'date_format', 'number_format',
            'over_purchase_requires_authorization', 'unreasonable_qty_multiplier',
            'email_notifications_enabled', 'min_stock_notification',
        ];
        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = setting($key);
        }

        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'currency_code' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'date_format' => ['required', 'string', 'max:20'],
            'number_format' => ['required', 'string', 'max:80'],
            'unreasonable_qty_multiplier' => ['required', 'numeric', 'min:1'],
            'over_purchase_requires_authorization' => ['sometimes', 'boolean'],
            'email_notifications_enabled' => ['sometimes', 'boolean'],
            'min_stock_notification' => ['sometimes', 'boolean'],
        ]);
        foreach (['currency_code', 'currency_symbol', 'date_format', 'number_format', 'unreasonable_qty_multiplier'] as $key) {
            Settings::put($key, $data[$key]);
        }
        Settings::put('over_purchase_requires_authorization', $request->boolean('over_purchase_requires_authorization'));
        Settings::put('email_notifications_enabled', $request->boolean('email_notifications_enabled'));
        Settings::put('min_stock_notification', $request->boolean('min_stock_notification'));

        return back()->with('success', 'Pengaturan disimpan.');
    }
}
