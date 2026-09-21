@extends('layouts.app')

@section('content')
<div class="container p-3">
    <h1>{{ __('content-management.faq_categories.form.edit_title') }}</h1>
    <form action="{{ route('faq-categories.update', $faq_category) }}" method="POST">
        @csrf
        @method('PUT')
        @include('faq-categories.partials.form', ['faq_category' => $faq_category, 'submit_label' => __('content-management.faq_categories.form.update')])
    </form>
</div>
@endsection
