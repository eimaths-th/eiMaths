@extends('layouts.quiz_layout')

@section('title', $quiz->title)

@push('css')
    <style>
        /* Modern Quiz Styles */
        .quiz-nav {
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            outline: none;
        }

        .quiz-nav:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .quiz-nav:focus {
            outline: 2px solid #f97316;
            outline-offset: 2px;
        }

        /* Radio button custom styling */
        input[type="radio"]:checked {
            background-color: #f97316;
            border-color: #f97316;
        }

        input[type="radio"]:focus {
            ring-color: #f97316;
            ring-offset-color: #ffffff;
        }

        /* Fraction input styling */
        .fraction-input {
            transition: all 0.2s ease;
        }

        .fraction-input:focus {
            transform: scale(1.02);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }

        /* Answer option hover effects */
        .answer-option {
            transition: all 0.2s ease;
        }

        .answer-option:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Progress bar animation */
        .progress-bar {
            transition: width 0.5s ease-in-out;
        }

        /* Timer styling */
        #quiz-timer {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .w-64 {
                width: 100%;
                margin-bottom: 1rem;
            }

            .flex {
                flex-direction: column;
            }

            .grid-cols-5 {
                grid-template-columns: repeat(10, minmax(0, 1fr));
            }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: #f97316;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #ea580c;
        }

        /* Loading animation for buttons */
        .btn-loading {
            position: relative;
            pointer-events: none;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    <style>
        /* Optional: Adjust overall input appearance */
        input[type="text"] {
            font-size: 16px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .modal-overlay {
            backdrop-filter: blur(4px);
        }

        .symbol-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .tab-button.active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }
    </style>
@endpush
@push('script')
@endpush
@section('content')
    <div style="min-height: 100vh; background-color: #f9fafb;">
        <!-- Top Progress Bar -->
        <div style="background-color: white; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); border-bottom: 1px solid #e5e7eb;">
            <div style="max-width: 80rem; margin: 0 auto; padding: 1rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                    <h1 style="font-size: 1.125rem; font-weight: 600; color: #1f2937;"></h1>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: #4b5563;">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span id="quiz-timer">20:05</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 0.875rem; color: #4b5563;">{{ $currentQuestionIndex + 1 }} /
                        {{ $questionsCount }} Questions</span>
                    <div
                        style="flex: 1; background-color: #e5e7eb; border-radius: 9999px; height: 0.5rem; margin-left: 0.75rem;">
                        <div
                            style="background-color: #f97316; height: 0.5rem; border-radius: 9999px; transition: all 0.3s; width: {{ (($currentQuestionIndex + 1) / $questionsCount) * 100 }}%;">
                        </div>
                    </div>
                    <span
                        style="font-size: 0.875rem; color: #4b5563;">{{ round((($currentQuestionIndex + 1) / $questionsCount) * 100) }}%
                        Completed</span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div style="max-width: 80rem; margin: 0 auto; padding: 1.5rem;">
            <div style="display: flex; gap: 1.5rem;">
                <!-- Left Sidebar - Question Navigation -->
                <div
                    style="width: 16rem; background-color: white; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); padding: 1rem;">
                    <h3 style="font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 1rem;">Questions</h3>
                    <div
                        style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 0.5rem; margin-bottom: 1rem;">
                        @for ($i = 0; $i < $questionsCount; $i++)
                            @php
                                $isAnswered =
                                    isset($answersOfQuestions[$i]) && !empty($answersOfQuestions[$i]['answer']);
                                $isCurrent = $i == $currentQuestionIndex;
                            @endphp
                            <button
                                style="width: 2rem; height: 2rem; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 500; transition: all 0.3s; border: none; cursor: pointer; {{ $isCurrent ? 'background-color: rgb(255, 165, 0); color: white' : ($isAnswered ? 'background-color: rgb(76, 175, 80); color: white' : 'background-color: rgb(229, 231, 235); color: rgb(75, 85, 99)') }}"
                                class="quiz-nav" data-question-index="{{ $i }}">
                                {{ $i + 1 }}
                            </button>
                        @endfor
                    </div>
                    <div style="font-size: 0.75rem; color: #6b7280; display: flex; flex-direction: column; gap: 0.25rem;">
                        <div style="display: flex; align-items: center;">
                            <div
                                style="width: 0.75rem; height: 0.75rem; background-color: rgb(76, 175, 80); border-radius: 0.25rem; margin-right: 0.5rem;">
                            </div>
                            <span>Answered</span>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <div
                                style="width: 0.75rem; height: 0.75rem; background-color: #f97316; border-radius: 0.25rem; margin-right: 0.5rem;">
                            </div>
                            <span>Answering</span>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <div
                                style="width: 0.75rem; height: 0.75rem; background-color: #f3f4f6; border-radius: 0.25rem; margin-right: 0.5rem;">
                            </div>
                            <span>Not Answered</span>
                        </div>
                    </div>

                    <button
                        style="width: 100%; margin-top: 1.5rem; background-color: #f97316; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 500; border: none; cursor: pointer; transition: background-color 0.3s;"
                        onmouseover="this.style.backgroundColor='#ea580c'" onmouseout="this.style.backgroundColor='#f97316'"
                        onclick="document.getElementById('quiz-form').submit()">
                        Submit Answer
                    </button>
                </div>

                <!-- Main Quiz Content -->
                <div
                    style="flex: 1; background-color: white; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); padding: 1.5rem;">
                    <div style="width: 100%;">

                        @if (!$quiz->public && !auth()->check())
                            <div
                                style="background-color: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; padding: 0.75rem 1rem; border-radius: 0.25rem; margin-bottom: 1rem;">
                                <span style="display: block;">
                                    This test is available only for registered users. Please <a href="{{ route('login') }}"
                                        style="text-decoration: underline; color: #b91c1c;">Log in</a> or <a
                                        href="{{ route('register') }}"
                                        style="text-decoration: underline; color: #b91c1c;">Register</a>
                                </span>
                            </div>
                        @else
                            @php
                                $secondsPerQuestion = $startTimeInSeconds;
                            @endphp

                            <div id="quiz-container" data-seconds-left="0">
                                <!-- Question Header -->
                                <div style="margin-bottom: 1.5rem;">
                                    <div
                                        style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                                        <span style="font-size: 0.875rem; font-weight: 500; color: #4b5563;">Question {{ $currentQuestionIndex + 1 }}</span>
                                        <span
                                            style="font-size: 0.75rem; color: #6b7280;">{{ str_pad($currentQuestionIndex + 1, 6, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                    <h2 style="font-size: 1.125rem; font-weight: 500; color: #111827; line-height: 1.75;">
                                        @if ($currentQuestion->type === 'fraction')
                                            @php
                                                $text = replaceMixedFractions($currentQuestion->text);
                                            @endphp
                                            {!! $text !!}
                                        @else
                                            {!! replaceMixedFractions($currentQuestion->text ?? '') !!}
                                        @endif
                                    </h2>
                                </div>
                                @if ($currentQuestion->img_name)
                                    <div style="margin-bottom: 1.5rem; display: flex; justify-content: center;">
                                        <img style="max-width: 100%; height: auto; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);"
                                            src="{{ asset('img_questions/' . $currentQuestion->img_name) }}"
                                            alt="Question image">
                                    </div>
                                @endif
                                @if ($currentQuestion->code_snippet)
                                    <pre
                                        style="margin-bottom: 1.5rem; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1rem; font-size: 0.875rem; overflow-x: auto;">{{ $currentQuestion->code_snippet }}</pre>
                                @endif

                                <!-- Answer Options -->
                                <form id="quiz-form"
                                    action="{{ route('quiz.next', ['quiz' => $quiz, 'currentQuestionIndex' => $currentQuestionIndex, 'quizStartTime' => $quizStartTime]) }}"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="startTimeInSeconds" id="startTimeInSeconds"
                                        value="{{ $secondsPerQuestion }}">
                                    <input type="hidden" id="currentQuestionIndex" name="currentQuestionIndex"
                                        value="{{ $currentQuestionIndex }}">
                                    <input type="hidden" name="currentQuestionId" id="currentQuestionId"
                                        value="{{ $currentQuestion->id }}">
                                    <input type="hidden" id="currentQuestionType" name="currentQuestionType"
                                        value="{{ $currentQuestion->type }}">
                                    <input type="hidden" id="correctAnswer" value="{{ $correctAnswer }}">
                                    @if ($currentQuestion->type === 'written')
                                        <div style="margin-bottom: 1.5rem;">
                                            <div class="flex gap-3">
                                                <input type="text" name="answerOfQuestion[{{ $currentQuestionIndex }}]"
                                                    id="answerOfQuestion_{{ $currentQuestionIndex }}"
                                                    class="shadow-md hover:shadow-lg"
                                                    style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; outline: none; transition: all 0.3s;"
                                                    onfocus="this.style.borderColor='#f97316'; this.style.boxShadow='0 0 0 2px rgba(249, 115, 22, 0.2)'"
                                                    onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"
                                                    placeholder="กรอกคำตอบของคุณ" autocomplete="off"
                                                    value="{{ $getAnserSessionIndexWritten }}">
                                                {{-- <input type="text" id="mathInput"
                                                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg"
                                                    placeholder="คลิกปุ่มเพื่อเลือกสัญลักษณ์ทางคณิตศาสตร์..." /> --}}
                                                <button id="openModalBtn" type="button"
                                                    style="padding: 0.75rem 1rem; background-color: #3b82f6; color: #ffffff; border-radius: 0.5rem; font-weight: 500; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); transition: all 0.3s; cursor: pointer; hover:background-color: #1d4ed8; hover:box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);">
                                                    Select Math Symbols
                                                </button>

                                                <button id="clearBtn"type="button"
                                                    class="px-4 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all duration-200 shadow-md hover:shadow-lg">
                                                    Clear
                                                </button>
                                            </div>
                                            <div class="preview-answer"
                                                style="margin-top: 1rem; padding: 1rem; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; display: none;">
                                                <h3
                                                    style="margin: 0 0 0.5rem 0; font-size: 14px; font-weight: 600; color: #6b7280;">
                                                    Your Answer : </h3>
                                                <p id="preview" {{-- style="margin: 0; padding: 0.5rem; background-color: white; border: 1px solid #d1d5db; border-radius: 0.375rem; min-height: 2rem; display: flex; align-items: center;"> --}}
                                                    style="margin: 0 0 0.5rem 0; font-size: 14px; font-weight: 600; color: #6b7280;">
                                                </p>
                                            </div>

                                        </div>
                                    @elseif ($currentQuestion->type === 'options')
                                        <div
                                            style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem;">
                                            @foreach ($currentQuestion->options as $index => $option)
                                                @php
                                                    $checked =
                                                        $option->id === (int) $getAnserSessionIndexWritten
                                                            ? 'checked'
                                                            : '';
                                                @endphp
                                                <label for="option-{{ $option->id }}"
                                                    style="display: flex; align-items: center; padding: 1rem; border: 1px solid {{ $checked ? '#f97316' : '#e5e7eb' }}; border-radius: 0.5rem; cursor: pointer; transition: all 0.3s; background-color: {{ $checked ? '#fff7ed' : 'white' }};"
                                                    onmouseover="if (!this.querySelector('input').checked) { this.style.backgroundColor='#f9fafb'; }"
                                                    onmouseout="if (!this.querySelector('input').checked) { this.style.backgroundColor='white'; }">
                                                    <input type="radio" id="option-{{ $option->id }}"
                                                        name="answerOfQuestion[{{ $currentQuestionIndex }}]"
                                                        value="{{ $option->id }}" {{ $checked }}
                                                        style="width: 1rem; height: 1rem; accent-color: #f97316; margin-right: 0.75rem;">
                                                    <span style="color: #111827;">
                                                        {!! replaceMixedFractions($option->text) !!}
                                                    </span>
                                                    <input type="hidden" id="optionAnswer" value="{{ $option->text }}">
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif ($currentQuestion->type === 'image')
                                        <div
                                            style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                                            @foreach ($currentQuestion->images as $index => $image)
                                                @php
                                                    $checked =
                                                        $image->id === (int) $getAnserSessionIndexWritten
                                                            ? 'checked'
                                                            : '';
                                                @endphp
                                                <label for="img-option-{{ $image->id }}"
                                                    style="display: flex; align-items: center; gap: 1rem; border: 2px solid {{ $checked ? '#f97316' : '#e5e7eb' }}; border-radius: 0.5rem; padding: 1rem; cursor: pointer; transition: all 0.3s; background-color: {{ $checked ? '#fff7ed' : 'white' }};"
                                                    onmouseover="if (!this.querySelector('input').checked) { this.style.backgroundColor='#f9fafb'; }"
                                                    onmouseout="if (!this.querySelector('input').checked) { this.style.backgroundColor='white'; }">
                                                    <input type="radio" id="img-option-{{ $image->id }}"
                                                        name="answerOfQuestion[{{ $currentQuestionIndex }}]"
                                                        value="{{ $image->id }}" {{ $checked }}
                                                        style="flex-shrink: 0; width: 1rem; height: 1rem; accent-color: #f97316;">
                                                    <div style="text-align: center; flex-grow: 1;">
                                                        <img src="{{ asset('images_options/' . $image->img_name) }}"
                                                            style="max-width: 100%; height: auto; max-height: 8rem; object-fit: contain; border-radius: 0.25rem;"
                                                            alt="Image option">
                                                    </div>
                                                    <input type="hidden" id="imgOptionAnswer"
                                                        value="{{ $image->img_name }}">
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif ($currentQuestion->type === 'fraction')
                                        @if ($currentQuestion->fractions->first()->type === 'written')
                                            <div class="mb-6">
                                                <input type="hidden" id="type_frac" name="currentQuestionFracType"
                                                    value="{{ $currentQuestion->fractions->first()->type }}">
                                                <input type="hidden" id="answer_type_frac"
                                                    name="currentQuestionFracAnswerType"
                                                    value="{{ $currentQuestion->fractions->first()->answer_type }}">

                                                <style>
                                                    .frac-inputs {
                                                        display: flex;
                                                        align-items: center;
                                                        justify-content: center;
                                                        gap: 0.5rem;
                                                    }

                                                    .frac-inputs input {
                                                        width: 5.5rem;
                                                        text-align: center;
                                                        border: 1px solid #e5e7eb;
                                                        border-radius: 0.25rem;
                                                        padding: 0.5rem;
                                                        font-size: 1rem;
                                                    }

                                                    .frac-inputs input:focus {
                                                        outline: none;
                                                        border-color: #f97316;
                                                        box-shadow: 0 0 0 2px #f97316;
                                                    }

                                                    /* .frac-inputs .frac-whole {
                                                                                                                        width: 5rem;
                                                                                                                    } */

                                                    .frac-inputs .frac-line {
                                                        width: 5.5rem;
                                                        height: 2px;
                                                        background-color: #e5e7eb;
                                                    }
                                                </style>
                                                @if ($currentQuestion->fractions->first()->answer_type === 'frac')
                                                    <div class="frac-inputs">
                                                        <input type="text" name="currentQuestionFracNumerator"
                                                            placeholder="เศษ"
                                                            value="{{ $getAnserSessionIndexNumerator }}">
                                                        <div class="frac-line"></div>
                                                        <input type="text" name="currentQuestionFracDenominator"
                                                            placeholder="ส่วน"
                                                            value="{{ $getAnserSessionIndexDenominator }}">
                                                    </div>
                                                @else
                                                    <div class="frac-inputs">
                                                        <input type="text" name="currentQuestionFracWhole"
                                                            placeholder="จำนวนเต็ม" class="frac-whole"
                                                            value="{{ $getAnserSessionIndexWhole }}" />
                                                        <div class="flex flex-col gap-3">
                                                            <input type="text" name="currentQuestionFracNumerator"
                                                                placeholder="เศษ"
                                                                value="{{ $getAnserSessionIndexNumerator }}">
                                                            <div class="frac-line"></div>
                                                            <input type="text" name="currentQuestionFracDenominator"
                                                                placeholder="ส่วน"
                                                                value="{{ $getAnserSessionIndexDenominator }}">
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <!-- Fraction options -->
                                            <div class="space-y-3 mb-6">
                                                <input type="hidden" name="currentQuestionFracType" value="options">
                                                @foreach ($currentQuestion->fractions as $index => $option)
                                                    @php
                                                        $checked =
                                                            $option->id === (int) $getAnserSessionIndexWritten
                                                                ? 'checked'
                                                                : '';
                                                        if ($option->answer_type == 'mixed') {
                                                            $textOption = toMixedFraction(
                                                                $option->numerator,
                                                                $option->denominator,
                                                                $option->whole ?? null
                                                            );
                                                        } else {
                                                            $textOption = toFraction(
                                                                $option->numerator,
                                                                $option->denominator,
                                                            );
                                                        }
                                                    @endphp
                                                    <label for="fractionOption-{{ $option->id }}"
                                                        class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors {{ $checked ? 'border-orange-500 bg-orange-50' : '' }}">
                                                        <input type="radio" id="fractionOption-{{ $option->id }}"
                                                            name="answerOfQuestion[{{ $currentQuestionIndex }}]"
                                                            value="{{ $option->id }}" {{ $checked }}
                                                            class="w-4 h-4 text-orange-500 border-gray-300 focus:ring-orange-500">
                                                        <span class="ml-3 text-gray-900">
                                                            {!! $textOption !!}
                                                        </span>
                                                        <input type="hidden" id="optionFracAnswer"
                                                            value="{{ $option->text }}">
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                    <div id="answer-container">

                                    </div>
                                    <!-- Navigation Buttons -->
                                    <div class="flex justify-center mt-8">
                                        @if ($currentQuestionIndex < $questionsCount - 1)
                                            <button type="submit" id="nextQuestion"
                                                style="background-color: #f97316; color: white; font-weight: 500; padding: 0.75rem 1.5rem; border-radius: 0.5rem; transition: all 0.3s; cursor: pointer;"
                                                class="hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                                                Next
                                            </button>
                                        @else
                                            <button type="submit"
                                                style="background-color: #34C759; color: white; font-weight: 500; padding: 0.75rem 1.5rem; border-radius: 0.5rem; transition: all 0.3s; cursor: pointer;"
                                                class="hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                                Submit Answer
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- @include('home.modal_math') --}}
    @include('home.modal_math2')
@endsection
@push('scripts')
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const quizForm = document.getElementById("quiz-form");
            const navButtons = document.querySelectorAll(".quiz-nav");

            if (navButtons.length > 0 && quizForm) {
                navButtons.forEach(button => {
                    button.addEventListener("click", function(event) {
                        event.preventDefault();

                        let changeToquestionIndex = this.getAttribute("data-question-index");
                        saveAnswer(changeToquestionIndex);
                    });
                });
            }

            function saveAnswer(changeToIndex) {
                let formData = new FormData(quizForm);

                // Append additional data
                formData.append("quiz_id", "{{ $quiz->id }}");
                formData.append("changeToQuestionIndex", changeToIndex);
                formData.append("quizStartTime", "{{ $quizStartTime }}");

                fetch("{{ route('quiz.saveAnswer') }}", {
                        method: "POST", // <- changed from GET to POST
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                        },
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Answer saved:", data);
                        moveToNextQuestion(data);
                    })
                    .catch(error => {
                        console.error("Error saving answer:", error);
                    });
            }

            function moveToNextQuestion(data) {
                let changeToIndex = data.changeToIndex;
                let quiz = data.quiz.slug; // now this is a slug or ID
                let quizStartTime = data.quizStartTime;

                // Replace placeholders in route with dynamic values
                let nextUrl =
                    `{{ route('quiz.show', ['quiz' => '__QUIZ__', 'currentQuestionIndex' => '__INDEX__']) }}`
                    .replace("__QUIZ__", quiz)
                    .replace("__INDEX__", changeToIndex)

                window.location.href = nextUrl;
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let quizStartTime = {{ $quizStartTime ?? 'null' }};
            if (!quizStartTime) {
                quizStartTime = new Date().getTime(); // fallback
            } else {
                quizStartTime = quizStartTime * 1000; // convert to milliseconds
            }

            let timerDisplay = document.getElementById("quiz-timer");

            function updateTimer() {
                let now = new Date().getTime();
                let elapsed = Math.floor((now - quizStartTime) / 1000); // Elapsed seconds

                let minutes = Math.floor(elapsed / 60);
                let seconds = elapsed % 60;

                timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, "0")}`;
            }

            setInterval(updateTimer, 1000);
            updateTimer();
        });
    </script>

    <script>
        // document.addEventListener('DOMContentLoaded', function() {
        //     let secondsLeft = 0;
        //     const timeElapsedSpan = document.getElementById('time-elapsed');
        //     const quizForm = document.getElementById('quiz-form');

        //     setInterval(() => {
        //         secondsLeft++;
        //         let hours = Math.floor(secondsLeft / 3600).toString().padStart(2, '0');
        //         let minutes = Math.floor((secondsLeft % 3600) / 60).toString().padStart(2, '0');
        //         let seconds = (secondsLeft % 60).toString().padStart(2, '0');
        //         timeElapsedSpan.textContent = `${hours}:${minutes}:${seconds}`;
        //     }, 1000);

        //     quizForm.addEventListener('submit', () => {
        //         document.getElementById('startTimeInSeconds').value = Math.floor(Date.now() / 1000);
        //     });
        // });

        $(document).ready(function() {
            // $('#nextQuestion').hide();
            $('#answer-container').hide();

            $('#showAnswer').click(function() {
                var userAnswer;
                var correctAnswer = $('#correctAnswer').val();
                var questionType = $('#currentQuestionType').val();

                if (questionType === 'written') {
                    userAnswer = $('input[name="answerOfQuestion[{{ $currentQuestionIndex }}]"]').val();

                } else if (questionType === 'options' || questionType === 'image') {
                    userAnswer = $('input[name="answerOfQuestion[{{ $currentQuestionIndex }}]"]:checked')
                        .val();

                }

                if (!userAnswer) {
                    alert('Please select an answer.');
                    return;
                }

                var data = {
                    userAnswer: userAnswer,
                    type: questionType,
                    correctAnswer: correctAnswer,
                    _token: '{{ csrf_token() }}'
                };

                var url = '{{ route('check.answer') }}';

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: data,
                    success: function(result) {
                        // alert(result.result);
                        if (result.type === 'written') {
                            $('input[name="answerOfQuestion[{{ $currentQuestionIndex }}]"]')
                                .prop('readonly', true);
                        } else if (result.type === 'options' || result.type === 'image') {
                            $('input[name="answerOfQuestion[{{ $currentQuestionIndex }}]"]')
                                .each(function() {
                                    if ($(this).is(':checked')) {
                                        $(this).attr('data-selected', true);
                                    } else {
                                        $(this).prop('disabled', true);
                                    }
                                });
                        }
                        if (result.type === 'image' && result.imageUrl) {
                            var html = `
                                    <div class="answer-wrapper">
                                        <div class="answer-label">Your Answer</div>
                                        <div class="image-answer-container">
                                            <div class="image-answer-item">
                                                <label for="image-answer">
                                                    <p>${result.result}</p>
                                                    <img src="${result.imageUrl}" alt="Image option">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            $('#answer-container').html(html);
                        } else {
                            $('#answer-container').html(`
                                <div class="answer-wrapper">
                                    <div class="answer-label">Your Answer</div>
                                     <div class="text-answer">${result.result + result.answerCorrect}</div>
                                </div>
                                `);
                        }

                        // Change background color based on whether the answer is correct
                        if (result.checkCorrect === 1) {
                            // $('#answer-container').css('background-color', 'lightgreen');// change to border solid 4px color lightgreen

                            $('#answer-container').css({
                                'border': '4px solid lightgreen', // Set a 4px solid border with lightgreen color
                                'padding': '10px', // Optional: Add padding to make it look nicer
                                'border-radius': '8px' // Optional: Add rounded corners for a smoother look
                            });


                        } else {
                            // $('#answer-container').css('background-color', 'lightcoral');
                            $('#answer-container').css({
                                'border': '4px solid lightcoral', // Set a 4px solid border with lightgreen color
                                'padding': '10px', // Optional: Add padding to make it look nicer
                                'border-radius': '8px' // Optional: Add rounded corners for a smoother look
                            });
                        }


                        $('#nextQuestion').show();
                        $('#answer-container').show();
                        // $('#showAnswer').hide();
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                    }
                });

                // Uncomment these lines to toggle buttons after checking the answer

            });



        });
    </script>
    <script>
        // Math symbols data
        const mathSymbolsSave = {
            arithmetic: {
                title: 'การคำนวณพื้นฐาน (Basic Arithmetic)',
                symbols: [{
                        symbol: '+',
                        name: 'บวก (Addition)'
                    },
                    {
                        symbol: '−',
                        name: 'ลบ (Subtraction)'
                    },
                    {
                        symbol: '×',
                        name: 'คูณ (Multiplication)'
                    },
                    {
                        symbol: '⋅',
                        name: 'คูณ (Dot Multiplication)'
                    },
                    {
                        symbol: '÷',
                        name: 'หาร (Division)'
                    },
                    {
                        symbol: '/',
                        name: 'เศษส่วน (Slash Division)'
                    },
                    {
                        symbol: '=',
                        name: 'เท่ากับ (Equal)'
                    },
                    {
                        symbol: '%',
                        name: 'เปอร์เซ็นต์ (Percent)'
                    },
                    {
                        symbol: '!',
                        name: 'แฟกทอเรียล (Factorial)'
                    },
                    {
                        symbol: '°',
                        name: 'องศา (Degree)'
                    }
                ]
            },
            comparison: {
                title: 'การเปรียบเทียบ (Comparison)',
                symbols: [{
                        symbol: '≠',
                        name: 'ไม่เท่ากับ (Not Equal)'
                    },
                    {
                        symbol: '<',
                        name: 'น้อยกว่า (Less Than)'
                    },
                    {
                        symbol: '>',
                        name: 'มากกว่า (Greater Than)'
                    },
                    {
                        symbol: '≤',
                        name: 'น้อยกว่าหรือเท่ากับ (Less Than or Equal)'
                    },
                    {
                        symbol: '≥',
                        name: 'มากกว่าหรือเท่ากับ (Greater Than or Equal)'
                    },
                    {
                        symbol: '≈',
                        name: 'ประมาณ (Approximately)'
                    }
                ]
            },
            sets: {
                title: 'ทฤษฎีเซต (Set Theory)',
                symbols: [{
                        symbol: '{ }',
                        name: 'วงเล็บปีกกา (Set Brackets)'
                    },
                    {
                        symbol: '∈',
                        name: 'เป็นสมาชิกของ (Element Of)'
                    },
                    {
                        symbol: '∉',
                        name: 'ไม่เป็นสมาชิกของ (Not Element Of)'
                    },
                    {
                        symbol: '⊂',
                        name: 'เป็นสับเซตของ (Subset)'
                    },
                    {
                        symbol: '⊃',
                        name: 'เป็นซูเปอร์เซตของ (Superset)'
                    },
                    {
                        symbol: '∪',
                        name: 'ยูเนียน (Union)'
                    },
                    {
                        symbol: '∩',
                        name: 'อินเตอร์เซกชัน (Intersection)'
                    },
                    {
                        symbol: '∅',
                        name: 'เซตว่าง (Empty Set)'
                    }
                ]
            },
            logic: {
                title: 'ตรรกศาสตร์ (Logic)',
                symbols: [{
                        symbol: '∀',
                        name: 'สำหรับทุกตัว (For All)'
                    },
                    {
                        symbol: '∃',
                        name: 'มีบางตัว (There Exists)'
                    },
                    {
                        symbol: '¬',
                        name: 'นิเสธ (Not)'
                    },
                    {
                        symbol: '∧',
                        name: 'และ (And)'
                    },
                    {
                        symbol: '∨',
                        name: 'หรือ (Or)'
                    },
                    {
                        symbol: '→',
                        name: 'ถ้า...แล้ว... (If...Then)'
                    },
                    {
                        symbol: '↔',
                        name: 'ก็ต่อเมื่อ (If and Only If)'
                    }
                ]
            },
            calculus: {
                title: 'แคลคูลัสและพีชคณิตขั้นสูง (Calculus & Advanced Algebra)',
                symbols: [{
                        symbol: '∑',
                        name: 'ซิกมา (Summation)'
                    },
                    {
                        symbol: '∏',
                        name: 'พายตัวใหญ่ (Product)'
                    },
                    {
                        symbol: '∫',
                        name: 'อินทิกรัล (Integral)'
                    },
                    {
                        symbol: 'd/dx',
                        name: 'อนุพันธ์ (Derivative)'
                    },
                    {
                        symbol: '∂',
                        name: 'อนุพันธ์ย่อย (Partial Derivative)'
                    },
                    {
                        symbol: '∞',
                        name: 'อนันต์ (Infinity)'
                    },
                    {
                        symbol: '√',
                        name: 'รากที่สอง (Square Root)'
                    },
                    {
                        symbol: '∛',
                        name: 'รากที่สาม (Cube Root)'
                    },
                    {
                        symbol: '|x|',
                        name: 'ค่าสัมบูรณ์ (Absolute Value)'
                    }
                ]
            },
            greek: {
                title: 'อักษรกรีก (Greek Letters)',
                symbols: [{
                        symbol: 'π',
                        name: 'พาย (Pi)'
                    },
                    {
                        symbol: 'θ',
                        name: 'ทีตา (Theta)'
                    },
                    {
                        symbol: 'α',
                        name: 'อัลฟา (Alpha)'
                    },
                    {
                        symbol: 'β',
                        name: 'เบตา (Beta)'
                    },
                    {
                        symbol: 'γ',
                        name: 'แกมมา (Gamma)'
                    },
                    {
                        symbol: 'Δ',
                        name: 'เดลตาตัวใหญ่ (Delta)'
                    },
                    {
                        symbol: 'δ',
                        name: 'เดลตาตัวเล็ก (delta)'
                    },
                    {
                        symbol: 'λ',
                        name: 'แลมบ์ดา (Lambda)'
                    },
                    {
                        symbol: 'μ',
                        name: 'มิว (Mu)'
                    },
                    {
                        symbol: 'σ',
                        name: 'ซิกมาตัวเล็ก (sigma)'
                    }
                ]
            },
            fraction: {
                title: 'เศษส่วน (Fraction)',
                symbols: [{
                        symbol: 'เศษส่วนธรรมดา',
                        name: 'เศษส่วนธรรมดา (Simple Fraction)',
                        type: 'fraction',
                        fractionType: 'simple'
                    },
                    {
                        symbol: 'จำนวนคละ',
                        name: 'จำนวนคละ (Mixed Number)',
                        type: 'fraction',
                        fractionType: 'mixed'
                    }
                ]
            }
        };

        const mathSymbols = {
            arithmetic: {
                title: 'Basic Arithmetic',
                symbols: [{
                        symbol: 'Simple Fraction',
                        name: 'Simple Fraction',
                        type: 'fraction',
                        fractionType: 'simple'
                    },
                    {
                        symbol: 'Mixed Number',
                        name: 'Mixed Number',
                        type: 'fraction',
                        fractionType: 'mixed'
                    },
                    {
                        symbol: '+',
                        name: 'Addition'
                    },
                    {
                        symbol: '−',
                        name: 'Subtraction'
                    },
                    {
                        symbol: '×',
                        name: 'Multiplication'
                    },
                    {
                        symbol: '⋅',
                        name: 'Dot Multiplication'
                    },
                    {
                        symbol: '÷',
                        name: 'Division'
                    },
                    {
                        symbol: '/',
                        name: 'Slash Division'
                    },
                    {
                        symbol: '=',
                        name: 'Equal'
                    },
                    {
                        symbol: '%',
                        name: 'Percent'
                    },
                    {
                        symbol: '!',
                        name: 'Factorial'
                    },
                    {
                        symbol: '°',
                        name: 'Degree'
                    }, {
                        symbol: '≠',
                        name: 'Not Equal'
                    },
                    {
                        symbol: '<',
                        name: 'Less Than'
                    },
                    {
                        symbol: '>',
                        name: 'Greater Than'
                    },
                    {
                        symbol: '≤',
                        name: 'Less Than or Equal'
                    },
                    {
                        symbol: '≥',
                        name: 'Greater Than or Equal'
                    },
                    {
                        symbol: '≈',
                        name: 'Approximately'
                    }, {
                        symbol: '{ }',
                        name: 'Set Brackets'
                    },
                    {
                        symbol: '∈',
                        name: 'Element Of'
                    },
                    {
                        symbol: '∉',
                        name: 'Not Element Of'
                    },
                    {
                        symbol: '⊂',
                        name: 'Subset'
                    },
                    {
                        symbol: '⊃',
                        name: 'Superset'
                    },
                    {
                        symbol: '∪',
                        name: 'Union'
                    },
                    {
                        symbol: '∩',
                        name: 'Intersection'
                    },
                    {
                        symbol: '∅',
                        name: 'Empty Set'
                    },
                    {
                        symbol: '∀',
                        name: 'For All'
                    },
                    {
                        symbol: '∃',
                        name: 'There Exists'
                    },
                    {
                        symbol: '¬',
                        name: 'Not'
                    },
                    {
                        symbol: '∧',
                        name: 'And'
                    },
                    {
                        symbol: '∨',
                        name: 'Or'
                    },
                    {
                        symbol: '→',
                        name: 'If...Then'
                    },
                    {
                        symbol: '↔',
                        name: 'If and Only If'
                    },
                    {
                        symbol: '∑',
                        name: 'Summation'
                    },
                    {
                        symbol: '∏',
                        name: 'Product'
                    },
                    {
                        symbol: '∫',
                        name: 'Integral'
                    },
                    {
                        symbol: 'd/dx',
                        name: 'Derivative'
                    },
                    {
                        symbol: '∂',
                        name: 'Partial Derivative'
                    },
                    {
                        symbol: '∞',
                        name: 'Infinity'
                    },
                    {
                        symbol: '√',
                        name: 'Square Root'
                    },
                    {
                        symbol: '∛',
                        name: 'Cube Root'
                    },
                    {
                        symbol: '|x|',
                        name: 'Absolute Value'
                    },
                    {
                        symbol: 'π',
                        name: 'Pi'
                    },
                    {
                        symbol: 'θ',
                        name: 'Theta'
                    },
                    {
                        symbol: 'α',
                        name: 'Alpha'
                    },
                    {
                        symbol: 'β',
                        name: 'Beta'
                    },
                    {
                        symbol: 'γ',
                        name: 'Gamma'
                    },
                    {
                        symbol: 'Δ',
                        name: 'Delta (uppercase)'
                    },
                    {
                        symbol: 'δ',
                        name: 'Delta (lowercase)'
                    },
                    {
                        symbol: 'λ',
                        name: 'Lambda'
                    },
                    {
                        symbol: 'μ',
                        name: 'Mu'
                    },
                    {
                        symbol: 'σ',
                        name: 'Sigma (lowercase)'
                    },
                ]
            },

        };
        // Render symbols
        function renderSymbols2(category) {
            const categoryData = mathSymbols[category];
            categoryTitle.textContent = categoryData.title;

            symbolsGrid.innerHTML = '';

            categoryData.symbols.forEach(item => {
                const button = document.createElement('button');
                button.className =
                    'symbol-button p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 group';
                button.title = item.name;
                button.innerHTML = `
                    <div class="text-2xl font-bold text-center mb-2 text-gray-800 group-hover:text-blue-600">
                        ${item.symbol}
                    </div>
                    <div class="text-xs text-gray-600 text-center leading-tight">
                        ${item.name}
                    </div>
                `;

                button.addEventListener('click', () => {
                    if (item.type === 'fraction') {
                        insertFraction(item.fractionType);
                    } else {
                        insertSymbol(item.symbol);
                    }
                });

                symbolsGrid.appendChild(button);
            });
        }

        function renderSymbols(category) {
            const categoryData = mathSymbols[category];
            categoryTitle.textContent = categoryData.title;

            symbolsGrid.innerHTML = '';

            categoryData.symbols.forEach(item => {
                const button = document.createElement('button');
                button.className =
                    'symbol-button p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 group';
                button.title = item.name;
                button.innerHTML = `
                    <div class="text-2xl font-bold text-center mb-2 text-gray-800 group-hover:text-blue-600">
                        ${item.symbol}
                    </div>
                    <div class="text-xs text-gray-600 text-center leading-tight">
                        ${item.name}
                    </div>
                `;

                button.addEventListener('click', () => {
                    if (item.type === 'fraction') {
                        insertFraction(item.fractionType);
                    } else {
                        insertSymbol(item.symbol);
                    }
                });

                symbolsGrid.appendChild(button);
            });
        }

        // DOM elements
        const mathInput = document.getElementById('answerOfQuestion_{{ $currentQuestionIndex }}');
        const mathModal = document.getElementById('mathModal2');
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const closeModalFooterBtn = document.getElementById('closeModalFooterBtn');

        // เพิ่ม event listener สำหรับ realtime preview
        if (mathInput) {
            // แสดง preview เมื่อเริ่มต้น
            updatePreview();

            // แสดง preview แบบ realtime เมื่อพิมพ์
            mathInput.addEventListener('input', updatePreview);
            mathInput.addEventListener('keyup', updatePreview);
            mathInput.addEventListener('paste', () => {
                setTimeout(updatePreview, 10); // delay เล็กน้อยสำหรับ paste
            });
        }
        const clearBtn = document.getElementById('clearBtn');
        const previewText = document.getElementById('previewText');
        const categoryTitle = document.getElementById('categoryTitle');
        const symbolsGrid = document.getElementById('symbolsGrid');
        const categoryTabs = document.getElementById('categoryTabs');

        let activeCategory = 'arithmetic';

        // Initialize
        function init() {
            renderSymbols(activeCategory);
            setupEventListeners();
            updatePreview();
        }

        // Setup event listeners
        function setupEventListeners() {
            openModalBtn.addEventListener('click', openModal);
            closeModalBtn.addEventListener('click', closeModal);
            closeModalFooterBtn.addEventListener('click', closeModal);
            clearBtn.addEventListener('click', clearInput);
            mathInput.addEventListener('input', updatePreview);

            // Category tabs
            categoryTabs.addEventListener('click', (e) => {
                if (e.target.classList.contains('tab-button')) {
                    const category = e.target.dataset.category;
                    setActiveCategory(category);
                }
            });

            // Close modal on overlay click
            // mathModal.addEventListener('click', (e) => {
            //     console.log(e.target);
            //     console.log(mathModal);

            //     if (e.target === mathModal) {
            //         closeModal();
            //     }
            // });

            // Close modal on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !mathModal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        }

        // Open modal
        function openModal() {
            mathModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Close modal
        function closeModal() {
            mathModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Clear input
        function clearInput() {
            mathInput.value = '';
            updatePreview();
        }



        // Update preview
        function updatePreview() {
            const value = mathInput.value.trim();
            const previewElement = document.getElementById('preview');

            var preview = replaceMixedFractions(value);
            if (previewElement) {
                if (value) {
                    // แสดง preview ของ input value
                    previewElement.innerHTML =
                        `<span style="font-family: 'Times New Roman', serif; font-size: 18px; color: #374151;">${preview}</span>`;
                    previewElement.parentElement.style.display = 'block';

                    // เรียก MathJax เพื่อ render LaTeX (รอให้ MathJax โหลดเสร็จก่อน)
                    if (typeof MathJax !== 'undefined') {
                        // ใช้ MathJax.startup.promise เพื่อรอให้ MathJax โหลดเสร็จ
                        MathJax.startup.promise.then(function() {
                            if (MathJax.typesetPromise) {
                                return MathJax.typesetPromise([previewElement]);
                            }
                        }).catch(function(err) {
                            console.log('MathJax typeset error:', err);
                        });
                    }
                    closeModal();
                } else {
                    // ซ่อน preview ถ้าไม่มี value
                    previewElement.innerHTML = '';
                    previewElement.parentElement.style.display = 'none';
                }
            }
        }

        // JavaScript functions for fraction conversion
        function replaceMixedFractions(text) {
            return text.replace(/(MixFrac|Frac)\((\d+)(?:,\s*(\d+))?(?:,\s*(\d+))?\)/g, function(match, func, arg1, arg2,
                arg3) {
                const functionName = func; // "MixFrac" or "Frac"
                let whole = null;
                let numerator, denominator;

                if (functionName === 'MixFrac') {
                    if (arg3 !== undefined) {
                        // MixFrac(whole, num, denom)
                        whole = parseInt(arg1);
                        numerator = parseInt(arg2);
                        denominator = parseInt(arg3);
                    } else {
                        // Fallback if only 2 numbers - MixFrac(num, denom)
                        numerator = parseInt(arg1);
                        denominator = parseInt(arg2);
                    }
                    return toMixedFraction( numerator, denominator,whole);
                } else {
                    // Frac(num, denom)
                    numerator = parseInt(arg1);
                    denominator = parseInt(arg2);
                    return toFraction(numerator, denominator);
                }
            });
        }

        function textFraction(numerator, denominator) {
            // This generates LaTeX syntax for the fraction
            return `\\(\\frac{${numerator}}{${denominator}}\\)`;
        }

        function toFraction(numerator, denominator) {
            // if (numerator % denominator === 0) {
            //     return (numerator / denominator).toString(); // Whole number
            // }
            return `\\(\\frac{${numerator}}{${denominator}}\\)`;
        }

        function toMixedFraction( numerator, denominator ,whole = null,) {
            let remainder;

            if (whole !== null && whole !== undefined) {
                remainder = numerator % denominator;
                whole = whole + Math.floor(numerator / denominator);
            } else {
                if (numerator % denominator === 0) {
                    return (numerator / denominator).toString(); // Whole number
                }

                whole = Math.floor(numerator / denominator);
                remainder = numerator % denominator;
            }

            return whole > 0 ?
                `\\(${whole} \\frac{${remainder}}{${denominator}}\\)` :
                `\\(\\frac{${remainder}}{${denominator}}\\)`;
        }
        // Set active category
        function setActiveCategory(category) {
            activeCategory = category;

            // Update tab buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                if (btn.dataset.category === category) {
                    btn.classList.add('active', 'text-white');
                    btn.classList.remove('text-gray-600', 'hover:text-blue-600', 'hover:bg-gray-100');
                } else {
                    btn.classList.remove('active', 'text-white');
                    btn.classList.add('text-gray-600', 'hover:text-blue-600', 'hover:bg-gray-100');
                }
            });

            renderSymbols(category);
        }



        // Insert symbol into input
        function insertSymbol(symbol) {
            const cursorPosition = mathInput.selectionStart;
            const currentValue = mathInput.value;
            const newValue = currentValue.slice(0, cursorPosition) + symbol + currentValue.slice(cursorPosition);

            mathInput.value = newValue;
            mathInput.focus();
            mathInput.setSelectionRange(cursorPosition + symbol.length, cursorPosition + symbol.length);

            updatePreview();
        }

        // Insert fraction form
        function insertFraction(fractionType) {
            // Close the modal first
            closeModal();

            // Create fraction form container
            const fractionContainer = document.createElement('div');
            fractionContainer.className = 'fraction-form-container';
            fractionContainer.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                padding: 30px;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
                z-index: 1000;
                min-width: 400px;
            `;

            let formHTML = '';
            if (fractionType === 'mixed') {
                formHTML = `
                    <h3 style="margin-bottom: 20px; color: #374151; font-size: 18px; font-weight: 600;">จำนวนคละ (Mixed Number)</h3>
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                        <input type="text" id="fracWhole" placeholder="จำนวนเต็ม" style="
                            width: 80px;
                            padding: 10px;
                            border: 2px solid #d1d5db;
                            border-radius: 6px;
                            text-align: center;
                            font-size: 16px;
                        " />
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <input type="text" id="fracNumerator" placeholder="เศษ" style="
                                width: 80px;
                                padding: 8px;
                                border: 2px solid #d1d5db;
                                border-radius: 6px;
                                text-align: center;
                                font-size: 16px;
                            " />
                            <div style="height: 2px; background: #374151; margin: 0 5px;"></div>
                            <input type="text" id="fracDenominator" placeholder="ส่วน" style="
                                width: 80px;
                                padding: 8px;
                                border: 2px solid #d1d5db;
                                border-radius: 6px;
                                text-align: center;
                                font-size: 16px;
                            " />
                        </div>
                    </div>
                `;
            } else {
                formHTML = `
                    <h3 style="margin-bottom: 20px; color: #374151; font-size: 18px; font-weight: 600;">เศษส่วนธรรมดา (Simple Fraction)</h3>
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px; justify-content: center;">
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <input type="text" id="fracNumerator" placeholder="เศษ" style="
                                width: 100px;
                                padding: 10px;
                                border: 2px solid #d1d5db;
                                border-radius: 6px;
                                text-align: center;
                                font-size: 16px;
                            " />
                            <div style="height: 2px; background: #374151; margin: 0 10px;"></div>
                            <input type="text" id="fracDenominator" placeholder="ส่วน" style="
                                width: 100px;
                                padding: 10px;
                                border: 2px solid #d1d5db;
                                border-radius: 6px;
                                text-align: center;
                                font-size: 16px;
                            " />
                        </div>
                    </div>
                `;
            }

            formHTML += `
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button id="cancelFraction" style="
                        padding: 10px 20px;
                        background: #6b7280;
                        color: white;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        font-size: 14px;
                    ">ยกเลิก</button>
                    <button id="insertFractionBtn" style="
                        padding: 10px 20px;
                        background: #f97316;
                        color: white;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        font-size: 14px;
                    ">เพิ่มเศษส่วน</button>
                </div>
            `;

            fractionContainer.innerHTML = formHTML;

            // Create overlay
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            `;

            document.body.appendChild(overlay);
            document.body.appendChild(fractionContainer);

            // Event listeners
            document.getElementById('cancelFraction').addEventListener('click', () => {
                document.body.removeChild(overlay);
                document.body.removeChild(fractionContainer);
            });

            document.getElementById('insertFractionBtn').addEventListener('click', () => {
                const numerator = document.getElementById('fracNumerator').value.trim();
                const denominator = document.getElementById('fracDenominator').value.trim();

                if (!numerator || !denominator) {
                    alert('กรุณากรอกข้อมูลเศษและส่วน');
                    return;
                }

                let output = '';
                if (fractionType === 'mixed') {
                    const whole = document.getElementById('fracWhole').value.trim();
                    if (whole) {
                        // คำนวณจำนวนคละให้เป็นเศษส่วนธรรมดา
                        // สูตร: (จำนวนเต็ม × ส่วน + เศษ) / ส่วน
                        const wholeNum = parseInt(whole);
                        const numeratorNum = parseInt(numerator);
                        const denominatorNum = parseInt(denominator);

                        if (isNaN(wholeNum) || isNaN(numeratorNum) || isNaN(denominatorNum)) {
                            alert('กรุณากรอกตัวเลขที่ถูกต้อง');
                            return;
                        }

                        const newNumerator = (wholeNum * denominatorNum) + numeratorNum;
                        output = `MixFrac(${newNumerator},${denominatorNum})`;
                    } else {
                        output = `Frac(${numerator},${denominator})`;
                    }
                } else {
                    output = `Frac(${numerator},${denominator})`;
                }

                // Insert into math input
                const cursorPosition = mathInput.selectionStart;
                const currentValue = mathInput.value;
                const newValue = currentValue.slice(0, cursorPosition) + output + currentValue.slice(
                    cursorPosition);

                mathInput.value = newValue;
                mathInput.focus();
                mathInput.setSelectionRange(cursorPosition + output.length, cursorPosition + output.length);

                // Clean up
                document.body.removeChild(overlay);
                document.body.removeChild(fractionContainer);

                updatePreview();
            });

            // Focus on first input
            setTimeout(() => {
                if (fractionType === 'mixed') {
                    document.getElementById('fracWhole').focus();
                } else {
                    document.getElementById('fracNumerator').focus();
                }
            }, 100);
        }

        // Initialize the app
        init();
    </script>

    <!-- MathJax Configuration and CDN -->
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [
                    ['\\(', '\\)']
                ],
                displayMath: [
                    ['\\[', '\\]']
                ],
                processEscapes: true,
                processEnvironments: true
            },
            options: {
                skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre']
            },
            startup: {
                ready: function() {
                    console.log('MathJax is loaded and ready.');
                    MathJax.startup.defaultReady();
                }
            }
        };
    </script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
@endpush
