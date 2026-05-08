@extends('affiliate.backend.my_affiliate-layout')

@section('content')
<!-- Path Route -->
<section class="sms-breadcrumb mb-10 white-box">
    <div class="container-fluid p-0">
        <div class="d-flex flex-wrap justify-content-between">
            <h1 class="text-uppercase">Marketing Materials (สื่อการตลาด)</h1>
        </div>
    </div>
</section>
<!-- End Path Route -->

<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        <!-- Content Composer Section -->
        <div class="row">
            <div class="col-lg-12">
                <div class="white_box mb_30">
                    <div class="box_header common_table_header">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">สร้างคอนเทนต์ของคุณเอง (Content Composer)</h3>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="primary_input mb-25">
                                <label class="primary_input_label" for="custom_caption">เขียนข้อความเชิญชวน (Caption)</label>
                                <textarea class="primary_textarea" name="custom_caption" id="custom_caption" placeholder="เช่น 'เรียนคณิตศาสตร์กับ eiMaths วันนี้รับสิทธิพิเศษมากมาย!'" rows="4"></textarea>
                            </div>
                            
                            <div class="primary_input mb-25">
                                <label class="primary_input_label">ลิงก์แนะนำของคุณ (Your Referral Link)</label>
                                <input class="primary_input_field" type="text" id="referral_url_display" value="{{ $referral_link }}" readonly>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary" id="generate_and_copy">
                                    <i class="ti-files"></i> คัดลอกข้อความและลิงก์
                                </button>
                                <button class="btn btn-outline-success" id="share_fb">
                                    <i class="fab fa-facebook-f"></i> แชร์ไป Facebook
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="preview-area p-3 border rounded" style="background: #f9f9f9; min-height: 200px;">
                                <label class="small text-muted d-block mb-2">ตัวอย่าง (Preview)</label>
                                <div id="media_preview" class="mb-2">
                                    <div class="text-center p-4 text-muted border border-dashed rounded">
                                        ยังไม่ได้เลือกรูปภาพ/วิดีโอ
                                    </div>
                                </div>
                                <div id="text_preview" class="small text-dark" style="white-space: pre-wrap;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Materials Section -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="white_box mb_30">
                    <div class="box_header common_table_header">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">เลือกสื่อจากระบบ (System Materials)</h3>
                        </div>
                    </div>
                    
                    <div class="row">
                        @forelse($contents as $content)
                            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                <div class="card marketing-card h-100 shadow-sm border-0" style="border-radius: 10px; overflow: hidden; border: 1px solid #eee !important;">
                                    <div class="media-container position-relative">
                                        @if($content->type == 'video')
                                            <video width="100%" height="180" style="background: #000; object-fit: contain;">
                                                <source src="{{ asset($content->img) }}" type="video/mp4">
                                            </video>
                                        @else
                                            <img src="{{ asset($content->img) }}" alt="{{ $content->title }}" class="card-img-top" style="height: 180px; object-fit: cover;">
                                        @endif
                                        
                                        <div class="position-absolute top-0 right-0 p-2">
                                            <span class="badge {{ $content->type == 'video' ? 'bg-danger' : 'bg-primary' }} text-white">
                                                {{ ucfirst($content->type) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body p-3">
                                        <h6 class="font-weight-bold mb-1">{{ $content->title }}</h6>
                                        <div class="d-flex flex-column gap-1">
                                            <button class="btn btn-sm btn-outline-primary select-media" 
                                                    data-src="{{ asset($content->img) }}" 
                                                    data-type="{{ $content->type }}"
                                                    data-caption="{{ $content->description }}">
                                                <i class="ti-check-box"></i> เลือกรูปนี้
                                            </button>
                                            <a href="{{ asset($content->img) }}" download class="btn btn-sm btn-link text-muted">โหลดไฟล์ต้นฉบับ</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center p-5">
                                <img src="{{ asset('backend/img/no_data.png') }}" style="max-width: 150px; opacity: 0.5;" class="mb-3">
                                <p class="text-muted">ยังไม่มีสื่อสำเร็จรูปในระบบ แต่คุณสามารถเขียนข้อความแชร์ลิงก์ได้จากส่วนด้านบน</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .gap-2 { gap: 0.5rem; }
    .gap-1 { gap: 0.25rem; }
    .marketing-card { transition: all 0.2s; cursor: pointer; }
    .marketing-card:hover { border-color: var(--system_primery_color) !important; transform: translateY(-3px); }
    .card.selected { border: 2px solid var(--system_primery_color) !important; }
    .primary_textarea {
        width: 100%;
        border: 1px solid #eceef4;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 14px;
        color: #415094;
    }
    .primary_textarea:focus { outline: none; border-color: var(--system_primery_color); }
</style>

@section('script')
<script>
$(document).ready(function() {
    let selectedMedia = null;
    let selectedType = null;
    const refUrl = $('#referral_url_display').val();

    // Sync Text Preview
    $('#custom_caption').on('input', function() {
        $('#text_preview').text($(this).val());
    });

    // Select Media
    $('.select-media').click(function() {
        const src = $(this).data('src');
        const type = $(this).data('type');
        const caption = $(this).data('caption');
        
        selectedMedia = src;
        selectedType = type;
        
        // Update Preview
        if (type === 'video') {
            $('#media_preview').html(`<video width="100%" controls class="rounded"><source src="${src}" type="video/mp4"></video>`);
        } else {
            $('#media_preview').html(`<img src="${src}" class="img-fluid rounded" style="max-height: 200px; width: 100%; object-fit: cover;">`);
        }

        // Optional: Auto-fill caption if user hasn't typed much
        if ($('#custom_caption').val().length < 5 && caption) {
            $('#custom_caption').val(caption).trigger('input');
        }

        $('.marketing-card').removeClass('selected');
        $(this).closest('.marketing-card').addClass('selected');
        toastr.info('เลือกรูปภาพแล้ว');
    });

    // Generate and Copy
    $('#generate_and_copy').click(function() {
        const caption = $('#custom_caption').val();
        const fullText = (caption ? caption + "\n\n" : "") + refUrl;
        
        navigator.clipboard.writeText(fullText).then(() => {
            toastr.success('คัดลอกข้อความและลิงก์แล้ว!');
        });
    });

    // Share Facebook
    $('#share_fb').click(function() {
        const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(refUrl)}`;
        window.open(url, '_blank', 'width=600,height=400');
    });
});
</script>
@endsection
@endsection
