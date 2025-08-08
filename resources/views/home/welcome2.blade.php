@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">ยินดีต้อนรับ</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active">หน้าแรก</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Welcome Banner -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-home"></i>
                            ยินดีต้อนรับสู่ระบบ eiMaths
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="lead">เลือกบริการที่ต้องการใช้งาน</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Services Row -->
        <div class="row">
            <!-- Affiliate Section -->
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="card card-success card-outline h-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users"></i>
                            ระบบพันธมิตร (Affiliate)
                        </h3>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p class="card-text">เข้าสู่ระบบพันธมิตรเพื่อจัดการการแนะนำและรับค่าคอมมิชชั่น</p>

                        <div class="row mt-3">
                            <div class="col-md-6 mb-2">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">รายได้</span>
                                        <span class="info-box-number">ติดตาม</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="info-box bg-info">
                                    <span class="info-box-icon"><i class="fas fa-link"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">ลิงก์แนะนำ</span>
                                        <span class="info-box-number">จัดการ</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <div class="btn-group w-100" role="group">
                                @can('affiliate-list')
                                    <a href="{{ route('affiliate.index') }}" class="btn btn-success">
                                        <i class="fas fa-external-link-alt"></i>
                                        เข้าสู่หน้าพันธมิตร
                                    </a>
                                @endcan

                                @php
                                    $affiliateRoute = null;
                                    try {
                                        $affiliateRoute = route('my_affiliate.index');
                                    } catch (Exception $e) {
                                        // Route doesn't exist
                                    }
                                @endphp

                                @if($affiliateRoute)
                                    <a href="{{ $affiliateRoute }}" class="btn btn-outline-success">
                                        <i class="fas fa-user-tie"></i>
                                        จัดการพันธมิตรของฉัน
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blog Section -->
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="card card-info card-outline h-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-blog"></i>
                            ระบบบล็อก (Blog)
                        </h3>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p class="card-text">เขียนและจัดการบทความบล็อกของคุณ</p>

                        <div class="row mt-3">
                            <div class="col-md-6 mb-2">
                                <div class="info-box bg-primary">
                                    <span class="info-box-icon"><i class="fas fa-pen"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">เขียนบทความ</span>
                                        <span class="info-box-number">ใหม่</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="info-box bg-warning">
                                    <span class="info-box-icon"><i class="fas fa-list"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">จัดการ</span>
                                        <span class="info-box-number">บทความ</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <div class="btn-group w-100" role="group">
                                @can('blog-create')
                                    <a href="{{ route('blogs.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        เขียนบล็อกใหม่
                                    </a>
                                @endcan

                                @can('blog-list')
                                    <a href="{{ route('blogs.index') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-list"></i>
                                        จัดการบล็อก
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt"></i>
                            การดำเนินการด่วน
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Quick Stats -->
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3 id="total-users">-</h3>
                                        <p>ผู้ใช้ทั้งหมด</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3 id="active-affiliates">-</h3>
                                        <p>พันธมิตรที่ใช้งาน</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-handshake"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3 id="published-blogs">-</h3>
                                        <p>บล็อกที่เผยแพร่</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-blog"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3 id="pending-reviews">-</h3>
                                        <p>รอการอนุมัติ</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Load quick stats
    loadQuickStats();

    function loadQuickStats() {
        // You can implement AJAX calls here to load actual statistics
        // For now, showing placeholder values
        $('#total-users').text('Loading...');
        $('#active-affiliates').text('Loading...');
        $('#published-blogs').text('Loading...');
        $('#pending-reviews').text('Loading...');

        // Simulate loading delay
        setTimeout(function() {
            $('#total-users').text('0');
            $('#active-affiliates').text('0');
            $('#published-blogs').text('0');
            $('#pending-reviews').text('0');
        }, 1000);
    }

    // Add hover effects to cards
    $('.card').hover(
        function() {
            $(this).addClass('shadow-lg').css('transform', 'translateY(-2px)');
        },
        function() {
            $(this).removeClass('shadow-lg').css('transform', 'translateY(0)');
        }
    );
});
</script>
@endsection

@section('style')
<style>
.card {
    transition: all 0.3s ease;
    border-radius: 10px;
}

.card:hover {
    transform: translateY(-2px);
}

.info-box {
    border-radius: 8px;
    margin-bottom: 10px;
}

.small-box {
    border-radius: 8px;
}

.btn-group .btn {
    border-radius: 6px;
}

.btn-group .btn:first-child {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.btn-group .btn:last-child {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.content-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 0 0 20px 20px;
    margin-bottom: 20px;
}

.content-header h1 {
    color: white;
}

.breadcrumb {
    background: transparent;
}

.breadcrumb-item.active {
    color: rgba(255, 255, 255, 0.8);
}
</style>
@endsection
