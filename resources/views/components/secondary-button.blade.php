<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-secondary disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
