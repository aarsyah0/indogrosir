<x-app-layout title="Dashboard">
    <div class="page-intro dashboard-intro">
        <div>
            <p class="dashboard-eyebrow">Indogrosir &middot; ATK &amp; ABT</p>
            <h2 class="dashboard-heading">Ringkasan operasional</h2>
            <p class="dashboard-description">Pantau pengajuan, persetujuan, dan kebutuhan persediaan Anda.</p>
        </div>
        <p class="dashboard-date"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <!-- Stat cards -->
    <div class="dashboard-stats grid grid-cols-1 gap-3 min-[480px]:grid-cols-2 sm:gap-4 xl:grid-cols-4">
        @foreach ($data['cards'] as $index => $card)
            @php
                $label = strtolower($card['label']);
                $statIcon = match (true) {
                    str_contains($label, 'periode') => 'calendar',
                    str_contains($label, 'barang aktif') => 'box',
                    str_contains($label, 'stok rendah') => 'alert',
                    str_contains($label, 'penerimaan') => 'package-check',
                    str_contains($label, 'siap dibeli'), str_contains($label, 'pembelian') => 'cart',
                    str_contains($label, 'pengeluaran'), str_contains($label, 'estimasi'), str_contains($label, 'total kebutuhan') => 'currency',
                    str_contains($label, 'qty') => 'scale',
                    str_contains($label, 'revisi'), str_contains($label, 'dikembalikan') => 'rotate',
                    str_contains($label, 'selesai') => 'check',
                    str_contains($label, 'masuk') => 'inbox',
                    str_contains($label, 'menunggu'), str_contains($label, 'proses') => 'clock',
                    default => 'clipboard',
                };
                $statTone = match ($statIcon) {
                    'alert', 'rotate' => 'red',
                    'calendar', 'scale' => 'gold',
                    'currency', 'check', 'package-check' => 'green',
                    'clipboard', 'inbox', 'box', 'cart', 'clock' => 'blue',
                    default => 'slate',
                };
            @endphp
            <div class="stat-card dashboard-stat-card" data-tone="{{ $statTone }}" style="--card-index: {{ $index }}">
                <div class="dashboard-stat-topline">
                    <span class="stat-label">{{ $card['label'] }}</span>
                    <x-context-icon :name="$statIcon" :tone="$statTone" class="dashboard-stat-icon" />
                </div>
                <p class="stat-value">{{ $card['value'] }}</p>
                <span class="dashboard-stat-rule" aria-hidden="true"></span>
            </div>
        @endforeach
    </div>

    @if (($data['role'] ?? '') === 'admin')
        @php
            $adminActions = [
                ['label' => 'Master barang', 'description' => 'Katalog dan harga referensi', 'route' => 'master.items.index', 'permission' => 'items.manage', 'icon' => 'package'],
                ['label' => 'Periode pengajuan', 'description' => 'Buka atau tutup periode', 'route' => 'master.periods.index', 'permission' => 'periods.manage', 'icon' => 'calendar'],
                ['label' => 'Manajemen pengguna', 'description' => 'Akun, divisi, dan peran', 'route' => 'users.index', 'permission' => 'users.manage', 'icon' => 'users'],
                ['label' => 'Laporan bulanan', 'description' => 'Ringkasan dan unduhan', 'route' => 'reports.index', 'permission' => 'reports.view', 'icon' => 'chart'],
            ];
        @endphp
        <section class="mt-5 card">
            <div class="card-header">
                <h2 class="card-title">Akses administrasi</h2>
                <span class="text-xs text-slate-400">Tugas yang paling sering dikelola admin</span>
            </div>
            <div class="card-body">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($adminActions as $action)
                        @if (auth()->user()->hasPermission($action['permission']))
                            @php
                                $actionTone = match ($action['icon']) {
                                    'package' => 'blue',
                                    'calendar' => 'gold',
                                    'users' => 'slate',
                                    default => 'red',
                                };
                            @endphp
                            <a href="{{ route($action['route']) }}" class="group flex items-start gap-3 rounded-xl border border-transparent p-3 transition duration-150 hover:border-slate-200 hover:bg-slate-50 hover:shadow-sm"
                                aria-label="{{ $action['label'] }}">
                                <x-context-icon :name="$action['icon']" :tone="$actionTone" class="dashboard-action-icon" />
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 truncate">{{ $action['label'] }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ $action['description'] }}</p>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if (($data['role'] ?? '') === 'ga')
        <div class="mt-5 grid gap-4 xl:grid-cols-2">
            <section class="card">
                <div class="card-header">
                    <h2 class="card-title">Kebutuhan bulanan</h2>
                    <span class="text-xs text-slate-500">Estimasi permintaan per bulan</span>
                </div>
                <div class="card-body">
                    @if (empty($data['monthlyNeeds']['labels']))
                        <div class="empty-state">
                            <p class="text-sm text-slate-500">Belum ada data kebutuhan.</p>
                        </div>
                    @else
                        <div class="relative h-64 w-full">
                            <canvas id="needsChart" role="img" aria-label="Grafik kebutuhan bulanan"></canvas>
                        </div>
                    @endif
                </div>
            </section>

            <section class="card">
                <div class="card-header">
                    <h2 class="card-title">Stok minimum</h2>
                    <span class="text-xs text-slate-500">Barang yang perlu diisi ulang</span>
                </div>
                <div class="card-body">
                    @if ($data['lowStock']->isEmpty())
                        <div class="empty-state">
                            <p class="text-sm text-slate-500">Tidak ada barang di bawah stok minimum.</p>
                        </div>
                    @else
                        <div class="space-y-0">
                            @foreach ($data['lowStock'] as $item)
                                <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-2 last:border-0">
                                    <span class="text-sm text-slate-700 min-w-0 flex-1 truncate">{{ $item->name }}</span>
                                    <span class="flex shrink-0 items-center gap-2 text-xs font-semibold text-rose-700">
                                        <span>{{ $item->currentStock() }}</span>
                                        <span class="text-slate-400">stok</span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <section class="mt-4 card">
            <div class="card-header">
                <h2 class="card-title">Kenaikan harga</h2>
                <span class="text-xs text-slate-500">Rasio kenaikan harga referensi</span>
            </div>
            <div class="card-body">
                @if ($data['priceIncreases']->isEmpty())
                    <div class="empty-state">
                        <p class="text-sm text-slate-500">Belum ada data pembanding harga.</p>
                    </div>
                @else
                    <div class="space-y-0">
                        @foreach ($data['priceIncreases'] as $row)
                            <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-2.5 last:border-0">
                                <span class="text-sm font-medium text-slate-800 truncate">{{ $row['item']->name }}</span>
                                <span class="flex shrink-0 items-center gap-2 text-xs font-semibold text-amber-700">
                                    <span class="text-amber-600">+{{ $row['pct'] }}%</span>
                                    <span class="text-slate-400">dari</span>
                                    <span>{{ format_money($row['old']) }}</span>
                                    <span aria-hidden="true">&rarr;</span>
                                    <span>{{ format_money($row['new']) }}</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if (($data['role'] ?? '') === 'staff' && ($data['activePeriod'] ?? null))
        <section class="mt-5 card">
            <div class="card-body">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-base font-semibold text-slate-900">Periode aktif: {{ $data['activePeriod']->name }}</p>
                        <p class="text-sm text-slate-500">Batas pengajuan: {{ format_date($data['activePeriod']->deadline_date) }}</p>
                    </div>
                    @can('create', App\Models\Requisition::class)
                        <a href="{{ route('requisitions.create') }}" class="btn-primary">Buat pengajuan</a>
                    @endcan
                </div>
            </div>
        </section>
    @endif

    @if (($data['role'] ?? '') === 'admin' || ($data['role'] ?? '') === 'ga')
        @if (isset($data['list']) || isset($data['queue']))
            <div class="mt-5 grid gap-4 xl:grid-cols-2">
                @if (isset($data['list']))
                    <section class="card overflow-hidden">
                        <div class="card-header">
                            <h2 class="card-title">Pengajuan terkini</h2>
                            <span class="text-xs text-slate-500">{{ $data['list']->count() }} pengajuan</span>
                        </div>
                        <div class="card-body">
                            <x-requisition-table :requisitions="$data['list']" />
                        </div>
                    </section>
                @endif
                @if (isset($data['queue']))
                    <section class="card overflow-hidden">
                        <div class="card-header">
                            <h2 class="card-title">Antrian persetujuan</h2>
                            <span class="text-xs text-slate-500">{{ $data['queue']->count() }} pengajuan</span>
                        </div>
                        <div class="card-body">
                            <x-requisition-table :requisitions="$data['queue']" />
                        </div>
                    </section>
                @endif
            </div>
        @endif
    @endif

    @if (!empty($data['monthlyNeeds']['labels']) && ($data['role'] ?? '') === 'ga')
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const ctx = document.getElementById('needsChart');
                    if (!ctx || !window.Chart) return;
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($data['monthlyNeeds']['labels']),
                            datasets: [{
                                label: 'Estimasi kebutuhan',
                                data: @json($data['monthlyNeeds']['data']),
                                borderColor: '#D71920',
                                backgroundColor: 'rgba(215, 25, 32, .08)',
                                fill: true,
                                tension: .25
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: true, position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } }
                            },
                            scales: {
                                x: { grid: { display: false } },
                                y: { beginAtZero: true, grid: { color: '#f1f5f9' } }
                            }
                        }
                    });
                });
            </script>
        @endpush
    @endif
</x-app-layout>
