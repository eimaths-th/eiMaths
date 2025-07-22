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
                                Question Quiz Create
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
                                <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="alert alert-success alert-dismissible fade show" role="alert"
                                style="display: none;">
                                <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true"></span>
                                </button>
                            </div>


                            <div class="col-lg-6">
                                <h4 class="mb-4">Quiz</h4>
                                <div class="mb-3">
                                    <label for="selectquiz" class=" form-label">Select Quiz</label>
                                    <select id="SelectQuiz" name="SelectQuiz" class="SelectQuiz form-control" required>
                                        <option value="" selected>Select Quiz</option>
                                        <option value="create">Create Quiz</option>
                                        @foreach ($quizzes as $quizze)
                                            <option value="{{ $quizze->id }}">{{ $quizze->title }}</option>
                                        @endforeach
                                    </select>

                                </div>
                                <form class="form" action="#" method="POST" id="QuestionsQuizCrate">
                                    @csrf

                                    <div id="quizCrate">
                                        <div>
                                            <label for="title" class="form-label">Title</label>
                                            <input id="title" class="form-control" type="text" name="title"
                                                value="{{ old('title', $quiz->title ?? '') }}">
                                            @error('title')
                                                <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="mt-4">
                                            <label for="slug" class="form-label">Slug</label>
                                            <input id="slug" class="form-control" type="text" name="slug"
                                                value="{{ old('slug', $quiz->slug ?? '') }}" disabled>
                                            @error('slug')
                                                <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="mt-4">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea id="description" class="form-control" name="description">{{ old('description', $quiz->description ?? '') }}</textarea>
                                            @error('description')
                                                <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="mt-4">
                                                    <label for="level" class=" form-label">Grade</label>
                                                    <select id="level" name="level" class="form-control">
                                                        <option value="" disabled selected>Select term</option>
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
                                                    <label for="term" class=" form-label">Term</label>
                                                    <select id="term" name="term" class="form-control">
                                                        <option value="" disabled selected>Select term</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                    </select>

                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="mt-4">
                                                    <label for="section" class=" form-label">Level</label>
                                                    <select id="section" name="section" class="form-control">
                                                        <option value="" disabled selected>Select Level</option>
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
                                                <label for="public" class="form-label">Public</label>
                                                <input type="checkbox" id="public" class="form-check-input ml-2"
                                                    name="public"
                                                    {{ old('public', $quiz->public ?? false) ? 'checked' : '' }}>
                                            </div>
                                            @error('public')
                                                <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="modal-footer">

                                        </div>
                                    </div>

                                    <div class="my-4">
                                        <label for="questions_id" class="form-label">Select Questions</label>
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
                                                <h4 class="mb-4">Create Question #1</h4>

                                                <input type="hidden" id="formMethod" name="_method" value="POST">
                                                <div class="mb-3">
                                                    <select name="type" id="SelectType"
                                                        class="SelectType form-control">
                                                        <option value="">Select type answer</option>
                                                        <option value="options">Options</option>
                                                        <option value="written">Written</option>
                                                        <option value="image">Image</option>
                                                        <option value="fraction">Fraction</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="text" class="form-label">Question text</label>
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
                                                                Fraction
                                                            </button>
                                                        </div>
                                                        <div class="col-lg-2">
                                                            <button type="button" class="btn btn-primary"
                                                                onclick="insertMixed()">
                                                                {{-- จำนวนคละ --}}
                                                                MixFrac
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="text" class="form-label">Question Image</label>
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
                                                            onclick="removeQuestionImg()">Remove Image</button>
                                                    </div>
                                                </div>

                                                <div class="mb-3 written_type">
                                                    <label for="written" class="form-label">Question Written</label>
                                                    <br>
                                                    <div class="wrtiten-container">
                                                        <input type="text" class="form-control" id="written"
                                                            name="written" placeholder="Enter correct answer">
                                                    </div>
                                                </div>

                                                <div class="mb-3 options_type">
                                                    <label for="options" class="form-label">Question options</label>
                                                    <br>
                                                    <button type="button" class="btn btn-sm btn-primary mt-2 addOption2"
                                                        id="addOption2">Add</button>
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
                                                                        Correct&nbsp
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
                                                    <label for="image" class="form-label">Question Image
                                                        Options</label>
                                                    <br>
                                                    <button type="button" class="btn btn-sm btn-primary mt-2"
                                                        id="addOptionImg">Add</button>

                                                    <div id="image-options-container">


                                                    </div>
                                                    @error('image_options')
                                                        <div class="text-danger mt-2">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3 fraction_type">
                                                    <hr>
                                                    <h2>answers fraction</h2>
                                                    <div class="mb-3">
                                                        <label for="answer_type" class="form-label">Type Answer</label>
                                                        <select name="answer_type_fraction" id="answer_type_fraction"
                                                            class="answer_type_fraction form-control">
                                                            <option value="">select type answer fraction</option>
                                                            <option value="written">written</option>
                                                            <option value="options">options</option>
                                                        </select>
                                                    </div>
                                                    <div class="written_type_fraction" style="display: none;">
                                                        <div class="mb-3">
                                                            <label for="answer_numerator"
                                                                class="form-label">Numerator</label>
                                                            <input type="number" class="form-control"
                                                                name="answer_numerator" id="answer_numerator"
                                                                placeholder="numerator" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="answer_denominator"
                                                                class="form-label">Denominator</label>
                                                            <input type="number" class="form-control"
                                                                name="answer_denominator" required id="answer_denominator"
                                                                placeholder="denominator" min="1">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="answer_type" class="form-label">Type
                                                                Fraction</label>
                                                            <select name="answer_type" id="answer_type"
                                                                class="form-control">
                                                                <option value="frac">Fraction</option>
                                                                <option value="mixed" selected>Mixed Fraction</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="options_type_fraction" style="display: none;">
                                                        <button type="button" class="btn btn-sm btn-primary mt-2"
                                                            id="add-fraction-option">Add Fraction</button>
                                                        <div id="add-fraction-container">

                                                        </div>

                                                    </div>


                                                    <hr>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-4">
                                                        <div class="mt-4">
                                                            <label for="level"
                                                                class="question-grade form-label">Grade</label>
                                                            <select id="level_1" name="level"
                                                                class="question-grade form-control">
                                                                <option value="" selected>Select term
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
                                                            <label for="term" class="form-label">Term</label>
                                                            <select id="term_1" name="term"
                                                                class="question-term form-control">
                                                                <option value="" selected>Select term
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
                                                            <label for="section" class=" form-label">Level</label>
                                                            <select id="section_1" name="section"
                                                                class="question-level form-control">
                                                                <option value="" selected>Select Level
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
                                                    <label for="code_snippet" class="form-label">Hint</label>
                                                    <textarea id="code_snippet" class="form-control" name="code_snippet">{{ $question->code_snippet ?? old('code_snippet') }}</textarea>
                                                    @error('code_snippet')
                                                        <div class="text-danger mt-2">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <!-- Answer explanation -->
                                                <div class="mb-3">
                                                    <label for="answer_explanation" class="form-label">Answer
                                                        explanation</label>
                                                    <textarea id="answer_explanation" class="form-control" name="answer_explanation">{{ $question->answer_explanation ?? old('answer_explanation') }}</textarea>
                                                    @error('answer_explanation')
                                                        <div class="text-danger mt-2">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <!-- Answer Explanation Image -->
                                                <div class="mb-3">
                                                    <label for="answer_explanation_image" class="form-label">Answer
                                                        Explanation Image</label>
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
                                                            alt="Answer Explanation Image"
                                                            style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                                                        <br>
                                                        <button type="button" class="btn btn-sm btn-danger mt-2"
                                                            onclick="removeAnswerExplanationImg()">Remove Image</button>
                                                    </div>
                                                </div>

                                                <!-- More info link -->
                                                <div class="mb-3">
                                                    <label for="more_info_link" class="form-label">More info link</label>
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
                                    <button id="AddQuestionForm" class="btn btn-info">Add Question</button>
                                    <button id="SubmitQuestionForm" type="submit"
                                        class="btn btn-success">Submit</button>

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
