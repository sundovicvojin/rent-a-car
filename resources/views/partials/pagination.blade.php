@if ($paginator->hasPages())
    <nav class="mt-10 flex items-center justify-between gap-4" aria-label="Stranice">
        @if ($paginator->onFirstPage())
            <span class="btn btn-ghost btn-sm" aria-disabled="true">Prethodna</span>
        @else
            <a class="btn btn-ghost btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev">Prethodna</a>
        @endif
        <p class="num text-sm text-ink-2">Strana {{ $paginator->currentPage() }} od {{ $paginator->lastPage() }}</p>
        @if ($paginator->hasMorePages())
            <a class="btn btn-ghost btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next">Sledeća</a>
        @else
            <span class="btn btn-ghost btn-sm" aria-disabled="true">Sledeća</span>
        @endif
    </nav>
@endif
