@extends('layouts.app')

@section('content')
<div class="container p-3">
    <h1>Create New FAQ</h1>

    <form action="{{ route('faqs.store') }}" method="POST">
        @csrf
        @include('faqs.partials.form', ['faq' => null, 'submit_label' => 'Create FAQ'])
    </form>
</div>
@endsection

@section('script')
    @include('faqs.partials.editor-script')
@endsection
