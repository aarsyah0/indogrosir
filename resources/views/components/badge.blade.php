@props(['tone' => 'default', 'label' => null, 'classes' => ''])
<span class="badge {{ $tone === 'default' ? 'badge-default' : match($tone) {
    'primary' => 'badge-primary',
    'success' => 'badge-success',
    'warning' => 'badge-warning',
    'danger' => 'badge-danger',
    'ghost' => 'badge-ghost',
} . ($classes ? ' ' . $classes : '') }}">
    @if ($label !== null)
        {{ $label }}
    @else
        {{ $slot }}
    @endif
</span>
