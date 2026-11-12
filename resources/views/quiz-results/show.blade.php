@extends('layouts.app')
@section('title', $test->quiz->title)
@section('style')
    @include('home.result.styles')
    @include('quiz-results.styles')
@endsection
@section('content')
    <section class="backoffice-quiz-results result-page">
        <main class="result-main">
            <nav class="quiz-results-breadcrumb" aria-label="{{ __('quiz-management.preview.results_heading') }}">
                @can('quiz-results-list')
                    <a href="{{ route('backoffice.quiz-results.index') }}">{{ __('quiz-management.preview.results_heading') }}</a>
                @else
                    <span>{{ __('quiz-management.preview.results_heading') }}</span>
                @endcan
                <span aria-hidden="true">/</span>
                <span aria-current="page">#{{ $test->id }}</span>
            </nav>
            @include('home.result.content')
        </main>
    </section>
@endsection
@push('scripts')
    <script async src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.7/MathJax.js?config=TeX-MML-AM_CHTML"></script>
@endpush
