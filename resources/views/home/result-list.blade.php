@extends('layouts.quiz_layout', ['quiz_portal_page' => true])

@section('title', 'My Results')


@push('script')
@endpush
@section('content')
    <div class="results-dashboard-page">
        <!-- Header Section -->
        <div class="portal-header">
            <h1 class="dashboard-title">
                My Results
            </h1>
            <p class="dashboard-subtitle">
                Comprehensive overview of all test performances and results
            </p>
        </div>

        <!-- Cards Container -->
        <div class="cards-container">
            <!-- Summary Statistics Card -->
            @if($tests->count() > 0)
                <div class="summary-card">
                    <h2 class="text-xl font-bold text-gray-900 mb-2"> Performance Summary</h2>
                    <p class="text-gray-600 mb-4">Overview of your test performance statistics</p>

                    <div class="summary-stats">
                        <div class="summary-stat">
                            <div class="summary-stat-number">{{ $tests->count() }}</div>
                            <div class="summary-stat-label">Total Tests</div>
                        </div>
                        <div class="summary-stat">
                            <div class="summary-stat-number">{{ $tests->sum('result') }}/{{ $tests->sum('quiz.questions_count') }}</div>
                            <div class="summary-stat-label">Total Score</div>
                        </div>
                        <div class="summary-stat">
                            @php
                                $totalScore = $tests->sum('result');
                                $totalQuestions = $tests->sum('quiz.questions_count');
                                $avgPercentage = $totalQuestions > 0 ? round(($totalScore / $totalQuestions) * 100, 1) : 0;
                            @endphp
                            <div class="summary-stat-number">{{ $avgPercentage }}%</div>
                            <div class="summary-stat-label">Average Score</div>
                        </div>
                        <div class="summary-stat">
                            @php
                                $excellentCount = 0;
                                foreach($tests as $test) {
                                    $score = $test->result ?? 0;
                                    $total = $test->quiz->questions_count ?? 1;
                                    $percentage = $total > 0 ? round(($score / $total) * 100) : 0;
                                    if($percentage >= 80) $excellentCount++;
                                }
                            @endphp
                            <div class="summary-stat-number">{{ $excellentCount }}</div>
                            <div class="summary-stat-label">Excellent Scores</div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Results Grid -->
            <div class="results-grid">
                @forelse($tests as $test)
                    <div class="result-card">
                        <!-- Card Header -->
                        <div class="card-header">
                            <div class="card-number">{{ $loop->iteration }}</div>
                            <div class="card-title">
                                <h2>{{ $test->quiz->title }}</h2>
                                <p>{{ $test->created_at->format('d/m/Y h:i A') }}</p>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="card-content">
                            <div class="card-info-item">
                                <span class="card-info-label"> Grade</span>
                                <div class="card-info-value">{{ $test->quiz->level ?? 'N/A' }}</div>
                            </div>
                            <div class="card-info-item">
                                <span class="card-info-label"> Term</span>
                                <div class="card-info-value">{{ $test->quiz->term ?? 'N/A' }}</div>
                            </div>
                            <div class="card-info-item">
                                <span class="card-info-label"> Level</span>
                                <div class="card-info-value">{{ $test->quiz->section ?? 'N/A' }}</div>
                            </div>
                            <div class="card-info-item">
                                <span class="card-info-label"> Time Spent</span>
                                @php
                                    $hour = intdiv($test->time_spent, 3600);
                                    $minutes = intdiv($test->time_spent % 3600, 60);
                                    $seconds = $test->time_spent % 60;
                                @endphp
                                <div class="card-info-value time-display">
                                    {{ sprintf('%02d:%02d:%02d', $hour, $minutes, $seconds) }}
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer">
                            <div class="card-score">
                                @php
                                    $score = $test->result ?? 0;
                                    $total = $test->quiz->questions_count ?? 1;
                                    $percentage = $total > 0 ? round(($score / $total) * 100) : 0;

                                    if ($percentage >= 80) {
                                        $badgeClass = 'score-display score-excellent';
                                        $performance_label = 'Excellent';
                                    } elseif ($percentage >= 60) {
                                        $badgeClass = 'score-display score-good';
                                        $performance_label = 'Good';
                                    } else {
                                        $badgeClass = 'score-display score-needs-improvement';
                                        $performance_label = 'Needs Improvement';
                                    }
                                @endphp

                                <div class="card-score-percentage">
                                    <div class="card-score-value">{{ $percentage }}%</div>
                                </div>

                                <div class="card-score-details">
                                    <div class="card-score-fraction">{{ $score }}/{{ $total }}</div>
                                    <div class="{{ $badgeClass }}">
                                        <span>{{ $performance_label }}</span>
                                        <span class="card-score-percentage">Score</span>
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('results.show', $test) }}" class="action-btn" aria-label="View Details #{{ $test->id }}">
                                View Details
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="text-lg font-semibold mb-2">No test results found</div>
                        <div class="text-sm">There are no test results to display at the moment.</div>
                    </div>
                @endforelse
            </div>

            <!-- Custom Pagination -->
            @if ($tests->hasPages())
                <nav class="portal-pagination" aria-label="Results pagination">
                    {{-- Previous Page Link --}}
                    @if ($tests->onFirstPage())
                        <span>
                            <svg fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            ก่อนหน้า
                        </span>
                    @else
                        <a href="{{ $tests->previousPageUrl() }}">
                            <svg fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            ก่อนหน้า
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($tests->getUrlRange(1, $tests->lastPage()) as $page => $url)
                        @if ($page == $tests->currentPage())
                            <span class="portal-current-page" aria-current="page">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($tests->hasMorePages())
                        <a href="{{ $tests->nextPageUrl() }}">
                            ถัดไป
                            <svg fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    @else
                        <span>
                            ถัดไป
                            <svg fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </span>
                    @endif
                </nav>

                <!-- Pagination Info -->
                <div>
                    แสดงผล {{ $tests->firstItem() }} ถึง {{ $tests->lastItem() }} จากทั้งหมด {{ $tests->total() }} รายการ
                </div>
            @endif
        </div>
    </div>

    {{-- <script>
        // Apply body class for layout integration
        document.body.classList.add('results-dashboard-body');
    </script> --}}

@endsection
@section('script')
@endsection
