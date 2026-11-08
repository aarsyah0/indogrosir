@props([
    'columns' => [],
    'rows' => [],
    'empty' => null,
    'headClass' => 'bg-navy-50',
])
@if ($rows)
    <div class="table-wrap">
        <table class="min-w-full text-sm">
            <thead class="{{ $headClass }}">
                <tr>
                    @foreach ($columns as $column)
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            {{ $column['label'] ?? $column }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        @foreach ($columns as $key => $column)
                            @php
                                $value = data_get($row, $key);
                                $cellClass = data_get($column, 'class', '');
                                $align = data_get($column, 'align', 'left');
                                $sortable = data_get($column, 'sortable', false);
                            @endphp
                            <td
                                class="{{ $cellClass }}"
                                style="{{ $align === 'right' ? 'text-align: right' : ($align === 'center' ? 'text-align: center' : '')) }}"
                            >
                                {{ $value ?? '' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    @if ($empty)
        <div class="px-4 py-10 text-center">
            <p class="text-sm text-slate-500">{{ $empty }}</p>
        </div>
    @else
        <div class="px-4 py-10 text-center text-slate-500">
            <p class="text-sm text-slate-500">Tidak ada data.</p>
        </div>
    @endif
@endif
