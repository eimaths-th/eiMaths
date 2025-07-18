@extends('layouts.qiuz_layout')

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
    </style>
    <script type="text/javascript" async
        src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.7/MathJax.js?config=TeX-MML-AM_CHTML"></script>
@endpush
@push('script')
@endpush
@section('content')
    <div style="min-height: 100vh; background-color: #f9fafb;">
        <!-- Top Progress Bar -->
        <div style="background-color: white; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); border-bottom: 1px solid #e5e7eb;">
            <div style="max-width: 80rem; margin: 0 auto; padding: 1rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                    <h1 style="font-size: 1.125rem; font-weight: 600; color: #1f2937;">คณิตศาสตร์</h1>
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
                        {{ $questionsCount }} ข้อ</span>
                    <div
                        style="flex: 1; background-color: #e5e7eb; border-radius: 9999px; height: 0.5rem; margin-left: 0.75rem;">
                        <div
                            style="background-color: #f97316; height: 0.5rem; border-radius: 9999px; transition: all 0.3s; width: {{ (($currentQuestionIndex + 1) / $questionsCount) * 100 }}%;">
                        </div>
                    </div>
                    <span
                        style="font-size: 0.875rem; color: #4b5563;">{{ round((($currentQuestionIndex + 1) / $questionsCount) * 100) }}%
                        เสร็จสิ้น</span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div style="max-width: 80rem; margin: 0 auto; padding: 1.5rem;">
            <div style="display: flex; gap: 1.5rem;">
                <!-- Left Sidebar - Question Navigation -->
                <div
                    style="width: 16rem; background-color: white; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); padding: 1rem;">
                    <h3 style="font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 1rem;">ข้อคำถาม</h3>
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
                            <span>ตอบแล้ว</span>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <div
                                style="width: 0.75rem; height: 0.75rem; background-color: #f97316; border-radius: 0.25rem; margin-right: 0.5rem;">
                            </div>
                            <span>กำลังตอบ</span>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <div
                                style="width: 0.75rem; height: 0.75rem; background-color: #f3f4f6; border-radius: 0.25rem; margin-right: 0.5rem;">
                            </div>
                            <span>ยังไม่ตอบ</span>
                        </div>
                    </div>

                    <button
                        style="width: 100%; margin-top: 1.5rem; background-color: #f97316; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 500; border: none; cursor: pointer; transition: background-color 0.3s;"
                        onmouseover="this.style.backgroundColor='#ea580c'" onmouseout="this.style.backgroundColor='#f97316'"
                        onclick="document.getElementById('quiz-form').submit()">
                        ส่งคำตอบ
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
                                        <span style="font-size: 0.875rem; font-weight: 500; color: #4b5563;">ข้อที่
                                            {{ $currentQuestionIndex + 1 }}</span>
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
                                            <input type="text" name="answerOfQuestion[{{ $currentQuestionIndex }}]"
                                                style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; outline: none; transition: all 0.3s;"
                                                onfocus="this.style.borderColor='#f97316'; this.style.boxShadow='0 0 0 2px rgba(249, 115, 22, 0.2)'"
                                                onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"
                                                placeholder="กรอกคำตอบของคุณ" autocomplete="off"
                                                value="{{ $getAnserSessionIndexWritten }}">
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
                                                                $option->whole ?? null,
                                                                $option->numerator,
                                                                $option->denominator,
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
                                                ถัดไป
                                            </button>
                                        @else
                                            <button type="submit"
                                                style="background-color: #34C759; color: white; font-weight: 500; padding: 0.75rem 1.5rem; border-radius: 0.5rem; transition: all 0.3s; cursor: pointer;"
                                                class="hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                                ส่งคำตอบ
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

@endsection
@push('scripts')
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
        document.addEventListener('DOMContentLoaded', function() {
            let secondsLeft = 0;
            const timeElapsedSpan = document.getElementById('time-elapsed');
            const quizForm = document.getElementById('quiz-form');

            setInterval(() => {
                secondsLeft++;
                let hours = Math.floor(secondsLeft / 3600).toString().padStart(2, '0');
                let minutes = Math.floor((secondsLeft % 3600) / 60).toString().padStart(2, '0');
                let seconds = (secondsLeft % 60).toString().padStart(2, '0');
                timeElapsedSpan.textContent = `${hours}:${minutes}:${seconds}`;
            }, 1000);

            quizForm.addEventListener('submit', () => {
                document.getElementById('startTimeInSeconds').value = Math.floor(Date.now() / 1000);
            });
        });

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

        // $(document).ready(function() {
        //     $('#nextQuestion').hide();
        //     $('#answer-container').hide();

        //     $('#showAnswer').click(function() {
        //         var userAnswer;
        //         var correctAnswer = $('#correctAnswer').val();
        //         var questionType = $('#currentQuestionType').val();

        //         if (questionType === 'written') {
        //             userAnswer = $(
        //                     'input[name="answerOfQuestion[{{ $currentQuestionIndex }}]"]')
        //                 .val();
        //             $('input[name="answerOfQuestion[{{ $currentQuestionIndex }}]"]').prop(
        //                 'disabled',
        //                 true); // Disable text input
        //         } else if (questionType === 'options' || questionType === 'image') {
        //             userAnswer = $(
        //                     'input[name="answerOfQuestion[{{ $currentQuestionIndex }}]"]:checked'
        //                 )
        //                 .val();
        //             $('input[name="answerOfQuestion[{{ $currentQuestionIndex }}]"]').prop(
        //                 'disabled',
        //                 true); // Disable radio inputs
        //         }
        //         if (!userAnswer) {
        //             alert('Please select an answer.');
        //             return;
        //         }
        //         var data = {
        //             userAnswer: userAnswer,
        //             type: questionType,
        //             correctAnswer: correctAnswer,
        //             _token: '{{ csrf_token() }}'
        //         };

        //         var url = '{{ route('check.answer') }}';

        //         $.ajax({
        //             url: url,
        //             method: 'POST',
        //             data: data,
        //             headers: {
        //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
        //                     'content') // Include CSRF token
        //             },
        //             success: function(result) {
        //                 console.log(result);
        //                 if (result.checkCorrect == 1) {
        //                     $('#answer-container').css('background-color',
        //                         'lightgreen');
        //                 } else {
        //                     $('#answer-container').css('background-color',
        //                         'lightcoral');
        //                 }
        //                 $('#answer-container').html(result.result);
        //             },
        //         });

        //         // Show the "Next Question" button and hide the "Show Answer" button
        //         $('#nextQuestion').show();
        //         $('#answer-container').show();
        //         $('#showAnswer').hide();
        //     });

        // });
    </script>
@endpush
