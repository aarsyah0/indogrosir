@props([
    'name' => null,
    'id' => null,
    'options' => [],
    'optionsExpression' => null,
    'selected' => '',
    'placeholder' => 'Pilih opsi',
    'searchPlaceholder' => 'Cari pilihan…',
    'required' => false,
])

@php
    $searchOptions = collect($options)->map(fn ($option) => [
        'value' => (string) data_get($option, 'value', ''),
        'label' => (string) data_get($option, 'label', ''),
    ])->values();
@endphp

<div
    x-data="searchableSelect({ options: @if ($optionsExpression) {{ $optionsExpression }} @else @js($searchOptions) @endif, value: @js((string) $selected), required: @js($required) })"
    x-modelable="selected"
    {{ $attributes->whereStartsWith('x-model') }}
    {{ $attributes->except(['x-model', 'x-bind:name', 'x-bind:id', 'aria-label'])->merge(['class' => 'relative']) }}
    x-id="['searchable-select']"
    @click.outside="close()"
    @resize.window="positionMenu()"
    @scroll.window.capture="positionMenu()"
>
    <input type="hidden" @if ($name) name="{{ $name }}" @endif {{ $attributes->whereStartsWith('x-bind:name') }} x-model="selected">
    <div class="relative">
        <input
            x-ref="search"
            @if ($id) id="{{ $id }}" @endif
            {{ $attributes->whereStartsWith('x-bind:id') }}
            {{ $attributes->whereStartsWith('aria-label') }}
            type="search"
            role="combobox"
            aria-autocomplete="list"
            aria-haspopup="listbox"
            :aria-expanded="open.toString()"
            :aria-controls="$id('searchable-select') + '-listbox'"
            :aria-activedescendant="open && filteredOptions[activeIndex] ? $id('searchable-select') + '-option-' + activeIndex : null"
            :value="open ? query : selectedLabel"
            @focus="startSearch()"
            @input="search($event); validate()"
            @keydown.arrow-down.prevent="moveActive(1)"
            @keydown.arrow-up.prevent="moveActive(-1)"
            @keydown.enter.prevent="chooseActive()"
            @keydown.escape.prevent="close()"
            @keydown.tab="close()"
            @keydown="validate()"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            spellcheck="false"
            @if ($required) aria-required="true" @endif
            class="input w-full pr-16"
        >
        <div class="absolute inset-y-0 right-1 flex items-center gap-0.5">
            <button
                type="button"
                x-show="selected"
                x-cloak
                @click="clear(); $refs.search.focus()"
                class="inline-flex h-8 w-8 items-center justify-center rounded text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                aria-label="Hapus pilihan"
            >
                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="m5 5 10 10M15 5 5 15" /></svg>
            </button>
            <button
                type="button"
                @click="toggle()"
                class="inline-flex h-8 w-8 items-center justify-center rounded text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                aria-label="Tampilkan pilihan"
                :aria-expanded="open.toString()"
            >
                <svg aria-hidden="true" class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m5 7.5 5 5 5-5" /></svg>
            </button>
        </div>
    </div>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            x-transition.opacity.duration.100ms
            :id="$id('searchable-select') + '-listbox'"
            role="listbox"
            :style="`top: ${menuTop}px; left: ${menuLeft}px; width: ${menuWidth}px`"
            class="fixed z-[100] max-h-60 overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
            @click.stop
        >
            <template x-for="(option, index) in filteredOptions" :key="option.value">
                <button
                    type="button"
                    role="option"
                    :id="$id('searchable-select') + '-option-' + index"
                    :aria-selected="(option.value === selected).toString()"
                    @mouseenter="activeIndex = index"
                    @mousedown.prevent
                    @click="choose(option)"
                    class="block w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-red-50 hover:text-red-800"
                    :class="activeIndex === index ? 'bg-slate-50' : ''"
                    x-text="option.label"
                ></button>
            </template>
            <p x-show="filteredOptions.length === 0" class="px-3 py-3 text-sm text-slate-500">{{ __('Tidak ada pilihan yang cocok.') }}</p>
        </div>
    </template>
    <p class="sr-only" aria-live="polite" x-text="open ? filteredOptions.length + ' pilihan ditemukan' : ''"></p>
</div>
