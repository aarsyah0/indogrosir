@props(['id', 'label', 'placeholder', 'resetRoute'])

<form method="GET" class="master-search master-search--inline" role="search">
    <label class="sr-only" for="{{ $id }}">{{ $label }}</label>
    <div class="master-search-controls">
        <div class="master-search-field">
            <input id="{{ $id }}" type="search" name="q" value="{{ request('q') }}"
                class="input master-search-input pl-9" placeholder="{{ $placeholder }}" />
            <svg aria-hidden="true" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-4.35-4.35"/></svg>
        </div>
        <button type="submit" class="btn-secondary">Cari</button>
        @if (request()->filled('q'))
            <a href="{{ route($resetRoute) }}" class="btn-ghost">Reset</a>
        @endif
    </div>
</form>
