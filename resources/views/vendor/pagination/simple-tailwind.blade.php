@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex gap-2 items-center justify-between w-full">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-3.5 py-2 text-xs font-bold text-slate-300 bg-slate-50 border border-slate-200/60 cursor-not-allowed rounded-xl">
                &laquo; Sebelumnya
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-3.5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200/80 rounded-xl hover:bg-slate-50 hover:text-blue-600 shadow-xs active:scale-95 transition-all">
                &laquo; Sebelumnya
            </a>
        @endif

        <span class="text-xs font-semibold text-slate-500">
            Hal <strong class="text-slate-800">{{ $paginator->currentPage() }}</strong> / {{ $paginator->lastPage() }}
        </span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-3.5 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200/80 rounded-xl hover:bg-slate-50 hover:text-blue-600 shadow-xs active:scale-95 transition-all">
                Berikutnya &raquo;
            </a>
        @else
            <span class="inline-flex items-center px-3.5 py-2 text-xs font-bold text-slate-300 bg-slate-50 border border-slate-200/60 cursor-not-allowed rounded-xl">
                Berikutnya &raquo;
            </span>
        @endif
    </nav>
@endif
