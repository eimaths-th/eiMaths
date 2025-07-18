@extends('layouts.app')

@section('style')
    @include('users.style')
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript" async
        src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.7/MathJax.js?config=TeX-MML-AM_CHTML"></script>
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    {{-- <h4>Users & Roles Management</h4> --}}
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                    </ol>

                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-user"></i> Quiz View Ceck</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <h4>Quiz Title: {{ $quizzes->title }}</h4>
                            <p>
                                <strong> Grade: </strong>{{ $quizzes->level }}
                                <strong> Term: </strong>{{ $quizzes->term }}
                                <strong> Section: </strong>{{ $quizzes->section }}
                            </p>

                            <hr>

                            <h5>Questions</h5>
                            @if ($questions->isEmpty())
                                <p>No questions found.</p>
                            @else
                                <div class="row">
                                    @foreach ($questions as $key => $question)
                                        <div class="col-lg-6 mb-4">
                                            <div id="question-container-{{ $question->id }}"
                                                class="border rounded p-3 h-100">
                                                <strong>{{ replaceMixedFractions($question->text) }}

                                                </strong><br>
                                                <small>Type: {{ $question->type }}</small><br>

                                                @if ($question->img_name)
                                                    <img src="{{ asset('img_questions/' . $question->img_name) }}"
                                                        class="img-thumbnail mt-2" style="max-width: 200px;"
                                                        alt="Question Image">
                                                @endif

                                                {{-- WRITTEN TYPE --}}
                                                @if ($question->type === 'written')
                                                    <p>answer:  {{ replaceMixedFractions($question->written_answer) }}</p>
                                                    {{-- <input type="text" class="form-control mt-3"
                                                        value="{{ $question->written_answer }}" readonly> --}}

                                                    {{-- OPTIONS TYPE --}}
                                                @elseif ($question->type === 'options')
                                                    @foreach ($question->options as $option)
                                                        <div class="form-check mt-2">
                                                            <input class="form-check-input" type="radio"
                                                                id="option-{{ $key }}-{{ $option->id }}"
                                                                name="answerOfQuestion[{{ $key }}]"
                                                                value="{{ $option->id }}" @checked($option->correct)>
                                                            <label class="form-check-label"
                                                                for="option-{{ $key }}-{{ $option->id }}">
                                                                {{ replaceMixedFractions($option->text) }}
                                                            </label>
                                                        </div>
                                                    @endforeach

                                                    {{-- IMAGE TYPE --}}
                                                @elseif ($question->type === 'image')
                                                    <div class="row mt-2">
                                                        @foreach ($question->images as $image)
                                                            <div class="col-6 text-center mb-3">
                                                                <input type="radio"
                                                                    id="img-option-{{ $key }}-{{ $image->id }}"
                                                                    name="answerOfQuestion[{{ $key }}]"
                                                                    value="{{ $image->id }}"
                                                                    @checked($image->correct)>
                                                                <label
                                                                    for="img-option-{{ $key }}-{{ $image->id }}">
                                                                    <img src="{{ asset('images_options/' . $image->img_name) }}"
                                                                        class="img-thumbnail" style="max-width: 100px;"
                                                                        alt="Image option">
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    {{-- FRACTION TYPE --}}
                                                @elseif ($question->type === 'fraction')
                                                    @php $fraction = $question->fractions->first(); @endphp

                                                    {{-- Written Fraction --}}
                                                    @if ($fraction && $fraction->type === 'written')
                                                        <input type="hidden"
                                                            name="currentQuestionFracType[{{ $key }}]"
                                                            value="{{ $fraction->type }}">
                                                        <input type="hidden"
                                                            name="currentQuestionFracAnswerType[{{ $key }}]"
                                                            value="{{ $fraction->answer_type }}">
                                                        @php
                                                            // dd($fraction);
                                                        @endphp
                                                        @if ($fraction->answer_type === 'frac')
                                                            <div class="frac mt-3">
                                                                <span>
                                                                    <strong>
                                                                        {{ toFraction($fraction->numerator, $fraction->denominator) }}
                                                                    </strong>
                                                                </span>

                                                            </div>
                                                        @else
                                                            {{-- Mixed --}}
                                                            <div class="mt-3">


                                                                <div class="">
                                                                    <span>
                                                                        {{ toMixedFraction(null, $fraction->numerator, $fraction->denominator) }}
                                                                    </span>

                                                                </div>
                                                            </div>
                                                        @endif

                                                        {{-- Option-based Fraction --}}
                                                    @else
                                                        <div class="row mt-3">
                                                            @foreach ($question->fractions as $option)
                                                                @php
                                                                    $textOption =
                                                                        $option->answer_type === 'mixed'
                                                                            ? toMixedFraction(null,
                                                                                $option->numerator,
                                                                                $option->denominator,
                                                                            )
                                                                            : toFraction(
                                                                                $option->numerator,
                                                                                $option->denominator,
                                                                            );
                                                                @endphp

                                                                <div class="col-md-6 mt-2">
                                                                    <input type="hidden"
                                                                        name="currentQuestionFracType[{{ $key }}]"
                                                                        value="options">
                                                                    <label
                                                                        for="frac-option-{{ $key }}-{{ $option->id }}">
                                                                        <input type="radio"
                                                                            id="frac-option-{{ $key }}-{{ $option->id }}"
                                                                            name="answerOfQuestion[{{ $key }}]"
                                                                            value="{{ $option->id }}"
                                                                            @checked($option->correct)>
                                                                        {{ $textOption }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @endif

                                                {{-- Check Button --}}
                                                <div class="mt-3 text-end">
                                                    <button class="btn btn-sm btn-primary check-button"
                                                        data-question-id="{{ $question->id }}">
                                                        Check
                                                    </button>
                                                    <button class="btn btn-sm btn-danger uncheck-button"
                                                        data-question-id="{{ $question->id }}" style="display: none;">
                                                        Uncheck
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                        </div>


                    </div>

                </div>

            </div>

        </div>

    </section>

    {{-- @include('question.question-form', ['editing' => false, 'question' => null, 'options' => []]) --}}
    {{-- @include('quiz.quiz-form', [ 'editing' => false,'quiz' => null, 'options' => []])
    @include('quiz.quiz-assign') --}}

    {{-- @include('question.edit') --}}

    {{--  {!! $data->render() !!} --}}
@endsection

@section('script')
    @include('quiz.scripts')
    <script>
        let checkedQuestions = new Set(JSON.parse(localStorage.getItem('checkedQuestions')) || []);

        function updateUI(questionId, isChecked) {
            const $container = $('#question-container-' + questionId);
            const $checkBtn = $('.check-button[data-question-id="' + questionId + '"]');
            const $uncheckBtn = $('.uncheck-button[data-question-id="' + questionId + '"]');

            if (isChecked) {
                $container.addClass('bg-success text-white');
                $checkBtn.hide();
                $uncheckBtn.show();
            } else {
                $container.removeClass('bg-success text-white');
                $checkBtn.show();
                $uncheckBtn.hide();
            }
        }

        $(document).ready(function() {
            checkedQuestions.forEach(id => {
                updateUI(id, true);
            });
        });

        $(document).on('click', '.check-button', function() {
            const questionId = $(this).data('question-id');
            checkedQuestions.add(questionId);
            localStorage.setItem('checkedQuestions', JSON.stringify([...checkedQuestions]));
            updateUI(questionId, true);
        });

        $(document).on('click', '.uncheck-button', function() {
            const questionId = $(this).data('question-id');
            checkedQuestions.delete(questionId);
            localStorage.setItem('checkedQuestions', JSON.stringify([...checkedQuestions]));
            updateUI(questionId, false);
        });
    </script>
@endsection
