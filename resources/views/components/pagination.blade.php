@props(['paginator'])
@if ($paginator && $paginator->hasPages())
    <div class="flex items-center justify-between gap-3 px-4 py-3">
        <div class="flex flex-1 items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-500">&laquo; Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50">&laquo; Sebelumnya</a>
            @endif
            <span class="text-xs text-slate-500">
                Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}
            </span>
        </div>
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50">Selanjutnya &raquo;</a>
        @else
            <span class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-500">Selanjutnya &raquo;</span>
        @endif
    </div>
@endif
