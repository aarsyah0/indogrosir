@props(['name', 'tone' => 'blue', 'class' => ''])

@php
    $paths = [
        'clipboard' => 'M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2M9 3h6v4H9zM8 12h8M8 16h6',
        'clock' => 'M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'box' => 'm12 3 9 5-9 5-9-5 9-5Zm-9 5v9l9 5 9-5V8m-9 5v9',
        'package' => 'm12 3 9 5-9 5-9-5 9-5Zm-9 5v9l9 5 9-5V8m-9 5v9',
        'calendar' => 'M4 5h16v16H4zM8 3v4m8-4v4M4 10h16',
        'inbox' => 'M4 4h16v13h-4l-2 3h-4l-2-3H4V4Zm0 9h5l1 2h4l1-2h5',
        'check' => 'm5 12 4 4L19 6',
        'shield' => 'M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Zm-3-11 2 2 4-4',
        'cart' => 'M3 4h2l2.2 11.3A2 2 0 0 0 9.2 17h8.6a2 2 0 0 0 2-1.6L21 8H6m4 13h.01M17 21h.01',
        'package-check' => 'm12 3 9 5v8m-9-3L3 8m9 5v8m-9-5V8l9-5 9 5v5m-5 4 2 2 4-4',
        'currency' => 'M12 2v20m5-16H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6',
        'users' => 'M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-7a4 4 0 0 1 0 8m2 3a4 4 0 0 1 3 4v2',
        'chart' => 'M4 19V5m0 14h17M8 15l4-4 3 2 5-6',
        'rotate' => 'M20 7v5h-5M4 17v-5h5m-4-2a8 8 0 0 1 13.7-3L20 12M4 12l1.3 5A8 8 0 0 0 19 14',
        'scale' => 'M12 3v18m-7-2h14M5 7h14M7 7l-4 8h8L7 7Zm10 0-4 8h8l-4-8Z',
        'alert' => 'M12 9v4m0 4h.01M10.3 3.9 2.4 18a2 2 0 0 0 1.8 3h15.6a2 2 0 0 0 1.8-3l-7.9-14.1a2 2 0 0 0-3.4 0Z',
        'arrow-in' => 'M12 3v12m-5-5 5 5 5-5M4 21h16',
        'arrow-out' => 'M12 21V9m5 5-5-5-5 5M4 3h16',
    ];
    $tone = in_array($tone, ['red', 'blue', 'gold', 'green', 'slate'], true) ? $tone : 'blue';
@endphp

<span aria-hidden="true" {{ $attributes->class(['context-icon', 'context-icon-' . $tone, $class]) }}>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="{{ $paths[$name] ?? $paths['clipboard'] }}" />
    </svg>
</span>
