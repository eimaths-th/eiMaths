@if($paginator->hasPages())
    <nav aria-label="{{ __('ebooks.pagination') }}">
        <ul class="pagination">
            @if($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="{{ __('ebooks.actions.previous') }}"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('ebooks.actions.previous') }}">&lsaquo;</a></li>
            @endif
            @foreach($elements as $element)
                @if(is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @elseif(is_array($element))
                    @foreach($element as $page => $url)
                        <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}" @if($page == $paginator->currentPage()) aria-current="page" @endif>
                            @if($page == $paginator->currentPage())<span class="page-link">{{ $page }}</span>
                            @else<a class="page-link" href="{{ $url }}" aria-label="{{ __('ebooks.page_number', ['number' => $page]) }}">{{ $page }}</a>@endif
                        </li>
                    @endforeach
                @endif
            @endforeach
            @if($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('ebooks.actions.next') }}">&rsaquo;</a></li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="{{ __('ebooks.actions.next') }}"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
            @endif
        </ul>
    </nav>
@endif
