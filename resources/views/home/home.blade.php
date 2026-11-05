@extends('layouts.quiz_layout', ['quiz_portal_page' => true])
@section('title', __('quiz-management.home.page_title'))

@section('content')
    <header class="portal-header">
        <h1>{{ $header ?? __('quiz-management.home.title') }}</h1>
        <p>{{ __('quiz-management.home.subtitle') }}</p>
    </header>
    <section class="portal-home-section" aria-labelledby="student-quizzes-heading">
        <h2 id="student-quizzes-heading">{{ __('quiz-management.home.student_quizzes') }}</h2>
        <div class="portal-selection-grid">
            @switch($levelCheck)
                @case(1)
                    @forelse($public_quizzes_term as $term => $quiz_term)
                        <a class="portal-selection-card" href="{{ route('home.term', [$level, $term]) }}">
                            <span class="portal-tag">{{ $term }}</span>
                            <h3>{{ __('quiz-management.home.term', ['term' => $term]) }}</h3>
                            <p>{{ __('quiz-management.home.term_th', ['term' => $term]) }}</p>
                            <strong>{{ __('quiz-management.home.levels_available', ['count' => $quiz_term->groupBy('section')->count()]) }}</strong>
                        </a>
                    @empty
                        @include('home.portal.empty-home')
                    @endforelse
                    @break
                @case(2)
                    @forelse($public_quizzes_term as $section => $quiz_section)
                        <a class="portal-selection-card" href="{{ route('home.section', [$level, $term, $section]) }}">
                            <span class="portal-tag">L{{ $section }}</span>
                            <h3>{{ __('quiz-management.home.level', ['level' => $section]) }}</h3>
                            <p>{{ __('quiz-management.home.level_th', ['level' => $section]) }}</p>
                            <strong>{{ __('quiz-management.home.quizzes_available', ['count' => $quiz_section->count()]) }}</strong>
                        </a>
                    @empty
                        @include('home.portal.empty-home')
                    @endforelse
                    @break
                @case(3)
                    @forelse($public_quizzes_term as $quiz)
                        <a class="portal-selection-card" href="{{ route('quiz.show', $quiz->slug) }}">
                            <span class="portal-tag">{{ __('quiz-management.home.quiz_badge') }}</span>
                            <h3>{{ $quiz->title }}</h3>
                            <strong>{{ __('quiz-management.home.questions', ['count' => $quiz->questions_count]) }}</strong>
                        </a>
                    @empty
                        @include('home.portal.empty-home')
                    @endforelse
                    @break
                @default
                    @forelse($public_quizzes_level as $level => $quiz_level)
                        <a class="portal-selection-card" href="{{ route('home.level', $level) }}">
                            <span class="portal-tag">{{ substr($level, 0, 2) }}</span>
                            <h3>{{ $level }}</h3>
                            <p>{{ __('quiz-management.home.education_level') }}</p>
                            <strong>{{ __('quiz-management.home.terms_available', ['count' => $quiz_level->groupBy('term')->count()]) }}</strong>
                        </a>
                    @empty
                        @include('home.portal.empty-home')
                    @endforelse
            @endswitch
        </div>
    </section>

    {{-- <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-orange-500">
                    <div class="p-6 text-gray-900">
                        <h6 class="text-xl font-bold mb-5">Quizzes for Registered Users</h6>
                        <div class="flex flex-wrap">
                            @switch($levelCheck)
                                @case(1)
                                    @forelse($registered_only_quizzes as $term => $quiz_term)
                                        <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                                            <a href="{{ route('home.term', [$level, $term]) }}">
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
                                    @forelse($registered_only_quizzes as $section => $quiz_section)
                                        <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                                            <a href="{{ route('home.section', [$level, $term, $section]) }}">
                                                <div
                                                    class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0 border-2 border-orange-500">
                                                    <div class="flex-auto p-4">
                                                        Level-{{ $section }}
                                                        <p class="text-sm">Questions: <span>{{ $quiz_section->count() }}</span></p>

                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="mt-2">No public quizzes found.</div>
                                    @endforelse
                                @break

                                @case(3)
                                    @forelse($registered_only_quizzes as  $quiz)
                                        <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                                            <a href="{{ route('quiz.show', $quiz->slug) }}">
                                                <div
                                                    class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0 border-2 border-orange-500">
                                                    <div class="flex-auto p-4">
                                                        {{ $quiz->title }}
                                                        <p class="text-sm">Questions2: <span>{{ $quiz->questions_count }}</span></p>

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
            </div> --}}



@endsection

@push('script')
@endpush
