@extends('layouts.qiuz_layout')

@section('title', $quiz->title)

@push('css')
    <style>
        /* Container for all images */
        .image-container {
            display: flex;
            /* Use flexbox to handle layout */
            flex-wrap: wrap;
            /* Wrap items to new rows as needed */
            gap: 10px;
            /* Space between items */
        }

        /* Container for each image item */
        .image-item {
            flex: 1 1 calc(50% - 10px);
            /* Take up half of the container width minus gap */
            box-sizing: border-box;
            /* Include padding and borders in width */
            display: flex;
            /* align-items: center; */
            /* Center content vertically */
            /* justify-content: center; */
            /* Center content horizontally */
            padding: 5px;
            /* Optional padding around images */
            text-align: center;
            /* Center-align text and content */
        }

        /* Style for images */
        .image-item img {
            width: 100%;
            /* Image will take up the full width of its container */
            height: auto;
            /* Maintain aspect ratio */
            max-height: 75px;
            /* Optional: limit the height to prevent overly large images */
            object-fit: cover;
            /* Ensure image covers the container without stretching */
        }

        .img-container {
            display: flex;
            justify-content: center;
            /* Center horizontally */
            align-items: center;
            /* Center vertically */
            height: 100%;
            /* Ensure the container takes up available space */
        }

        .img-custom {
            max-height: 300px;
            max-width: 100%;
            /* Ensure the image scales correctly */
            height: auto;
            width: auto;
        }

        label {
            display: flex;
            align-items: center;
        }

        input[type="radio"] {
            margin-right: 10px;
            /* Space between the radio button and the image */
        }
    </style>
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
                            <h2 class="mb-4 text-2xl">{{ $currentQuestion->text ?? ''}}</h2>
                            @if ($currentQuestion->img_name)
                                <div class="img-container mb-4">
                                    <img class="img-custom" src="{{ asset('img_questions/' . $currentQuestion->img_name) }}"
                                        alt="Image option">
                                </div>
                            @endif
                            @if ($currentQuestion->code_snippet)
                                <pre class="mb-4 border-2 border-solid bg-gray-50 p-2">{{ $currentQuestion->code_snippet }}</pre>
                            @endif

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
                                                    {{-- name="answerOfQuestion[{{ $currentQuestion->id }}]" --}} value="{{ $option->id }}">
                                                {{ $option->text }}
                                            </label>
                                        </div>
                                    @endforeach
                                @elseif ($currentQuestion->type === 'image')
                                    <div class="image-container">
                                        @foreach ($currentQuestion->images as $image)
                                            <div class="image-item">
                                                <label for="option-{{ $image->id }}">
                                                    <input type="radio" id="option-{{ $image->id }}"
                                                        name="answerOfQuestion[{{ $currentQuestionIndex }}]"
                                                        value="{{ $image->id }}">
                                                    <img src="{{ asset('images_options/' . $image->img_name) }}" alt="Image option">
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
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
