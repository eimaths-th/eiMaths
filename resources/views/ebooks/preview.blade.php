@extends($layout)
@section('content')
<div class="content-header"><div class="container-fluid d-flex flex-wrap align-items-center justify-content-between">
    <h1>{{ __('ebooks.preview.title', ['code' => $book->code]) }} <small class="text-muted">{{ __('ebooks.edition', ['number' => $edition->number]) }}</small></h1>
    <a class="btn btn-outline-secondary" href="{{ route($routePrefix.'.index', ['section' => 'library']) }}">{{ __('ebooks.actions.back_library') }}</a>
</div></div>
<div class="content"><div class="container-fluid">
    <div class="card"><div class="card-body d-flex flex-wrap justify-content-center align-items-center" style="gap:12px">
        @if($number > 1)<a class="btn btn-outline-primary" href="{{ route($routePrefix.'.preview', ['version' => $edition->id, 'page' => $number - 1]) }}">{{ __('ebooks.actions.previous') }}</a>@endif
        <form method="get" class="d-flex align-items-center" style="gap:8px">
            <label for="preview-page" class="mb-0">{{ __('ebooks.fields.page') }}</label>
            <select id="preview-page" name="page" class="form-control" style="width:85px">@for($i = 1; $i <= $edition->page_count; $i++)<option value="{{ $i }}" @selected($i === $number)>{{ $i }}</option>@endfor</select>
            <span>{{ __('ebooks.preview.of_pages', ['count' => $edition->page_count]) }}</span><button class="btn btn-primary">{{ __('ebooks.actions.go_to_page') }}</button>
        </form>
        @if($number < $edition->page_count)<a class="btn btn-outline-primary" href="{{ route($routePrefix.'.preview', ['version' => $edition->id, 'page' => $number + 1]) }}">{{ __('ebooks.actions.next') }}</a>@endif
    </div></div>
    <div class="text-center pb-5"><img src="{{ route($routePrefix.'.preview-image', ['version' => $edition->id, 'page' => $number]) }}" alt="{{ __('ebooks.preview.image_alt', ['code' => $book->code, 'number' => $number]) }}" style="width:100%;max-width:900px;height:auto;background:white;box-shadow:0 4px 20px #0002"></div>
</div></div>
@endsection
