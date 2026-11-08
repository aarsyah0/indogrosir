<x-app-layout title="Notifikasi">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-auto">
            <x-page-header
                header="Notifikasi"
                subheader="Ikuti perkembangan proses pengajuan dan persetujuan Anda."
                class="mb-4"
            >
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    {{ $notifications->total() }} notifikasi
                </span>
            </x-page-header>
        </div>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn-secondary">Tandai semua dibaca</button>
        </form>
    </div>

    <div class="card">
        <div class="card-body">
            @forelse ($notifications as $notification)
                <div class="flex flex-col gap-3 border-b border-slate-100 p-4 transition-colors last:border-0 last:bg-slate-50/30"
                    style="{{ $notification->read_at ? '' : 'background: #f0f4ff;' }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-slate-900">{{ $notification->data['title'] ?? 'Notifikasi' }}</p>
                            <p class="text-sm text-slate-600 mt-0.5">
                                {{ $notification->data['message'] ?? 'Ada pembaruan pada sistem.' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-xs text-slate-500">{{ $notification->created_at->diffForHumans() }}</span>
                            @if ($notification->read_at)
                                <span class="badge badge-default">Dibaca</span>
                            @else
                                <span class="badge badge-success">Baru</span>
                            @endif
                            <a href="{{ route('notifications.read', $notification->id) }}"
                                class="inline-flex items-center gap-1.5 text-brand font-medium text-sm transition-colors hover:text-brand-600">
                                Buka
                                <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 13l5 5 5-5M7 21l5-5 5 5"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <div class="empty-state">
                        <p class="text-sm text-slate-500">Belum ada notifikasi.</p>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
            <span class="text-xs text-slate-500">{{ $notifications->total() }} notifikasi</span>
            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>
