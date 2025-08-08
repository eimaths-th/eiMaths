<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ยินดีต้อนรับ - eiMaths</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Kanit', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #ffffff 0%, #ffd7a0 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 1200px;
            width: 100%;
        }

        .header-section {
            background: linear-gradient(135deg, #ff8a50 0%, #ff6b35 100%);
            color: white;
            padding: 40px;
            border-radius: 20px 20px 0 0;
            text-align: center;
        }

        .header-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header-section p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin: 0;
        }

        .content-section {
            padding: 40px;
        }

        .service-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin: 0 auto 20px;
        }

        .affiliate-icon {
            background: linear-gradient(135deg, #ff8a50 0%, #ff6b35 100%);
        }

        .blog-icon {
            background: linear-gradient(135deg, #ff9a56 0%, #ff7b42 100%);
        }

        .service-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            text-align: center;
        }

        .service-description {
            color: #666;
            text-align: center;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin-bottom: 30px;
        }

        .feature-list li {
            padding: 8px 0;
            color: #555;
            position: relative;
            padding-left: 25px;
        }

        .feature-list li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #28a745;
            font-weight: bold;
        }

        .btn-custom {
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            display: inline-block;
            text-align: center;
        }

        .btn-affiliate {
            background: linear-gradient(135deg, #ff8a50 0%, #ff6b35 100%);
            color: white;
        }

        .btn-affiliate:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 138, 80, 0.3);
            color: white;
        }

        .btn-blog {
            background: linear-gradient(135deg, #ff9a56 0%, #ff7b42 100%);
            color: white;
        }

        .btn-blog:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 154, 86, 0.3);
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #ddd;
            color: #666;
        }

        .btn-outline:hover {
            background: #f8f9fa;
            border-color: #ff8a50;
            color: #ff8a50;
        }

        .stats-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-top: 40px;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .footer-section {
            background: linear-gradient(135deg, #ff8a50 0%, #ff6b35 100%);
            color: white;
            text-align: center;
            padding: 20px;
            border-radius: 0 0 20px 20px;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 10px;
            }

            .header-section {
                padding: 30px 20px;
            }

            .header-section h1 {
                font-size: 2rem;
            }

            .content-section {
                padding: 30px 20px;
            }

            .service-card {
                padding: 20px;
                margin-bottom: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="welcome-card">
            <!-- Header Section -->
            <div class="header-section">
                <h1><i class="fas fa-home me-3"></i>ยินดีต้อนรับคุณ {{ Auth::user()->name }} สู่ eiMaths</h1>
                <p>เลือกบริการที่ต้องการใช้งาน</p>
            </div>

            <!-- Content Section -->
            <div class="content-section">
                <div class="row g-4">
                    <!-- Affiliate Section -->
                    @if (Auth::user()->hasRole('Affiliate-user'))
                        <div class="col-lg-6">
                            <div class="service-card">
                                <div class="service-icon affiliate-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h3 class="service-title">ระบบพันธมิตร</h3>
                                <p class="service-description">
                                    เข้าสู่ระบบพันธมิตรเพื่อจัดการการแนะนำและรับค่าคอมมิชชั่น
                                </p>

                                <ul class="feature-list">
                                    <li>ติดตามรายได้และคอมมิชชั่น</li>
                                    <li>จัดการลิงก์แนะนำ</li>
                                    <li>ดูสถิติการขาย</li>
                                    <li>สร้างรายได้ระยะยาว</li>
                                </ul>

                                <div class="d-grid gap-2">
                                    <a href="/affiliate" class="btn-custom btn-affiliate">
                                        <i class="fas fa-external-link-alt me-2"></i>
                                        เข้าสู่หน้าพันธมิตร
                                    </a>
                                    <a href="/affiliate/my_affiliate" class="btn-custom btn-outline">
                                        <i class="fas fa-user-tie me-2"></i>
                                        จัดการพันธมิตรของฉัน
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                    <!-- Blog Section -->
                    <div class="col-lg-6">
                        <div class="service-card">
                            <div class="service-icon blog-icon">
                                <i class="fas fa-blog"></i>
                            </div>
                            <h3 class="service-title">ระบบบล็อก</h3>
                            <p class="service-description">
                                เขียนและจัดการบทความบล็อกของคุณ
                            </p>

                            <ul class="feature-list">
                                <li>เขียนบทความใหม่</li>
                                <li>จัดการบทความที่มีอยู่</li>
                                <li>เผยแพร่และแชร์</li>
                                <li>ติดตามยอดเข้าชม</li>
                            </ul>

                            <div class="d-grid gap-2">
                                <a href="/blogs/create" class="btn-custom btn-blog">
                                    <i class="fas fa-plus me-2"></i>
                                    เขียนบล็อกใหม่
                                </a>
                                <a href="/blogs" class="btn-custom btn-outline">
                                    <i class="fas fa-list me-2"></i>
                                    จัดการบล็อก
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Section -->
                {{-- <div class="stats-section">
                    <h4 class="text-center mb-4">
                        <i class="fas fa-chart-bar me-2"></i>
                        สถิติการใช้งาน
                    </h4>
                    <div class="row">
                        <div class="col-6 col-lg-3">
                            <div class="stat-item">
                                <div class="stat-number" id="total-users">0</div>
                                <div class="stat-label">ผู้ใช้ทั้งหมด</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-item">
                                <div class="stat-number" id="active-affiliates">0</div>
                                <div class="stat-label">พันธมิตรที่ใช้งาน</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-item">
                                <div class="stat-number" id="published-blogs">0</div>
                                <div class="stat-label">บล็อกที่เผยแพร่</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-item">
                                <div class="stat-number" id="pending-reviews">0</div>
                                <div class="stat-label">รอการอนุมัติ</div>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>

            <!-- Footer Section -->
            <div class="footer-section">
                <p>&copy; {{ date('Y') }} eiMaths. สงวนลิขสิทธิ์ทุกประการ</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Animate numbers on load
            animateNumbers();

            function animateNumbers() {
                const stats = {
                    'total-users': 150,
                    'active-affiliates': 25,
                    'published-blogs': 48,
                    'pending-reviews': 12
                };

                Object.keys(stats).forEach(key => {
                    animateValue(key, 0, stats[key], 2000);
                });
            }

            function animateValue(id, start, end, duration) {
                const element = document.getElementById(id);
                const range = end - start;
                const increment = end > start ? 1 : -1;
                const stepTime = Math.abs(Math.floor(duration / range));
                let current = start;

                const timer = setInterval(() => {
                    current += increment;
                    element.textContent = current;
                    if (current === end) {
                        clearInterval(timer);
                    }
                }, stepTime);
            }

            // Add click effects
            $('.btn-custom').on('click', function(e) {
                const ripple = $('<span class="ripple"></span>');
                $(this).append(ripple);

                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
    </script>
</body>

</html>
