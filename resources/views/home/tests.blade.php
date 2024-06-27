@extends('layouts.qiuz_layout')

@section('title', 'Tests')

@push('CSS')
@endpush
@push('script')
@endpush
@section('content')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- <select class="p-3 w-full text-sm leading-5 rounded border-0 shadow text-slate-600" wire:model="quiz_id">
                        <option value="0">All quizzes</option>
                        @foreach ($quizzes as $quiz)
                            <option value="{{ $quiz->id }}">{{ $quiz->title }}</option>
                        @endforeach
                    </select> --}}
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
                                <th class="w-16 bg-gray-50 px-6 py-3 text-left">
                                    <span
                                        class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">ID</span>
                                </th>
                                <th class="bg-gray-50 px-6 py-3 text-left">
                                    <span
                                        class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">User</span>
                                </th>
                                <th class="bg-gray-50 px-6 py-3 text-center">
                                    <span
                                        class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Quiz</span>
                                </th>
                                <th class="bg-gray-50 px-6 py-3 text-left">
                                    <span
                                        class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Result</span>
                                </th>
                                <th class="bg-gray-50 px-6 py-3 text-left">
                                    <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">IP
                                        Address</span>
                                </th>
                                <th class="bg-gray-50 px-6 py-3 text-center">
                                    <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Date</span>
                                </th>
                                <th class="bg-gray-50 px-6 py-3 text-left">
                                    <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Time
                                        Spent</span>
                                </th>
                                <th class="w-40 bg-gray-50 px-6 py-3 text-left">
                                </th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                            @forelse($tests as $test)
                                {{-- <tr class="bg-gray-100"> --}}
                                <tr class="bg-white">
                                    <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                        {{ $test->id }}
                                    </td>
                                    <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                        {{ $test->user->name ?? 'Guest' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm leading-5 text-center text-gray-900 whitespace-no-wrap">
                                        {{ $test->quiz->title }}
                                    </td>
                                    <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                        {{ $test->result . '/' . $test->questions_count }}
                                    </td>
                                    <td class="px-6 py-4 text-sm leading-5  text-gray-900 whitespace-no-wrap">
                                        {{ $test->ip_address }}
                                    </td>
                                    <td class="px-6 py-4 text-sm leading-5 text-center text-gray-900 whitespace-no-wrap">
                                        {{ $test->created_at->format('D d/m/Y, h:i A') ?? '' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm leading-5 text-gray-900  whitespace-no-wrap text-nowrap">

                                        @php
                                            $hour = intdiv($test->time_spent, 3600);
                                            $minutes = intdiv($test->time_spent % 3600, 60);
                                            $seconds = $test->time_spent % 60;
                                        @endphp
                                        time: {{ sprintf('%02d', $hour) }}:{{ sprintf('%02d', $minutes) }}:{{ sprintf('%02d', $seconds) }}
                                    </td>
                                    <td>
                                        <a href="{{ route('results.show', $test) }}"
                                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8"
                                        class="px-6 py-4 text-center leading-5 text-gray-900 whitespace-no-wrap">
                                        No tests were found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{-- {{ $tests->links() }} --}}
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
@endsection
