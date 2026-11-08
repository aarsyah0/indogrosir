@props(['title', 'description' => null, 'action' => null, 'icon' => 'inbox'])
<div class="empty-state" role="region" aria-label="{{ $title }}">
    <svg aria-hidden="true" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        @switch($icon)
            @case('inbox')
                <path d="M4 4h16v13h-4l-2 3h-4l-2-3H4V4Z"/><path d="M6 8h12"/>
            @break
            @case('add')
                <path d="M12 5v14M5 12h14"/>
            @break
            @case('search')
                <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/>
            @break
            @case('document')
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>
            @break
            @case('chart')
                <path d="M4 19V5m0 14h17M8 15l4-4 3 2 5-6"/>
            @break
            @case('users')
                <circle cx="9" cy="7" r="3"/><path d="M3 21v-2a4 4 0 0 1 4-4h14a4 4 0 0 1 4 4v2"/>
            @break
            @default
                <path d="M12 4v16m8-8H4"/>
        @endswitch
    </svg>
    <p class="empty-title">{{ $title }}</p>
    @if ($description)
        <p class="empty-desc">{{ $description }}</p>
    @endif
    @if ($action)
        <div class="mt-3">
            {{ $action }}
        </div>
    @endif
</div>
