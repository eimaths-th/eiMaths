@extends('layouts.app')

@section('style')
    @include('users.style')
    <style>
        #loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
        }

        .loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px;
            height: 50px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }
    </style>
@endsection

@section('content')
    <div id="loading" style="display: none;">
        <div class="loading-spinner"></div>
    </div>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    {{-- <h4>Users & Roles Management</h4> --}}
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        {{-- <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Users Management</li> --}}
                        {{-- <a href="{{ route('question2.create') }}" class="btn btn-success">
                            Create Question
                        </a> --}}


                    </ol>

                </div>
            </div>
        </div>
    </section>

    {{-- add loading --}}

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fa fa-user"></i>
                                {{ __('quiz-management.builder.title') }}
                            </h3>
                            <div class="card-tools">

                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @if ($message = Session::get('success'))
                                <script>
                                    toastr.success('{{ $message }}', {
                                        timeOut: 5000
                                    });
                                </script>
                            @endif
                            <div class="alert alert-danger alert-dismissible fade show" role="alert"
                                style="display: none;">
                                <button type="button" class="btn-close" data-dismiss="alert" aria-label="{{ __('quiz-management.builder.close') }}">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="alert alert-success alert-dismissible fade show" role="alert"
                                style="display: none;">
                                <button type="button" class="btn-close" data-dismiss="alert" aria-label="{{ __('quiz-management.builder.close') }}">
                                    <span aria-hidden="true"></span>
                                </button>
                            </div>


                            <div class="col-lg-6">
                                <h4 class="mb-4">{{ __('quiz-management.builder.title') }}</h4>
                                <div class="mb-3">
                                    <label for="selectquiz" class=" form-label">{{ __('quiz-management.builder.select_quiz') }}</label>
                                    <select id="SelectQuiz" name="SelectQuiz" class="SelectQuiz form-control" required>
                                        <option value="" selected>{{ __('quiz-management.builder.select_quiz') }}</option>
                                        <option value="create">{{ __('quiz-management.builder.create_quiz') }}</option>
                                        @foreach ($quizzes as $quizze)
                                            <option value="{{ $quizze->id }}">{{ $quizze->title }}</option>
                                        @endforeach
                                    </select>

                                </div>
                                <form class="form" action="#" method="POST" id="QuestionsQuizCrate">
                                    @csrf

                                    <div id="quizCrate">
                                        <div>
                                            <label for="title" class="form-label">{{ __('quiz-management.builder.course_title') }}</label>
                                            <input id="title" class="form-control" type="text" name="title"
                                                value="{{ old('title', $quiz->title ?? '') }}">
                                            @error('title')
                                                <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="mt-4">
                                            <label for="slug" class="form-label">{{ __('quiz-management.builder.slug') }}</label>
                                            <input id="slug" class="form-control" type="text" name="slug"
                                                value="{{ old('slug', $quiz->slug ?? '') }}" disabled>
                                            @error('slug')
                                                <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="mt-4">
                                            <label for="description" class="form-label">{{ __('quiz-management.builder.description') }}</label>
                                            <textarea id="description" class="form-control" name="description">{{ old('description', $quiz->description ?? '') }}</textarea>
                                            @error('description')
                                                <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="mt-4">
                                                    <label for="level" class=" form-label">{{ __('quiz-management.builder.grade') }}</label>
                                                    <select id="level" name="level" class="form-control">
                                                        <option value="" disabled selected>{{ __('quiz-management.builder.select_grade') }}</option>
                                                        <option value="K1">K1</option>
                                                        <option value="K2">K2</option>
                                                        <option value="P1">P1</option>
                                                        <option value="P2">P2</option>
                                                        <option value="P3">P3</option>
                                                        <option value="P4">P4</option>
                                                        <option value="P5">P5</option>
                                                        <option value="P6">P6</option>
                                                    </select>

                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="mt-4">
                                                    <label for="term" class=" form-label">{{ __('quiz-management.builder.term') }}</label>
                                                    <select id="term" name="term" class="form-control">
                                                        <option value="" disabled selected>{{ __('quiz-management.builder.select_term') }}</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                    </select>

                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="mt-4">
                                                    <label for="section" class=" form-label">{{ __('quiz-management.builder.level') }}</label>
                                                    <select id="section" name="section" class="form-control">
                                                        <option value="" disabled selected>{{ __('quiz-management.builder.select_level') }}</option>
                                                        @for ($i = 1; $i <= 20; $i++)
                                                            <option value="{{ $i }}">{{ $i }}
                                                            </option>
                                                        @endfor

                                                    </select>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-4 form-check">
                                            <div class="flex items-center">
                                                <label for="public" class="form-label">{{ __('quiz-management.builder.public') }}</label>
                                                <input type="checkbox" id="public" class="form-check-input ml-2"
                                                    name="public"
                                                    {{ old('public', $quiz->public ?? false) ? 'checked' : '' }}>
                                            </div>
                                            @error('public')
                                                <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="mt-2 form-check">
                                            <div class="flex items-center">
                                                <label for="is_free" class="form-label">{{ __('quiz-management.builder.free') }} <small class="text-muted">({{ __('quiz-management.builder.free_hint') }})</small></label>
                                                <input type="checkbox" id="is_free" class="form-check-input ml-2"
                                                    name="is_free"
                                                    {{ old('is_free', $quiz->is_free ?? false) ? 'checked' : '' }}>
                                            </div>
                                            @error('is_free')
                                                <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="modal-footer">

                                        </div>
                                    </div>

                                    <div class="my-4">
                                        <label for="questions_id" class="form-label">{{ __('quiz-management.builder.select_questions') }}</label>
                                        <select id="questions_id" name="questions_id[]"
                                            class="questionsId select2 form-control" multiple>
                                        </select>
                                        @error('questions_id')
                                            <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div id="questionCreate" class="questionCreate">
                                        <div class="question-form">
                                            <div class="question-form-index">
                                                <h4 class="mb-4">{{ __('quiz-management.builder.create_question', ['number' => 1]) }}</h4>

                                                <input type="hidden" id="formMethod" name="_method" value="POST">
                                                <div class="mb-3">
                                                    <select name="type" id="SelectType"
                                                        class="SelectType form-control">
                                                        <option value="">{{ __('quiz-management.builder.select_answer_type') }}</option>
                                                        <option value="options">{{ __('quiz-management.builder.options') }}</option>
                                                        <option value="written">{{ __('quiz-management.builder.written') }}</option>
                                                        <option value="image">{{ __('quiz-management.builder.image') }}</option>
                                                        <option value="fraction">{{ __('quiz-management.builder.fraction') }}</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="text" class="form-label">{{ __('quiz-management.builder.question_text') }}</label>
                                                    <textarea id="text" class="form-control" name=text>{{ $question->text ?? old('text') }}</textarea>
                                                    @error('text')
                                                        <div class="text-danger mt-2">{{ $message }}</div>
                                                    @enderror
                                                    <div class="row mt-2 fraction-button" id="fraction-button"
                                                        style="display: none;">
                                                        <div class="col-lg-2">
                                                            <button type="button" class="btn btn-primary"
                                                                onclick="insertFrac()">
                                                                {{-- เศษส่วน --}}
                                                                {{ __('quiz-management.builder.fraction') }}
                                                            </button>
                                                        </div>
                                                        <div class="col-lg-2">
                                                            <button type="button" class="btn btn-primary"
                                                                onclick="insertMixed()">
                                                                {{-- จำนวนคละ --}}
                                                                {{ __('quiz-management.builder.mixed_fraction') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="text" class="form-label">{{ __('quiz-management.builder.question_image') }}</label>
                                                    <div id="add-image-question-container">
                                                        <input type="file" id="imageQuestion" name="imageQuestion"
                                                            class="form-control" accept="image/*"
                                                            onchange="showQuestionImg(this)">
                                                    </div>

                                                    @error('text')
                                                        <div class="text-danger mt-2">{{ $message }}</div>
                                                    @enderror
                                                    <!-- Image Preview -->
                                                    <div id="question_img_preview" class="mt-2"
                                                        style="{{ isset($question->img_name) && $question->img_name ? '' : 'display: none;' }}">
                                                        <img id="question_img_thumbnail"
                                                            src="{{ isset($question->img_name) && $question->img_name ? asset('img_questions/' . $question->img_name) : '' }}"
                                                            alt="Answer Explanation Image"
                                                            style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                                                        <br>
                                                        <button type="button" class="btn btn-sm btn-danger mt-2"
                                                            onclick="removeQuestionImg()">{{ __('quiz-management.builder.remove_image') }}</button>
                                                    </div>
                                                </div>

                                                <div class="mb-3 written_type">
                                                    <label for="written" class="form-label">{{ __('quiz-management.builder.question_written') }}</label>
                                                    <br>
                                                    <div class="wrtiten-container">
                                                        <input type="text" class="form-control" id="written"
                                                            name="written" placeholder="{{ __('quiz-management.builder.correct_answer_placeholder') }}">
                                                    </div>
                                                </div>

                                                <div class="mb-3 options_type">
                                                    <label for="options" class="form-label">{{ __('quiz-management.builder.question_options') }}</label>
                                                    <br>
                                                    <button type="button" class="btn btn-sm btn-primary mt-2 addOption2"
                                                        id="addOption2">{{ __('quiz-management.builder.add') }}</button>
                                                    <div id="options-container">
                                                        @if (old('options', $options ?? []))
                                                            @foreach (old('options', $options ?? []) as $index => $option)
                                                                <div class="option-item mt-2 form-row align-items-center">
                                                                    <div class="col-xs-10 col-sm-10 col-md-10 text-center">
                                                                        <input type="text" class="form-control"
                                                                            name="options[{{ $index }}][text]"
                                                                            value="{{ $option['text'] ?? '' }}"
                                                                            autocomplete="off" />
                                                                    </div>
                                                                    <div class="col-auto text-nowrap">
                                                                        <input type="hidden"
                                                                            name="options[{{ $index }}][correct]"
                                                                            value="0">
                                                                        <input type="checkbox" class="text-left"
                                                                            name="options[{{ $index }}][correct]"
                                                                            value="1"
                                                                            {{ isset($option['correct']) && $option['correct'] ? 'checked' : '' }}>
                                                                        {{ __('quiz-management.builder.correct') }}&nbsp
                                                                        <button type="button"
                                                                            class="btn btn-danger btn-sm remove-option">&nbsp&nbsp<i
                                                                                class="fa fa-trash"></i>&nbsp&nbsp</button>
                                                                    </div>
                                                                </div>
                                                                @error('options.' . $index . '.text')
                                                                    <div class="text-danger mt-2">{{ $message }}</div>
                                                                @enderror
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                    @error('options')
                                                        <div class="text-danger mt-2">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="mb-3 image_type">
                                                    <label for="image" class="form-label">{{ __('quiz-management.builder.question_image_options') }}</label>
                                                    <br>
                                                    <button type="button" class="btn btn-sm btn-primary mt-2"
                                                        id="addOptionImg">{{ __('quiz-management.builder.add') }}</button>

                                                    <div id="image-options-container">


                                                    </div>
                                                    @error('image_options')
                                                        <div class="text-danger mt-2">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3 fraction_type">
                                                    <hr>
                                                    <h2>{{ __('quiz-management.builder.answers_fraction') }}</h2>
                                                    <div class="mb-3">
                                                        <label for="answer_type" class="form-label">{{ __('quiz-management.builder.type_answer') }}</label>
                                                        <select name="answer_type_fraction" id="answer_type_fraction"
                                                            class="answer_type_fraction form-control">
                                                            <option value="">{{ __('quiz-management.builder.select_fraction_answer') }}</option>
                                                            <option value="written">{{ __('quiz-management.builder.written') }}</option>
                                                            <option value="options">{{ __('quiz-management.builder.options') }}</option>
                                                        </select>
                                                    </div>
                                                    <div class="written_type_fraction" style="display: none;">
                                                        <div class="mb-3">
                                                            <label for="answer_numerator"
                                                                class="form-label">{{ __('quiz-management.builder.numerator') }}</label>
                                                            <input type="number" class="form-control"
                                                                name="answer_numerator" id="answer_numerator"
                                                                placeholder="{{ __('quiz-management.builder.numerator') }}" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="answer_denominator"
                                                                class="form-label">{{ __('quiz-management.builder.denominator') }}</label>
                                                            <input type="number" class="form-control"
                                                                name="answer_denominator" required id="answer_denominator"
                                                                placeholder="{{ __('quiz-management.builder.denominator') }}" min="1">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="answer_type" class="form-label">{{ __('quiz-management.builder.type_fraction') }}</label>
                                                            <select name="answer_type" id="answer_type"
                                                                class="form-control">
                                                                <option value="frac">{{ __('quiz-management.builder.fraction') }}</option>
                                                                <option value="mixed" selected>{{ __('quiz-management.builder.mixed_fraction') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="options_type_fraction" style="display: none;">
                                                        <button type="button" class="btn btn-sm btn-primary mt-2"
                                                            id="add-fraction-option">{{ __('quiz-management.builder.add_fraction') }}</button>
                                                        <div id="add-fraction-container">

                                                        </div>

                                                    </div>


                                                    <hr>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-4">
                                                        <div class="mt-4">
                                                            <label for="level"
                                                                class="question-grade form-label">{{ __('quiz-management.builder.grade') }}</label>
                                                            <select id="level_1" name="level"
                                                                class="question-grade form-control">
                                                                <option value="" selected>{{ __('quiz-management.builder.select_grade') }}
                                                                </option>
                                                                <option value="K1">K1</option>
                                                                <option value="K2">K2</option>
                                                                <option value="P1">P1</option>
                                                                <option value="P2">P2</option>
                                                                <option value="P3">P3</option>
                                                                <option value="P4">P4</option>
                                                                <option value="P5">P5</option>
                                                                <option value="P6">P6</option>
                                                            </select>

                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <div class="mt-4">
                                                            <label for="term" class="form-label">{{ __('quiz-management.builder.term') }}</label>
                                                            <select id="term_1" name="term"
                                                                class="question-term form-control">
                                                                <option value="" selected>{{ __('quiz-management.builder.select_term') }}
                                                                </option>
                                                                <option value="1">1</option>
                                                                <option value="2">2</option>
                                                                <option value="3">3</option>
                                                                <option value="4">4</option>
                                                            </select>

                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <div class="mt-4">
                                                            <label for="section" class=" form-label">{{ __('quiz-management.builder.level') }}</label>
                                                            <select id="section_1" name="section"
                                                                class="question-level form-control">
                                                                <option value="" selected>{{ __('quiz-management.builder.select_level') }}
                                                                </option>
                                                                @for ($i = 1; $i <= 20; $i++)
                                                                    <option value="{{ $i }}">
                                                                        {{ $i }}</option>
                                                                @endfor

                                                            </select>

                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Hint -->
                                                <div class="mb-3">
                                                    <label for="code_snippet" class="form-label">{{ __('quiz-management.builder.hint') }}</label>
                                                    <textarea id="code_snippet" class="form-control" name="code_snippet">{{ $question->code_snippet ?? old('code_snippet') }}</textarea>
                                                    @error('code_snippet')
                                                        <div class="text-danger mt-2">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <!-- Answer explanation -->
                                                <div class="mb-3">
                                                    <label for="answer_explanation" class="form-label">{{ __('quiz-management.builder.answer_explanation') }}</label>
                                                    <textarea id="answer_explanation" class="form-control" name="answer_explanation">{{ $question->answer_explanation ?? old('answer_explanation') }}</textarea>
                                                    @error('answer_explanation')
                                                        <div class="text-danger mt-2">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <!-- Answer Explanation Image -->
                                                <div class="mb-3">
                                                    <label for="answer_explanation_image" class="form-label">{{ __('quiz-management.builder.answer_explanation') }} {{ __('quiz-management.builder.image') }}</label>
                                                    <input type="file" id="answer_explanation_image"
                                                        class="form-control" name="answer_explanation_image"
                                                        accept="image/*" onchange="showAnswerExplanationImg(this)">
                                                    <input type="hidden" name="old_answer_explanation_image"
                                                        value="{{ $question->answer_explanation_image ?? '' }}">
                                                    @error('answer_explanation_image')
                                                        <div class="text-danger mt-2">{{ $message }}</div>
                                                    @enderror

                                                    <!-- Image Preview -->
                                                    <div id="answer_explanation_img_preview" class="mt-2"
                                                        style="{{ isset($question->answer_explanation_image) && $question->answer_explanation_image ? '' : 'display: none;' }}">
                                                        <img id="answer_explanation_img_thumbnail"
                                                            src="{{ isset($question->answer_explanation_image) && $question->answer_explanation_image ? asset('img_questions/' . $question->answer_explanation_image) : '' }}"
                                                            alt="{{ __('quiz-management.builder.answer_explanation') }} {{ __('quiz-management.builder.image') }}"
                                                            style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                                                        <br>
                                                        <button type="button" class="btn btn-sm btn-danger mt-2"
                                                            onclick="removeAnswerExplanationImg()">{{ __('quiz-management.builder.remove_image') }}</button>
                                                    </div>
                                                </div>

                                                <!-- More info link -->
                                                <div class="mb-3">
                                                    <label for="more_info_link" class="form-label">{{ __('quiz-management.builder.more_info_link') }}</label>
                                                    <input type="text" id="more_info_link" class="form-control"
                                                        name="more_info_link"
                                                        value="{{ $question->more_info_link ?? old('more_info_link') }}" />
                                                    @error('more_info_link')
                                                        <div class="text-danger mt-2">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="modal-footer">
                                                </div>
                                            </div>

                                        </div>
                                        {{-- <button id="DeleteQuestionForm" class="btn btn-danger DeleteQuestionForm">Delete Question</button> --}}

                                    </div>


                                </form>
                                <div class="mt-3">
                                    <button id="AddQuestionForm" class="btn btn-info">{{ __('quiz-management.builder.add_question') }}</button>
                                    <button id="SubmitQuestionForm" type="submit"
                                        class="btn btn-success">{{ __('quiz-management.builder.submit') }}</button>

                                </div>

                            </div>

                        </div>
                    </div>

                </div>

            </div>

        </div>

    </section>

    {{-- @include('question.question-form', ['editing' => false, 'question' => null, 'options' => []]) - --}}
    @include('questionquizzes.form', ['editing' => true, 'question' => null, 'options' => []])

@endsection

@section('script')
    @include('questionquizzes.scripts')
@endsection
