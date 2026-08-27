@extends('layouts.app')

@section('content')
<div class="container p-3">
    <h1>Edit FAQ Category</h1>
    <form action="{{ route('faq-categories.update', $faq_category) }}" method="POST">
        @csrf
        @method('PUT')
        @include('faq-categories.partials.form', ['faq_category' => $faq_category, 'submit_label' => 'Update Category'])
    </form>
</div>
@endsection
