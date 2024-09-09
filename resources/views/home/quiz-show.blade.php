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

        #answer-container {
            position: relative;
            text-align: left;

            padding: 10px;
            margin-top: 15px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            /* Subtle shadow */
            position: relative;
            border-radius: 8px;

        }

        /* .answer-container {
                position: relative;
                width: 100%;
                border: 2px solid orange;
                border-radius: 5px;
                padding: 15px;
                box-sizing: border-box;
            } */


        .image-answer-container {
            display: flex;
            /* Align children horizontally */
            justify-content: left;
            /* Center content horizontally */
            align-items: left;
        }

        .image-answer-item {
            max-width: 100%;
            max-height: 100%;
        }

        .image-answer-item img {
            display: block;
            /* Remove bottom margin/space */
            max-width: 100px;
            /* Make image responsive */
            height: auto;
            /* Maintain aspect ratio */
            border-radius: 4px;
            /* Rounded corners for the image */
        }

        .answer-wrapper {}


        .answer-label {
            text-align: left;
            position: absolute;
            top: -15px;
            left: 15px;
            background-color: #fff;
            padding: 0 10px;
            font-weight: bold;
            font-size: 14px;
            color: #333;
            /* border: 1px solid #ffbb00; */
            /* Matching border */
            border-radius: 5px;
            /* Rounded label */
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);

        }

        /* .answer-label {
                position: absolute;
                top: -15px;
                left: 15px;
                font-size: 14px;
                background-color: white;
                padding: 0 5px;
                color: #000000;
                font-weight: bold;
            } */

        .answer-box {
            font-size: 16px;
            color: #333;
            padding-left: 10px;
        }


        .text-answer {
            padding: 10px;
            font-size: 16px;
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
                            <h2 class="mb-4 text-2xl">{{ $currentQuestion->text ?? '' }}</h2>
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
                                <input type="hidden" name="currentQuestionId" id="currentQuestionId"
                                    value="{{ $currentQuestion->id }}">
                                <input type="hidden" id="currentQuestionType" name="currentQuestionType"
                                    value="{{ $currentQuestion->type }}">
                                <input type="hidden" id="correctAnswer" value="{{ $correctAnswer }}">
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
                                                <input type="hidden" id="optionAnswer" value="{{ $option->text }}">
                                            </label>
                                        </div>
                                    @endforeach
                                @elseif ($currentQuestion->type === 'image')
                                    <div class="image-container">
                                        @foreach ($currentQuestion->images as $image)
                                            <div class="image-item">
                                                <label for="img-option-{{ $image->id }}">
                                                    <input type="radio" id="img-option-{{ $image->id }}"
                                                        name="answerOfQuestion[{{ $currentQuestionIndex }}]"
                                                        value="{{ $image->id }}">
                                                    <img src="{{ asset('images_options/' . $image->img_name) }}"
                                                        id="img-option-{{ $image->id }}" alt="Image option">
                                                    <input type="hidden" id="imgOptionAnswer"
                                                        value="{{ $image->img_name }}">

                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div id="answer-container">

                                </div>
                                <div class="mt-4">
                                    @if ($currentQuestionIndex < $questionsCount - 1)
                                        {{-- <button id="showAnswer" type="button"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm focus:outline-none disabled:opacity-25 transition ease-in-out duration-150">
                                            Confirm
                                        </button> --}}
                                        <button type="submit" id="nextQuestion"
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
                console.log('User Answer:', userAnswer);
                console.log('Correct Answer:', correctAnswer);
                console.log('Question Type:', questionType);

                var url = '{{ route('check.answer') }}';

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: data,
                    success: function(result) {
                        console.log(result);
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
