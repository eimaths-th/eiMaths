@extends('layouts.qiuz_layout')

@section('title', 'Home Page')

@push('CSS')
@endpush
@push('script')
@endpush
@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-orange-500">
                <div class="p-6 text-gray-900">
                    <h6 class="text-xl font-bold mb-5">Public quizzes</h6>
                    <div class="flex flex-wrap">
                        @switch($levelCheck)
                            @case(1)
                                @forelse($public_quizzes_term as $term => $quiz_term)
                                    <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                                        <a href="{{ route('home.term', [$level, $term]) }}">
                                            <div
                                                class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0 border-2 border-orange-500">
                                                <div class="flex-auto p-4">
                                                    Term {{ $term }}
                                                    <p class="text-sm">Quizzes: <span>{{ $quiz_term->count() }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @empty
                                    <div class="mt-2">No public quizzes found.</div>
                                @endforelse
                            @break

                            @case(2)
                                @forelse($public_quizzes_term as $quiz)
                                    <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                                        <a href="{{ route('quiz.show', $quiz->slug) }}">
                                            <div
                                                class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0 border-2 border-orange-500">
                                                <div class="flex-auto p-4">
                                                    {{ $quiz->title }}
                                                    {{-- <p class="text-sm">quizzes count: <span>{{ $quiz_term->count() }}</span> --}}
                                                    <p class="text-sm">Questions: <span>{{ $quiz->questions_count }}</span></p>

                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @empty
                                    <div class="mt-2">No public quizzes found.</div>
                                @endforelse
                            @break

                            @default
                                @forelse($public_quizzes_level as $level => $quiz_level)
                                    <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                                        <a href="{{ route('home.level', $level) }}">
                                            <div
                                                class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0 border-2 border-orange-500">
                                                <div class="flex-auto p-4">
                                                    <p><span class="text-lg font-bold">{{ $level }}</span></p>
                                                    <p class="text-base">Quizzes: <span>{{ $quiz_level->count() }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @empty
                                    <div class="mt-2">No public quizzes found.</div>
                                @endforelse
                            @endswitch

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-orange-500">
                    <div class="p-6 text-gray-900">
                        <h6 class="text-xl font-bold mb-5">Quizzes for Registered Users</h6>
                        <div class="flex flex-wrap">
                            @switch($levelCheck)
                                @case(1)
                                    @forelse($registered_only_quizzes as $term => $quiz_term)
                                        <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                                            <a href="{{ route('home.level', $term) }}">
                                                <div
                                                    class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0 border-2 border-orange-500">
                                                    <div class="flex-auto p-4">
                                                        Term {{ $term }}
                                                        <p class="text-sm">quizzes count: <span>{{ $quiz_term->count() }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="mt-2">No public quizzes found.</div>
                                    @endforelse
                                @break

                                @case(2)
                                    @forelse($registered_only_quizzes as  $quiz)
                                        <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                                            <a href="{{ route('quiz.show', $quiz->slug) }}">
                                                <div
                                                    class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0 border-2 border-orange-500">
                                                    <div class="flex-auto p-4">
                                                        {{ $quiz->title }}
                                                        {{-- <p class="text-sm">quizzes count: <span>{{ $quiz_term->count() }}</span> --}}
                                                        <p class="text-sm">Questions: <span>{{ $quiz->questions_count }}</span></p>

                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="mt-2">No public quizzes found.</div>
                                    @endforelse
                                @break

                                @default
                                    @forelse($registered_only_quizzes as $level => $quiz_level)
                                        <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                                            <a href="{{ route('home.level', $level) }}">
                                                <div
                                                    class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0 border-2 border-orange-500">
                                                    <div class="flex-auto p-4">
                                                        <p><span class="text-lg font-bold">{{ $level }}</span></p>
                                                        <p class="text-base">Quizzes: <span>{{ $quiz_level->count() }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="mt-2">No public quizzes found.</div>
                                    @endforelse
                                @endswitch

                            </div>
                        </div>
                    </div>
                </div>
            </div>


        @endsection
        @push('script')
        @endpush
        {{--
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h6 class="text-xl font-bold">Public quizzes</h6>
                <div class="flex flex-wrap">
                    @forelse($public_quizzes as $quiz)
                        <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                            <div
                                class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0">
                                <div class="flex-auto p-4">
                                    <a href="{{ route('quiz.show', $quiz->slug) }}">{{ $quiz->title }}</a>
                                    <p class="text-sm">Questions: <span>{{ $quiz->questions_count }}</span></p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="mt-2">No public quizzes found.</div>
                    @endforelse
                </div>
            </div>
        </div>
        </div>
        </div>
        --}}
        {{-- @forelse($public_quizzes_level as $level => $quiz_level)
                    <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                        <div class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0">
                            <div class="flex-auto p-4">
                                <a href="#">{{ $level }}</a>
                                <p class="text-sm">quizzes count: <span>{{ $quiz_level->count() }}</span></p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="mt-2">No public quizzes found.</div>
                @endforelse --}}
