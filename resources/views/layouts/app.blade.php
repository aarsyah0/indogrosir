<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — Indogrosir | ATK/ABT</title>
    <link rel="icon" type="image/png" href="https://www.indogrosir.co.id/images/logo/logo_2feb2026.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen" x-data="{ sidebar: false, sidebarCollapsed: false, userMenu: false }" @keydown.escape.window="sidebar = false; userMenu = false">
    @php
        $currentUser = auth()->user();
        $navGroups = [];
        $navIconPaths = [
            'home' => 'M3 10.5 12 3l9 7.5v9a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5v-9ZM9 21v-6h6v6',
            'inbox' => 'M4 4h16v13h-4l-2 3h-4l-2-3H4V4Zm0 9h5l1 2h4l1-2h5',
            'document' => 'M6 3h8l4 4v14H6V3Zm8 0v5h5M9 12h6M9 16h6',
            'check' => 'm5 12 4 4L19 6',
            'search' => 'M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm6-2 4 4',
            'shield' => 'M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Zm-3-11 2 2 4-4',
            'cart' => 'M3 4h2l2.2 11.3A2 2 0 0 0 9.2 17h8.6a2 2 0 0 0 2-1.6L21 8H6m4 13h.01M17 21h.01',
            'package' => 'm12 3 9 5-9 5-9-5 9-5Zm-9 5v9l9 5 9-5V8m-9 5v9',
            'building' => 'M4 21V5l8-3 8 3v16M8 8h2m4 0h2M8 12h2m4 0h2M8 16h2m4 0h2M2 21h20',
            'calendar' => 'M4 5h16v16H4V5Zm4-3v6m8-6v6M4 10h16',
            'chart' => 'M4 19V5m0 14h17M8 15l4-4 3 2 5-6',
            'bell' => 'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 12h4',
            'users' =>
                'M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-7a4 4 0 0 1 0 8m2 3a4 4 0 0 1 3 4v2',
            'settings' =>
                'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm0-5v2m0 14v2m9-9h-2M5 12H3m15.4-6.4-1.4 1.4M7 17l-1.4 1.4m12.8 0L17 17M7 7 5.6 5.6',
        ];
        foreach ($navItems ?? [] as $navItem) {
            $routeName = $navItem['route'];
            $groupName = match (true) {
                $routeName === 'dashboard' => 'Ringkasan',
                in_array($routeName, ['inbox.index', 'requisitions.index']) || str_starts_with($routeName, 'approvals.')
                    => 'Pengajuan & Persetujuan',
                in_array($routeName, ['procurements.index', 'receipts.index']) => 'Pengadaan',
                in_array($routeName, ['recap.index', 'stock.index', 'needs.index', 'reports.index', 'charts.index'])
                    => 'Monitoring & Laporan',
                str_starts_with($routeName, 'master.') => 'Master Data',
                in_array($routeName, ['notifications.index', 'users.index', 'settings.edit', 'audit.index'])
                    => 'Administrasi & Audit',
                default => 'Lainnya',
            };
            $navGroups[$groupName][] = $navItem;
        }
        $navGroupOrder = [
            'Ringkasan',
            'Pengajuan & Persetujuan',
            'Pengadaan',
            'Monitoring & Laporan',
            'Master Data',
            'Administrasi & Audit',
            'Lainnya',
        ];
        $orderedNavGroups = array_filter(
            array_replace(array_fill_keys($navGroupOrder, []), $navGroups),
            static fn(array $items): bool => $items !== []
        );
    @endphp

    <div class="min-h-screen lg:flex">
        <!-- Mobile overlay -->
        <div x-show="sidebar" x-cloak x-transition.opacity.duration.200ms class="fixed inset-0 z-30 bg-slate-950/50 backdrop-blur-[2px] lg:hidden"
            @click="sidebar=false" aria-hidden="true"></div>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-40 flex w-72 transform flex-col border-r border-slate-200 bg-white shadow-xl transition-[width,transform] duration-200 ease-out lg:static lg:min-h-screen lg:w-64 lg:flex-shrink-0 lg:shadow-none"
            :class="[sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0', sidebarCollapsed ? 'lg:w-20' : 'lg:w-64']"
            :aria-label="sidebarCollapsed ? 'Navigasi utama diciutkan' : 'Navigasi utama'">
            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-200 px-4 transition-[padding] duration-200"
                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : 'lg:px-5'">
                <div class="min-w-0 flex-1 transition-opacity duration-150" :class="sidebarCollapsed ? 'lg:hidden' : ''">
                    <x-indogrosir-logo class="h-8 max-w-36 shrink-0 object-left" />
                    <p class="mt-0.5 truncate text-[10px] font-medium text-slate-500">ATK &amp; ABT</p>
                </div>
                <button type="button" @click="sidebarCollapsed = !sidebarCollapsed"
                    class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 focus-visible:ring-2 focus-visible:ring-brand-500 lg:inline-flex"
                    :aria-label="sidebarCollapsed ? 'Perluas navigasi' : 'Ciutkan navigasi'"
                    :title="sidebarCollapsed ? 'Perluas navigasi' : 'Ciutkan navigasi'"
                    :aria-expanded="(!sidebarCollapsed).toString()">
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform duration-200" :class="sidebarCollapsed ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16m6-10 2 2-2 2"/></svg>
                </button>
            </div>

            <nav aria-label="Navigasi utama" class="flex-1 overflow-y-auto py-4 transition-[padding] duration-200"
                :class="sidebarCollapsed ? 'px-1.5 lg:px-1.5' : 'px-3'">
                @foreach ($orderedNavGroups as $groupLabel => $items)
                    @php
                        $groupIsActive = collect($items)->contains(function (array $navItem): bool {
                            return request()->routeIs($navItem['route'] . '*') ||
                                request()->routeIs(str_replace('.index', '', $navItem['route']) . '.*');
                        });
                    @endphp
                    <details class="nav-section" @if ($groupIsActive) open @endif>
                        <summary title="{{ $groupLabel }}" class="mb-1 flex min-h-11 cursor-pointer list-none items-center justify-between rounded-lg px-3 py-2 text-[10px] font-semibold uppercase tracking-[.12em] text-slate-400 transition-colors duration-150 hover:bg-slate-50 hover:text-slate-700"
                            :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                            <span class="flex items-center gap-2">
                                <svg aria-hidden="true" class="h-3 w-3" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 4h10M3 8h6M3 12h4" stroke-linecap="round"/></svg>
                                <span class="transition-opacity duration-150" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ $groupLabel }}</span>
                            </span>
                            <svg :class="sidebarCollapsed ? 'lg:hidden' : ''" aria-hidden="true" class="nav-chevron h-3 w-3 transition-transform duration-200" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 3 5 5-5 5" /></svg>
                        </summary>
                        <div class="space-y-0.5">
                            @foreach ($items as $item)
                                @php
                                    $icon = match ($item['icon']) {
                                        'home' => 'home',
                                        'inbox' => 'inbox',
                                        'check' => 'check',
                                        'shield' => 'shield',
                                        'search' => 'search',
                                        'cart' => 'cart',
                                        'package', 'box', 'layers', 'truck' => 'package',
                                        'building', 'users' => 'building',
                                        'calendar' => 'calendar',
                                        'trend', 'chart' => 'chart',
                                        'bell' => 'bell',
                                        'settings' => 'settings',
                                        default => 'document',
                                    };
                                    $isActive =
                                        request()->routeIs($item['route'] . '*') ||
                                        request()->routeIs(str_replace('.index', '', $item['route']) . '.*');
                                @endphp
                                <a href="{{ route($item['route']) }}" @click="sidebar=false" title="{{ $item['label'] }}"
                                    aria-current="{{ $isActive ? 'page' : 'false' }}"
                                    @class([
                                        'group flex min-h-11 items-center gap-2.5 rounded-lg px-3 py-2.5 transition-colors duration-150',
                                        'bg-brand/10 text-brand font-semibold' => $isActive,
                                        'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => !$isActive,
                                    ])
                                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                                    <svg aria-hidden="true"
                                        class="h-4 w-4 shrink-0 {{ $isActive ? 'text-brand' : 'text-slate-400 group-hover:text-slate-700' }}"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="{{ $navIconPaths[$icon] ?? $navIconPaths['document'] }}" />
                                    </svg>
                                    <span :class="sidebarCollapsed ? 'lg:hidden' : ''" class="min-w-0 flex-1 truncate text-xs transition-opacity duration-150 sm:text-sm">{{ $item['label'] }}</span>
                                    @if ($isActive)
                                        <svg :class="sidebarCollapsed ? 'lg:hidden' : ''" aria-hidden="true" class="h-3.5 w-3.5 shrink-0 text-brand" viewBox="0 0 16 16" fill="currentColor"><path d="M2 8h10l-2-2H4a2 2 0 0 1 2-2z"/></svg>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </nav>

            <!-- Sidebar footer -->
            <div class="border-t border-slate-200 px-3 py-3.5 transition-[padding] duration-200"
                :class="sidebarCollapsed ? 'lg:px-1.5' : ''">
                @if ($currentUser)
                    <div class="flex items-center gap-3 rounded-lg px-2 py-2.5 transition-colors duration-150 hover:bg-slate-50"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-navy-100 text-xs font-bold text-navy-700">
                            {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                        </span>
                        <div :class="sidebarCollapsed ? 'lg:hidden' : ''" class="min-w-0 flex-1 transition-opacity duration-150">
                            <p class="truncate text-xs font-semibold text-slate-800">{{ $currentUser->name }}</p>
                            <p class="truncate text-[10px] text-slate-500">{{ $currentUser->primaryRoleLabel() }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </aside>

        <!-- Main column -->
        <div class="flex-1 min-w-0">
            <!-- Header -->
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-3 backdrop-blur sm:px-6">
                <div class="flex items-center gap-3">
                    <button class="-ml-1 inline-flex h-11 w-11 items-center justify-center rounded-lg text-slate-600 transition-colors duration-150 hover:bg-slate-100 focus:outline-none focus:bg-slate-100 lg:hidden"
                        @click="sidebar=true" type="button" aria-label="Buka navigasi" :aria-expanded="sidebar.toString()">
                        <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="min-w-0">
                        <h1 class="truncate text-base font-semibold tracking-tight text-slate-900">{{ $title ?? 'Dashboard' }}</h1>
                        @isset($breadcrumb)
                            <p class="truncate text-xs text-slate-500">{{ $breadcrumb }}</p>
                        @endisset
                    </div>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-3">
                    <!-- Notifications -->
                    <a href="{{ route('notifications.index') }}" aria-label="Notifikasi"
                        class="relative inline-flex h-11 items-center gap-2 rounded-lg px-3 text-slate-600 transition-colors duration-150 hover:bg-slate-50 sm:px-4">
                        <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 12h4"/></svg>
                        <span class="hidden text-xs font-medium text-slate-600 sm:inline">Notifikasi</span>
                        @if (($unreadNotifications ?? 0) > 0)
                            <span class="absolute right-1.5 top-1.5 min-w-4 rounded-full bg-rose-600 px-1.5 text-center text-[10px] font-semibold leading-4 text-white">{{ $unreadNotifications }}</span>
                        @endif
                    </a>

                    <!-- User menu -->
                    <div class="relative" @click.away="userMenu=false">
                        <button type="button"
                            class="inline-flex h-11 items-center gap-2 rounded-lg px-2.5 text-slate-600 transition-colors duration-150 hover:bg-slate-50 sm:gap-3 sm:px-3"
                            @click="userMenu=!userMenu" :aria-expanded="userMenu.toString()" aria-haspopup="true">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-navy-100 text-xs font-bold text-navy-700">{{ strtoupper(substr($currentUser->name, 0, 1)) }}</span>
                            <span class="hidden text-left sm:block">
                                <span class="block max-w-40 truncate text-sm font-semibold text-slate-800 leading-tight">{{ $currentUser->name }}</span>
                                <span class="block max-w-40 truncate text-[10px] text-slate-500">{{ $currentUser->primaryRoleLabel() }}</span>
                            </span>
                            <svg aria-hidden="true" class="hidden h-4 w-4 text-slate-500 sm:block" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 7.47a.75.75 0 0 1 1.06 0L10 11.19l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="userMenu" x-cloak x-transition.origin.top.right.duration.150ms
                            class="absolute right-0 mt-2 w-52 rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl" role="menu">
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50" role="menuitem">
                                <svg aria-hidden="true" class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-7a4 4 0 0 1 0 8m2 3a4 4 0 0 1 3 4v2"/><circle cx="12" cy="12" r="3"/></svg>
                            Profil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-rose-700 transition-colors hover:bg-rose-50" role="menuitem">
                                    <svg aria-hidden="true" class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m10 0h2a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2m-4 4h4a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2h-4"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/></svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main content -->
            <main class="page-enter mx-auto w-full max-w-screen-2xl p-3 sm:p-6 lg:p-7">
                <!-- Success feedback -->
                @if (session('success'))
                    <div role="status" class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200/80 bg-emerald-50/80 px-4 py-3.5 text-sm text-emerald-800 shadow-sm">
                        <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12 9 6l5 6 5 6 5-6 5 6H4z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                <!-- Error feedback -->
                @if ($errors->any())
                    <div role="alert" class="mb-5 flex items-start gap-3 rounded-xl border border-rose-200/80 bg-rose-50/80 px-4 py-3.5 text-sm text-rose-800 shadow-sm">
                        <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v8"/><path d="M12 16h.01"/></svg>
                        <span>{{ $errors->first('business') ?: $errors->first() }}</span>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
