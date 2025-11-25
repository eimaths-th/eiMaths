@extends('affiliate.frontend.main')
@section('style')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        #loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 99999;
        }

        #loading .loading-content {
            background: linear-gradient(135deg, #FF8C00, #FF6A00);
            padding: 30px 50px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-top: 5px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 10px auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        footer {
            background: #ffb618;
            color: white;
        }

        footer a {
            text-decoration: none;
            transition: 0.3s;
        }

        footer a:hover {
            color: #f8d210;
        }

        .bg-dark {
            background: #ff951c !important;
        }
    </style>
    <style>
        .premium-footer {
            background: linear-gradient(135deg, #FF8C00 0%, #FF6A00 50%, #E55100 100%);
            position: relative;
            overflow: hidden;
        }

        .premium-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.05) 25%, transparent 25%, transparent 75%, rgba(255, 255, 255, 0.05) 75%);
            background-size: 40px 40px;
            animation: footerPattern 20s linear infinite;
        }

        @keyframes footerPattern {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(40px);
            }
        }

        .premium-footer .footer-content {
            position: relative;
            z-index: 2;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.08);
            border-radius: 20px 20px 0 0;
            margin: 0 -15px;
            padding: 3rem 2rem 2rem;
        }

        .premium-footer .footer-logo {
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.2));
            transition: transform 0.3s ease;
        }

        .premium-footer .footer-logo:hover {
            transform: scale(1.05);
        }

        .premium-footer .footer-title {
            color: #fff;
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 1rem;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .premium-footer .footer-text {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .premium-footer .social-links {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1.5rem;
        }

        .premium-footer .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            color: #fff;
            font-size: 1.4rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .premium-footer .social-link:hover {
            transform: translateY(-5px) scale(1.1);
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.4);
            color: #fff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .premium-footer .social-link.facebook:hover {
            background: linear-gradient(135deg, #1877f2, #166fe5);
            border-color: #1877f2;
        }

        .premium-footer .social-link.youtube:hover {
            background: linear-gradient(135deg, #ff0000, #cc0000);
            border-color: #ff0000;
        }

        .premium-footer .social-link.tiktok:hover {
            background: linear-gradient(135deg, #000000, #333333);
            border-color: #000000;
        }

        .premium-footer .footer-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            border: none;
            margin: 2rem 0 1.5rem;
        }

        .premium-footer .copyright {
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .premium-footer .footer-content {
                padding: 2rem 1.5rem 1.5rem;
                margin: 0 -10px;
            }

            .premium-footer .social-links {
                justify-content: center;
                margin-top: 1rem;
            }

            .premium-footer .social-link {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
            }
        }
    </style>
@endsection
@section('content')
    <div id="loading" style="display: none;">
        <div class="loading-content">
            <div class="spinner"></div>
            <p class="text-white mb-0 mt-2" style="font-size: 1.1rem; font-weight: 600;">กำลังส่งข้อมูล</p>
            <p class="text-white mb-0" style="font-size: 0.9rem; opacity: 0.9;">กรุณารอสักครู่...</p>
        </div>
    </div>
    <div>
        <div class="breadcrumb_area bradcam_bg_2"
            style="background-image: url('{{ asset('uploads/main/files/09-08-2024/bb.png') }}')">
            <div class="container">
                <div class="row">
                    <div class="col-lg-10 offset-lg-1">
                        <div class="breadcam_wrap">
                            <span>
                                Courses
                            </span>
                            <h3>
                                We Help Students Achieve Excellent Results in Maths withour Proven Process
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Premium Courses Section -->
    <div class="courses-section-premium">
        <div class="container">
            <!-- Header Section -->
            <div class="section-header text-center mb-5">
                <div class="header-decoration">
                    <span class="decoration-line"></span>
                    <i class="fas fa-graduation-cap header-icon"></i>
                    <span class="decoration-line"></span>
                </div>
                <h2 class="section-title">คอร์สเรียนคณิตศาสตร์</h2>
                <p class="section-subtitle">เลือกคอร์สที่เหมาะสมกับระดับการเรียนของคุณ</p>
                <div class="courses-count">
                    <span class="count-number">{{ method_exists($courses, 'total') ? $courses->total() : (is_countable($courses) ? count($courses) : 0) }}</span>
                    <span class="count-text">คอร์สพร้อมให้เรียน</span>
                </div>
            </div>

            <!-- Courses Grid -->
            <div class="courses-grid">
                @if(isset($courses) && count($courses) > 0)
                    @foreach($courses as $course)
                        <div class="course-card-wrapper">
                            <div class="course-card">
                                <div class="course-image-container">
                                    @php
                                        $rawThumb = $course->thumbnail ?? '';
                                        // Normalize to a web path under public/
                                        $webThumb = $rawThumb ? str_replace(['public\\', 'public/', '\\'], ['', '', '/'], $rawThumb) : '';
                                        $fullThumbPath = $webThumb ? public_path($webThumb) : '';
                                        $hasThumb = $webThumb && file_exists($fullThumbPath);
                                        $imgSrc = $hasThumb ? asset($webThumb) : asset('uploads/ei-defult.jpg');
                                    @endphp
                                    <img src="{{ $imgSrc }}" alt="{{ $course->title }}" class="course-image">
                                    <div class="course-overlay">
                                        <button class="btn-enroll"
                                                data-bs-toggle="modal"
                                                data-bs-target="#receiptModal"
                                                data-course-id="{{ $course->id }}"
                                                data-title="{{ $course->title }}"
                                                data-price="{{ $course->price }}">
                                            <i class="fas fa-play"></i>
                                            เริ่มเรียน
                                        </button>
                                    </div>
                                    <div class="price-badge">
                                        <span class="price">{{ number_format($course->price) }}฿</span>
                                    </div>
                                </div>
                                <div class="course-content">
                                    <h3 class="course-title">{{ $course->title }}</h3>
                                    <div class="course-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-book-open"></i>
                                            <span>{{ $course->total_lessons ?? 0 }} บทเรียน</span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-users"></i>
                                            <span>{{ $course->total_enrolled ?? 0 }} นักเรียน</span>
                                        </div>
                                    </div>
                                    <div class="course-footer">
                                        <button class="btn-details"
                                                data-bs-toggle="modal"
                                                data-bs-target="#receiptModal"
                                                data-course-id="{{ $course->id }}"
                                                data-title="{{ $course->title }}"
                                                data-price="{{ $course->price }}">
                                            ดูรายละเอียด
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="no-courses-found">
                        <div class="no-courses-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>ไม่พบคอร์สที่ค้นหา</h3>
                        <p>ลองเปลี่ยนเงื่อนไขการค้นหาหรือกลับมาใหม่ภายหลัง</p>
                    </div>
                @endif
            </div>
            @if(isset($courses) && method_exists($courses, 'links'))
                <div class="d-flex justify-content-center mt-4">
                    {{ $courses->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    <style>
        .courses-section-premium {
            padding: 4rem 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
        }
        .courses-section-premium::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23FF8C00" opacity="0.05"/><circle cx="75" cy="75" r="1" fill="%23FF6A00" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        .section-header {
            position: relative;
            z-index: 2;
        }
        .header-decoration {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .decoration-line {
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #FF8C00, transparent);
        }
        .header-icon {
            font-size: 2.5rem;
            color: #FF8C00;
            margin: 0 1.5rem;
            filter: drop-shadow(0 4px 8px rgba(255,140,0,0.3));
        }
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section-subtitle {
            font-size: 1.2rem;
            color: #718096;
            margin-bottom: 2rem;
        }
        .courses-count {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #FF8C00, #FF6A00);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(255,140,0,0.3);
        }
        .count-number {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            position: relative;
            z-index: 2;
        }
        .course-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            border: 1px solid rgba(255,140,0,0.1);
        }
        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            border-color: rgba(255,140,0,0.3);
        }
        .course-image-container {
            position: relative;
            height: 220px;
            overflow: hidden;
        }
        .course-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .course-card:hover .course-image {
            transform: scale(1.05);
        }
        .course-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,140,0,0.8), rgba(255,106,0,0.8));
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .course-card:hover .course-overlay {
            opacity: 1;
        }
        .btn-enroll {
            background: white;
            color: #FF8C00;
            border: none;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .btn-enroll:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            color: #FF6A00;
        }
        .price-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: linear-gradient(135deg, #FF8C00, #FF6A00);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 15px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(255,140,0,0.4);
        }
        .course-content {
            padding: 1.5rem;
        }
        .course-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
            line-height: 1.4;
        }
        .course-meta {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #718096;
            font-size: 0.9rem;
        }
        .meta-item i {
            color: #FF8C00;
        }
        .course-footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 1rem;
        }
        .btn-details {
            width: 100%;
            background: linear-gradient(135deg, #FF8C00, #FF6A00);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-details:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255,140,0,0.4);
            color: white;
        }
        .no-courses-found {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        .no-courses-icon {
            font-size: 4rem;
            color: #FF8C00;
            margin-bottom: 1.5rem;
        }
        .no-courses-found h3 {
            color: #2d3748;
            margin-bottom: 1rem;
        }
        .no-courses-found p {
            color: #718096;
        }
        @media (max-width: 768px) {
            .courses-section-premium {
                padding: 2rem 0;
            }
            .section-title {
                font-size: 2rem;
            }
            .courses-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            .course-meta {
                flex-direction: column;
                gap: 0.75rem;
            }
        }
    </style>

    @if (request('ref'))
        @php
            $ref = request('ref');
            // dd(request('ref'), $ref);
        @endphp
    @else
        @php
            $ref = '';
            // dd(request('ref'), $ref);
        @endphp
    @endif

    <footer class="premium-footer py-5">
        <div class="container">
            <div class="footer-content">
                <div class="row g-4">
                    <!-- Column 1: Logo & About -->
                    <div class="col-lg-4 text-center text-lg-start">
                        <img src="{{ asset('images/logo.png') }}" alt="eiMaths-TH Logo" class="footer-logo mb-3"
                            style="width: 50%; max-width: 180px; height: auto;">
                        <p class="footer-text">{{ $footer->description }}</p>
                    </div>

                    <!-- Column 2: Info -->
                    <div class="col-lg-4 text-center text-lg-start">
                        <h5 class="footer-title">{{ $footer->title }}</h5>
                        <div class="footer-text">{{ $footer->description2 }}</div>
                    </div>

                    <!-- Column 3: Social Media -->
                    <div class="col-lg-4 text-center">
                        <h5 class="footer-title">Follow Us</h5>
                        <p class="footer-text mb-3">ติดตามข่าวสารและเทคนิคการเรียนคณิตศาสตร์</p>
                        <div class="social-links">
                            <a href="https://www.facebook.com/eimaths.th/?locale=th_TH" target="_blank"
                                class="social-link facebook" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://www.youtube.com/channel/UCiD8a78ZN6SNQViVmG1St0A" target="_blank"
                                class="social-link youtube" title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a href="https://www.tiktok.com/@eimaths.th" target="_blank" class="social-link tiktok"
                                title="TikTok">
                                <i class="fab fa-tiktok"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <hr class="footer-divider">

                <!-- Copyright -->
                <div class="copyright">
                    <p class="mb-0">© {{ date('Y') }} eiMaths-TH. All Rights Reserved. |
                        ระบบการเรียนการสอนคณิตศาสตร์ออนไลน์</p>
                </div>
            </div>
        </div>
    </footer>


    @if ($promotion->count() > 0)
        @include('affiliate.frontend.promotion_modal')
    @endif
@endsection
@section('script')
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script> --}}
    <script>
        $(document).ready(function() {
            const promoEl = document.getElementById('promoModal');
            if (promoEl && window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(promoEl).show();
            } else if (window.jQuery) {
                $('#promoModal').modal('show');
            }
            // setTimeout(() => { if (promoEl && window.bootstrap && bootstrap.Modal) { bootstrap.Modal.getOrCreateInstance(promoEl).show(); } }, 2000);
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
            // After a modal hides, if no modals remain visible, clean up
            setTimeout(() => {
                if (!document.querySelector('.modal.show')) {
                    cleanupBackdrops();
                }
            }, 50);
        });
        // Extra safety: when modalLineContact is closed, cleanup and dispose its instance
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
        // document.addEventListener('click', function(e) {
        //     if (e.target.closest('[data-bs-toggle="modal"]')) {
        //         const link = e.target.closest('[data-bs-toggle="modal"]');
        //         const title = link.getAttribute('data-title');
        //         const thumbnail = link.getAttribute('data-thumbnail');

        //         // Update modal content
        //         const modal = document.getElementById('receiptModal');
        //         modal.querySelector('.modal-title').textContent = title;

        //         // modal.querySelector('.modal-body').innerHTML = `
    //         //     <img src="${thumbnail}" alt="${title}" class="img-fluid" />
    //         // `;

        //         modal.querySelector('#course_name').value = title;

        //     }
        // });


        document.addEventListener('DOMContentLoaded', () => {
            // Only handle buttons that open the purchase modal (#receiptModal)
            const selector = '[data-bs-target="#receiptModal"], [data-target="#receiptModal"]';
            document.querySelectorAll(selector).forEach(button => {
                button.addEventListener('click', (e) => {
                    const link = e.currentTarget; // the actual trigger
                    const title = link.getAttribute('data-title') || '';
                    const courseId = link.getAttribute('data-course-id') || '';
                    const price = link.getAttribute('data-price') || '';

                    // Populate the form fields inside the modal
                    $('#purchaseModalLabel').text(title);
                    $('#course_id').val(courseId);
                    $('#course_name').val(title);
                    $('#course_price').val(price);

                    // Ensure no other modals are open to avoid stacked backdrops
                    document.querySelectorAll('.modal.show').forEach(el => {
                        if (window.bootstrap && bootstrap.Modal) {
                            bootstrap.Modal.getOrCreateInstance(el).hide();
                        } else if (window.jQuery) {
                            $(el).modal('hide');
                        }
                    });

                    const receiptEl = document.getElementById('receiptModal');
                    if (receiptEl && window.bootstrap && bootstrap.Modal) {
                        bootstrap.Modal.getOrCreateInstance(receiptEl).show();
                    } else if (window.jQuery) {
                        $('#receiptModal').modal('show');
                    }

                    // const modal = document.getElementById('receiptModal');
                    // modal.querySelector('.modal-title').textContent = title;
                    // modal.querySelector('#course_id').value = title;
                    // modal.querySelector('#course_name').value = title;
                    // modal.querySelector('input[name="course_id"]').value = courseId; // Set course ID
                    // modal.querySelector('input[name="course_name"]').value =
                    // courseName; // Set course name
                });
            });
        });
    </script>
    <script>
        document.getElementById('courseForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Show loading overlay and disable submit button
            document.getElementById('loading').style.display = 'block';
            document.getElementById('submitForm').disabled = true;
            document.getElementById('submitForm').innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังส่ง...';

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
                    // Hide loading overlay
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('submitForm').disabled = false;
                    document.getElementById('submitForm').innerHTML = 'Submit';

                    if (data.success) {
                        console.log(data);

                        $('.form').trigger('reset');
                        const receiptEl = document.getElementById('receiptModal');
                        if (receiptEl && window.bootstrap && bootstrap.Modal) {
                            bootstrap.Modal.getOrCreateInstance(receiptEl).hide();
                        } else if (window.jQuery) {
                            $('#receiptModal').modal('hide');
                        }
                        const lineEl = document.getElementById('modalLineContact');
                        if (lineEl && window.bootstrap && bootstrap.Modal) {
                            bootstrap.Modal.getOrCreateInstance(lineEl).show();
                        } else if (window.jQuery) {
                            $('#modalLineContact').modal('show');
                        }
                        // Update the QR code image source
                        // $('#qrCodePayment').attr('src', data.qrCodePayment);

                        // // Handle QR Code download
                        // $('#downloadQrButton').off('click').on('click', function() {
                        //     const qrUrl = data.qrCodePayment;
                        //     const link = document.createElement('a');
                        //     link.href = qrUrl;
                        //     link.download = 'qr_code.png';
                        //     document.body.appendChild(link);
                        //     link.click();
                        //     document.body.removeChild(link);
                        // });

                        // $('.modal-backdrop').removeClass('modal-backdrop');
                    } else {
                        toastr.error(data.message, {
                            timeOut: 5000
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Hide loading overlay on error
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('submitForm').disabled = false;
                    document.getElementById('submitForm').innerHTML = 'Submit';
                    toastr.error('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');
                });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Listen for change events on #department and #day selects
            $('#department, #day').on('change', function() {
                let department_id = $('#department').val();
                let day = $('#day').val();

                // Check if both fields have values
                if (department_id && day) {
                    $.ajax({
                        url: '/courses/findPeriodOptions/' + department_id + '/' + day,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            $('#teaching_period').html(response.html);
                        },
                        error: function(xhr) {
                            console.log('Error:', xhr.responseText);
                        }
                    });
                } else {
                    // Reset period dropdown if no department or day is selected
                    $('#teaching_period').html('<option value="">Period</option>');
                }
            });


        });
    </script>
    <script>
        document.getElementById('copy_account_number_btn').addEventListener('click', function() {
            // Get the text content of the account number
            const accountNumber = document.getElementById('copy_account_number').textContent;

            // Create a temporary textarea element
            const tempInput = document.createElement('textarea');
            tempInput.value = accountNumber;
            document.body.appendChild(tempInput);

            // Select and copy the text
            tempInput.select();
            document.execCommand('copy');

            // Remove the temporary input
            document.body.removeChild(tempInput);

            // Show a success message (optional)
            alert('Copied: ' + accountNumber);
        });
    </script>
    <script>
        document.getElementById('payment_slip').addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('slip_preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const paymentForm = document.getElementById('paymentForm');

            if (paymentForm) { // Ensure the form exists
                paymentForm.addEventListener('submit', function(event) {
                    event.preventDefault(); // Prevent default form submission

                    let formData = new FormData(this);

                    var _token = "{{ csrf_token() }}";
                    formData.append('_token', _token)

                    fetch("{{ route('payment.upload') }}", {
                            method: "POST",
                            body: formData,

                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: "Success!",
                                    text: "Payment slip uploaded successfully!",
                                    icon: "success",
                                    confirmButtonText: "OK"
                                });

                                document.getElementById('paymentForm').reset();
                                document.getElementById('slip_preview').style.display = 'none';
                                document.getElementById('loading').style.display = 'block';
                                // Send an email confirmation
                                fetch("{{ route('senMailconfirm') }}", {
                                        method: "POST",
                                        headers: {
                                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            coursePending_id: data.coursePending_id
                                        })
                                    })
                                    .then(response => response.json())
                                    .then(mailData => {
                                        Swal.fire({
                                            title: "Purchase completed!",
                                            text: mailData.message,
                                            icon: "success",
                                            confirmButtonText: "OK"
                                        }).then(() => {
                                            location
                                                .reload(); // Reload the page after user clicks "OK"
                                        });
                                    })
                                    .catch(error => {
                                        Swal.fire({
                                            title: "Error!",
                                            text: "Failed to send email.",
                                            icon: "error",
                                            confirmButtonText: "OK"
                                        });
                                        console.error("Error sending email:", error);
                                    })
                                    .finally(() => {
                                        document.getElementById('loading').style.display =
                                            'none'; // Hide loading animation
                                    });
                            }
                        })
                        .catch(error => console.error("Error:", error));

                });
            } else {
                console.log('paymentForm element not found!');
            }
        });
    </script>
@endsection
