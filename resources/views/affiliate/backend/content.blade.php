@extends('affiliate.backend.my_affiliate-layout')

@section('content')
<div class="container-fluid p-0">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="white_box mb_30">
                <div class="box_header common_table_header">
                    <div class="main-title d-md-flex">
                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">Marketing Materials (สื่อการตลาด)</h3>
                    </div>
                </div>
                
                <div class="row">
                    @forelse($contents as $content)
                        <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                            <div class="card marketing-card h-100 shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                                <div class="media-container position-relative">
                                    @if($content->type == 'video')
                                        <video width="100%" height="220" controls style="background: #000; object-fit: contain;">
                                            <source src="{{ asset($content->img) }}" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    @else
                                        <img src="{{ asset($content->img) }}" alt="{{ $content->title }}" class="card-img-top" style="height: 220px; object-fit: cover;">
                                    @endif
                                    
                                    <div class="type-badge position-absolute top-0 right-0 p-2">
                                        <span class="badge {{ $content->type == 'video' ? 'bg-danger' : 'bg-primary' }} text-white">
                                            {{ ucfirst($content->type) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <h5 class="card-title font-weight-bold">{{ $content->title }}</h5>
                                    <p class="card-text text-muted mb-3" style="font-size: 0.9rem; line-height: 1.4;">
                                        {{ $content->description ?? 'ไม่มีคำบรรยาย' }}
                                    </p>
                                    
                                    <div class="actions d-flex flex-wrap gap-2">
                                        <!-- Download Button -->
                                        <a href="{{ asset($content->img) }}" download="{{ $content->title }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                                            <i class="ti-download"></i> Download
                                        </a>
                                        
                                        <!-- Copy Caption & Link -->
                                        @php
                                            $ref_link = ($content->url ?: url('/')) . (strpos(($content->url ?: url('/')), '?') !== false ? '&' : '?') . 'ref=' . $user->id;
                                            $share_text = ($content->description ? $content->description . "\n\n" : "") . $ref_link;
                                        @endphp
                                        <button class="btn btn-sm btn-primary flex-grow-1 copy-btn" data-text="{{ $share_text }}">
                                            <i class="ti-files"></i> Copy Link
                                        </button>
                                    </div>
                                    
                                    <div class="social-share mt-3 pt-3 border-top d-flex justify-content-around">
                                        <!-- Facebook Share -->
                                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($ref_link) }}" target="_blank" class="text-primary">
                                            <i class="fab fa-facebook-square fa-2x"></i>
                                        </a>
                                        <!-- LINE Share -->
                                        <a href="https://social-plugins.line.me/lineit/share?url={{ urlencode($ref_link) }}&text={{ urlencode($content->description) }}" target="_blank" class="text-success">
                                            <i class="fab fa-line fa-2x"></i>
                                        </a>
                                        <!-- Twitter Share -->
                                        <a href="https://twitter.com/intent/tweet?url={{ urlencode($ref_link) }}&text={{ urlencode($content->description) }}" target="_blank" class="text-info">
                                            <i class="fab fa-twitter-square fa-2x"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center p-5">
                            <p class="text-muted">ขณะนี้ยังไม่มีสื่อการตลาดเตรียมไว้ให้สมาชิก</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .marketing-card {
        transition: transform 0.2s;
    }
    .marketing-card:hover {
        transform: translateY(-5px);
    }
    .gap-2 { gap: 0.5rem; }
</style>

<script>
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const text = this.getAttribute('data-text');
        navigator.clipboard.writeText(text).then(() => {
            toastr.success('คัดลอกข้อความและลิงก์แล้ว!', {timeOut: 2000});
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    });
});
</script>
@endsection
