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
            margin: 0 auto 2rem;
            line-height: 1.6;
        }

        .hero-features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.25rem;
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

        .price-clarity-note {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            color: #92400E;
            background: #FFFBEB;
            border: 1px solid #FDE68A;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            margin: -0.75rem 0 1.5rem;
            font-size: 0.84rem;
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
            .price-clarity-note {
                margin-top: 0;
                font-size: 0.78rem;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Loading Overlay -->
    <div id="loading" style="display: none;">
        <div class="loading-content">
            <div class="spinner"></div>
            <p class="text-white mb-0 mt-2" style="font-size: 1.1rem; font-weight: 600;">กำลังบันทึกข้อมูลการจอง</p>
            <p class="text-white mb-0" style="font-size: 0.88rem; opacity: 0.9;">กรุณารอสักครู่...</p>
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
                <span>คุณได้รับสิทธิพิเศษผ่านคำแนะนำจากพาร์ทเนอร์</span>
                <span class="ref-badge">Ref: {{ $ref }}</span>
                <span>— รับคำปรึกษาและสิทธิ์ประเมินทักษะคณิตศาสตร์ฟรี!</span>
            </div>
        </div>
    @endif

    <!-- Modern Hero Section -->
    <section class="courses-hero-section">
        <div class="container text-center">
            <div class="hero-badge-tag">
                <i class="fas fa-award"></i>
                <span>สถาบันคณิตศาสตร์คิดวิเคราะห์ eiMaths มาตรฐานสิงคโปร์</span>
            </div>
            <h1 class="hero-title">
                คอร์สเรียนคณิตศาสตร์ <span class="text-gradient">พัฒนาทักษะสู่ความเป็นเลิศ</span>
            </h1>
            <p class="hero-subtitle">
                เรียนรู้คณิตศาสตร์ด้วยกระบวนการ CPA Approach ที่เน้นความเข้าใจจากรูปธรรมสู่ภาพและสัญลักษณ์ ปูพื้นฐานแน่น คิดวิเคราะห์เป็น และพร้อมรับมือทุกโจทย์แข่งขัน
            </p>

            <div class="hero-features">
                <div class="hero-feature-item">
                    <i class="fas fa-shapes"></i>
                    <span>สื่อการสอนรูปธรรม CPA</span>
                </div>
                <div class="hero-feature-item">
                    <i class="fas fa-brain"></i>
                    <span>พัฒนาตรรกะและการคิดวิเคราะห์</span>
                </div>
                <div class="hero-feature-item">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>ครูผู้สอนผ่านการรับรองมาตรฐาน</span>
                </div>
                <div class="hero-feature-item">
                    <i class="fas fa-chart-line"></i>
                    <span>ประเมินพัฒนาการรายบุคคล</span>
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
                    <div class="category-tabs" id="categoryTabs" role="group" aria-label="กรองคอร์สตามระดับชั้น">
                        <button type="button" class="category-tab-btn active" data-category="all" aria-pressed="true">
                            <i class="fas fa-th-large"></i> ทั้งหมด
                        </button>
                        <button type="button" class="category-tab-btn" data-category="kindergarten" aria-pressed="false">
                            <i class="fas fa-shapes"></i> อนุบาล (K1 - K2)
                        </button>
                        <button type="button" class="category-tab-btn" data-category="primary_lower" aria-pressed="false">
                            <i class="fas fa-pencil-alt"></i> ประถมต้น (P1 - P3)
                        </button>
                        <button type="button" class="category-tab-btn" data-category="primary_upper" aria-pressed="false">
                            <i class="fas fa-calculator"></i> ประถมปลาย (P4 - P6)
                        </button>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="search-box-wrap">
                        <i class="fas fa-search"></i>
                        <label for="courseSearchInput" class="visually-hidden">ค้นหาชื่อคอร์สหรือระดับชั้น</label>
                        <input type="search" id="courseSearchInput" placeholder="ค้นหาชื่อคอร์ส หรือระดับชั้น..." autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Grid Container -->
        <div class="courses-main-container">
            <div class="courses-section-header">
                <div>
                    <h2 class="fw-bold mb-1" style="color: #0F172A; font-size: 1.5rem;">คอร์สเรียนทั้งหมด</h2>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">เลือกคอร์สที่ตรงกับระดับชั้นและเป้าหมายของน้อง</p>
                </div>
                <div class="courses-result-count">
                    พร้อมให้เรียน <span id="activeCoursesCount">{{ method_exists($courses, 'total') ? $courses->total() : (is_countable($courses) ? count($courses) : 0) }}</span> คอร์ส
                </div>
            </div>

            <div class="price-clarity-note" role="note">
                <i class="fas fa-info-circle mt-1"></i>
                <span>ราคาที่แสดงเป็นค่าคอร์สเรียน และอาจยังไม่รวมค่าแรกเข้าและอุปกรณ์การเรียน กรุณาตรวจสอบรายละเอียดกับเจ้าหน้าที่ก่อนยืนยันการสมัคร</span>
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
                            $levelLabel = 'หลักสูตร eiMaths';

                            if ($catId == 4 || preg_match('/K[1-4]|KINDER|อนุบาล/i', $course->title)) {
                                $catClass = 'cat-kindergarten';
                                $levelLabel = 'อนุบาล K1 - K2';
                            } elseif ($catId == 1 || preg_match('/P[1-3]|PRIMARY\s*[1-3]|ประถม\s*[1-3]|ประถมต้น/i', $course->title)) {
                                $catClass = 'cat-primary_lower';
                                $levelLabel = 'ประถมต้น (P1 - P3)';
                            } elseif ($catId == 2 || preg_match('/P[4-6]|PRIMARY\s*[4-6]|ประถม\s*[4-6]|ประถมปลาย/i', $course->title)) {
                                $catClass = 'cat-primary_upper';
                                $levelLabel = 'ประถมปลาย (P4 - P6)';
                            }

                            $aboutText = trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($course->about ?? ''))));
                            $outcomesText = trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($course->affiliate_outcomes ?? ''))));
                            $requirementsText = trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($course->affiliate_requirements ?? ''))));
                            $aboutText = $aboutText ?: 'หลักสูตรคิดวิเคราะห์ ปูพื้นฐาน และฝึกแก้โจทย์ปัญหา';
                            $outcomesText = $outcomesText ?: 'พัฒนาตรรกะด้วยสื่อรูปธรรม CPA มาตรฐานสิงคโปร์';
                            $requirementsText = $requirementsText ?: 'คลาสกลุ่มเล็ก ดูแลใกล้ชิด และมีชุดสื่อการเรียน';
                            $deliveryLabel = match ((int) $course->mode_of_delivery) {
                                1 => 'เรียนออนไลน์',
                                2 => 'เรียนทางไกล',
                                default => 'เรียนที่สาขา',
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
                                    <img src="{{ $imgSrc }}" alt="ภาพหน้าปกคอร์ส {{ $displayTitle }}" loading="lazy" onerror="this.src='{{ asset('uploads/ei-defult.jpg') }}';">
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
                                            <span>สมัคร / จองคอร์สนี้</span>
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
                        <h4 class="fw-bold text-dark mb-2">ไม่พบคอร์สเรียนที่เปิดรับสมัคร</h4>
                        <p class="text-muted mb-0">กรุณากลับมาตรวจสอบอีกครั้ง หรือติดต่อแอดมินเพื่อสอบถามข้อมูลเพิ่มเติม</p>
                    </div>
                @endif
            </div>

            <!-- Client Filter Empty State (Hidden by Default) -->
            <div id="noFilterResults" class="no-courses-wrap mt-3" style="display: none;">
                <div class="no-courses-icon-wrap">
                    <i class="fas fa-search-minus"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">ไม่พบคอร์สที่ตรงกับเงื่อนไขการค้นหา</h4>
                <p class="text-muted mb-3">ลองเปลี่ยนคำค้นหา หรือเลือกระดับชั้นอื่นๆ ด้านบน</p>
                <button type="button" class="btn btn-outline-warning rounded-pill px-4" id="btnResetFilter">
                    <i class="fas fa-redo me-1"></i> ดูคอร์สทั้งหมด
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
                        <img src="{{ asset('images/logo.png') }}" alt="eiMaths-TH Logo" class="mb-3" style="max-width: 170px; height: auto;">
                        <p class="footer-text">{{ $footer->description ?? 'สถาบันคณิตศาสตร์คิดวิเคราะห์ eiMaths มุ่งเน้นการสอนเพื่อพัฒนาความเข้าใจและทักษะการแก้โจทย์ปัญหาเชิงลึก' }}</p>
                    </div>

                    <!-- Column 2: Info -->
                    <div class="col-lg-4 text-center text-lg-start">
                        <h5 class="footer-title">{{ $footer->title ?? 'ติดต่อเรา' }}</h5>
                        <div class="footer-text">{{ $footer->description2 ?? 'ยินดีให้คำปรึกษาและทดสอบวัดระดับความรู้ทางคณิตศาสตร์ฟรีสำหรับนักเรียนทุกระดับชั้น' }}</div>
                    </div>

                    <!-- Column 3: Social Media -->
                    <div class="col-lg-4 text-center text-lg-start">
                        <h5 class="footer-title">ช่องทางติดตามข่าวสาร</h5>
                        <p class="footer-text mb-3">ติดตามเทคนิคคณิตศาสตร์และกิจกรรมพิเศษจาก eiMaths</p>
                        <div class="social-links justify-content-center justify-content-lg-start">
                            <a href="https://www.facebook.com/eimaths.th/?locale=th_TH" target="_blank" rel="noopener noreferrer" class="social-link" title="Facebook" aria-label="ติดตาม eiMaths บน Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://www.youtube.com/channel/UCiD8a78ZN6SNQViVmG1St0A" target="_blank" rel="noopener noreferrer" class="social-link" title="YouTube" aria-label="ติดตาม eiMaths บน YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a href="https://www.tiktok.com/@eimaths.th" target="_blank" rel="noopener noreferrer" class="social-link" title="TikTok" aria-label="ติดตาม eiMaths บน TikTok">
                                <i class="fab fa-tiktok"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <hr class="footer-divider">

                <div class="copyright">
                    <p class="mb-0">© {{ date('Y') }} eiMaths Thailand. สงวนลิขสิทธิ์ทุกประการ | ระบบการเรียนการสอนคณิตศาสตร์</p>
                </div>
            </div>
        </div>
    </footer>

    @if ($promotion->count() > 0)
        @include('affiliate.frontend.promotion_modal')
    @endif
@endsection

@section('script')
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
                    $('#purchaseModalLabel').text('ลงทะเบียน: ' + title);

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
                    $('#teaching_period').html('<option value="">-- เลือกช่วงเวลา --</option>');
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
            document.getElementById('submitForm').innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> กำลังบันทึก...';

            const formData = new FormData(this);
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
                    document.getElementById('submitForm').innerHTML = '<i class="fas fa-check-circle me-1"></i> ยืนยันการจองคอร์สเรียน';

                    if (data.success) {
                        $('.form').trigger('reset');

                        // Hide receipt modal
                        const receiptEl = document.getElementById('receiptModal');
                        if (receiptEl && window.bootstrap && bootstrap.Modal) {
                            bootstrap.Modal.getOrCreateInstance(receiptEl).hide();
                        } else if (window.jQuery) {
                            $('#receiptModal').modal('hide');
                        }

                        // Show Line Contact Modal
                        const lineEl = document.getElementById('modalLineContact');
                        if (lineEl && window.bootstrap && bootstrap.Modal) {
                            bootstrap.Modal.getOrCreateInstance(lineEl).show();
                        } else if (window.jQuery) {
                            $('#modalLineContact').modal('show');
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'บันทึกการจองสำเร็จ!',
                            text: 'กรุณาติดต่อเจ้าหน้าที่ผ่านทาง LINE OA เพื่อยืนยันตารางเรียนและรับรายละเอียดการชำระเงิน',
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#FF7B00'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'ไม่สามารถทำรายการได้',
                            text: data.message || 'กรุณาตรวจสอบข้อมูลที่กรอกอีกครั้ง',
                            confirmButtonColor: '#FF7B00'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('submitForm').disabled = false;
                    document.getElementById('submitForm').innerHTML = '<i class="fas fa-check-circle me-1"></i> ยืนยันการจองคอร์สเรียน';

                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง',
                        confirmButtonColor: '#FF7B00'
                    });
                });
        });
    </script>
@endsection
