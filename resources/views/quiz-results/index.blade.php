@extends('layouts.app')
@section('title', __('quiz-management.preview.results_heading'))
@section('style')
    @include('home.test-preview.styles')
    @include('quiz-results.styles')
@endsection
@section('content')
    <section class="backoffice-quiz-results test-preview-page">
        <main class="test-preview-main">
            <nav class="quiz-results-breadcrumb" aria-label="{{ __('quiz-management.preview.results_heading') }}">
                <span>{{ __('navigation.menu.quiz_management') }}</span>
                <span aria-hidden="true">/</span>
                <span aria-current="page">{{ __('quiz-management.preview.results_heading') }}</span>
            </nav>
            @include('quiz-results.partials.list')
        </main>
    </section>
@endsection
