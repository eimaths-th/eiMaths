@extends('layouts.quiz_layout', ['test_preview_page' => true])
@section('title', __('quiz-management.preview.title'))

@section('content')
    <header class="test-preview-header">
        <h1 class="test-preview-heading">{{ __('quiz-management.preview.results_heading') }}</h1>
    </header>

    <div class="test-preview-toolbar">
        <form id="quiz-form" action="{{ route('submit.leaderboard') }}" method="POST">
            @csrf
            <label for="quiz-slug">{{ __('quiz-management.preview.quiz') }}</label>
            <select id="quiz-slug" name="quiz_slug" onchange="document.getElementById('quiz-form').submit();">
                <option value="0" {{ $quiz_slug === '0' ? 'selected' : '' }}>{{ __('quiz-management.preview.all_quizzes') }}</option>
                @foreach ($quizzes ?? [] as $quiz)
                    <option value="{{ $quiz->slug }}" {{ $quiz_slug === $quiz->slug ? 'selected' : '' }}>{{ $quiz->title ?? __('quiz-management.preview.not_available') }}</option>
                @endforeach
            </select>
            <input type="hidden" name="route" value="{{ request()->route()->getName() }}">
        </form>
        <div class="test-preview-page-size">
            <label for="per-page">{{ __('quiz-management.preview.show') }}</label>
            <select id="per-page" name="per_page" onchange="const params = new URLSearchParams(window.location.search); params.set('per_page', this.value); params.delete('page'); window.location.href = window.location.pathname + '?' + params.toString();">
                @foreach (($perPageOptions ?? [10, 20, 50, 100]) as $page_size)
                    <option value="{{ $page_size }}" {{ (int) ($perPage ?? 20) === $page_size ? 'selected' : '' }}>{{ $page_size }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($tests->count())
        <ul class="test-preview-list">
                @foreach ($tests as $test)
                    @php
                        $test_user = $test->type_user === 'student' && isset($test->student) ? $test->student : $test->user;
                        $unavailable = __('quiz-management.preview.not_available');
                        $participant_name = $test_user ? ($test_user->nickname ?? $test_user->name ?? $unavailable) : $unavailable;
                        $participant_detail = $test_user ? ($test_user->email ?? $test_user->name ?? $unavailable) : $unavailable;
                        $score = $test->result ?? 0;
                        $question_count = $test->questions_count ?? 1;
                        $percentage = $question_count > 0 ? round(($score / $question_count) * 100) : 0;
                        $performance_key = $percentage >= 80 ? 'excellent' : ($percentage >= 60 ? 'good' : 'needs_improvement');
                        $duration_seconds = $test->time_spent ?? 0;
                    @endphp
                    <li>
                        <article class="test-preview-card" aria-labelledby="attempt-title-{{ $test->id }}">
                            <div class="test-preview-identity">
                                <div class="test-preview-participant">
                                    <span class="test-preview-label">{{ __('quiz-management.preview.participant') }}</span>
                                    <strong>{{ $participant_name }}</strong>
                                    <span class="test-preview-attempt">#{{ $test->id ?? $unavailable }}</span>
                                </div>
                                @if ($participant_detail !== $participant_name)<span class="test-preview-secondary">{{ $participant_detail }}</span>@endif
                                <span class="test-preview-label test-preview-quiz-label">{{ __('quiz-management.preview.quiz') }}</span>
                                <h2 id="attempt-title-{{ $test->id }}">{{ $test->quiz->title ?? $unavailable }}</h2>
                                <dl class="test-preview-quiz-meta">
                                    <div><dt>{{ __('quiz-management.preview.grade') }}</dt><dd>{{ $test->quiz->level ?? $unavailable }}</dd></div>
                                    <div><dt>{{ __('quiz-management.preview.term') }}</dt><dd>{{ $test->quiz->term ?? $unavailable }}</dd></div>
                                    <div><dt>{{ __('quiz-management.preview.level') }}</dt><dd>{{ $test->quiz->section ?? $unavailable }}</dd></div>
                                </dl>
                            </div>
                            <div class="test-preview-outcome">
                                <div class="test-preview-score-block">
                                    <span class="test-preview-label">{{ __('quiz-management.preview.score') }}</span>
                                    <strong class="test-preview-score">{{ $score }}/{{ $question_count }}</strong>
                                    <span class="test-preview-performance">{{ $percentage }}% · {{ __('quiz-management.preview.' . $performance_key) }}</span>
                                </div>
                                <dl class="test-preview-attempt-meta">
                                    <div>
                                        <dt>{{ __('quiz-management.preview.duration') }}</dt>
                                        <dd class="test-preview-numeric">{{ sprintf('%02d:%02d:%02d', intdiv($duration_seconds, 3600), intdiv($duration_seconds % 3600, 60), $duration_seconds % 60) }}</dd>
                                    </div>
                                    <div>
                                        <dt>{{ __('quiz-management.preview.attempted_at') }}</dt>
                                        <dd>@if ($test->created_at)<time datetime="{{ $test->created_at->toIso8601String() }}">{{ $test->created_at->format('D d/m/Y, h:i A') }}</time>@else{{ $unavailable }}@endif</dd>
                                    </div>
                                </dl>
                                <a class="test-preview-detail-link" href="{{ route('results.show', $test) }}" aria-label="{{ __('quiz-management.preview.view_answers') }}: {{ $participant_name }}, {{ $test->quiz->title ?? $unavailable }}, #{{ $test->id }}">{{ __('quiz-management.preview.view_answers') }}</a>
                            </div>
                        </article>
                    </li>
                @endforeach
        </ul>
    @else
        <div class="test-preview-empty" role="status">
            <h2>{{ __('quiz-management.preview.no_tests') }}</h2>
            <p>{{ __('quiz-management.preview.no_tests_message') }}</p>
        </div>
    @endif

    @if (method_exists($tests, 'hasPages') && $tests->hasPages())
        <div class="test-preview-pagination">{{ $tests->links() }}</div>
    @endif
@endsection
