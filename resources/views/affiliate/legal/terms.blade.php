@extends('affiliate.auth.layouts.app')
@section('title', __('affiliate.legal.terms_title'))
@section('content')
<main class="container py-5" style="max-width: 900px">
    <article class="bg-white p-4 p-md-5 rounded shadow-sm">
        <h1>{{ __('affiliate.legal.terms_title') }}</h1>
        <p class="text-muted">{{ __('affiliate.legal.effective', ['date' => config('affiliate.legal.effective_date')]) }}</p>
        @foreach (__('affiliate.legal.terms_sections') as $heading => $paragraphs)
            <h2 class="h4 mt-4">{{ $heading }}</h2>
            @foreach ((array) $paragraphs as $paragraph)<p>{{ $paragraph }}</p>@endforeach
        @endforeach
        <p><a href="{{ route('affiliate.registration') }}">{{ __('affiliate.legal.back') }}</a></p>
    </article>
</main>
@endsection
