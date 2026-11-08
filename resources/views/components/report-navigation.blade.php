@php
    $tabs = [
        ['label' => 'Rekap pembelian', 'route' => 'recap.index'],
        ['label' => 'Stok & mutasi', 'route' => 'stock.index'],
        ['label' => 'Kebutuhan', 'route' => 'needs.index'],
        ['label' => 'Laporan bulanan', 'route' => 'reports.index'],
        ['label' => 'Grafik', 'route' => 'charts.index'],
    ];
@endphp

<nav aria-label="Menu monitoring dan laporan" class="mb-5 -mx-1 overflow-x-auto">
    <div class="flex min-w-max gap-1 border-b border-slate-200 px-1">
        @foreach ($tabs as $tab)
            @php($active = request()->routeIs($tab['route']))
            <a href="{{ route($tab['route']) }}" @class([
                'inline-flex min-h-11 items-center border-b-2 px-3 text-sm font-medium transition-colors duration-150',
                'border-brand text-brand' => $active,
                'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' => !$active,
            ]) @if ($active) aria-current="page" @endif>
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>
</nav>
