@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Create New Question</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('ts.questions.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="question_text" class="form-label">Question Text</label>
                <div class="row">
                    <div class="col-lg-8">
                        <input type="text" class="form-control" name="question_text" required>

                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-primary" onclick="insertFrac()">
                            {{-- เศษส่วน --}}
                            Fraction
                        </button>
                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-primary" onclick="insertMixed()">
                            {{-- จำนวนคละ --}}
                            MixFrac
                        </button>
                    </div>
                </div>
            </div>
            <hr>
            <h2>answers fraction</h2>

            <div class="mb-3">
                <label for="answer_numerator" class="form-label">Numerator</label>
                <input type="number" class="form-control" name="answer_numerator" required>
            </div>

            <div class="mb-3">
                <label for="answer_denominator" class="form-label">Denominator</label>
                <input type="number" class="form-control" name="answer_denominator" required min="1">
            </div>
            <div class="mb-3">
                <label for="answer_type" class="form-label">type</label>
                <select name="answer_type" id="answer_type" class="form-control">
                    <option value="frac">Fraction</option>
                    <option value="mixed" selected>Mixed Fraction</option>
                </select>
            </div>
            <a href="{{ route('ts.questions.index') }}" class="btn btn-secondary"> back </a>

            <button type="submit" class="btn btn-primary">Save Question</button>
        </form>
    </div>
@endsection
@section('script')
    <script>
        function insertFrac() {
            let question_text = document.querySelector('input[name="question_text"]');
            question_text.value += ' Frac(เศษ,ส่วน) ';
        }

        function insertMixed() {
            let question_text = document.querySelector('input[name="question_text"]');
            question_text.value += ' MixFrac(เศษ,ส่วน) ';
        }
    </script>
@endsection
