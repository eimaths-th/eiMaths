@extends('layouts.quiz_layout')

@section('title', 'Leaderboard')

@push('css')
    <style>
        /* Leaderboard Dashboard Premium Styling */
        .leaderboard-dashboard-page {
            margin: -32px !important;
            min-height: 100vh;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 50%, #ff8c42 100%) !important;
            position: relative;
            overflow: hidden;
            padding: 32px;
        }

        .leaderboard-dashboard-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 80%, rgba(255, 107, 53, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Header styling */
        .dashboard-header {
            text-align: center;
            margin-bottom: 48px;
            position: relative;
            z-index: 10;
        }

        .dashboard-title {
            color: #ffffff;
            font-size: 3rem;
            font-weight: 800;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.01em;
            line-height: 1.2;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }

        .dashboard-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.25rem;
            font-weight: 500;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        /* Filter section */
        .filter-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 32px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            position: relative;
            z-index: 10;
            overflow: hidden;
        }

        .filter-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b35 0%, #f59e0b 50%, #ff8c42 100%);
            border-radius: 20px 20px 0 0;
        }

        .filter-select {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid rgba(245, 158, 11, 0.2);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            font-weight: 600;
            color: #374151;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .filter-select:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        /* Leaderboard cards */
        .leaderboard-container {
            position: relative;
            z-index: 10;
        }

        .leaderboard-grid {
            display: grid;
            gap: 20px;
        }

        /* Podium for top 3 */
        .podium-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .podium-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 32px 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .podium-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.2);
        }

        .podium-card.first-place::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #ffd700 0%, #ffed4e 100%);
            border-radius: 20px 20px 0 0;
        }

        .podium-card.second-place::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #c0c0c0 0%, #e5e5e5 100%);
            border-radius: 20px 20px 0 0;
        }

        .podium-card.third-place::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #cd7f32 0%, #daa520 100%);
            border-radius: 20px 20px 0 0;
        }

        .podium-rank {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .podium-rank.first {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #b45309;
        }

        .podium-rank.second {
            background: linear-gradient(135deg, #c0c0c0 0%, #e5e5e5 100%);
            color: #374151;
        }

        .podium-rank.third {
            background: linear-gradient(135deg, #cd7f32 0%, #daa520 100%);
            color: #ffffff;
        }

        .podium-user {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .podium-quiz {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 16px;
        }

        .podium-score {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 12px;
        }

        .score-circle {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: conic-gradient(from 0deg, #ff6b35 0%, #f59e0b calc(var(--percentage) * 1%), #e5e7eb calc(var(--percentage) * 1%), #e5e7eb 100%);
        }

        .score-circle::before {
            content: '';
            position: absolute;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #ffffff;
        }

        .score-value {
            position: relative;
            z-index: 1;
            font-size: 1rem;
            font-weight: 700;
            color: #1f2937;
        }

        .podium-date {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        /* Regular leaderboard cards */
        .leaderboard-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .leaderboard-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .rank-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #ff6b35 0%, #f59e0b 100%);
            color: #ffffff;
            font-size: 1.25rem;
            font-weight: 700;
            box-shadow: 0 4px 16px rgba(255, 107, 53, 0.3);
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .user-email {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .quiz-info {
            flex: 1;
            text-align: center;
        }

        .quiz-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .quiz-date {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        .score-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .score-circle-small {
            position: relative;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: conic-gradient(from 0deg, #ff6b35 0%, #f59e0b calc(var(--percentage) * 1%), #e5e7eb calc(var(--percentage) * 1%), #e5e7eb 100%);
        }

        .score-circle-small::before {
            content: '';
            position: absolute;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #ffffff;
        }

        .score-value-small {
            position: relative;
            z-index: 1;
            font-size: 0.875rem;
            font-weight: 700;
            color: #1f2937;
        }

        .score-details {
            text-align: right;
        }

        .score-fraction {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .score-time {
            font-size: 0.75rem;
            color: #6b7280;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 24px;
            opacity: 0.6;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
        }

        .empty-message {
            font-size: 1rem;
            color: #6b7280;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .leaderboard-dashboard-page {
                padding: 20px;
                margin: -20px !important;
            }

            .dashboard-title {
                font-size: 2rem;
            }

            .podium-section {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .leaderboard-card {
                flex-direction: column;
                text-align: center;
                gap: 16px;
            }

            .quiz-info {
                text-align: center;
            }

            .score-details {
                text-align: center;
            }
        }
    </style>
@endpush
@push('script')
@endpush
@section('content')
    <div class="leaderboard-dashboard-page">
        <!-- Header Section -->
        <div class="dashboard-header">
            <h1 class="dashboard-title">
                <span>🏆</span>
                Leaderboard
            </h1>
            <p class="dashboard-subtitle">
                Top performers and quiz champions
            </p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h3 class="text-lg font-bold text-gray-900 mb-4">🔍 Filter by Quiz</h3>
            <form id="quiz-form" action="{{ route('submit.leaderboard') }}" method="POST">
                @csrf
                @php
                    $route = request()->route()->getName();
                @endphp
                <select name="quiz_slug" class="filter-select" onchange="document.getElementById('quiz-form').submit();">
                    <option value="0" {{ $quiz_slug === '0' ? 'selected' : '' }}>📊 All Quizzes</option>
                    @foreach ($quizzes as $quiz)
                        <option value="{{ $quiz->slug }}" {{ $quiz_slug === $quiz->slug ? 'selected' : '' }}>
                            📚 {{ $quiz->title }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="route" value="{{ $route }}">
            </form>
        </div>

        <!-- Leaderboard Container -->
        <div class="leaderboard-container">
            @if ($tests->count() > 0)
                @php
                    $topThree = $tests->take(3);
                    $remaining = $tests->skip(3);
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
                                $rankEmoji = ['🥇', '🥈', '🥉'][$index] ?? '';
                            @endphp

                            <div class="podium-card {{ $rankClass }}">
                                <div class="podium-rank {{ $rankBadgeClass }}">
                                    {{ $rankEmoji }}
                                </div>

                                <div class="podium-user">
                                    @if ($testUser)
                                        {{ $testUser->nickname ?? ($testUser->name ?? 'Unknown User') }}
                                    @else
                                        Unknown User
                                    @endif
                                </div>

                                <div class="podium-quiz">
                                    📚 {{ $test->quiz->title ?? 'Unknown Quiz' }}
                                </div>

                                <div class="podium-score">
                                    <div class="score-circle" style="--percentage: {{ $percentage }}">
                                        <div class="score-value">{{ $percentage }}%</div>
                                    </div>
                                </div>

                                <div class="score-fraction"
                                    style="font-size: 1.125rem; font-weight: 700; color: #1f2937; margin-bottom: 8px;">
                                    {{ $score }}/{{ $total }} correct
                                </div>

                                <div class="podium-date">
                                    🕒 {{ $test->created_at ? $test->created_at->format('M d, Y') : 'Unknown Date' }}
                                    <br>
                                    ⏱️ {{ sprintf('%02d:%02d:%02d', $hour, $minutes, $seconds) }}
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
                                    {{ $loop->iteration + 3 }}
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
                                            {{ $testUser->nickname ?? ($testUser->name ?? 'Unknown User') }}
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
                                    <div class="score-circle-small" style="--percentage: {{ $percentage }}">
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
            @else
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-icon">🏆</div>
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
