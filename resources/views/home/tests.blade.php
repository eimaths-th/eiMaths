@extends('layouts.quiz_layout')

@section('title', 'Tests')

@push('css')
    <style>
        /* Layout Integration Styles */
        body.tests-dashboard-body,
        .tests-dashboard-body {
            background: linear-gradient(135deg, #fff5f0 0%, #fed7aa 100%) !important;
        }

        /* Immediate body class application */
        body {
            transition: background 0.3s ease;
        }

        /* Neutralize layout container only on this page */
        body.tests-dashboard-body main>div>div,
        .tests-dashboard-body main>div>div {
            background: none !important;
            backdrop-filter: none !important;
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            padding: 0 !important;
            min-height: auto !important;
            overflow: visible !important;
        }

        /* Hide layout decorative elements to prevent visual conflicts */
        body.tests-dashboard-body main>div>div>div:first-child,
        body.tests-dashboard-body main>div>div>div:last-child,
        .tests-dashboard-body main>div>div>div:first-child,
        .tests-dashboard-body main>div>div>div:last-child {
            display: none !important;
        }

        /* Force override for layout container */
        main>div>div {
            background: transparent !important;
            padding: 0 !important;
        }

        /* Tests Dashboard Premium Styling */
        .tests-dashboard-page {
            margin: -32px !important;
            min-height: 100vh;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 50%, #ff8c42 100%) !important;
            position: relative;
            overflow: hidden;
        }

        .tests-dashboard-page::before {
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

        .glass-card {
            background: rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 20px !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
        }

        .dashboard-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .dashboard-stat-card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 20px;
            padding: 32px 24px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        }

        .dashboard-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #ff8c42 0%, #f7931e 50%, #ff6b35 100%);
            border-radius: 20px 20px 0 0;
        }

        .dashboard-stat-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
        }

        .dashboard-stat-card:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .dashboard-stat-card:hover::after {
            opacity: 1;
        }

        .dashboard-stat-icon {
            font-size: 3.5rem;
            margin-bottom: 16px;
            display: block;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
            animation: float 3s ease-in-out infinite;
        }

        .dashboard-stat-number {
            font-size: 2.75rem;
            font-weight: 800;
            color: white;
            margin-bottom: 8px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            line-height: 1;
            font-family: 'Inter', sans-serif;
        }

        .dashboard-stat-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 0;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .dashboard-stat-card:nth-child(1) .dashboard-stat-icon { animation-delay: 0s; }
        .dashboard-stat-card:nth-child(2) .dashboard-stat-icon { animation-delay: 0.5s; }
        .dashboard-stat-card:nth-child(3) .dashboard-stat-icon { animation-delay: 1s; }

        .test-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .test-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #ff8c42 0%, #ff6b35 100%);
        }

        .test-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .score-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .score-excellent {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }

        .score-good {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
        }

        .score-needs-improvement {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .filter-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 30px;
        }

        .premium-select {
            background: rgba(255, 255, 255, 0.9) !important;
            border: 2px solid rgba(255, 255, 255, 0.3) !important;
            border-radius: 12px !important;
            padding: 12px 16px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #374151 !important;
            transition: all 0.3s ease !important;
            backdrop-filter: blur(10px) !important;
        }

        .premium-select:focus {
            outline: none !important;
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
        }

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
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 2px dashed rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            margin: 40px 0;
        }

        .dashboard-header-title {
            color: #ffffff;
            font-size: 3.5rem;
            font-weight: 800;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.01em;
            line-height: 1.2;
            margin-bottom: 16px;
            display: inline-block;
        }

        .dashboard-header-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.25rem;
            font-weight: 500;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            letter-spacing: 0.025em;
            line-height: 1.6;
            margin-bottom: 0;
            opacity: 0.95;
        }

        .dashboard-header-container {
            text-align: center;
            margin-bottom: 48px;
            position: relative;
        }

        .dashboard-header-container::after {
            content: '';
            position: absolute;
            bottom: -16px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.6) 50%, transparent 100%);
            border-radius: 2px;
        }

        .dashboard-header-icon {
            font-size: 3.5rem;
            margin-right: 16px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
            display: inline-block;
            vertical-align: middle;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .test-card {
                padding: 16px;
            }

            .tests-dashboard-page {
                margin: -16px !important;
            }
        }

        @keyframes tests-loading {

            0%,
            100% {
                opacity: 0.4;
            }

            50% {
                opacity: 0.8;
            }
        }

        .loading-skeleton {
            animation: tests-loading 2s ease-in-out infinite;
        }

        /* Score Panel: enhanced three-column layout */
        .score-panel {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            align-items: center;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.85) 100%);
            border-radius: 20px;
            padding: 24px;
            margin-top: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.6);
            position: relative;
            overflow: hidden;
        }

        .score-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #ff6b35 0%, #f59e0b 50%, #ff8c42 100%);
            border-radius: 20px 20px 0 0;
        }

        @media (min-width: 1024px) {
            .score-panel {
                grid-template-columns: 1fr auto 1fr;
                gap: 32px;
                padding: 32px;
            }
        }

        /* Beautiful enhanced circular percentage meter */
        .score-meter {
            --val: 0;
            --deg: calc(var(--val) * 3.6deg);
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: conic-gradient(#f59e0b var(--deg), rgba(0, 0, 0, 0.04) 0);
            display: grid;
            place-items: center;
            position: relative;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.12),
                0 8px 24px rgba(0, 0, 0, 0.08),
                0 0 0 8px rgba(255, 255, 255, 0.8),
                0 0 0 12px rgba(255, 255, 255, 0.4);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: meter-glow 3s ease-in-out infinite alternate;
        }

        .score-meter:hover {
            transform: scale(1.08) rotate(2deg);
            box-shadow:
                0 25px 80px rgba(0, 0, 0, 0.18),
                0 12px 32px rgba(0, 0, 0, 0.12),
                0 0 0 8px rgba(255, 255, 255, 0.9),
                0 0 0 16px rgba(255, 255, 255, 0.6);
        }

        .score-meter::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.8) 0%, rgba(255, 255, 255, 0.2) 100%);
            z-index: -1;
            opacity: 0.6;
        }

        .score-meter::after {
            content: '';
            position: absolute;
            inset: 16px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #f1f5f9 100%);
            box-shadow:
                inset 0 4px 12px rgba(0, 0, 0, 0.06),
                inset 0 2px 4px rgba(0, 0, 0, 0.04),
                0 2px 8px rgba(255, 255, 255, 0.8);
        }

        @keyframes meter-glow {
            0% { filter: brightness(1) saturate(1); }
            100% { filter: brightness(1.1) saturate(1.2); }
        }

        .score-meter-value {
            position: relative;
            font-size: 1.375rem;
            font-weight: 900;
            color: #1f2937;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            letter-spacing: -0.025em;
            z-index: 10;
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
        }

        /* Enhanced meter color variants with gradients */
        .meter-excellent {
            background: conic-gradient(
                from 0deg,
                #10b981 0deg,
                #059669 calc(var(--deg) * 0.5),
                #047857 var(--deg),
                rgba(0, 0, 0, 0.04) var(--deg)
            );
        }

        .meter-good {
            background: conic-gradient(
                from 0deg,
                #f59e0b 0deg,
                #d97706 calc(var(--deg) * 0.5),
                #b45309 var(--deg),
                rgba(0, 0, 0, 0.04) var(--deg)
            );
        }

        .meter-needs-improvement {
            background: conic-gradient(
                from 0deg,
                #ef4444 0deg,
                #dc2626 calc(var(--deg) * 0.5),
                #b91c1c var(--deg),
                rgba(0, 0, 0, 0.04) var(--deg)
            );
        }

        /* Enhanced left/right columns */
        .score-left, .score-right {
            text-align: center;
            padding: 16px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .score-left:hover, .score-right:hover {
            background: rgba(255, 255, 255, 0.6);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        @media (min-width: 1024px) {
            .score-left { text-align: left; }
            .score-right { text-align: right; }
            .score-left, .score-right { padding: 20px; }
        }

        /* Score info styling */
        .score-info-number {
            font-size: 1.5rem;
            font-weight: 900;
            color: #1f2937;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin: 8px 0 4px 0;
            line-height: 1;
        }

        .score-info-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Date/time styling */
        .test-meta {
            background: rgba(255, 255, 255, 0.7);
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 16px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .test-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
            margin-bottom: 4px;
        }

        .test-meta-item:last-child {
            margin-bottom: 0;
        }

        /* Enhanced user info section */
        .user-info-section {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            backdrop-filter: blur(10px);
        }

        .user-avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #ff6b35 0%, #f59e0b 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 1rem;
            box-shadow: 0 4px 16px rgba(255, 170, 43, 0.3);
            border: 3px solid rgba(255, 255, 255, 0.9);
            position: relative;
        }

        .user-avatar::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.6), rgba(255, 255, 255, 0.2));
            z-index: -1;
        }

        .user-name {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-email {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
        }

        /* Enhanced quiz info card */
        .quiz-info-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.9);
            position: relative;
            overflow: hidden;
        }

        .quiz-info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #ff6b35 0%, #f59e0b 50%, #ff8c42 100%);
        }

        .quiz-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .quiz-info-item {
            background: rgba(255, 255, 255, 0.7);
            border-radius: 12px;
            padding: 16px;
            border: 1px solid rgba(0, 0, 0, 0.04);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
        }

        .quiz-info-item:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .quiz-info-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
            display: block;
        }

        .quiz-info-value {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .quiz-info-icon {
            font-size: 1.125rem;
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
        }
    </style>
@endpush

@section('content')

    <!-- Tests Dashboard with Layout Integration -->
    <div class="tests-dashboard-page">
        <div
        style="background: linear-gradient(135deg, #ff6b35 0%, #f7931e 50%, #ff8c42 100%); border-radius: 20px; padding: 32px; position: relative; overflow: visible; min-height: auto;">
        <!-- Background Pattern -->
        <div
            style="position: absolute; top: 0; left: 0; right: 0; height: 100%; background: radial-gradient(circle at 20% 80%, rgba(255, 107, 53, 0.3) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 50%); pointer-events: none; border-radius: 20px;">
        </div>

        <div style="position: relative; z-index: 10;">
            <!-- Header Section with Statistics -->
            <div class="mb-8">
                <div class="dashboard-header-container">
                    <h1 class="dashboard-header-title">
                        <span class="dashboard-header-icon">📊</span>
                        Tests Dashboard
                    </h1>
                    <p class="dashboard-header-subtitle">
                        Monitor student performance and quiz results with comprehensive analytics
                    </p>
                </div>

                <!-- Statistics Cards -->
                <div class="dashboard-stats-grid">
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-icon">📊</div>
                        <div class="dashboard-stat-number">{{ $tests ? $tests->count() : 0 }}</div>
                        <div class="dashboard-stat-label">Total Tests</div>
                    </div>
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-icon">🎯</div>
                        <div class="dashboard-stat-number">{{ $quizzes ? $quizzes->count() : 0 }}</div>
                        <div class="dashboard-stat-label">Active Quizzes</div>
                    </div>
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-icon">⭐</div>
                        <div class="dashboard-stat-number">
                            @php
                                $avgScore = 0;
                                if ($tests && $tests->count() > 0) {
                                    $totalScore = 0;
                                    $totalQuestions = 0;
                                    foreach ($tests as $test) {
                                        $totalScore += $test->result ?? 0;
                                        $totalQuestions += $test->questions_count ?? 1;
                                    }
                                    $avgScore = $totalQuestions > 0 ? round(($totalScore / $totalQuestions) * 100, 1) : 0;
                                }
                            @endphp
                            {{ $avgScore }}%
                        </div>
                        <div class="dashboard-stat-label">Average Score</div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div
                style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 16px; padding: 24px; margin-bottom: 30px;">
                <div class="flex items-center gap-3 mb-4">
                    <div class="text-2xl">🔍</div>
                    <h3 class="text-lg font-semibold text-white">Filter Tests</h3>
                </div>
                <form id="quiz-form" action="{{ route('submit.leaderboard') }}" method="POST">
                    @csrf
                    @php
                        $route = request()->route()->getName();
                    @endphp
                    <select name="quiz_slug"
                        style="background: rgba(255, 255, 255, 0.9) !important; border: 2px solid rgba(255, 255, 255, 0.3) !important; border-radius: 12px !important; padding: 12px 16px !important; font-size: 14px !important; font-weight: 500 !important; color: #374151 !important; transition: all 0.3s ease !important; backdrop-filter: blur(10px) !important; width: 100%;"
                        onchange="document.getElementById('quiz-form').submit();">
                        <option value="0" {{ $quiz_slug === '0' ? 'selected' : '' }}>🎯 All quizzes</option>
                        @if ($quizzes)
                            @foreach ($quizzes as $quiz)
                                <option value="{{ $quiz->slug }}" {{ $quiz_slug === $quiz->slug ? 'selected' : '' }}>
                                    📚 {{ $quiz->title ?? 'Untitled Quiz' }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <input type="hidden" name="route" value="{{ $route }}">
                </form>
            </div>
            <!-- Tests Cards Section -->
            <div class="space-y-4">
                @forelse($tests as $test)
                    <div
                        style="background: rgba(255, 255, 255, 0.95); border-radius: 16px; padding: 24px;
                        margin-bottom: 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); border: 1px solid rgba(255, 255, 255, 0.3);
                        transition: all 0.3s ease; position: relative; overflow: hidden; border-left: 4px solid #f59e0b;">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <!-- User Info -->
                            <div class="flex-1">
                                <!-- Enhanced User Info Section -->
                                <div class="user-info-section">
                                    <div class="flex items-center gap-4">
                                        <div class="user-avatar">
                                            {{ $test->id ?? 'N/A' }}
                                        </div>
                                        <div class="flex-1">
                                            @php
                                                if ($test->type_user === 'user' && isset($test->user)) {
                                                    $testUser = $test->user;
                                                } elseif ($test->type_user === 'student' && isset($test->student)) {
                                                    $testUser = $test->student;
                                                } else {
                                                    $testUser = $test->user ?? null;
                                                }
                                            @endphp
                                            <h3 class="user-name">
                                                <span class="text-lg">👤</span>
                                                {{ $testUser ? $testUser->nickname ?? ($testUser->name ?? 'Unknown User') : 'Unknown User' }}
                                            </h3>
                                            <p class="user-email">
                                                {{ $testUser ? $testUser->email ?? ($testUser->name ?? 'N/A') : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Enhanced Quiz Info Card -->
                                <div class="quiz-info-card">
                                    <div class="quiz-info-grid">
                                        <div class="quiz-info-item">
                                            <label class="quiz-info-label">Quiz</label>
                                            <div class="quiz-info-value">
                                                <span class="quiz-info-icon">📚</span>
                                                <span>{{ $test->quiz->title ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="quiz-info-item">
                                            <label class="quiz-info-label">Grade</label>
                                            <div class="quiz-info-value">
                                                <span class="quiz-info-icon">🎓</span>
                                                <span>{{ $test->quiz->level ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="quiz-info-item">
                                            <label class="quiz-info-label">Term</label>
                                            <div class="quiz-info-value">
                                                <span class="quiz-info-icon">📅</span>
                                                <span>{{ $test->quiz->term ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="quiz-info-item">
                                            <label class="quiz-info-label">Level</label>
                                            <div class="quiz-info-value">
                                                <span class="quiz-info-icon">📖</span>
                                                <span>{{ $test->quiz->section ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Score and Actions -->
                            <div class="score-panel">
                                @php
                                    $score = $test->result ?? 0;
                                    $total = $test->questions_count ?? 1;
                                    $percentage = $total > 0 ? round(($score / $total) * 100) : 0;

                                    if ($percentage >= 80) {
                                        $emoji = '🌟';
                                        $label = 'Excellent';
                                        $scoreClass = 'score-badge score-excellent';
                                        $meterClass = 'meter-excellent';
                                    } elseif ($percentage >= 60) {
                                        $emoji = '👍';
                                        $label = 'Good';
                                        $scoreClass = 'score-badge score-good';
                                        $meterClass = 'meter-good';
                                    } else {
                                        $emoji = '📈';
                                        $label = 'Needs Improvement';
                                        $scoreClass = 'score-badge score-needs-improvement';
                                        $meterClass = 'meter-needs-improvement';
                                    }
                                @endphp

                                <!-- Left: score info -->
                                <div class="score-left">
                                    <div class="{{ $scoreClass }} mb-3 inline-flex">{{ $emoji }} {{ $label }}</div>
                                    <div class="score-info-number">{{ $score }}/{{ $total }}</div>
                                    <div class="score-info-label">Score</div>
                                </div>

                                <!-- Center: circular percentage meter -->
                                <div class="flex items-center justify-center">
                                    <div class="score-meter {{ $meterClass }}" style="--val: {{ $percentage }};">
                                        <div class="score-meter-value">{{ $percentage }}%</div>
                                    </div>
                                </div>

                                <!-- Right: date and action -->
                                <div class="score-right">
                                    <div class="test-meta">
                                        <div class="test-meta-item">
                                            <span>📅</span>
                                            <span>{{ $test->created_at ? $test->created_at->format('D d/m/Y, h:i A') : 'N/A' }}</span>
                                        </div>
                                        @php
                                            $timeSpent = $test->time_spent ?? 0;
                                            $hour = intdiv($timeSpent, 3600);
                                            $minutes = intdiv($timeSpent % 3600, 60);
                                            $seconds = $timeSpent % 60;
                                        @endphp
                                        <div class="test-meta-item">
                                            <span>⏱️</span>
                                            <span>{{ sprintf('%02d:%02d:%02d', $hour, $minutes, $seconds) }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('results.show', $test) }}" class="action-btn inline-flex">View Details 👁️</a>
                                </div>
                            </div>
                        </div>
                    </div>

                @empty
                    <!-- Beautiful Empty State -->
                    <div
                        style="text-align: center; padding: 60px 20px; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 2px dashed rgba(255, 255, 255, 0.3); border-radius: 20px; margin: 40px 0;">
                        <div class="text-6xl mb-4">📊</div>
                        <h3 class="text-2xl font-bold text-white mb-2">No Tests Found</h3>
                        <p class="text-white/80 mb-6">There are no test results to display at the moment.</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-4xl mx-auto text-left">
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                                <div class="text-2xl mb-2">🎯</div>
                                <h4 class="font-semibold text-white mb-2">Create Quizzes</h4>
                                <p class="text-white/80 text-sm">Start by creating engaging quizzes for your students to
                                    take.</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                                <div class="text-2xl mb-2">👥</div>
                                <h4 class="font-semibold text-white mb-2">Invite Students</h4>
                                <p class="text-white/80 text-sm">Share quiz links with students to start collecting test
                                    results.
                                </p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                                <div class="text-2xl mb-2">📈</div>
                                <h4 class="font-semibold text-white mb-2">Track Progress</h4>
                                <p class="text-white/80 text-sm">Monitor student performance and identify areas for
                                    improvement.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

@endsection
@push('scripts')
@endpush
