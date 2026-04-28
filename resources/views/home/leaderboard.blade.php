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
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .podium-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 22px 18px;
            box-shadow: 0 14px 38px rgba(0, 0, 0, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .podium-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 54px rgba(0, 0, 0, 0.16);
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
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: 12px;
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
            font-size: 1.05rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .podium-quiz {
            font-size: 0.8125rem;
            color: #6b7280;
            margin-bottom: 12px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: conic-gradient(from 0deg, #ff6b35 0%, #f59e0b calc(var(--percentage) * 1%), #e5e7eb calc(var(--percentage) * 1%), #e5e7eb 100%);
        }

        .score-circle::before {
            content: '';
            position: absolute;
            width: 52px;
            height: 52px;
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
            line-height: 1.6;
        }

        /* Regular leaderboard cards */
        .leaderboard-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 16px 18px;
            box-shadow: 0 10px 28px rgba(31, 41, 55, 0.09);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: grid;
            grid-template-columns: auto minmax(180px, 1.1fr) minmax(220px, 1.2fr) auto;
            align-items: center;
            gap: 16px;
        }

        .leaderboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 36px rgba(31, 41, 55, 0.14);
        }

        .rank-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #ff6b35 0%, #f59e0b 100%);
            color: #ffffff;
            font-size: 1.05rem;
            font-weight: 700;
            box-shadow: 0 4px 16px rgba(255, 107, 53, 0.3);
        }

        .user-info {
            min-width: 0;
        }

        .user-name {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 3px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-email {
            font-size: 0.8125rem;
            color: #6b7280;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .quiz-info {
            min-width: 0;
            text-align: left;
            padding: 10px 12px;
            border-radius: 12px;
            background: rgba(249, 250, 251, 0.82);
            border: 1px solid rgba(229, 231, 235, 0.8);
        }

        .quiz-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .quiz-date {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        .score-info {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        .score-circle-small {
            position: relative;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: conic-gradient(from 0deg, #ff6b35 0%, #f59e0b calc(var(--percentage) * 1%), #e5e7eb calc(var(--percentage) * 1%), #e5e7eb 100%);
        }

        .score-circle-small::before {
            content: '';
            position: absolute;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #ffffff;
        }

        .score-value-small {
            position: relative;
            z-index: 1;
            font-size: 0.75rem;
            font-weight: 700;
            color: #1f2937;
        }

        .score-details {
            text-align: right;
        }

        .score-fraction {
            font-size: 1rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .score-time {
            font-size: 0.75rem;
            color: #6b7280;
            white-space: nowrap;
        }

        /* Pagination */
        .leaderboard-pagination {
            display: flex;
            justify-content: center;
            padding: 24px 0 4px;
            position: relative;
            z-index: 10;
        }

        .leaderboard-pagination-card {
            display: inline-flex;
            max-width: min(100%, 760px);
            padding: 12px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(14px);
            box-shadow: 0 14px 32px rgba(146, 64, 14, 0.14);
        }

        .leaderboard-pagination nav {
            width: 100%;
        }

        .leaderboard-pagination nav > div,
        .leaderboard-pagination nav .hidden,
        .leaderboard-pagination nav .relative {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
            width: auto !important;
        }

        .leaderboard-pagination nav p,
        .leaderboard-pagination nav .text-sm,
        .leaderboard-pagination nav .sm\:hidden {
            display: none !important;
        }

        .leaderboard-pagination a,
        .leaderboard-pagination span,
        .leaderboard-pagination button,
        .leaderboard-pagination .page-link {
            min-width: 38px !important;
            height: 38px !important;
            padding: 0 12px !important;
            border-radius: 10px !important;
            border: 1px solid rgba(245, 158, 11, 0.22) !important;
            background: rgba(255, 255, 255, 0.88) !important;
            color: #92400e !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 0.875rem !important;
            font-weight: 700 !important;
            line-height: 1 !important;
            text-decoration: none !important;
            box-shadow: 0 4px 12px rgba(146, 64, 14, 0.08) !important;
            margin: 0 !important;
        }

        .leaderboard-pagination a:hover,
        .leaderboard-pagination .page-link:hover {
            background: #fff7ed !important;
            border-color: rgba(249, 115, 22, 0.45) !important;
            transform: translateY(-1px);
        }

        .leaderboard-pagination span[aria-current="page"] span,
        .leaderboard-pagination .active .page-link,
        .leaderboard-pagination span.bg-blue-50 {
            background: linear-gradient(135deg, #f59e0b 0%, #ff6b35 100%) !important;
            border-color: transparent !important;
            color: #ffffff !important;
        }

        .leaderboard-pagination span[aria-disabled="true"] {
            opacity: 0.45;
            background: rgba(255, 255, 255, 0.72) !important;
            color: #9ca3af !important;
        }

        .leaderboard-pagination ul.pagination {
            display: flex !important;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
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
                grid-template-columns: 1fr;
                text-align: center;
                gap: 12px;
            }

            .quiz-info {
                text-align: center;
            }

            .rank-badge {
                margin: 0 auto;
            }

            .score-info {
                justify-content: center;
            }

            .score-details {
                text-align: center;
            }

            .leaderboard-pagination a,
            .leaderboard-pagination span,
            .leaderboard-pagination button,
            .leaderboard-pagination .page-link {
                min-width: 34px !important;
                height: 34px !important;
                padding: 0 10px !important;
                font-size: 0.8125rem !important;
            }
        }

        @media (min-width: 769px) and (max-width: 1050px) {
            .leaderboard-card {
                grid-template-columns: auto minmax(160px, 1fr) minmax(180px, 1fr);
            }

            .score-info {
                grid-column: 2 / -1;
                justify-content: flex-start;
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
