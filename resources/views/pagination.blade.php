@if ($paginator->hasPages())
  <nav style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
    @if ($paginator->onFirstPage())
      <span style="color:var(--tinta-lembut)">Sebelumnya</span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
    @endif

    <span style="color:var(--tinta-lembut)">
      Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}
    </span>

    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
    @else
      <span style="color:var(--tinta-lembut)">Berikutnya</span>
    @endif
  </nav>
@endif
