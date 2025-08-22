@extends('layouts.quiz_layout')

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
                        @php
                            $secondsPerQuestion = 0;
                        @endphp
                        <div id="quiz-app" data-seconds-per-question="{{ $secondsPerQuestion }}">

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

                            <form id="quiz-form" method="POST" action="{{ route('quiz.submit') }}">
                                {{-- <form id="quiz-form" method="POST" action="#"> --}}
                                @csrf
                                @foreach ($currentQuestion->options as $option)
                                    <div>
                                        <label for="option.{{ $option->id }}">
                                            <input type="radio" id="option.{{ $option->id }}"
                                                name="answers[{{ $currentQuestionIndex }}]" value="{{ $option->id }}">
                                            {{ $option->text }}
                                        </label>
                                    </div>
                                @endforeach

                                @if ($currentQuestionIndex < $questionsCount - 1)
                                    <div class="mt-4">
                                        <button type="button" {{-- class="btn btn-secondary"  --}}
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm focus:outline-none disabled:opacity-25 transition ease-in-out duration-150"
                                            id="next-question-button">
                                            Next question
                                        </button>
                                    </div>
                                @else
                                    <div class="mt-4">
                                        <button type="submit" {{-- class="btn btn-primary" --}}
                                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150">Submit</button>
                                    </div>
                                @endif
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <script>
        let secondsLeft = {{ $secondsPerQuestion }};
        let timerElement = document.getElementById('timer');

        function startTimer() {
            setInterval(() => {
                secondsLeft++;
                updateTimerDisplay();
            }, 1000);
        }

        function updateTimerDisplay() {
            let hours = Math.floor(secondsLeft / 3600);
            let minutes = Math.floor(secondsLeft % 3600 / 60);
            let seconds = secondsLeft % 60;

            timerElement.textContent =
                `${hours < 10 ? '0' : ''}${hours}:${minutes < 10 ? '0' : ''}${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        }

        function resetTimerAndNextQuestion() {
            secondsLeft = {{ $secondsPerQuestion }};
            document.getElementById('startTimeInSeconds').value = Math.floor(Date.now() / 1000); // Update start time
            updateTimerDisplay();
        }

        document.addEventListener('DOMContentLoaded', () => {
            startTimer();
        });
    </script>
@endpush
