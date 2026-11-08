@props(['paginator'])
@if ($paginator && $paginator->hasPages())
    <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3">
        <div class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex min-h-11 items-center gap-1.5 rounded-lg bg-slate-100 px-3 text-xs font-semibold text-slate-500">&laquo; Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex min-h-11 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50">&laquo; Sebelumnya</a>
            @endif
            <span class="text-xs text-slate-500">
                Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}
            </span>
        </div>
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex min-h-11 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50">Selanjutnya &raquo;</a>
        @else
            <span class="inline-flex min-h-11 items-center gap-1.5 rounded-lg bg-slate-100 px-3 text-xs font-semibold text-slate-500">Selanjutnya &raquo;</span>
        @endif
    </div>
@endif
