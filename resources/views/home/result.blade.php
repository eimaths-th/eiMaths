@extends('layouts.qiuz_layout')

@section('title', 'Home Page')

@push('css')
    <style>
        .img-container {
            display: flex;
            justify-content: left;
            /* Center horizontally */
            align-items: left;
            /* Center vertically */
            width: 100%;
            /* Ensure the container takes up available width */
            height: auto;
            /* Ensure the container adjusts to the content's height */
        }

        .img-custom {
            max-height: 75px;
            max-width: 100%;
            height: auto;
            width: auto;
            display: inline-block;
            /* Ensures the image stays on the same line */
            vertical-align: middle;
            /* Aligns the image vertically in the middle of the text */
        }

        li span {
            display: inline-block;
            /* Ensures the text stays on the same line */
            vertical-align: middle;
            /* Aligns the text vertically in the middle of the image */
        }
    </style>
    <script type="text/javascript" async
        src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.7/MathJax.js?config=TeX-MML-AM_CHTML"></script>
@endpush
@push('script')
@endpush
@section('content')
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h6 class="text-xl font-bold">Test Results</h6>

                    <table class="mt-4 table w-full table-view">
                        <tbody class="bg-white">
                            {{-- @if (auth()->user()?->is_admin) --}}
                            <tr class="w-28">
                            <tr class="w-28">
                                <th
                                    class="border border-solid bg-gray-100 px-6 py-3 text-left text-sm font-semibold uppercase text-slate-600">
                                    Quiz
                                </th>
                                <td class="border border-solid px-6 py-3">
                                    {{ $test->quiz->title }}
                                    {{-- @endif --}}
                                </td>
                            </tr>
                            <th
                                class="border border-solid bg-gray-100 px-6 py-3 text-left text-sm font-semibold uppercase text-slate-600">
                                User</th>
                            <td class="border border-solid px-6 py-3">
                                @php
                                    if ($test->type_user === 'user' && $test->user) {
                                        $testUser = $test->user;
                                    } elseif ($test->type_user === 'student' && $test->student) {
                                        $testUser = $test->student;
                                    } else {
                                        $testUser = null;
                                    }
                                @endphp
                                @if ($testUser)
                                    {{ $testUser->nickname ?? $testUser->name }}
                                    ({{ $testUser->email ?? $testUser->name }})
                                @else
                                    User not found
                                @endif
                            </td>
                            </tr>
                            {{-- @endif --}}
                            <tr class="w-28">
                                <th
                                    class="border border-solid bg-gray-100 px-6 py-3 text-left text-sm font-semibold uppercase text-slate-600">
                                    Date</th>
                                <td class="border border-solid px-6 py-3">
                                    {{ $test->created_at->format('D d/m/Y, h:i A') ?? '' }}
                                    {{-- {{ $test->created_at ?? '' }} --}}
                                </td>
                            </tr>
                            <tr class="w-28">
                                <th
                                    class="border border-solid bg-gray-100 px-6 py-3 text-left text-sm font-semibold uppercase text-slate-600">
                                    Result</th>
                                <td class="border border-solid px-6 py-3">
                                    {{ $test->result }} / {{ $questions_count }}
                                    {{-- @if ($test->time_spent) --}}
                                    @php
                                        $hour = intdiv($test->time_spent, 3600);
                                        $minutes = intdiv($test->time_spent, 60);
                                        $seconds = $test->time_spent % 60;
                                    @endphp
                                    ( time:
                                    {{ sprintf('%02d', $hour) }}:{{ sprintf('%02d', $minutes) }}:{{ sprintf('%02d', $seconds) }}
                                    )
                                    {{-- @endif --}}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <br>
        {{-- @isset($leaderboard)
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-12">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h6 class="text-xl font-bold">Leaderboard</h6>

                        <table class="table mt-4 w-full table-view">
                            <thead>
                                <th class="text-left">Rank</th>
                                <th class="text-left">Username</th>
                                <th class="text-left">Results</th>
                            </thead>
                            <tbody class="bg-white">
                                @foreach ($leaderboard as $test)
                                    <tr @class([
                                        'bg-gray-100' => auth()->user()->name == $test->user->name,
                                    ])>
                                        <td class="w-9">{{ $loop->iteration }}</td>
                                        <td class="w-1/2">{{ $test->user->name }}</td>
                                        <td>{{ $test->result }} / {{ $questions_count }} (time: {{ $test->time_spent }} sec)
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endisset --}}
        <br>

        {{-- @php
            dd($results);
        @endphp --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @foreach ($results as $result)
                        <table class="table table-view w-full my-4 bg-white">
                            <tbody class="bg-white">
                                <tr class="bg-gray-100">
                                    <td class="w-1/2">Question #{{ $loop->iteration }}</td>

                                    @if ($result->question->type === 'fraction')
                                        {{-- <td>{!! nl2br($result->question->text) !!}</td> --}}
                                        <td>{{ replaceMixedFractions($result->question->text) }}</td>
                                    @else
                                        <td>{!! nl2br($result->question->text) !!}</td>
                                    @endif
                                </tr>
                                <tr>
                                    @php
                                        $title = '';
                                        $type = $result->question->type;
                                        if ($type === 'options' || $type === 'image') {
                                            # code...
                                            $title = 'Options';
                                        } elseif ($type === 'written') {
                                            # code...
                                            $title = 'Written';
                                        } elseif ($type === 'fraction') {
                                            # code...
                                            $title = 'Fraction';
                                        } else {
                                            # code...
                                            $title = 'Title is not found';
                                        }
                                    @endphp
                                    <td>{{ $title }}</td>

                                    <td>
                                        @if ($type === 'options')
                                            @foreach ($result->question->options as $option)
                                                <li @class([
                                                    'underline' => $result->option_id == $option->id,
                                                    'font-bold text-orange-500' => $option->correct == 1,
                                                    'mt-5',
                                                ])>
                                                    {{ replaceMixedFractions($option->text) }}
                                                    @if ($option->correct == 1)
                                                        <span class="italic">(correct answer)</span>
                                                    @endif
                                                    @if ($result->option_id == $option->id)
                                                        <span class="italic">(your answer)</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                            @if (is_null($result->option_id))
                                                <span class="font-bold italic">Question unanswered.</span>
                                            @endif
                                        @elseif ($type === 'image')
                                            @foreach ($result->question->images as $image)
                                                <li @class([
                                                    'underline' => $result->image_id == $image->id,
                                                    'font-bold text-orange-500' => $image->correct == 1,
                                                    'mt-5',
                                                ])>
                                                    @if ($image->img_name)
                                                        <img class="img-custom"
                                                            src="{{ asset('images_options/' . $image->img_name) }}"
                                                            alt="Image option">
                                                    @endif

                                                    @if ($image->correct == 1)
                                                        <span class="italic">(correct answer)</span>
                                                    @endif

                                                    @if ($result->image_id == $image->id)
                                                        <span class="italic">(your answer)</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                            @if (is_null($result->image_id))
                                                <span class="font-bold italic">Question unanswered.</span>
                                            @endif
                                        @elseif ($type === 'written')
                                            <li @class([
                                                'underline' => $result->correct != 1,
                                                'font-bold text-orange-500' => $result->correct == 1,
                                                'mt-5',
                                            ])>
                                                {{ $result->written_answer }} (your answer)
                                            </li>
                                            <li class="font-bold text-orange-500">
                                                {{ $result->question->written_answer }} (correct answer)
                                            </li>
                                        @elseif ($type === 'fraction')
                                            @if ($result->question->fractions->first()->type === 'options')
                                                @foreach ($result->question->fractions as $fractions)
                                                    {{-- @php
                                                        dd($fractions->answer_type);
                                                    @endphp --}}
                                                    <li @class([
                                                        'underline' => $result->fraction == $fractions->id,
                                                        'font-bold text-orange-500' => $fractions->correct == 1,
                                                        'mt-5',
                                                    ])>
                                                        @if ($fractions->answer_type == 'mixed')
                                                            {{-- {{toFraction($fraction->numerator, $fraction->denominator) }} --}}
                                                            {{ toMixedFraction(null, $fractions->numerator, $fractions->denominator) }}
                                                        @else
                                                            {{ toFraction($fractions->numerator, $fractions->denominator) }}
                                                        @endif
                                                        {{-- {{ $fractions->numerator }} / {{ $fractions->denominator }} --}}
                                                        @if ($fractions->correct == 1)
                                                            <span class="italic">(correct answer)</span>
                                                        @endif
                                                        @if ($result->fraction == $fractions->id)
                                                            <span class="italic">(your answer)</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            @else
                                                @foreach ($result->question->fractions as $fraction)
                                                    @php
                                                        // dd($fraction->answer_type);
                                                    @endphp
                                                    <li @class([
                                                        'underline' => $result->correct != 1,
                                                        'font-bold text-orange-500' => $result->correct == 1,
                                                        'mt-5',
                                                    ])>
                                                        {{ replaceMixedFractions($result->fraction) }} (your answer)
                                                    </li>
                                                    <li class="font-bold text-orange-500 mt-5">
                                                        @if ($fraction->answer_type == 'mixed')
                                                            {{-- {{toFraction($fraction->numerator, $fraction->denominator) }} --}}
                                                            {{ toMixedFraction(null, $fraction->numerator, $fraction->denominator) }}
                                                        @else
                                                            {{ toFraction($fraction->numerator, $fraction->denominator) }}
                                                        @endif
                                                        (correct answer)
                                                        {{-- {{ $fraction->numerator }} / {{ $fraction->denominator }} (correct
                                                        answer) --}}
                                                    </li>
                                                @endforeach
                                            @endif

                                            @if (is_null($result->fraction))
                                                <span class="font-bold italic">Question unanswered.</span>
                                            @endif
                                        @else
                                            <span class="font-bold italic">Question unanswered.</span>
                                        @endif

                                    </td>
                                </tr>
                                @if ($result->question->answer_explanation || $result->question->more_info_link)
                                    <tr>
                                        <td>Answer Explanation</td>
                                        <td>
                                            {{ $result->question->answer_explanation }}
                                        </td>
                                    </tr>
                                    @if ($result->question->more_info_link)
                                        <tr>
                                            <td>
                                                Read more:
                                            </td>
                                            <td>
                                                <div class="mt-4">
                                                    <a href="{{ $result->question->more_info_link }}"
                                                        class="hover:underline" target="_blank">
                                                        {{ $result->question->more_info_link }}
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                            </tbody>
                        </table>

                        @if (!$loop->last)
                            <hr class="my-4 md:min-w-full">
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        // function insertFrac() {
        //     let question_text = document.querySelector('#text');
        //     if (question_text) {
        //         question_text.value += ' Frac(เศษ,ส่วน) ';
        //     }
        // }

        // function insertMixed() {
        //     let question_text = document.querySelector('#text');
        //     if (question_text) {
        //         question_text.value += ' MixFrac(เศษ,ส่วน) ';
        //     }
        // }
        window.addEventListener('popstate', function(event) {
            // Redirect to the home page
            window.location.href =
                '/quiz/{{ $test->quiz->slug }}'; // Change '/' to the URL of your home page if it's different
        });

        // Push a new state to make sure the popstate event is triggered
        history.pushState(null, null, window.location.href);
    </script>
@endpush
