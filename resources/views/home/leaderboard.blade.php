@extends('layouts.quiz_layout', ['quiz_portal_page' => true])

@section('title', 'Leaderboard')


@push('script')
@endpush
@section('content')
    <div class="leaderboard-dashboard-page">
        <!-- Header Section -->
        <div class="portal-header">
            <h1 class="dashboard-title">
                Leaderboard
            </h1>
            <p class="dashboard-subtitle">
                Top performers and quiz champions
            </p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <label for="leaderboard-quiz">Filter by Quiz</label>
            <form id="quiz-form" action="{{ route('submit.leaderboard') }}" method="POST">
                @csrf
                @php
                    $route = request()->route()->getName();
                @endphp
                <select id="leaderboard-quiz" name="quiz_slug" class="filter-select" onchange="document.getElementById('quiz-form').submit();">
                    <option value="0" {{ $quiz_slug === '0' ? 'selected' : '' }}> All Quizzes</option>
                    @foreach ($quizzes as $quiz)
                        <option value="{{ $quiz->slug }}" {{ $quiz_slug === $quiz->slug ? 'selected' : '' }}>
                             {{ $quiz->title }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="route" value="{{ $route }}">
            </form>
        </div>

        <!-- Leaderboard Container -->
        <div class="leaderboard-container">
            @if ($tests->count() > 0)
                @php
                    $isFirstPage = !method_exists($tests, 'currentPage') || $tests->currentPage() === 1;
                    $topThree = $isFirstPage ? $tests->take(3) : collect();
                    $remaining = $isFirstPage ? $tests->skip(3) : $tests->getCollection();
                @endphp

                <!-- Podium Section (Top 3) -->
                @if ($topThree->count() > 0)
                    <div class="podium-section">
                        @foreach ($topThree as $index => $test)
                            @php
                                if ($test->type_user === 'user' && $test->user) {
                                    $testUser = $test->user;
                                } elseif ($test->type_user === 'student' && $test->student) {
                                    $testUser = $test->student;
                                } else {
                                    $testUser = $test->user ?? null;
                                }

                                $score = $test->result ?? 0;
                                $total = $test->quiz->questions_count ?? 1;
                                $percentage = $total > 0 ? round(($score / $total) * 100) : 0;

                                $hour = intdiv($test->time_spent ?? 0, 3600);
                                $minutes = intdiv(($test->time_spent ?? 0) % 3600, 60);
                                $seconds = ($test->time_spent ?? 0) % 60;

                                $rankClass = ['first-place', 'second-place', 'third-place'][$index] ?? '';
                                $rankBadgeClass = ['first', 'second', 'third'][$index] ?? '';
                            @endphp

                            <div class="podium-card {{ $rankClass }}">
                                <div class="podium-rank {{ $rankBadgeClass }}">
                                    Rank {{ $index + 1 }}
                                </div>

                                <div class="podium-user">
                                    @if ($testUser)
                                        {{ $testUser->nickname ?? ($testUser->name ?? 'Unknown User') }}
                                    @else
                                        Unknown User
                                    @endif
                                </div>

                                <div class="podium-quiz">
                                     {{ $test->quiz->title ?? 'Unknown Quiz' }}
                                </div>

                                <div class="podium-score">
                                    <div class="score-circle">
                                        <div class="score-value">{{ $percentage }}%</div>
                                    </div>
                                </div>

                                <div class="score-fraction">
                                    {{ $score }}/{{ $total }} correct
                                </div>

                                <div class="podium-date">
                                     {{ $test->created_at ? $test->created_at->format('M d, Y') : 'Unknown Date' }}
                                    <br>
                                     {{ sprintf('%02d:%02d:%02d', $hour, $minutes, $seconds) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Regular Leaderboard Cards -->
                @if ($remaining->count() > 0)
                    <div class="leaderboard-grid">
                        @foreach ($remaining as $test)
                            @php
                                $rankNumber = $isFirstPage
                                    ? $loop->iteration + 3
                                    : (method_exists($tests, 'firstItem') ? $tests->firstItem() + $loop->index : $loop->iteration);

                                if ($test->type_user === 'user' && $test->user) {
                                    $testUser = $test->user;
                                } elseif ($test->type_user === 'student' && $test->student) {
                                    $testUser = $test->student;
                                } else {
                                    $testUser = $test->user ?? null;
                                }

                                $score = $test->result ?? 0;
                                $total = $test->quiz->questions_count ?? 1;
                                $percentage = $total > 0 ? round(($score / $total) * 100) : 0;

                                $hour = intdiv($test->time_spent ?? 0, 3600);
                                $minutes = intdiv(($test->time_spent ?? 0) % 3600, 60);
                                $seconds = ($test->time_spent ?? 0) % 60;
                            @endphp

                            <div class="leaderboard-card">
                                <div class="rank-badge">
                                    {{ $rankNumber }}
                                </div>

                                <div class="user-info">
                                    <div class="user-name">
                                        @if ($testUser)
                                            {{ $testUser->nickname ?? ($testUser->name ?? 'Unknown User') }}
                                        @else
                                            Unknown User
                                        @endif
                                    </div>
                                    <div class="user-email">
                                        @if ($testUser)
                                            {{ $testUser->email ?? ($testUser->name ?? 'Unknown User') }}
                                        @else
                                            Unknown User
                                        @endif
                                    </div>
                                </div>

                                <div class="quiz-info">
                                    <div class="quiz-title">
                                        {{ $test->quiz->title ?? 'Unknown Quiz' }}
                                    </div>
                                    <div class="quiz-date">
                                        {{ $test->created_at ? $test->created_at->format('M d, Y h:i A') : 'Unknown Date' }}
                                    </div>
                                </div>

                                <div class="score-info">
                                    <div class="score-circle-small">
                                        <div class="score-value-small">{{ $percentage }}%</div>
                                    </div>

                                    <div class="score-details">
                                        <div class="score-fraction">
                                            {{ $score }}/{{ $total }}
                                        </div>
                                        <div class="score-time">
                                            {{ sprintf('%02d:%02d:%02d', $hour, $minutes, $seconds) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if(method_exists($tests, 'hasPages') && $tests->hasPages())
                    <div class="leaderboard-pagination">
                        <div class="leaderboard-pagination-card">
                            {{ $tests->links() }}
                        </div>
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-title">No Results Yet</div>
                    <div class="empty-message">
                        The leaderboard is waiting for quiz results to appear. Take a quiz to see your name here!
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
@endsection
