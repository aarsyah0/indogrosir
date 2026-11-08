@props([
    'header',
    'headerRight',
    'subheader',
    'actions',
    'class' => '',
])
<div class="card {{ $class }}">
    <div class="flex flex-col gap-3">
        @if ($header ?? false)
            <div class="card-header">
                <div>
                    @if ($subheader ?? false)
                        <h2 class="card-title text-base">{{ $header }}</h2>
                        <p class="card-subtitle">{{ $subheader }}</p>
                    @else
                        <h2 class="card-title">{{ $header }}</h2>
                    @endif
                </div>
                @if ($actions ?? false)
                    <div class="flex flex-wrap items-center gap-2">
                        @if (is_array($actions))
                            @foreach ($actions as $action)
                                {{ $action }}
                            @endforeach
                        @else
                            {{ $actions }}
                        @endif
                    </div>
                @endif
            </div>
        @endif
        <div class="card-body">
            {{ $slot }}
        </div>
    </div>
</div>
