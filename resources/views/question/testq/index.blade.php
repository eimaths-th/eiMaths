@extends('layouts.app')

@section('style')
<style>
    .pagination {
        justify-content: center;
    }
</style>
<script type="text/javascript" async
  src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.7/MathJax.js?config=TeX-MML-AM_CHTML">
</script>
@endsection

@section('content')
<div class="container">
    <h2>Questions List</h2>

    <a href="{{ route('ts.questions.create') }}" class="btn btn-success mb-3">Add New Question</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Question</th>
                <th>Answer (Fraction)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($questions as $question)
            <tr>
                <td>{{ $question->question_text }}</td>
                <td>{{ $question->answer_numerator }}/{{ $question->answer_denominator }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $questions->links() }} <!-- Pagination Links -->
</div>
@endsection
