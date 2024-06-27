@extends('layouts.qiuz_layout')

@section('title', 'Leader Board')

@push('CSS')
@endpush
@push('script')
@endpush
@section('content')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form id="quiz-form" action="{{ route('submit.leaderboard') }}" method="POST">
                        @csrf
                        @php
                            $route = request()->route()->getName();
                        @endphp
                        <select name="quiz_slug" class="p-3 w-full text-sm leading-5 rounded border-0 shadow text-slate-600"
                            onchange="document.getElementById('quiz-form').submit();">
                            <option value="0" {{ $quiz_slug === '0' ? 'selected' : '' }}>All quizzes</option>
                            @foreach ($quizzes as $quiz)
                                <option value="{{ $quiz->slug }}" {{ $quiz_slug === $quiz->slug ? 'selected' : '' }}>
                                    {{ $quiz->title }}</option>
                            @endforeach

                        </select>
                        <input type="hidden" name="route" value="{{ $route }}">

                    </form>

                    <table class="table mt-4 w-full table-view">
                        <thead>
                            <tr>
                                <th class="bg-gray-50 px-6 py-3 text-left "></th>
                                <th class="bg-gray-50 px-6 py-3 text-left ">
                                    <span
                                        class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Username</span>
                                </th>
                                <th class="bg-gray-50 px-6 py-3 text-center">
                                    <span
                                        class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Quiz</span>
                                </th>
                                <th class="bg-gray-50 px-6 py-3 text-center">
                                    <span
                                        class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Date</span>
                                </th>
                                <th class="bg-gray-50 px-6 py-3 text-left">
                                    <span
                                        class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Correct
                                        answers</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tests as $test)

                                <tr class="bg-gray-100">
                                    <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">{{ $test->user->name }}</td>
                                    <td class="px-6 py-4 text-sm leading-5 text-center text-gray-900 whitespace-no-wrap text-nowrap">{{ $test->quiz->title }}</td>
                                    <td class="px-6 py-4 text-sm leading-5 text-center text-gray-900 whitespace-no-wrap">{{ $test->created_at->format('D d/m/Y, h:i A') ?? '' }}</td>
                                    <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap text-nowrap">
                                        {{ $test->result }} / {{ $test->quiz->questions_count }}
                                        @php
                                            $hour = intdiv($test->time_spent, 3600);
                                            $minutes = intdiv($test->time_spent % 3600, 60);
                                            $seconds = $test->time_spent % 60;
                                        @endphp
                                        (time: {{ sprintf('%02d', $hour) }}:{{ sprintf('%02d', $minutes) }}:{{ sprintf('%02d', $seconds) }})
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                        No results.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>

            </div>
        </div>
    </div>


@endsection
@section('script')
@endsection
