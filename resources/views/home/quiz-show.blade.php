@extends('layouts.qiuz_layout')

@section('title', $quiz->title)

@push('CSS')
@endpush
@push('script')
@endpush
@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (!$quiz->public && !auth()->check())
                        <div class="text-white px-6 py-4 border-0 rounded relative mb-4 bg-red-700">
                            <span class="inline-block align-middle mr-8">
                                This test is available only for registered users. Please <a href="{{ route('login') }}"
                                    class="hover:underline">Log in</a> or <a href="{{ route('register') }}"
                                    class="hover:underline">Register</a>
                            </span>
                        </div>
                    @else
                        {{-- รอมาทำต่อ next Question  --}}
                        @php
                            $secondsPerQuestion = $startTimeInSeconds;
                        @endphp

                        <div id="quiz-container" data-seconds-left="0">
                            <div class="mb-2">
                                Time elapsed:
                                <span id="time-elapsed" class="font-bold">
                                    00:00:00
                                </span>
                                sec.
                            </div>

                            <span class="text-bold">Question {{ $currentQuestionIndex + 1 }} of
                                {{ $questionsCount }}:</span>
                            <h2 class="mb-4 text-2xl">{{ $currentQuestion->text }}</h2>

                            @if ($currentQuestion->code_snippet)
                                <pre class="mb-4 border-2 border-solid bg-gray-50 p-2">{{ $currentQuestion->code_snippet }}</pre>
                            @endif

                            {{-- <form id="quiz-form" action="{{ route('quiz.next', ['quiz' => $quiz, 'currentQuestionIndex' => $currentQuestionIndex]) }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="startTimeInSeconds" id="startTimeInSeconds"
                                    value="{{ $secondsPerQuestion }}">
                                <input type="hidden" name="currentQuestionIndex" value="{{ $currentQuestionIndex }}">
                                @if ($currentQuestion->type === 'written')
                                    <div><input type="text" name="answerOfWritten[{{ $currentQuestion->id }}]" class="form-control" placeholder="Enter answer" autocomplete="off">
                                    </div>
                                @elseif ($currentQuestion->type === 'options')
                                    @foreach ($currentQuestion->options as $option)
                                        <div>
                                            <label for="option-{{ $option->id }}">
                                                <input type="radio" id="option-{{ $option->id }}"
                                                    name="answerOfQuestion[{{ $currentQuestionIndex }}]"
                                                    value="{{ $option->id }}">
                                                {{ $option->text }}
                                            </label>
                                        </div>
                                    @endforeach
                                @else
                                @endif


                                <div class="mt-4">
                                    @if ($currentQuestionIndex < $questionsCount - 1)
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm focus:outline-none disabled:opacity-25 transition ease-in-out duration-150">
                                            Next question
                                        </button>
                                    @else
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150">
                                            Submit
                                        </button>
                                    @endif
                                </div>
                            </form> --}}
                            <form id="quiz-form"
                                action="{{ route('quiz.next', ['quiz' => $quiz, 'currentQuestionIndex' => $currentQuestionIndex]) }}"
                                method="POST">
                                @csrf
                                <input type="hidden" name="startTimeInSeconds" id="startTimeInSeconds"
                                    value="{{ $secondsPerQuestion }}">
                                <input type="hidden" name="currentQuestionIndex" value="{{ $currentQuestionIndex }}">
                                <input type="hidden" name="currentQuestionId" value="{{ $currentQuestion->id }}">
                                <input type="hidden" name="currentQuestionType" value="{{ $currentQuestion->type }}">

                                @if ($currentQuestion->type === 'written')
                                    <div>
                                        <input type="text" name="answerOfQuestion[{{ $currentQuestionIndex }}]"
                                            class="form-control" placeholder="Enter answer" autocomplete="off">
                                    </div>
                                @elseif ($currentQuestion->type === 'options')
                                    @foreach ($currentQuestion->options as $option)
                                        <div>
                                            <label for="option-{{ $option->id }}">
                                                <input type="radio" id="option-{{ $option->id }}"
                                                    name="answerOfQuestion[{{ $currentQuestionIndex }}]"
                                                    {{-- name="answerOfQuestion[{{ $currentQuestion->id }}]" --}}
                                                    value="{{ $option->id }}">
                                                {{ $option->text }}
                                            </label>
                                        </div>
                                    @endforeach
                                @endif

                                <div class="mt-4">
                                    @if ($currentQuestionIndex < $questionsCount - 1)
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm focus:outline-none disabled:opacity-25 transition ease-in-out duration-150">
                                            Next question
                                        </button>
                                    @else
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150">
                                            Submit
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
        document.addEventListener('DOMContentLoaded', function() {
            let secondsLeft = 0;
            const quizContainer = document.getElementById('quiz-container');
            const timeElapsedSpan = document.getElementById('time-elapsed');
            const quizForm = document.getElementById('quiz-form');
            const startTimeInSeconds = document.getElementById('startTimeInSeconds').value;

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
    </script>
@endpush
