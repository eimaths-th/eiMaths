@extends('layouts.app')

@section('content')
<div class="container p-3">
    <h1>{{ __('content-management.faq_categories.form.create_title') }}</h1>
    <form action="{{ route('faq-categories.store') }}" method="POST">
        @csrf
        @include('faq-categories.partials.form', ['faq_category' => null, 'submit_label' => __('content-management.faq_categories.form.create')])
    </form>
</div>
@endsection
