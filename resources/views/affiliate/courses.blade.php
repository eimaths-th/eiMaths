@extends('affiliate.frontend.main')
@section('style')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        :root {
            --primary-orange: #FF7B00;
            --primary-dark-orange: #E66000;
            --primary-light-orange: #FFF7ED;
            --accent-orange: #FF9E2C;
            --dark-text: #1E293B;
            --muted-text: #64748B;
            --border-color: #E2E8F0;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
            --card-hover-shadow: 0 20px 35px -5px rgba(255, 123, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        body {
            font-family: 'Prompt', 'Kanit', system-ui, -apple-system, sans-serif;
            color: var(--dark-text);
            background-color: #F8FAFC;
        }

        #loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 99999;
        }

        #loading .loading-content {
            background: linear-gradient(135deg, #FF8C00, #FF6A00);
            padding: 35px 55px;
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
        }

        .spinner {
            width: 44px;
            height: 44px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #ffffff;
            border-radius: 50%;
            animation: spin 0.9s linear infinite;
            margin: 10px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Affiliate Welcome Banner */
        .affiliate-welcome-bar {
            background: linear-gradient(90deg, #10B981 0%, #059669 100%);
            color: #ffffff;
            padding: 0.75rem 1rem;
            text-align: center;
            font-size: 0.95rem;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
            position: relative;
            z-index: 10;
        }

        .affiliate-welcome-bar .ref-badge {
            background: rgba(255, 255, 255, 0.25);
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            font-weight: 700;
            margin: 0 0.35rem;
            letter-spacing: 0.5px;
        }

        /* Hero Banner Section */
        .courses-hero-section {
            background: linear-gradient(135deg, #FFF7ED 0%, #FFEDD5 50%, #FED7AA 100%);
            padding: 4rem 0 3.5rem;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(255, 140, 0, 0.15);
        }

        .courses-hero-section::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(255, 123, 0, 0.12) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .courses-hero-section::after {
            content: '';
            position: absolute;
            bottom: -60px;
            left: -60px;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(255, 166, 46, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .courses-hero-section > .container {
            position: relative;
            z-index: 1;
        }

        .hero-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(380px, 0.95fr);
            align-items: center;
            gap: clamp(2rem, 4vw, 4.5rem);
        }

        .hero-copy {
            min-width: 0;
            text-align: left;
        }

        .hero-badge-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #ffffff;
            color: #EA580C;
            padding: 0.45rem 1.1rem;
            border-radius: 999px;
            font-size: 0.88rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(234, 88, 12, 0.12);
            margin-bottom: 1.25rem;
            border: 1px solid rgba(234, 88, 12, 0.15);
        }

        .hero-title {
            font-size: 2.75rem;
            font-weight: 800;
            color: #0F172A;
            line-height: 1.25;
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }

        .hero-title .text-gradient {
            background: linear-gradient(135deg, #FF7B00, #EA580C);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.15rem;
            color: #475569;
            max-width: 720px;
            margin: 0 0 2rem;
            line-height: 1.6;
        }

        .hero-features {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .hero-feature-item {
            background: #ffffff;
            padding: 0.65rem 1.25rem;
            border-radius: 14px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .hero-feature-item i {
            color: #EA580C;
            font-size: 1.1rem;
        }

        .hero-slider-wrap {
            position: relative;
            width: 100%;
            min-width: 0;
            padding: 0.65rem;
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(255, 255, 255, 0.95);
            box-shadow: 0 24px 55px rgba(154, 52, 18, 0.18);
            backdrop-filter: blur(10px);
        }

        .hero-slider {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 10;
            overflow: hidden;
            border-radius: 23px;
            background: #FED7AA;
        }

        .hero-slide {
            position: absolute;
            inset: 0;
            margin: 0;
            opacity: 0;
            visibility: hidden;
            transform: scale(1.035);
            transition: opacity 0.85s ease, transform 1.1s ease, visibility 0.85s;
        }

        .hero-slide.is-active {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
            z-index: 1;
        }

        .hero-slide img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        @media (prefers-reduced-motion: reduce) {
            .hero-slide {
                transition: none;
                transform: none;
            }
        }

        .hero-slider-dots {
            position: absolute;
            left: 50%;
            bottom: 1.25rem;
            z-index: 3;
            display: flex;
            gap: 0.45rem;
            transform: translateX(-50%);
            padding: 0.38rem 0.55rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.42);
            backdrop-filter: blur(6px);
        }

        .hero-slider-dot {
            width: 8px;
            height: 8px;
            padding: 0;
            border: 0;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.58);
            cursor: pointer;
            transition: width 0.25s ease, background-color 0.25s ease;
        }

        .hero-slider-dot.is-active {
            width: 24px;
            background: #ffffff;
        }

        .hero-slider-dot:focus-visible {
            outline: 3px solid rgba(255, 255, 255, 0.9);
            outline-offset: 3px;
        }

        .hero-photo-label {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            z-index: 3;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.5rem 0.8rem;
            color: #ffffff;
            background: rgba(234, 88, 12, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 999px;
            box-shadow: 0 8px 20px rgba(124, 45, 18, 0.2);
            font-size: 0.76rem;
            font-weight: 600;
            backdrop-filter: blur(6px);
        }

        /* Filter & Search Bar */
        .filter-section {
            background: #ffffff;
            border-radius: 20px;
            padding: 1.25rem 1.5rem;
            margin-top: -2rem;
            position: relative;
            z-index: 5;
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.08);
            border: 1px solid #F1F5F9;
        }

        .category-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }

        .category-tab-btn {
            background: #F1F5F9;
            border: 1px solid transparent;
            color: #475569;
            padding: 0.6rem 1.2rem;
            border-radius: 12px;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .category-tab-btn:hover {
            background: #E2E8F0;
            color: #0F172A;
        }

        .category-tab-btn.active {
            background: linear-gradient(135deg, #FF8C00, #FF6A00);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(255, 106, 0, 0.3);
        }

        .search-box-wrap {
            position: relative;
        }

        .search-box-wrap input {
            width: 100%;
            border-radius: 12px;
            border: 1.5px solid #E2E8F0;
            padding: 0.65rem 1rem 0.65rem 2.6rem;
            font-size: 0.92rem;
            transition: all 0.2s ease;
            background: #F8FAFC;
        }

        .search-box-wrap input:focus {
            background: #ffffff;
            border-color: #FF7B00;
            box-shadow: 0 0 0 3px rgba(255, 123, 0, 0.15);
            outline: none;
        }

        .search-box-wrap i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 0.95rem;
        }

        /* Course Cards Section */
        .courses-main-container {
            padding: 3rem 0 5rem;
        }

        .courses-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .courses-result-count {
            font-size: 1rem;
            font-weight: 600;
            color: #64748B;
        }

        .courses-result-count span {
            color: #EA580C;
            font-weight: 700;
            font-size: 1.15rem;
        }

        .courses-grid-modern {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.75rem;
        }

        .course-modern-card {
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #E2E8F0;
            box-shadow: var(--card-shadow);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .course-modern-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--card-hover-shadow);
            border-color: #FED7AA;
        }

        .course-card-thumb-wrap {
            position: relative;
            height: 200px;
            overflow: hidden;
            background: #F1F5F9;
        }

        .course-card-thumb-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .course-modern-card:hover .course-card-thumb-wrap img {
            transform: scale(1.05);
        }

        .course-level-badge {
            position: absolute;
            top: 0.85rem;
            left: 0.85rem;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .course-price-badge-top {
            position: absolute;
            bottom: 0.85rem;
            right: 0.85rem;
            background: linear-gradient(135deg, #FF8C00, #EA580C);
            color: #ffffff;
            font-size: 1.05rem;
            font-weight: 800;
            padding: 0.4rem 0.9rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 2;
        }

        .course-card-body {
            padding: 1.35rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .course-card-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 0.85rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.8rem;
        }

        .course-features-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 1px dashed #E2E8F0;
            flex-grow: 1;
        }

        .course-feat-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #64748B;
        }

        .course-feat-row i {
            width: 18px;
            color: #EA580C;
            text-align: center;
            flex: 0 0 18px;
        }

        .course-delivery-meta {
            display: inline-flex;
            align-items: center;
            align-self: flex-start;
            gap: 0.4rem;
            color: #475569;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 999px;
            padding: 0.35rem 0.7rem;
            margin: -0.35rem 0 0.9rem;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .category-tab-btn:focus-visible,
        .btn-card-primary:focus-visible,
        .premium-footer .social-link:focus-visible {
            outline: 3px solid rgba(255, 123, 0, 0.35);
            outline-offset: 3px;
        }

        .course-card-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .btn-card-primary {
            flex: 1;
            background: linear-gradient(135deg, #FF8C00 0%, #FF6A00 100%);
            color: #ffffff;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.92rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 15px rgba(255, 106, 0, 0.25);
            cursor: pointer;
            text-decoration: none;
        }

        .btn-card-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 106, 0, 0.35);
            color: #ffffff;
        }

        /* Empty State */
        .no-courses-wrap {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 2rem;
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid #E2E8F0;
            box-shadow: var(--card-shadow);
        }

        .no-courses-icon-wrap {
            width: 80px;
            height: 80px;
            background: #FFF7ED;
            color: #EA580C;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin: 0 auto 1.5rem;
        }

        /* Footer */
        .premium-footer {
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            color: #F8FAFC;
            position: relative;
            overflow: hidden;
            border-top: 3px solid #FF8C00;
        }

        .premium-footer .footer-content {
            padding: 3.5rem 0 2rem;
        }

        .premium-footer .footer-title {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 1.2rem;
        }

        .premium-footer .footer-text {
            color: #94A3B8;
            line-height: 1.7;
            font-size: 0.92rem;
        }

        .premium-footer .social-links {
            display: flex;
            gap: 0.75rem;
        }

        .premium-footer .social-link {
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #CBD5E1;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .premium-footer .social-link:hover {
            background: #FF8C00;
            color: #ffffff;
            transform: translateY(-3px);
            border-color: #FF8C00;
        }

        .premium-footer .footer-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 2rem 0 1.5rem;
        }

        .premium-footer .copyright {
            text-align: center;
            color: #64748B;
            font-size: 0.85rem;
        }

        @media (max-width: 991.98px) {
            .hero-layout {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .hero-copy {
                text-align: center;
            }
            .hero-subtitle {
                margin-left: auto;
                margin-right: auto;
            }
            .hero-features {
                justify-content: center;
            }
            .hero-slider-wrap {
                width: min(100%, 720px);
                margin: 0 auto;
            }
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            .hero-subtitle {
                font-size: 1rem;
            }
            .courses-hero-section {
                padding: 3rem 0 2.5rem;
            }
            .filter-section {
                margin-top: -1rem;
                padding: 1rem;
            }
            .category-tabs {
                width: 100%;
                justify-content: flex-start;
            }
            .search-box-wrap {
                width: 100%;
                margin-top: 0.75rem;
            }
            .courses-grid-modern {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 576px) {
            .affiliate-welcome-bar {
                padding: 0.55rem 0.5rem;
                font-size: 0.8rem;
                line-height: 1.45;
            }
            .affiliate-welcome-bar .container {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: center;
                gap: 0.2rem 0.3rem;
            }
            .affiliate-welcome-bar .ref-badge {
                margin: 0;
            }
            .courses-hero-section {
                padding: 1.75rem 0 1.5rem;
            }
            .hero-layout {
                gap: 1.25rem;
            }
            .hero-badge-tag {
                font-size: 0.78rem;
                line-height: 1.35;
                margin-bottom: 0.9rem;
                padding: 0.4rem 0.8rem;
            }
            .hero-title {
                font-size: 1.7rem;
                line-height: 1.3;
                margin-bottom: 0.7rem;
            }
            .hero-subtitle {
                font-size: 0.9rem;
                line-height: 1.55;
                margin-bottom: 1.1rem;
            }
            .hero-features {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.55rem;
                margin-top: 0.75rem;
            }
            .hero-feature-item {
                width: 100%;
                min-height: 58px;
                justify-content: center;
                padding: 0.55rem 0.45rem;
                font-size: 0.74rem;
                line-height: 1.35;
            }
            .hero-slider-wrap {
                padding: 0.4rem;
                border-radius: 20px;
            }
            .hero-slider {
                aspect-ratio: 4 / 3;
                border-radius: 16px;
            }
            .hero-photo-label {
                top: 0.85rem;
                right: 0.85rem;
                font-size: 0.68rem;
                padding: 0.4rem 0.65rem;
            }
            .hero-slider-dots {
                bottom: 0.9rem;
            }
            .filter-section {
                margin-top: -0.75rem;
                border-radius: 16px;
            }
            .category-tabs {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.5rem;
            }
            .category-tab-btn {
                width: 100%;
                min-height: 48px;
                justify-content: center;
                padding: 0.5rem;
                font-size: 0.78rem;
                line-height: 1.3;
            }
            .search-box-wrap {
                margin-top: 0;
            }
            .courses-main-container {
                padding: 2rem 0 3rem;
            }
            .courses-section-header {
                margin-bottom: 1.25rem;
            }
            .courses-grid-modern {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Loading Overlay -->
    <div id="loading" style="display: none;">
        <div class="loading-content">
            <div class="spinner"></div>
            <p class="text-white mb-0 mt-2" style="font-size: 1.1rem; font-weight: 600;">{{ __('course-website.loading.title') }}</p>
            <p class="text-white mb-0" style="font-size: 0.88rem; opacity: 0.9;">{{ __('course-website.loading.message') }}</p>
        </div>
    </div>

    @php
        $ref = request('ref', '');
    @endphp

    <!-- Affiliate Welcome Top Ribbon -->
    @if (!empty($ref))
        <div class="affiliate-welcome-bar">
            <div class="container">
                <i class="fas fa-gift text-warning me-2"></i>
                <span>{{ __('course-website.ribbon.partner_benefit') }}</span>
                <span class="ref-badge">Ref: {{ $ref }}</span>
                <span>{{ __('course-website.ribbon.free_consultation') }}</span>
            </div>
        </div>
    @endif

    <!-- Modern Hero Section -->
    <section class="courses-hero-section">
        <div class="container">
            <div class="hero-layout">
                <div class="hero-copy">
                    <div class="hero-badge-tag">
                        <i class="fas fa-award"></i>
                        <span>{{ __('course-website.hero.badge') }}</span>
                    </div>
                    <h1 class="hero-title">
                        {{ __('course-website.hero.title') }} <span class="text-gradient">{{ __('course-website.hero.title_highlight') }}</span>
                    </h1>
                    <p class="hero-subtitle">
                        {{ __('course-website.hero.subtitle') }}
                    </p>

                    <div class="hero-features">
                        <div class="hero-feature-item">
                            <i class="fas fa-shapes"></i>
                            <span>{{ __('course-website.hero.features.cpa') }}</span>
                        </div>
                        <div class="hero-feature-item">
                            <i class="fas fa-brain"></i>
                            <span>{{ __('course-website.hero.features.analysis') }}</span>
                        </div>
                        <div class="hero-feature-item">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>{{ __('course-website.hero.features.teachers') }}</span>
                        </div>
                        <div class="hero-feature-item">
                            <i class="fas fa-chart-line"></i>
                            <span>{{ __('course-website.hero.features.assessment') }}</span>
                        </div>
                    </div>
                </div>

                <div class="hero-slider-wrap" data-hero-slider aria-label="{{ __('course-website.hero.slider_label') }}">
                    <div class="hero-photo-label"><i class="fas fa-camera"></i> {{ __('course-website.hero.photo_label') }}</div>
                    <div class="hero-slider">
                        <figure class="hero-slide is-active" aria-hidden="false">
                            <img src="{{ asset('uploads/affiliate/hero/eimaths-teacher-high-five.webp') }}"
                                alt="{{ __('course-website.hero.image_1_alt') }}" width="1600" height="1067"
                                loading="eager" fetchpriority="high" decoding="async">
                        </figure>
                        <figure class="hero-slide" aria-hidden="true">
                            <img src="{{ asset('uploads/affiliate/hero/eimaths-class-thumbs-up.webp') }}"
                                alt="{{ __('course-website.hero.image_2_alt') }}" width="1600" height="1067"
                                loading="lazy" decoding="async">
                        </figure>
                        <figure class="hero-slide" aria-hidden="true">
                            <img src="{{ asset('uploads/affiliate/hero/eimaths-cpa-shapes.webp') }}"
                                alt="{{ __('course-website.hero.image_3_alt') }}" width="1600" height="1067"
                                loading="lazy" decoding="async">
                        </figure>
                        <figure class="hero-slide" aria-hidden="true">
                            <img src="{{ asset('uploads/affiliate/hero/eimaths-student-group.webp') }}"
                                alt="{{ __('course-website.hero.image_4_alt') }}" width="1600" height="1067"
                                loading="lazy" decoding="async">
                        </figure>
                    </div>
                    <div class="hero-slider-dots" role="group" aria-label="{{ __('course-website.hero.dots_label') }}">
                        <button type="button" class="hero-slider-dot is-active" data-slide-index="0" aria-label="{{ __('course-website.hero.dot_label', ['number' => 1]) }}" aria-current="true"></button>
                        <button type="button" class="hero-slider-dot" data-slide-index="1" aria-label="{{ __('course-website.hero.dot_label', ['number' => 2]) }}"></button>
                        <button type="button" class="hero-slider-dot" data-slide-index="2" aria-label="{{ __('course-website.hero.dot_label', ['number' => 3]) }}"></button>
                        <button type="button" class="hero-slider-dot" data-slide-index="3" aria-label="{{ __('course-website.hero.dot_label', ['number' => 4]) }}"></button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Container -->
    <div class="container">
        <!-- Interactive Filter & Search Bar -->
        <div class="filter-section">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <div class="category-tabs" id="categoryTabs" role="group" aria-label="{{ __('course-website.filters.aria_label') }}">
                        <button type="button" class="category-tab-btn active" data-category="all" aria-pressed="true">
                            <i class="fas fa-th-large"></i> {{ __('course-website.filters.all') }}
                        </button>
                        <button type="button" class="category-tab-btn" data-category="kindergarten" aria-pressed="false">
                            <i class="fas fa-shapes"></i> {{ __('course-website.filters.kindergarten') }}
                        </button>
                        <button type="button" class="category-tab-btn" data-category="primary_lower" aria-pressed="false">
                            <i class="fas fa-pencil-alt"></i> {{ __('course-website.filters.primary_lower') }}
                        </button>
                        <button type="button" class="category-tab-btn" data-category="primary_upper" aria-pressed="false">
                            <i class="fas fa-calculator"></i> {{ __('course-website.filters.primary_upper') }}
                        </button>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="search-box-wrap">
                        <i class="fas fa-search"></i>
                        <label for="courseSearchInput" class="visually-hidden">{{ __('course-website.filters.search_label') }}</label>
                        <input type="search" id="courseSearchInput" placeholder="{{ __('course-website.filters.search_placeholder') }}" autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Grid Container -->
        <div class="courses-main-container">
            <div class="courses-section-header">
                <div>
                    <h2 class="fw-bold mb-1" style="color: #0F172A; font-size: 1.5rem;">{{ __('course-website.courses.title') }}</h2>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">{{ __('course-website.courses.subtitle') }}</p>
                </div>
                <div class="courses-result-count">
                    {{ __('course-website.courses.available_prefix') }} <span id="activeCoursesCount">{{ method_exists($courses, 'total') ? $courses->total() : (is_countable($courses) ? count($courses) : 0) }}</span> {{ __('course-website.courses.available_suffix') }}
                </div>
            </div>

            <!-- Modern Courses Grid -->
            <div class="courses-grid-modern" id="coursesGrid">
                @if(isset($courses) && count($courses) > 0)
                    @foreach($courses as $course)
                        @php
                            $displayTitle = preg_replace('/\s+/u', ' ', trim((string) $course->title));
                            $rawThumb = $course->thumbnail ?: ($course->image ?? '');
                            $webThumb = $rawThumb ? str_replace(['public\\', 'public/', '\\'], ['', '', '/'], $rawThumb) : '';
                            $fullThumbPath = $webThumb ? public_path($webThumb) : '';
                            $hasThumb = $webThumb && file_exists($fullThumbPath);
                            $imgSrc = $hasThumb ? asset($webThumb) : asset('uploads/ei-defult.jpg');

                            // Detect Level Category for Filtering (from DB category_id & Title keywords)
                            $catId = $course->category_id ?? null;
                            $catClass = 'cat-general';
                            $levelLabel = __('course-website.courses.level_general');

                            if ($catId == 4 || preg_match('/K[1-4]|KINDER|อนุบาล/i', $course->title)) {
                                $catClass = 'cat-kindergarten';
                                $levelLabel = __('course-website.courses.level_kindergarten');
                            } elseif ($catId == 1 || preg_match('/P[1-3]|PRIMARY\s*[1-3]|ประถม\s*[1-3]|ประถมต้น/i', $course->title)) {
                                $catClass = 'cat-primary_lower';
                                $levelLabel = __('course-website.courses.level_primary_lower');
                            } elseif ($catId == 2 || preg_match('/P[4-6]|PRIMARY\s*[4-6]|ประถม\s*[4-6]|ประถมปลาย/i', $course->title)) {
                                $catClass = 'cat-primary_upper';
                                $levelLabel = __('course-website.courses.level_primary_upper');
                            }

                            $aboutText = trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($course->about ?? ''))));
                            $outcomesText = trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($course->affiliate_outcomes ?? ''))));
                            $requirementsText = trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($course->affiliate_requirements ?? ''))));
                            $aboutText = $aboutText ?: __('course-website.courses.fallback_about');
                            $outcomesText = $outcomesText ?: __('course-website.courses.fallback_outcomes');
                            $requirementsText = $requirementsText ?: __('course-website.courses.fallback_requirements');
                            $deliveryLabel = match ((int) $course->mode_of_delivery) {
                                1 => __('course-website.courses.delivery_online'),
                                2 => __('course-website.courses.delivery_distance'),
                                default => __('course-website.courses.delivery_branch'),
                            };
                            $searchText = mb_strtolower(implode(' ', [
                                $displayTitle,
                                $levelLabel,
                                $aboutText,
                                $outcomesText,
                                $requirementsText,
                                $deliveryLabel,
                            ]), 'UTF-8');
                        @endphp

                        <div class="course-item-wrapper {{ $catClass }}" data-search="{{ $searchText }}" data-category="{{ $catClass }}">
                            <div class="course-modern-card">
                                <div class="course-card-thumb-wrap">
                                    <span class="course-level-badge">
                                        <i class="fas fa-graduation-cap"></i> {{ $levelLabel }}
                                    </span>
                                    <img src="{{ $imgSrc }}" alt="{{ __('course-website.courses.cover_alt', ['title' => $displayTitle]) }}" loading="lazy" onerror="this.src='{{ asset('uploads/ei-defult.jpg') }}';">
                                    <div class="course-price-badge-top">
                                        {{ number_format($course->price) }} ฿
                                    </div>
                                </div>

                                <div class="course-card-body">
                                    <h3 class="course-card-title" title="{{ $displayTitle }}">{{ $displayTitle }}</h3>
                                    <div class="course-delivery-meta">
                                        <i class="fas {{ (int) $course->mode_of_delivery === 1 ? 'fa-laptop' : ((int) $course->mode_of_delivery === 2 ? 'fa-video' : 'fa-school') }}"></i>
                                        <span>{{ $deliveryLabel }}</span>
                                    </div>

                                    <div class="course-features-list">
                                        <div class="course-feat-row">
                                            <i class="fas fa-book-reader"></i>
                                            <span>{{ \Illuminate\Support\Str::limit($aboutText, 105) }}</span>
                                        </div>
                                        <div class="course-feat-row">
                                            <i class="fas fa-cubes"></i>
                                            <span>{{ \Illuminate\Support\Str::limit($outcomesText, 105) }}</span>
                                        </div>
                                        <div class="course-feat-row">
                                            <i class="fas fa-user-check"></i>
                                            <span>{{ \Illuminate\Support\Str::limit($requirementsText, 105) }}</span>
                                        </div>
                                    </div>

                                    <div class="course-card-actions">
                                        <button type="button"
                                                class="btn-card-primary btn-open-booking"
                                                data-course-id="{{ $course->id }}"
                                                data-title="{{ $displayTitle }}"
                                                data-price="{{ $course->price }}"
                                                data-price-formatted="{{ number_format($course->price) }} ฿">
                                            <i class="fas fa-calendar-check"></i>
                                            <span>{{ __('course-website.courses.book_register') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="no-courses-wrap">
                        <div class="no-courses-icon-wrap">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">{{ __('course-website.courses.empty_title') }}</h4>
                        <p class="text-muted mb-0">{{ __('course-website.courses.empty_message') }}</p>
                    </div>
                @endif
            </div>

            <!-- Client Filter Empty State (Hidden by Default) -->
            <div id="noFilterResults" class="no-courses-wrap mt-3" style="display: none;">
                <div class="no-courses-icon-wrap">
                    <i class="fas fa-search-minus"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">{{ __('course-website.courses.filter_empty_title') }}</h4>
                <p class="text-muted mb-3">{{ __('course-website.courses.filter_empty_message') }}</p>
                <button type="button" class="btn btn-outline-warning rounded-pill px-4" id="btnResetFilter">
                    <i class="fas fa-redo me-1"></i> {{ __('course-website.courses.reset') }}
                </button>
            </div>

            <!-- Pagination (if applicable) -->
            @if(isset($courses) && method_exists($courses, 'links'))
                <div class="d-flex justify-content-center mt-5">
                    {{ $courses->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modern Clean Footer -->
    <footer class="premium-footer">
        <div class="container">
            <div class="footer-content">
                <div class="row g-4">
                    <!-- Column 1: Logo & About -->
                    <div class="col-lg-4 text-center text-lg-start">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ __('course-website.footer.logo_alt') }}" class="mb-3" style="max-width: 170px; height: auto;">
                        <p class="footer-text">{{ $footer->description ?? __('course-website.footer.about_fallback') }}</p>
                    </div>

                    <!-- Column 2: Info -->
                    <div class="col-lg-4 text-center text-lg-start">
                        <h5 class="footer-title">{{ $footer->title ?? __('course-website.footer.contact_title') }}</h5>
                        <div class="footer-text">{{ $footer->description2 ?? __('course-website.footer.contact_fallback') }}</div>
                    </div>

                    <!-- Column 3: Social Media -->
                    <div class="col-lg-4 text-center text-lg-start">
                        <h5 class="footer-title">{{ __('course-website.footer.social_title') }}</h5>
                        <p class="footer-text mb-3">{{ __('course-website.footer.social_description') }}</p>
                        <div class="social-links justify-content-center justify-content-lg-start">
                            <a href="https://www.facebook.com/eimaths.th/?locale=th_TH" target="_blank" rel="noopener noreferrer" class="social-link" title="Facebook" aria-label="{{ __('course-website.footer.facebook_aria') }}">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://www.youtube.com/channel/UCiD8a78ZN6SNQViVmG1St0A" target="_blank" rel="noopener noreferrer" class="social-link" title="YouTube" aria-label="{{ __('course-website.footer.youtube_aria') }}">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a href="https://www.tiktok.com/@eimaths.th" target="_blank" rel="noopener noreferrer" class="social-link" title="TikTok" aria-label="{{ __('course-website.footer.tiktok_aria') }}">
                                <i class="fab fa-tiktok"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <hr class="footer-divider">

                <div class="copyright">
                    <p class="mb-0">© {{ date('Y') }} eiMaths Thailand. {{ __('course-website.footer.copyright') }}</p>
                </div>
            </div>
        </div>
    </footer>

    @if ($promotion->count() > 0)
        @include('affiliate.frontend.promotion_modal')
    @endif
@endsection

@section('script')
    @php
        $course_website_ui = [
            'registered_title_prefix' => __('course-website.messages.registered_title_prefix'),
            'period_placeholder' => __('course-website.messages.period_placeholder'),
            'saving' => __('course-website.messages.saving'),
            'submit' => __('course-website.booking.submit'),
            'cannot_process' => __('course-website.messages.cannot_process'),
            'check_input' => __('course-website.messages.check_input'),
            'unexpected_error' => __('course-website.messages.unexpected_error'),
            'server_error' => __('course-website.messages.server_error'),
        ];
    @endphp
    <script>
        const course_website_ui = {!! json_encode($course_website_ui, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const slider = document.querySelector('[data-hero-slider]');
            if (!slider) return;

            const slides = Array.from(slider.querySelectorAll('.hero-slide'));
            const dots = Array.from(slider.querySelectorAll('.hero-slider-dot'));
            let activeIndex = 0;
            let rotationTimer = null;

            const showSlide = (nextIndex) => {
                activeIndex = (nextIndex + slides.length) % slides.length;
                slides.forEach((slide, index) => {
                    const isActive = index === activeIndex;
                    slide.classList.toggle('is-active', isActive);
                    slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                });
                dots.forEach((dot, index) => {
                    const isActive = index === activeIndex;
                    dot.classList.toggle('is-active', isActive);
                    if (isActive) {
                        dot.setAttribute('aria-current', 'true');
                    } else {
                        dot.removeAttribute('aria-current');
                    }
                });
            };

            const stopRotation = () => {
                if (rotationTimer !== null) {
                    window.clearInterval(rotationTimer);
                    rotationTimer = null;
                }
            };

            const startRotation = () => {
                stopRotation();
                if (!document.hidden && slides.length > 1) {
                    rotationTimer = window.setInterval(() => showSlide(activeIndex + 1), 5000);
                }
            };

            dots.forEach((dot) => {
                dot.addEventListener('click', () => {
                    showSlide(Number(dot.dataset.slideIndex || 0));
                    startRotation();
                });
            });

            slider.addEventListener('mouseenter', stopRotation);
            slider.addEventListener('mouseleave', startRotation);
            slider.addEventListener('focusin', stopRotation);
            slider.addEventListener('focusout', startRotation);
            document.addEventListener('visibilitychange', () => document.hidden ? stopRotation() : startRotation());

            startRotation();
        });
    </script>

    <script>
        $(document).ready(function() {
            // Auto open Promo Modal if available
            const promoEl = document.getElementById('promoModal');
            if (promoEl && window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(promoEl).show();
            } else if (window.jQuery && $('#promoModal').length) {
                $('#promoModal').modal('show');
            }
        });
    </script>

    <script>
        // Global cleanup to prevent lingering backdrops/body lock
        function cleanupBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('paddingRight');
        }

        document.addEventListener('hidden.bs.modal', () => {
            setTimeout(() => {
                if (!document.querySelector('.modal.show')) {
                    cleanupBackdrops();
                }
            }, 50);
        });

        const modalLineEl = document.getElementById('modalLineContact');
        if (modalLineEl) {
            modalLineEl.addEventListener('hidden.bs.modal', () => {
                cleanupBackdrops();
                if (window.bootstrap && bootstrap.Modal) {
                    const inst = bootstrap.Modal.getInstance(modalLineEl);
                    if (inst) inst.dispose();
                }
            });
        }
    </script>

    <script>
        // Modal Trigger & Data Population
        document.addEventListener('DOMContentLoaded', () => {
            const bookingButtons = document.querySelectorAll('.btn-open-booking');
            bookingButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    const btn = e.currentTarget;
                    const title = btn.getAttribute('data-title') || '';
                    const courseId = btn.getAttribute('data-course-id') || '';
                    const price = btn.getAttribute('data-price') || '';
                    const priceFormatted = btn.getAttribute('data-price-formatted') || (price + ' ฿');

                    // Fill form fields
                    $('#course_id').val(courseId);
                    $('#course_name').val(title);
                    $('#course_price').val(price);

                    // Update UI Display Elements in modal
                    $('#modalCourseTitleDisplay').text(title);
                    $('#modalCoursePriceDisplay').text(priceFormatted);
                    $('#purchaseModalLabel').text(course_website_ui.registered_title_prefix + title);

                    // Hide open modals
                    document.querySelectorAll('.modal.show').forEach(el => {
                        if (window.bootstrap && bootstrap.Modal) {
                            bootstrap.Modal.getOrCreateInstance(el).hide();
                        } else if (window.jQuery) {
                            $(el).modal('hide');
                        }
                    });

                    // Show Receipt/Booking Modal
                    const receiptEl = document.getElementById('receiptModal');
                    if (receiptEl && window.bootstrap && bootstrap.Modal) {
                        bootstrap.Modal.getOrCreateInstance(receiptEl).show();
                    } else if (window.jQuery) {
                        $('#receiptModal').modal('show');
                    }
                });
            });
        });
    </script>

    <script>
        // Category Tabs & Live Search Filter Logic
        $(document).ready(function() {
            let activeCategory = 'all';
            let searchQuery = '';

            const normalizeSearchText = (value) => String(value || '')
                .toLocaleLowerCase('th')
                .replace(/\s+/g, ' ')
                .trim();

            function filterCourses() {
                let visibleCount = 0;
                const items = $('.course-item-wrapper');

                items.each(function() {
                    const item = $(this);
                    const itemSearch = normalizeSearchText(item.attr('data-search'));
                    const itemCategory = item.data('category') || '';

                    let matchCategory = (activeCategory === 'all') || (itemCategory === 'cat-' + activeCategory);
                    let matchSearch = searchQuery === '' || itemSearch.includes(searchQuery);

                    if (matchCategory && matchSearch) {
                        item.fadeIn(200);
                        visibleCount++;
                    } else {
                        item.hide();
                    }
                });

                $('#activeCoursesCount').text(visibleCount);

                if (visibleCount === 0) {
                    $('#noFilterResults').fadeIn(200);
                } else {
                    $('#noFilterResults').hide();
                }
            }

            // Tab Click
            $('.category-tab-btn').on('click', function() {
                $('.category-tab-btn').removeClass('active').attr('aria-pressed', 'false');
                $(this).addClass('active').attr('aria-pressed', 'true');
                activeCategory = $(this).data('category');
                filterCourses();
            });

            // Search Typing
            $('#courseSearchInput').on('input', function() {
                searchQuery = normalizeSearchText($(this).val());
                filterCourses();
            });

            // Reset Button
            $('#btnResetFilter').on('click', function() {
                $('#courseSearchInput').val('');
                searchQuery = '';
                $('.category-tab-btn').removeClass('active').attr('aria-pressed', 'false');
                $('.category-tab-btn[data-category="all"]').addClass('active').attr('aria-pressed', 'true');
                activeCategory = 'all';
                filterCourses();
            });
        });
    </script>

    <script>
        // Branch & Day change AJAX for Teaching Periods
        $(document).ready(function() {
            $('#department, #day').on('change', function() {
                let department_id = $('#department').val();
                let day = $('#day').val();

                if (department_id && day) {
                    $.ajax({
                        url: '/courses/findPeriodOptions/' + department_id + '/' + day,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            $('#teaching_period').html(response.html);
                        },
                        error: function(xhr) {
                            console.log('Error loading periods:', xhr.responseText);
                        }
                    });
                } else {
                    $('#teaching_period').html('<option value="">' + course_website_ui.period_placeholder + '</option>');
                }
            });
        });
    </script>

    <script>
        // Form Submission Logic
        document.getElementById('courseForm').addEventListener('submit', function(event) {
            event.preventDefault();

            // Show loading overlay
            document.getElementById('loading').style.display = 'flex';
            document.getElementById('submitForm').disabled = true;
            document.getElementById('submitForm').innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> ' + course_website_ui.saving;

            const formData = new FormData(this);
            const departmentSelect = this.querySelector('[name="department_id"]');
            const selectedDepartmentOption = departmentSelect?.options[departmentSelect.selectedIndex];
            const submittedBranch = {
                id: formData.get('department_id'),
                code: selectedDepartmentOption?.dataset.branchCode || '',
                name: selectedDepartmentOption?.dataset.branchName || selectedDepartmentOption?.textContent?.trim() || '',
            };
            fetch('{{ route('courses.pending.store') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('submitForm').disabled = false;
                    document.getElementById('submitForm').innerHTML = '<i class="fas fa-check-circle me-1"></i> ' + course_website_ui.submit;

                    if (data.success) {
                        const selectedBranch = data.branch || submittedBranch;
                        if (typeof window.setCourseSuccessBranch === 'function') {
                            window.setCourseSuccessBranch(selectedBranch);
                        }

                        this.reset();

                        const showSuccessModal = () => {
                            const lineEl = document.getElementById('modalLineContact');
                            if (lineEl && window.bootstrap && bootstrap.Modal) {
                                bootstrap.Modal.getOrCreateInstance(lineEl).show();
                            } else if (window.jQuery) {
                                $('#modalLineContact').modal('show');
                            }
                        };

                        // Hide receipt modal
                        const receiptEl = document.getElementById('receiptModal');
                        if (receiptEl && window.bootstrap && bootstrap.Modal) {
                            receiptEl.addEventListener('hidden.bs.modal', showSuccessModal, { once: true });
                            bootstrap.Modal.getOrCreateInstance(receiptEl).hide();
                        } else if (window.jQuery) {
                            $('#receiptModal').one('hidden.bs.modal', showSuccessModal).modal('hide');
                        } else {
                            showSuccessModal();
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: course_website_ui.cannot_process,
                            text: data.message || course_website_ui.check_input,
                            confirmButtonColor: '#FF7B00'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('submitForm').disabled = false;
                    document.getElementById('submitForm').innerHTML = '<i class="fas fa-check-circle me-1"></i> ' + course_website_ui.submit;

                    Swal.fire({
                        icon: 'error',
                        title: course_website_ui.unexpected_error,
                        text: course_website_ui.server_error,
                        confirmButtonColor: '#FF7B00'
                    });
                });
        });
    </script>
@endsection
