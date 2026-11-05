<nav class="ocean-pagination" aria-label="Phân trang">
    <p class="pagination-summary">
        @if($paginator->count())
            Hiển thị {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} / {{ $paginator->total() }} mục
        @else
            Tổng cộng {{ $paginator->total() }} mục
        @endif
    </p>
    @if($paginator->hasPages())
        <ul class="pagination-pages">
            <li>
                @if($paginator->onFirstPage())
                    <span class="pagination-link disabled" aria-disabled="true">← Trước</span>
                @else
                    <a class="pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">← Trước</a>
                @endif
            </li>
            @foreach($elements as $element)
                @if(is_string($element))
                    <li><span class="pagination-gap">{{ $element }}</span></li>
                @endif
                @if(is_array($element))
                    @foreach($element as $page => $url)
                        <li>
                            @if($page == $paginator->currentPage())
                                <span class="pagination-link current" aria-current="page" aria-label="Trang {{ $page }}">{{ $page }}</span>
                            @else
                                <a class="pagination-link" href="{{ $url }}" aria-label="Trang {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
            <li>
                @if($paginator->hasMorePages())
                    <a class="pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Sau →</a>
                @else
                    <span class="pagination-link disabled" aria-disabled="true">Sau →</span>
                @endif
            </li>
        </ul>
    @endif
</nav>
