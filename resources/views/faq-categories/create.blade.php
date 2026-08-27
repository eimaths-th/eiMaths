@extends('layouts.app')

@section('content')
<div class="container p-3">
    <h1>Create FAQ Category</h1>
    <form action="{{ route('faq-categories.store') }}" method="POST">
        @csrf
        @include('faq-categories.partials.form', ['faq_category' => null, 'submit_label' => 'Create Category'])
    </form>
</div>
@endsection
