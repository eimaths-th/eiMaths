@extends('layouts.quiz_layout')

@section('title', 'My Results')

@push('css')
    <style>
        /* Layout Integration Styles */
        body.results-dashboard-body,
        .results-dashboard-body {
            background: linear-gradient(135deg, #fff5f0 0%, #fed7aa 100%) !important;
        }

        /* Neutralize layout container only on this page */
        body.results-dashboard-body main>div>div,
        .results-dashboard-body main>div>div {
            background: none !important;
            backdrop-filter: none !important;
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            padding: 0 !important;
            min-height: auto !important;
            overflow: visible !important;
        }

        /* Results Dashboard Premium Styling */
        .results-dashboard-page {
            margin: -32px !important;
            min-height: 100vh;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 50%, #ff8c42 100%) !important;
            position: relative;
            overflow: hidden;
            padding: 32px;
        }

        .results-dashboard-page::before {
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

        /* Modern cards container */
        .cards-container {
            position: relative;
            z-index: 10;
        }

        /* Statistics summary card */
        .summary-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 32px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b35 0%, #f59e0b 50%, #ff8c42 100%);
            border-radius: 20px 20px 0 0;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 16px;
        }

        .summary-stat {
            text-align: center;
            padding: 16px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .summary-stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: #f59e0b;
            margin-bottom: 4px;
        }

        .summary-stat-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Result cards grid */
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        /* Individual result card */
        .result-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .result-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .result-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b35 0%, #f59e0b 50%, #ff8c42 100%);
            border-radius: 20px 20px 0 0;
        }

        /* Card header */
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .card-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            font-weight: 800;
            font-size: 1rem;
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
        }

        .card-title {
            flex: 1;
            margin-left: 16px;
        }

        .card-title h3 {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 4px 0;
        }

        .card-title p {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        /* Card content grid */
        .card-content {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .card-info-item {
            background: rgba(255, 255, 255, 0.6);
            border-radius: 12px;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .card-info-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: block;
        }

        .card-info-value {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Card footer */
        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 16px;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
        }

        .card-score {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Score badge styling */
        .score-display {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .score-excellent {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #166534;
            border: 1px solid #86efac;
        }

        .score-good {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e;
            border: 1px solid #fbbf24;
        }

        .score-needs-improvement {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border: 1px solid #f87171;
        }

        /* Action button styling */
        .action-btn {
            background: linear-gradient(135deg, #f59e0b 0%, #ff6b35 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
            color: white;
            text-decoration: none;
        }

        /* Circular score meter for cards */
        .card-score-meter {
            --val: 0;
            --deg: calc(var(--val) * 3.6deg);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: conic-gradient(#f59e0b var(--deg), rgba(0, 0, 0, 0.06) 0);
            display: grid;
            place-items: center;
            position: relative;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .card-score-meter::after {
            content: '';
            position: absolute;
            inset: 8px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
        }

        .card-score-value {
            position: relative;
            font-size: 0.75rem;
            font-weight: 800;
            color: #1f2937;
            text-align: center;
            line-height: 1;
        }

        .card-score-meter.meter-excellent { background: conic-gradient(#10b981 var(--deg), rgba(0, 0, 0, 0.06) 0); }
        .card-score-meter.meter-good { background: conic-gradient(#f59e0b var(--deg), rgba(0, 0, 0, 0.06) 0); }
        .card-score-meter.meter-needs-improvement { background: conic-gradient(#ef4444 var(--deg), rgba(0, 0, 0, 0.06) 0); }

        .card-score-details {
            text-align: center;
        }

        .card-score-fraction {
            font-size: 1.125rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .card-score-percentage {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 600;
        }

        /* Time display styling */
        .time-display {
            font-family: 'Monaco', 'Menlo', monospace;
            background: #f8fafc;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        /* Date display styling */
        .date-display {
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .results-dashboard-page {
                margin: -16px !important;
                padding: 16px;
            }

            .dashboard-title {
                font-size: 2rem;
            }

            .results-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .summary-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .card-content {
                grid-template-columns: 1fr;
            }

            .card-footer {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }
        }
    </style>
@endpush
@push('script')
@endpush
@section('content')
    <div class="results-dashboard-page">
        <!-- Header Section -->
        <div class="dashboard-header">
            <h1 class="dashboard-title">
                <span>📊</span>
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
                    <h2 class="text-xl font-bold text-gray-900 mb-2">📊 Performance Summary</h2>
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
                                <h3>{{ $test->quiz->title }}</h3>
                                <p>{{ $test->created_at->format('d/m/Y h:i A') }}</p>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="card-content">
                            <div class="card-info-item">
                                <span class="card-info-label">🎓 Grade</span>
                                <div class="card-info-value">{{ $test->quiz->level ?? 'N/A' }}</div>
                            </div>
                            <div class="card-info-item">
                                <span class="card-info-label">📅 Term</span>
                                <div class="card-info-value">{{ $test->quiz->term ?? 'N/A' }}</div>
                            </div>
                            <div class="card-info-item">
                                <span class="card-info-label">📖 Level</span>
                                <div class="card-info-value">{{ $test->quiz->section ?? 'N/A' }}</div>
                            </div>
                            <div class="card-info-item">
                                <span class="card-info-label">⏱️ Time Spent</span>
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
                                        $meterClass = 'meter-excellent';
                                        $badgeClass = 'score-display score-excellent';
                                        $emoji = '🌟';
                                    } elseif ($percentage >= 60) {
                                        $meterClass = 'meter-good';
                                        $badgeClass = 'score-display score-good';
                                        $emoji = '👍';
                                    } else {
                                        $meterClass = 'meter-needs-improvement';
                                        $badgeClass = 'score-display score-needs-improvement';
                                        $emoji = '📈';
                                    }
                                @endphp
                                
                                <div class="card-score-meter {{ $meterClass }}" style="--val: {{ $percentage }};">
                                    <div class="card-score-value">{{ $percentage }}%</div>
                                </div>
                                
                                <div class="card-score-details">
                                    <div class="card-score-fraction">{{ $score }}/{{ $total }}</div>
                                    <div class="{{ $badgeClass }}">
                                        <span>{{ $emoji }}</span>
                                        <span class="card-score-percentage">Score</span>
                                    </div>
                                </div>
                            </div>
                            
                            <a href="{{ route('results.show', $test) }}" class="action-btn">
                                View Details 👁️
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #6b7280;">
                        <div class="empty-state-icon" style="font-size: 4rem; margin-bottom: 16px; opacity: 0.5;">📊</div>
                        <div class="text-lg font-semibold mb-2">No test results found</div>
                        <div class="text-sm">There are no test results to display at the moment.</div>
                    </div>
                @endforelse
            </div>

            <!-- Custom Pagination -->
            @if ($tests->hasPages())
                <div style="display: flex; justify-content: center; align-items: center; margin-top: 24px; gap: 8px;">
                    {{-- Previous Page Link --}}
                    @if ($tests->onFirstPage())
                        <span
                            style="display: inline-flex; align-items: center; padding: 8px 12px; margin-left: 0; line-height: 1.25; color: #9ca3af; background-color: #f9fafb; border: 1px solid #d1d5db; border-radius: 6px; cursor: not-allowed;">
                            <svg style="width: 16px; height: 16px; margin-right: 4px;" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            ก่อนหน้า
                        </span>
                    @else
                        <a href="{{ $tests->previousPageUrl() }}"
                            style="display: inline-flex; align-items: center; padding: 8px 12px; margin-left: 0; line-height: 1.25; color: #374151; background-color: #ffffff; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; transition: all 0.15s ease-in-out;"
                            onmouseover="this.style.backgroundColor='#f3f4f6'; this.style.color='#f97316';"
                            onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#374151';">
                            <svg style="width: 16px; height: 16px; margin-right: 4px;" fill="currentColor"
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
                            <span
                                style="display: inline-flex; align-items: center; padding: 8px 12px; line-height: 1.25; color: #ffffff; background-color: #f97316; border: 1px solid #f97316; border-radius: 6px; font-weight: 600; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                                style="display: inline-flex; align-items: center; padding: 8px 12px; line-height: 1.25; color: #374151; background-color: #ffffff; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; transition: all 0.15s ease-in-out;"
                                onmouseover="this.style.backgroundColor='#f97316'; this.style.color='#ffffff'; this.style.borderColor='#f97316';"
                                onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#374151'; this.style.borderColor='#d1d5db';">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($tests->hasMorePages())
                        <a href="{{ $tests->nextPageUrl() }}"
                            style="display: inline-flex; align-items: center; padding: 8px 12px; margin-left: 0; line-height: 1.25; color: #374151; background-color: #ffffff; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; transition: all 0.15s ease-in-out;"
                            onmouseover="this.style.backgroundColor='#f3f4f6'; this.style.color='#f97316';"
                            onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#374151';">
                            ถัดไป
                            <svg style="width: 16px; height: 16px; margin-left: 4px;" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </a>
                    @else
                        <span
                            style="display: inline-flex; align-items: center; padding: 8px 12px; margin-left: 0; line-height: 1.25; color: #9ca3af; background-color: #f9fafb; border: 1px solid #d1d5db; border-radius: 6px; cursor: not-allowed;">
                            ถัดไป
                            <svg style="width: 16px; height: 16px; margin-left: 4px;" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </span>
                    @endif
                </div>

                <!-- Pagination Info -->
                <div style="text-align: center; margin-top: 16px; color: #6b7280; font-size: 14px;">
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
