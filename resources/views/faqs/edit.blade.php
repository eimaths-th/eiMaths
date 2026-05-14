@extends('layouts.app')

@section('content')
<div class="container p-3">
    <h1>Edit FAQ</h1>

    <form action="{{ route('faqs.update', $faq) }}" method="POST">
        @csrf
        @method('PUT')
        @include('faqs.partials.form', ['faq' => $faq, 'submit_label' => 'Update FAQ'])
    </form>
</div>
@endsection

@section('script')
    @include('faqs.partials.editor-script')
@endsection
