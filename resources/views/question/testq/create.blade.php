@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ __('quiz-management.system_question.create_title') }}</h2>

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
                <label for="question_text" class="form-label">{{ __('quiz-management.system_question.question_text') }}</label>
                <div class="row">
                    <div class="col-lg-8">
                        <input type="text" class="form-control" name="question_text" required>

                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-primary" onclick="insertFrac()">
                            {{-- เศษส่วน --}}
                            {{ __('quiz-management.system_question.fraction') }}
                        </button>
                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-primary" onclick="insertMixed()">
                            {{-- จำนวนคละ --}}
                            {{ __('quiz-management.system_question.mixed_fraction') }}
                        </button>
                    </div>
                </div>
            </div>
            <hr>
            <h2>{{ __('quiz-management.system_question.answers_fraction') }}</h2>

            <div class="mb-3">
                <label for="answer_numerator" class="form-label">{{ __('quiz-management.system_question.numerator') }}</label>
                <input type="number" class="form-control" name="answer_numerator" required>
            </div>

            <div class="mb-3">
                <label for="answer_denominator" class="form-label">{{ __('quiz-management.system_question.denominator') }}</label>
                <input type="number" class="form-control" name="answer_denominator" required min="1">
            </div>
            <div class="mb-3">
                <label for="answer_type" class="form-label">{{ __('quiz-management.system_question.type') }}</label>
                <select name="answer_type" id="answer_type" class="form-control">
                    <option value="frac">{{ __('quiz-management.system_question.fraction') }}</option>
                    <option value="mixed" selected>{{ __('quiz-management.system_question.mixed_fraction') }}</option>
                </select>
            </div>
            <a href="{{ route('ts.questions.index') }}" class="btn btn-secondary"> {{ __('quiz-management.system_question.back') }} </a>

            <button type="submit" class="btn btn-primary">{{ __('quiz-management.system_question.save') }}</button>
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
