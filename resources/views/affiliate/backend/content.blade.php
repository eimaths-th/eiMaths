@extends('affiliate.backend.my_affiliate-layout')

@section('style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    /* ช่อง Textarea รองรับ Dark/Light Mode */
    .primary_textarea {
        width: 100%;
        border: 1px solid var(--backend-border-color);
        background: var(--white-box-bg);
        color: var(--dynamic-text-color);
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 14px;
        resize: vertical;
    }
    .primary_textarea:focus {
        outline: none;
        border-color: var(--backend-primary-color);
    }
    .primary_textarea::placeholder {
        color: var(--muted-text);
    }

    /* การ์ดสื่อการตลาด */
    .marketing-card {
        transition: all 0.25s ease;
        cursor: pointer;
        border: 2px solid transparent !important;
        border-radius: 10px !important;
        overflow: hidden;
        background: var(--white-box-bg);
    }
    .marketing-card:hover {
        border-color: var(--backend-primary-color) !important;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .marketing-card.selected {
        border-color: var(--backend-primary-color) !important;
        box-shadow: 0 0 0 3px rgba(85, 110, 230, 0.25);
    }

    /* Preview Area */
    .preview-area {
        background: var(--white-box-bg);
        border-color: var(--backend-border-color) !important;
        border-radius: 8px;
    }

    /* ปุ่มแชร์ Social */
    .social-btn-group .btn {
        min-width: 110px;
        font-size: 13px;
    }

    /* Gap Utilities */
    .gap-2 { gap: 0.5rem; }
    .gap-1 { gap: 0.25rem; }

    /* Referral Link Input */
    .referral-link-box {
        background: var(--header-search-bg);
        border: 1px solid var(--backend-border-color);
        border-radius: 5px;
        padding: 10px 15px;
        font-family: monospace;
        font-size: 13px;
        color: var(--dynamic-text-color);
        width: 100%;
        cursor: text;
    }

    /* ข้อความในตัวอย่าง */
    #text_preview {
        color: var(--dynamic-text-color);
    }
    #link_preview {
        color: var(--backend-primary-color);
        word-break: break-all;
        font-size: 12px;
    }
</style>
@endsection

@section('content')
{{-- ส่วนหัวหน้า (Breadcrumb) --}}
<section class="sms-breadcrumb mb-10 white-box">
    <div class="container-fluid p-0">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <h1>สื่อการตลาด (Marketing Materials)</h1>
        </div>
    </div>
</section>

