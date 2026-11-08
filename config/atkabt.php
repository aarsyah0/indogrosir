<?php

return [
    'currency_code' => env('ATKABT_CURRENCY_CODE', 'IDR'),
    'currency_symbol' => env('ATKABT_CURRENCY_SYMBOL', 'Rp'),
    'date_format' => env('ATKABT_DATE_FORMAT', 'd/m/Y'),
    'number_format' => env('ATKABT_NUMBER_FORMAT', 'ATK/ABT/{YYYY}/{MM}/{SEQ}'),
    'over_purchase_requires_authorization' => env('ATKABT_OVER_PURCHASE_AUTH', true),
    'unreasonable_qty_multiplier' => (float) env('ATKABT_UNREASONABLE_QTY_MULTIPLIER', 2),
    'email_notifications_enabled' => env('ATKABT_EMAIL_NOTIFICATIONS', false),
    'min_stock_notification' => env('ATKABT_MIN_STOCK_NOTIFICATION', true),
    'max_upload_kb' => (int) env('ATKABT_MAX_UPLOAD_KB', 5120),
    'allowed_upload_mimes' => ['pdf', 'jpg', 'jpeg', 'png'],
];
