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

        /* Modern table container */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            position: relative;
            z-index: 10;
            overflow: hidden;
        }

        .table-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b35 0%, #f59e0b 50%, #ff8c42 100%);
            border-radius: 20px 20px 0 0;
        }

        /* Enhanced table styling */
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .modern-table thead th {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #374151;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 20px 16px;
            border-bottom: 2px solid #e5e7eb;
            position: relative;
        }

        .modern-table thead th:first-child {
            border-top-left-radius: 16px;
        }

        .modern-table thead th:last-child {
            border-top-right-radius: 16px;
        }

        .modern-table tbody tr {
            background: #ffffff;
            transition: all 0.3s ease;
            border-bottom: 1px solid #f1f5f9;
        }

        .modern-table tbody tr:hover {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .modern-table tbody td {
            padding: 16px;
            color: #374151;
            font-weight: 500;
            vertical-align: middle;
        }

        .modern-table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 16px;
        }

        .modern-table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 16px;
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

        /* Footer styling */
        .table-footer {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            font-weight: 700;
            color: #374151;
        }

        .table-footer td {
            padding: 20px 16px !important;
            border-top: 2px solid #e5e7eb;
        }

        /* Row number styling */
        .row-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.875rem;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
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

            .table-container {
                padding: 16px;
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

        <!-- Table Container -->
        <div class="table-container">
            <div class="overflow-x-auto">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>📚 Quiz Title</th>
                            <th>🎓 Grade</th>
                            <th>📅 Term</th>
                            <th>📖 Level</th>
                            <th>🎯 Result</th>
                            <th>⏱️ Time Spent</th>
                            <th>📆 Date Taken</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($tests as $test)
                            <tr>
                                <td>
                                    <div class="row-number">{{ $loop->iteration }}</div>
                                </td>
                                <td>
                                    <div class="font-semibold text-gray-900">{{ $test->quiz->title }}</div>
                                </td>
                                <td>{{ $test->quiz->level ?? 'N/A' }}</td>
                                <td>{{ $test->quiz->term ?? 'N/A' }}</td>
                                <td>{{ $test->quiz->section ?? 'N/A' }}</td>
                                <td>
                                    @php
                                        $score = $test->result ?? 0;
                                        $total = $test->quiz->questions_count ?? 1;
                                        $percentage = $total > 0 ? round(($score / $total) * 100) : 0;

                                        if ($percentage >= 80) {
                                            $scoreClass = 'score-display score-excellent';
                                            $emoji = '🌟';
                                        } elseif ($percentage >= 60) {
                                            $scoreClass = 'score-display score-good';
                                            $emoji = '👍';
                                        } else {
                                            $scoreClass = 'score-display score-needs-improvement';
                                            $emoji = '📈';
                                        }
                                    @endphp
                                    <div class="{{ $scoreClass }}">
                                        <span>{{ $emoji }}</span>
                                        <span>{{ $score }}/{{ $total }}</span>
                                        <span class="text-xs">({{ $percentage }}%)</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $hour = intdiv($test->time_spent, 3600);
                                        $minutes = intdiv($test->time_spent % 3600, 60);
                                        $seconds = $test->time_spent % 60;
                                    @endphp
                                    <div class="time-display">
                                        {{ sprintf('%02d:%02d:%02d', $hour, $minutes, $seconds) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="date-display">
                                        {{ $test->created_at->format('d/m/Y') }}<br>
                                        <span class="text-xs">{{ $test->created_at->format('h:i A') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('results.show', $test) }}" class="action-btn">
                                        View Details 👁️
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty-state">
                                    <div class="empty-state-icon">📊</div>
                                    <div class="text-lg font-semibold mb-2">No test results found</div>
                                    <div class="text-sm">There are no test results to display at the moment.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-footer">
                        <tr>
                            <td class="text-center">#</td>
                            <td colspan="4" class="text-center font-bold">📊 Total Summary</td>
                            <td class="text-center">
                                <div class="score-display score-good">
                                    <span>🎯</span>
                                    <span>{{ $tests->sum('result') }}/{{ $tests->sum('quiz.questions_count') }}</span>
                                </div>
                            </td>
                            <td colspan="3" class="text-center text-sm text-gray-600">
                                Total {{ $tests->count() }} test(s) completed
                            </td>
                        </tr>
                    </tfoot>
                </table>
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