<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">

        {{-- ส่วนวิธีใช้งาน (How to Use) --}}
        <div class="row mb-3">
            <div class="col-lg-12">
                <div class="white_box mb_20">
                    <div class="d-flex justify-content-between align-items-center cursor-pointer"
                         data-bs-toggle="collapse" data-bs-target="#howToUseGuide"
                         aria-expanded="false" aria-controls="howToUseGuide"
                         style="cursor:pointer;">
                        <h5 class="mb-0" style="color:var(--dynamic-text-color);">
                            <i class="ti-help-alt" style="color:var(--backend-primary-color);"></i>
                            วิธีใช้งาน — สร้างคอนเทนต์แชร์ลิงก์แนะนำ
                        </h5>
                        <span class="badge" style="background:var(--backend-primary-color); color:#fff; font-size:12px;">
                            <i class="ti-angle-down"></i> กดเพื่อดู
                        </span>
                    </div>

                    <div class="collapse mt-3" id="howToUseGuide">
                        <div class="row">
                            {{-- ขั้นตอนที่ 1 --}}
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="p-3 rounded text-center h-100" style="background:var(--header-search-bg); border:1px solid var(--backend-border-color);">
                                    <div class="mb-2" style="font-size:36px;">✍️</div>
                                    <h6 style="color:var(--dynamic-text-color);">ขั้นตอนที่ 1</h6>
                                    <p class="small mb-0" style="color:var(--muted-text); line-height:1.5;">
                                        <b>เขียนข้อความเชิญชวน</b><br>
                                        พิมพ์ Caption ที่น่าสนใจ เช่น โปรโมชั่น หรือจุดเด่นของคอร์สเรียน
                                    </p>
                                </div>
                            </div>
                            {{-- ขั้นตอนที่ 2 --}}
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="p-3 rounded text-center h-100" style="background:var(--header-search-bg); border:1px solid var(--backend-border-color);">
                                    <div class="mb-2" style="font-size:36px;">🖼️</div>
                                    <h6 style="color:var(--dynamic-text-color);">ขั้นตอนที่ 2</h6>
                                    <p class="small mb-0" style="color:var(--muted-text); line-height:1.5;">
                                        <b>เลือกรูปภาพ/วิดีโอ</b> (ถ้ามี)<br>
                                        เลื่อนลงไปเลือกสื่อจากระบบ หรือใช้รูปของคุณเอง กดดาวน์โหลดไฟล์ต้นฉบับได้
                                    </p>
                                </div>
                            </div>
                            {{-- ขั้นตอนที่ 3 --}}
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="p-3 rounded text-center h-100" style="background:var(--header-search-bg); border:1px solid var(--backend-border-color);">
                                    <div class="mb-2" style="font-size:36px;">📋</div>
                                    <h6 style="color:var(--dynamic-text-color);">ขั้นตอนที่ 3</h6>
                                    <p class="small mb-0" style="color:var(--muted-text); line-height:1.5;">
                                        <b>กดคัดลอกหรือแชร์</b><br>
                                        กด "คัดลอกทั้งหมด" จะได้ข้อความ + ลิงก์แนะนำ หรือกดปุ่ม Social เพื่อแชร์ตรง
                                    </p>
                                </div>
                            </div>
                            {{-- ขั้นตอนที่ 4 --}}
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="p-3 rounded text-center h-100" style="background:var(--header-search-bg); border:1px solid var(--backend-border-color);">
                                    <div class="mb-2" style="font-size:36px;">💰</div>
                                    <h6 style="color:var(--dynamic-text-color);">ขั้นตอนที่ 4</h6>
                                    <p class="small mb-0" style="color:var(--muted-text); line-height:1.5;">
                                        <b>รับค่าคอมมิชชัน!</b><br>
                                        เมื่อมีคนสมัครผ่านลิงก์ของคุณ ระบบจะคำนวณค่าคอมมิชชันให้อัตโนมัติ
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr style="border-color:var(--backend-border-color);">

                        <div class="row">
                            <div class="col-lg-6">
                                <h6 style="color:var(--dynamic-text-color);"><i class="ti-mobile"></i> แชร์บนมือถือ</h6>
                                <ul class="small" style="color:var(--muted-text); line-height:2;">
                                    <li>กดปุ่ม <b>Instagram</b> หรือ <b>TikTok</b> → ระบบจะเปิดหน้าต่างแชร์ของมือถือ ส่งรูป+ข้อความ+ลิงก์ได้เลย</li>
                                    <li>กดปุ่ม <b>Facebook</b> หรือ <b>LINE</b> → เปิดหน้าแชร์พร้อมข้อความอัตโนมัติ</li>
                                </ul>
                            </div>
                            <div class="col-lg-6">
                                <h6 style="color:var(--dynamic-text-color);"><i class="ti-desktop"></i> แชร์บนคอมพิวเตอร์</h6>
                                <ul class="small" style="color:var(--muted-text); line-height:2;">
                                    <li>กดปุ่ม <b>Instagram / TikTok</b> → ข้อความ+ลิงก์จะถูก<b>คัดลอกอัตโนมัติ</b> แล้วเปิดเว็บไซต์ให้ กด <b>Ctrl+V</b> วางข้อความได้ทันที</li>
                                    <li>ถ้าต้องการแนบรูป ให้กด <b>"ดาวน์โหลด"</b> บันทึกรูปก่อน แล้วอัปโหลดเองในโพสต์</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ส่วนที่ 1: Content Composer --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="white_box mb_30">
                    <div class="box_header common_table_header">
                        <div class="main-title">
                            <h3 class="mb-0">สร้างคอนเทนต์ของคุณเอง</h3>
                        </div>
                    </div>

                    <div class="row">
                        {{-- คอลัมน์ซ้าย: แบบฟอร์ม --}}
                        <div class="col-lg-7">
                            <div class="primary_input mb-15">
                                <label class="primary_input_label" for="custom_caption">เขียนข้อความเชิญชวน (Caption)</label>
                                <textarea class="primary_textarea" id="custom_caption" rows="4"
                                    placeholder="เช่น 'สนใจเรียนคณิตศาสตร์ eiMaths ลดพิเศษ! สมัครเลย 👇'"></textarea>
                            </div>

                            <div class="primary_input mb-15">
                                <label class="primary_input_label">ลิงก์แนะนำของคุณ (Referral Link)</label>
                                <input class="referral-link-box" type="text" id="referral_url_display"
                                       value="{{ $referral_link }}" readonly onclick="this.select()">
                            </div>

                            {{-- ปุ่มดำเนินการ --}}
                            <div class="d-flex flex-wrap gap-2 social-btn-group mb-15">
                                <button class="btn btn-primary" id="btn_copy_all" type="button">
                                    <i class="ti-files"></i> คัดลอกทั้งหมด
                                </button>
                                <button class="btn btn-primary" id="btn_copy_link" type="button" style="background:#34a853; border-color:#34a853;">
                                    <i class="ti-link"></i> คัดลอกลิงก์
                                </button>
                            </div>

                            <hr style="border-color: var(--backend-border-color);">

                            <label class="primary_input_label mb-10">แชร์ไปยัง Social Media</label>
                            <div class="d-flex flex-wrap gap-2 social-btn-group">
                                <button class="btn btn-sm" id="share_fb" type="button"
                                    style="background:#1877F2; color:#fff;">
                                    <i class="fab fa-facebook-f"></i> Facebook
                                </button>
                                <button class="btn btn-sm" id="share_line" type="button"
                                    style="background:#06C755; color:#fff;">
                                    <i class="fab fa-line"></i> LINE
                                </button>
                                <button class="btn btn-sm" id="share_ig" type="button"
                                    style="background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888); color:#fff;">
                                    <i class="fab fa-instagram"></i> Instagram
                                </button>
                                <button class="btn btn-sm" id="share_tt" type="button"
                                    style="background:#010101; color:#fff;">
                                    <i class="fab fa-tiktok"></i> TikTok
                                </button>
                                <button class="btn btn-sm" id="share_twitter" type="button"
                                    style="background:#1DA1F2; color:#fff;">
                                    <i class="fab fa-twitter"></i> Twitter
                                </button>
                            </div>
                        </div>

                        {{-- คอลัมน์ขวา: Live Preview --}}
                        <div class="col-lg-5 mt-3 mt-lg-0">
                            <div class="preview-area p-3 border rounded" style="min-height: 280px;">
                                <label class="small d-block mb-2" style="color:var(--muted-text);">
                                    <i class="ti-eye"></i> ตัวอย่างก่อนแชร์ (Live Preview)
                                </label>
                                <div id="media_preview" class="mb-2">
                                    <div class="text-center p-4 border rounded" style="border-style:dashed !important; color:var(--muted-text);">
                                        <i class="ti-image" style="font-size:32px;"></i>
                                        <p class="mt-2 mb-0">เลือกรูปภาพ/วิดีโอจากด้านล่าง</p>
                                    </div>
                                </div>
                                <div id="text_preview" class="small mt-2" style="white-space:pre-wrap; line-height:1.6;"></div>
                                <div id="link_preview" class="mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ส่วนที่ 2: สื่อจากระบบ --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="white_box mb_30">
                    <div class="box_header common_table_header">
                        <div class="main-title">
                            <h3 class="mb-0">เลือกสื่อจากระบบ (System Materials)</h3>
                        </div>
                    </div>

                    <div class="row">
                        @forelse($contents as $content)
                            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                <div class="card marketing-card h-100 shadow-sm">
                                    {{-- Preview รูป/วิดีโอ --}}
                                    <div class="media-container position-relative">
                                        @if($content->type == 'video')
                                            <video width="100%" height="160" style="background:#000; object-fit:contain;">
                                                <source src="{{ asset($content->file_path) }}" type="video/mp4">
                                            </video>
                                        @else
                                            <img src="{{ asset($content->file_path) }}" alt="{{ $content->title }}"
                                                 class="card-img-top" style="height:160px; object-fit:cover;">
                                        @endif
                                        <div class="position-absolute" style="top:8px; right:8px;">
                                            <span class="badge {{ $content->type == 'video' ? 'bg-danger' : 'bg-info' }} text-white">
                                                {{ $content->type == 'video' ? '🎬 Video' : '🖼 Image' }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- ข้อมูลการ์ด --}}
                                    <div class="card-body p-3">
                                        <h6 class="font-weight-bold mb-2" style="color:var(--dynamic-text-color);">
                                            {{ $content->title }}
                                        </h6>
                                        @if($content->description)
                                            <p class="small mb-2" style="color:var(--muted-text); line-height:1.4;">
                                                {{ Str::limit($content->description, 60) }}
                                            </p>
                                        @endif
                                        <div class="d-flex flex-column gap-1">
                                            <button class="btn btn-sm btn-outline-primary select-media"
                                                    data-src="{{ asset($content->file_path) }}"
                                                    data-type="{{ $content->type }}"
                                                    data-caption="{{ $content->description }}" type="button">
                                                <i class="ti-check-box"></i> เลือกใช้สื่อนี้
                                            </button>
                                            <a href="{{ asset($content->file_path) }}" download
                                               class="btn btn-sm btn-link" style="color:var(--muted-text);">
                                                <i class="ti-download"></i> ดาวน์โหลด
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center p-5">
                                <i class="ti-gallery" style="font-size:48px; color:var(--muted-text);"></i>
                                <p class="mt-3" style="color:var(--muted-text);">
                                    ยังไม่มีสื่อในระบบ คุณสามารถพิมพ์ข้อความ + ลิงก์แนะนำ แล้วแชร์ได้จากส่วนด้านบนเลย
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(document).ready(function() {
    // ค่าเริ่มต้น
    var selected_media = null;
    var ref_url = $('#referral_url_display').val();

    // กำหนดค่า toastr
    toastr.options = {
        closeButton: true,
        positionClass: 'toast-top-right',
        timeOut: 2500
    };

    // ฟังก์ชันคัดลอกข้อความ (รองรับทั้ง HTTPS และ HTTP)
    function CopyToClipboard(text, success_msg) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function() {
                toastr.success(success_msg || 'คัดลอกแล้ว!');
            });
        } else {
            // Fallback สำหรับ HTTP
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                toastr.success(success_msg || 'คัดลอกแล้ว!');
            } catch (err) {
                toastr.error('ไม่สามารถคัดลอกได้ กรุณาคัดลอกด้วยตนเอง');
            }
            document.body.removeChild(textarea);
        }
    }

    // สร้างข้อความเต็ม (Caption + Link)
    function BuildFullText() {
        var caption = $('#custom_caption').val().trim();
        return (caption ? caption + '\n\n' : '') + ref_url;
    }

    // ===== Live Preview =====
    function UpdatePreview() {
        var caption = $('#custom_caption').val().trim();
        $('#text_preview').text(caption || '');
        $('#link_preview').html(ref_url ? '<a href="' + ref_url + '" target="_blank">' + ref_url + '</a>' : '');
    }

    $('#custom_caption').on('input', UpdatePreview);
    UpdatePreview(); // แสดง link ตั้งแต่เริ่มต้น

    // ===== เลือกสื่อจากระบบ =====
    $('.select-media').click(function() {
        var src = $(this).data('src');
        var type = $(this).data('type');
        var caption = $(this).data('caption');

        selected_media = src;

        // อัปเดต Preview
        if (type === 'video') {
            $('#media_preview').html(
                '<video width="100%" controls class="rounded" style="max-height:220px;">' +
                '<source src="' + src + '" type="video/mp4"></video>'
            );
        } else {
            $('#media_preview').html(
                '<img src="' + src + '" class="img-fluid rounded" style="max-height:220px; width:100%; object-fit:cover;">'
            );
        }

        // ใส่ caption ตัวอย่างถ้าผู้ใช้ยังไม่ได้พิมพ์
        if ($('#custom_caption').val().trim().length < 5 && caption) {
            $('#custom_caption').val(caption);
            UpdatePreview();
        }

        // Highlight การ์ดที่เลือก
        $('.marketing-card').removeClass('selected');
        $(this).closest('.marketing-card').addClass('selected');
        toastr.info('เลือกสื่อแล้ว');
    });

    // ===== ปุ่มคัดลอก =====
    $('#btn_copy_all').click(function() {
        CopyToClipboard(BuildFullText(), 'คัดลอกข้อความ + ลิงก์แล้ว!');
    });

    $('#btn_copy_link').click(function() {
        CopyToClipboard(ref_url, 'คัดลอกลิงก์แล้ว!');
    });

    // ===== แชร์ Social Media =====

    // ฟังก์ชันหลัก: ใช้ Web Share API ถ้ารองรับ (มือถือ)
    async function NativeShare() {
        var text = BuildFullText();
        var share_data = {
            title: 'eiMaths - เรียนคณิตศาสตร์ออนไลน์',
            text: text,
            url: ref_url
        };

        // ถ้าเลือกรูปไว้ ให้แนบไฟล์ด้วย (Web Share API Level 2)
        if (selected_media && navigator.canShare) {
            try {
                var response = await fetch(selected_media);
                var blob = await response.blob();
                var file_name = selected_media.split('/').pop();
                var file = new File([blob], file_name, { type: blob.type });
                if (navigator.canShare({ files: [file] })) {
                    share_data.files = [file];
                }
            } catch(e) {
                // ไม่สามารถดึงไฟล์ได้ ข้ามไป
            }
        }

        try {
            await navigator.share(share_data);
            toastr.success('แชร์สำเร็จ!');
        } catch(err) {
            if (err.name !== 'AbortError') {
                toastr.info('ยกเลิกการแชร์');
            }
        }
    }

    // ฟังก์ชันแชร์แบบ Fallback: คัดลอก + เปิดแอป + แจ้งเตือน
    function ShareWithCopy(platform_name, platform_url) {
        var text = BuildFullText();
        CopyToClipboard(text, '');
        toastr.info(
            '<b>ข้อความและลิงก์ถูกคัดลอกแล้ว!</b><br>' +
            '📋 กด <b>วาง (Paste)</b> ใน ' + platform_name + ' เพื่อโพสต์<br>' +
            '<small>ถ้าเลือกรูปไว้ ให้บันทึกรูปแล้วแนบเอง</small>',
            '', { timeOut: 5000, extendedTimeOut: 3000, enableHtml: true }
        );
        setTimeout(function() {
            window.open(platform_url, '_blank');
        }, 1500);
    }

    // Facebook: ใช้ feed dialog ส่ง quote (ข้อความ) ได้
    $('#share_fb').click(function() {
        var caption = $('#custom_caption').val().trim();
        var quote = caption || '';
        var url = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(ref_url) +
                  '&quote=' + encodeURIComponent(quote);
        CopyToClipboard(BuildFullText(), '');
        toastr.info(
            '<b>กำลังเปิด Facebook...</b><br>' +
            '📋 ข้อความถูกคัดลอกแล้ว กด <b>วาง</b> ในโพสต์ได้เลย',
            '', { timeOut: 4000, enableHtml: true }
        );
        setTimeout(function() { window.open(url, '_blank', 'width=600,height=500'); }, 1000);
    });

    // LINE: ส่ง text ไปได้ตรง
    $('#share_line').click(function() {
        var text = BuildFullText();
        var url = 'https://social-plugins.line.me/lineit/share?url=' + encodeURIComponent(ref_url) +
                  '&text=' + encodeURIComponent(text);
        window.open(url, '_blank', 'width=600,height=500');
    });

    // Instagram: ไม่มี Share API จากเว็บ → คัดลอก + เปิด IG
    $('#share_ig').click(function() {
        if (navigator.share) {
            NativeShare();
        } else {
            ShareWithCopy('Instagram', 'https://www.instagram.com/');
        }
    });

    // TikTok: ไม่มี Share API จากเว็บ → คัดลอก + เปิด TikTok
    $('#share_tt').click(function() {
        if (navigator.share) {
            NativeShare();
        } else {
            ShareWithCopy('TikTok', 'https://www.tiktok.com/');
        }
    });

    // Twitter: ส่ง text ไปได้ตรง
    $('#share_twitter').click(function() {
        var text = BuildFullText();
        window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(text),
            '_blank', 'width=600,height=400');
    });
});
</script>
@endsection
