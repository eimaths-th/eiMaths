<style>
    /* Premium Modern Booking Modal */
    .purchase-modal.premium .modal-dialog {
        max-width: 680px;
        margin: 1.5rem auto;
    }

    .purchase-modal.premium .modal-content {
        border: none;
        border-radius: 24px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 25px 60px -15px rgba(255, 115, 0, 0.25), 0 0 1px 1px rgba(0, 0, 0, 0.05);
        animation: pmSlideUp .3s cubic-bezier(0.16, 1, 0.3, 1);
        font-family: 'Prompt', 'Kanit', system-ui, -apple-system, sans-serif;
        max-height: calc(100dvh - 3rem);
    }

    .purchase-modal.premium form {
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .purchase-modal.premium .modal-header {
        background: linear-gradient(135deg, #FF8C00 0%, #FF6A00 100%);
        border: none;
        padding: 1.5rem 1.75rem;
        position: relative;
        color: #ffffff;
    }

    .purchase-modal.premium .modal-title-wrap {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .purchase-modal.premium .modal-title-icon {
        width: 44px;
        height: 44px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .purchase-modal.premium .modal-title {
        color: #fff;
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.3;
    }

    .purchase-modal.premium .modal-subtitle {
        color: rgba(255, 255, 255, 0.88);
        font-size: 0.85rem;
        margin: 0.2rem 0 0 0;
    }

    .purchase-modal.premium .btn-close-x {
        position: absolute;
        right: 1.25rem;
        top: 1.25rem;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        border: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        cursor: pointer;
        transition: all .2s ease;
    }

    .purchase-modal.premium .btn-close-x:hover {
        transform: rotate(90deg);
        background: rgba(255, 255, 255, 0.35);
    }

    .purchase-modal.premium .modal-body {
        padding: 1.75rem;
        background: #fafbfc;
        max-height: calc(85vh - 120px);
        overflow-y: auto;
        min-height: 0;
    }

    /* Selected Course Card Summary */
    .selected-course-banner {
        background: #ffffff;
        border: 1.5px solid #ffedd5;
        border-radius: 16px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        box-shadow: 0 4px 15px rgba(255, 140, 0, 0.06);
    }

    .affiliate-payment-notice {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin: -0.25rem 0 1.5rem;
        padding: 0.9rem 1rem;
        border: 1px solid #f59e0b;
        border-left: 4px solid #ea580c;
        border-radius: 12px;
        background: #fff7ed;
        color: #7c2d12;
    }

    .affiliate-payment-notice__icon {
        flex: 0 0 auto;
        color: #ea580c;
        font-size: 1.1rem;
        line-height: 1.5;
    }

    .affiliate-payment-notice__title {
        margin: 0 0 0.25rem;
        color: #9a3412;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .affiliate-payment-notice__message {
        margin: 0;
        color: #7c2d12;
        font-size: 0.82rem;
        line-height: 1.6;
    }

    .selected-course-info {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .selected-course-badge-icon {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, #FFF7ED, #FFEDD5);
        color: #EA580C;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .selected-course-name {
        font-weight: 700;
        font-size: 1.05rem;
        color: #1f2937;
        margin: 0;
        line-height: 1.3;
    }

    .selected-course-tag {
        font-size: 0.8rem;
        color: #6b7280;
        display: block;
        margin-top: 2px;
    }

    .selected-course-price-wrap {
        text-align: right;
        flex-shrink: 0;
    }

    .selected-course-price-label {
        font-size: 0.75rem;
        color: #9ca3af;
        text-transform: uppercase;
        font-weight: 600;
    }

    .selected-course-price {
        font-size: 1.35rem;
        font-weight: 800;
        color: #ea580c;
        display: block;
        line-height: 1.1;
    }

    /* Section Cards */
    .form-section-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 1.25rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .form-section-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 1rem;
        padding-bottom: 0.6rem;
        border-bottom: 1px dashed #f3f4f6;
    }

    .form-section-header i {
        color: #ea580c;
        font-size: 1rem;
    }

    .form-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #374151;
        margin: 0;
    }

    .purchase-modal.premium label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 0.35rem;
    }

    .purchase-modal.premium .form-label-required::after {
        content: " *";
        color: #ef4444;
    }

    .purchase-modal.premium .form-control,
    .purchase-modal.premium select.form-control {
        border-radius: 12px;
        border: 1.5px solid #e5e7eb;
        padding: 0.65rem 0.95rem;
        font-size: 0.92rem;
        color: #1f2937;
        background-color: #fff;
        transition: all 0.2s ease;
    }

    .purchase-modal.premium .form-control:focus,
    .purchase-modal.premium select.form-control:focus {
        border-color: #FF8C00;
        background-color: #fff;
        box-shadow: 0 0 0 3.5px rgba(255, 140, 0, 0.15);
        outline: none;
    }

    /* Parent Radio Toggle */
    .parent-type-group {
        display: flex;
        gap: 0.75rem;
    }

    .parent-type-option {
        flex: 1;
        position: relative;
    }

    .parent-type-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .parent-type-label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.6rem 1rem;
        background: #f9fafb;
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 600;
        color: #4b5563;
        transition: all 0.2s ease;
        margin: 0;
    }

    .parent-type-option input[type="radio"]:checked + .parent-type-label {
        background: #fff7ed;
        border-color: #ea580c;
        color: #ea580c;
        box-shadow: 0 2px 8px rgba(234, 88, 12, 0.12);
    }

    /* Referral Tag */
    .referral-tag-badge {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        padding: 0.5rem 0.85rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 1rem;
    }

    /* Modal Footer */
    .purchase-modal.premium .modal-footer {
        border-top: 1px solid #f3f4f6;
        background: #ffffff;
        padding: 1rem 1.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }

    .purchase-modal.premium .btn-primary-orange {
        background: linear-gradient(135deg, #FF8C00 0%, #FF6A00 100%);
        border: 0;
        color: #fff;
        font-weight: 700;
        font-size: 0.95rem;
        padding: 0.75rem 1.75rem;
        border-radius: 14px;
        box-shadow: 0 8px 20px rgba(255, 106, 0, 0.3);
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }

    .purchase-modal.premium .btn-primary-orange:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(255, 106, 0, 0.4);
        color: #fff;
    }

    .purchase-modal.premium .btn-ghost {
        background: #f3f4f6;
        border: none;
        color: #4b5563;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 0.7rem 1.25rem;
        border-radius: 12px;
        transition: all 0.2s ease;
    }

    .purchase-modal.premium .btn-ghost:hover {
        background: #e5e7eb;
        color: #1f2937;
    }

    @keyframes pmSlideUp {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @media (max-width: 576px) {
        .purchase-modal.premium .modal-dialog {
            margin: 0.5rem;
            height: calc(100% - 1rem);
        }
        .purchase-modal.premium .modal-content {
            max-height: 100%;
        }
        .purchase-modal.premium .modal-header {
            padding: 1rem;
        }
        .purchase-modal.premium .modal-title-wrap {
            padding-right: 2.25rem;
        }
        .purchase-modal.premium .modal-title-icon {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
        }
        .purchase-modal.premium .modal-body {
            padding: 1.2rem;
            max-height: none;
            flex: 1 1 auto;
        }
        .selected-course-banner {
            flex-direction: column;
            align-items: flex-start;
        }
        .selected-course-price-wrap {
            text-align: left;
            width: 100%;
            border-top: 1px dashed #fed7aa;
            padding-top: 0.5rem;
            margin-top: 0.25rem;
        }
        .purchase-modal.premium .modal-footer {
            padding: 0.75rem;
            gap: 0.5rem;
        }
        .purchase-modal.premium .btn-primary-orange {
            flex: 1;
            justify-content: center;
            padding-inline: 0.75rem;
        }
    }
</style>

<div class="modal fade purchase-modal premium" id="receiptModal" tabindex="-1" aria-labelledby="purchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" id="modal-header">
                <div class="modal-title-wrap">
                    <div class="modal-title-icon">
                        <i class="fas fa-graduation-cap text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="purchaseModalLabel">ลงทะเบียนและจองคอร์สเรียน</h5>
                        <p class="modal-subtitle">กรอกข้อมูลผู้เรียนเพื่อรับคำปรึกษาและนัดหมายเข้าเรียน</p>
                    </div>
                </div>
                <button type="button" class="btn-close-x" data-bs-dismiss="modal" aria-label="ปิดฟอร์มสมัครคอร์ส">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="courseForm" class="form">
                @csrf

                <div class="modal-body" id="modal-body">
                    @if (!empty($ref))
                        <div class="referral-tag-badge">
                            <i class="fas fa-gift text-success"></i>
                            <span>สิทธิพิเศษจากผู้แนะนำ (Referral Code): <strong>{{ $ref }}</strong></span>
                        </div>
                    @endif

                    <!-- Selected Course Summary -->
                    <div class="selected-course-banner">
                        <div class="selected-course-info">
                            <div class="selected-course-badge-icon">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <div>
                                <h6 class="selected-course-name" id="modalCourseTitleDisplay">เลือกคอร์สเรียน</h6>
                                <span class="selected-course-tag">หลักสูตรคณิตศาสตร์ eiMaths</span>
                            </div>
                        </div>
                        <div class="selected-course-price-wrap">
                            <span class="selected-course-price-label">ค่าคอร์สเรียน</span>
                            <span class="selected-course-price" id="modalCoursePriceDisplay">0 ฿</span>
                        </div>
                    </div>

                    <aside class="affiliate-payment-notice" role="note" aria-labelledby="affiliate-payment-notice-title">
                        <div class="affiliate-payment-notice__icon" aria-hidden="true">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h6 id="affiliate-payment-notice-title" class="affiliate-payment-notice__title">
                                {{ __('affiliate.course_payment_notice.title') }}
                            </h6>
                            <p class="affiliate-payment-notice__message">
                                {{ __('affiliate.course_payment_notice.message') }}
                            </p>
                        </div>
                    </aside>

                    <!-- Hidden Form Inputs for Backend Compatibility -->
                    <input type="hidden" name="course_id" id="course_id" value="">
                    <input type="hidden" name="course_name" id="course_name" value="">
                    <input type="hidden" name="price" id="course_price" value="">
                    <input type="hidden" name="ref" value="{{ $ref ?? '' }}">

                    <!-- Section 1: Parent Information -->
                    <div class="form-section-card">
                        <div class="form-section-header">
                            <i class="fas fa-user-shield"></i>
                            <h6 class="form-section-title">ข้อมูลผู้ปกครอง (Parent Information)</h6>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label-required">ความสัมพันธ์กับนักเรียน</label>
                                <div class="parent-type-group">
                                    <div class="parent-type-option">
                                        <input type="radio" name="type_parent" id="type_parent_mother" value="mother" checked required>
                                        <label for="type_parent_mother" class="parent-type-label">
                                            <i class="fas fa-female"></i> คุณแม่ (Mother)
                                        </label>
                                    </div>
                                    <div class="parent-type-option">
                                        <input type="radio" name="type_parent" id="type_parent_father" value="father" required>
                                        <label for="type_parent_father" class="parent-type-label">
                                            <i class="fas fa-male"></i> คุณพ่อ (Father)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="name" class="form-label-required">ชื่อ - นามสกุลผู้ปกครอง</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="เช่น คุณสมศรี ใจดี" required>
                            </div>

                            <div class="col-md-6">
                                <label for="telp" class="form-label-required">เบอร์โทรศัพท์ติดต่อ</label>
                                <input type="tel" class="form-control" id="telp" name="telp" placeholder="08x-xxx-xxxx" required>
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label-required">อีเมล (สำหรับรับใบเสร็จและข้อมูลคอร์ส)</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="parent@example.com" required>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Student Information -->
                    <div class="form-section-card">
                        <div class="form-section-header">
                            <i class="fas fa-child"></i>
                            <h6 class="form-section-title">ข้อมูลนักเรียน (Student Information)</h6>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="student_name" class="form-label-required">ชื่อ - นามสกุลนักเรียน</label>
                                <input type="text" class="form-control" id="student_name" name="student_name" placeholder="เช่น ด.ช. รักเรียน ใจดี" required>
                            </div>

                            <div class="col-md-3">
                                <label for="student_nickname" class="form-label-required">ชื่อเล่น</label>
                                <input type="text" class="form-control" id="student_nickname" name="student_nickname" placeholder="น้องกาย" required>
                            </div>

                            <div class="col-md-3">
                                <label for="grade" class="form-label-required">ระดับชั้น</label>
                                <select name="grade" id="grade" class="form-control" required>
                                    <option value="">เลือกระดับชั้น</option>
                                    <optgroup label="อนุบาล (Kindergarten)">
                                        <option value="K1">K1 (อนุบาล 1)</option>
                                        <option value="K2">K2 (อนุบาล 2-3)</option>
                                    </optgroup>
                                    <optgroup label="ประถมศึกษา (Primary)">
                                        @for ($i = 1; $i <= 6; $i++)
                                            <option value="P{{ $i }}">P{{ $i }} (ประถม {{ $i }})</option>
                                        @endfor
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Branch & Schedule -->
                    <div class="form-section-card mb-0">
                        <div class="form-section-header">
                            <i class="fas fa-map-marker-alt"></i>
                            <h6 class="form-section-title">สาขาและเวลานัดหมายเข้าเรียน</h6>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="department" class="form-label-required">สาขาที่สะดวกเรียน</label>
                                <select name="department_id" id="department" class="form-control" required>
                                    <option value="">-- เลือกสาขา eiMaths --</option>
                                    @foreach ($departments as $department)
                                        <option
                                            value="{{ $department->id }}"
                                            data-branch-code="{{ strtoupper(trim((string) $department->code)) }}"
                                            data-branch-name="{{ $department->name }}"
                                        >{{ $department->name }} ({{ $department->code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="day" class="form-label-required">วันที่สะดวกเรียนประจำสัปดาห์</label>
                                <select name="day" id="day" class="form-control" required>
                                    <option value="">-- เลือกวัน --</option>
                                    @foreach ($days as $day)
                                        <option value="{{ $day }}">{{ $day }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="teaching_period" class="form-label-required">ช่วงเวลาเรียน (Period)</label>
                                <select name="period" id="teaching_period" class="form-control" required>
                                    <option value="">-- เลือกช่วงเวลา --</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="appointment_date" class="form-label-required">วันและเวลาที่นัดหมายเริ่มเรียน / ทดสอบทักษะ</label>
                                <input type="datetime-local" class="form-control" id="appointment_date" name="appointment_date" required>
                                <small class="text-muted mt-1 d-block">
                                    <i class="fas fa-info-circle text-warning me-1"></i> เจ้าหน้าที่จะติดต่อกลับเพื่อยืนยันตารางเรียนล่วงหน้า
                                </small>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer" id="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">
                        ยกเลิก
                    </button>
                    <button type="submit" id="submitForm" class="btn-primary-orange">
                        <i class="fas fa-check-circle"></i>
                        <span>ยืนยันการจองคอร์สเรียน</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
