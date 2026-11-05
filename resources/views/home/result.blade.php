@extends('layouts.quiz_layout', ['result_page' => true])
@section('title', $test->quiz->title)
@section('content')
    @include('home.result.content')
@endsection
@push('scripts')
    <script type="text/javascript" async src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.7/MathJax.js?config=TeX-MML-AM_CHTML"></script>
    <script>
        window.addEventListener('beforeunload', function(event) {
            event.preventDefault();
            event.returnValue = '';
        });
        window.addEventListener('popstate', function(event) {
            event.preventDefault();
            history.pushState(null, null, window.location.href);
            return false;
        });
        if (window.history && window.history.pushState) {
            window.history.replaceState(null, null, window.location.href);
            window.addEventListener('popstate', function() {
                window.history.replaceState(null, null, window.location.href);
            });
        }
        history.pushState(null, null, window.location.href);
    </script>
@endpush
