<style>
    /* Premium Orange Modal Theme */
    .purchase-modal.premium .modal-dialog {
        max-width: 720px;
    }

    .purchase-modal.premium .modal-content {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        background: linear-gradient(135deg, #FFA62E 0%, #FF7B00 100%);
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.18);
        animation: pm-slide-in .35s cubic-bezier(.25, .46, .45, .94);
    }

    .purchase-modal.premium .modal-header {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(14px);
        border: none;
        padding: 1.5rem 1.75rem;
        position: relative;
    }

    .purchase-modal.premium .modal-title {
        color: #fff;
        font-weight: 800;
        letter-spacing: .2px;
    }

    .purchase-modal.premium .btn-close-x {
        position: absolute;
        right: 12px;
        top: 12px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.22);
        color: #fff;
        border: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: transform .2s ease, background .2s ease;
    }

    .purchase-modal.premium .btn-close-x:hover {
        transform: scale(1.06);
        background: rgba(255, 255, 255, 0.32);
    }

    .purchase-modal.premium .modal-body {
        background: rgba(255, 255, 255, 0.96);
        padding: 1.75rem;
    }

    .purchase-modal.premium .intro {
        background: linear-gradient(135deg, rgba(255, 166, 46, .12), rgba(255, 123, 0, .12));
        border: 1px solid rgba(255, 166, 46, .35);
        border-radius: 14px;
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
    }

    .purchase-modal.premium .intro h6 {
        margin: 0 0 .25rem;
        color: #2d3748;
        font-weight: 700;
    }

    .purchase-modal.premium .intro p {
        margin: 0;
        color: #6b7280;
    }

    .purchase-modal.premium label {
        font-weight: 600;
        color: #374151;
    }

    .purchase-modal.premium .form-control,
    .purchase-modal.premium select {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: .7rem .9rem;
        transition: box-shadow .2s ease, border-color .2s ease;
    }

    .purchase-modal.premium .form-control:focus,
    .purchase-modal.premium select:focus {
        border-color: #FF8C00;
        box-shadow: 0 0 0 4px rgba(255, 140, 0, .15);
    }

    .purchase-modal.premium .divider {
        border-top: 1px dashed #e5e7eb;
        margin: 1rem 0;
    }

    .purchase-modal.premium .modal-footer {
        border: 0;
        background: rgba(255, 255, 255, 0.1);
        padding: 1rem 1.75rem 1.5rem;
    }

    .purchase-modal.premium .btn-primary-orange {
        background: linear-gradient(135deg, #FF8C00, #FF6A00);
        border: 0;
        color: #fff;
        font-weight: 700;
        padding: .7rem 1.25rem;
        border-radius: 999px;
        box-shadow: 0 8px 24px rgba(255, 140, 0, .35);
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .purchase-modal.premium .btn-primary-orange:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(255, 140, 0, .45);
    }

    .purchase-modal.premium .btn-ghost {
        background: transparent;
        border: 2px solid rgba(255, 166, 46, .55);
        color: #FF7B00;
        font-weight: 700;
        padding: .6rem 1.1rem;
        border-radius: 999px;
    }

    .purchase-modal.premium .help-text {
        color: #ef4444;
        font-weight: 600;
    }

    @keyframes pm-slide-in {
        from {
            opacity: 0;
            transform: translateY(-14px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 576px) {
        .purchase-modal.premium .modal-dialog {
            margin: .75rem;
        }
    }
</style>

<div class="modal fade purchase-modal premium" id="receiptModal" tabindex="-1" aria-labelledby="purchaseModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="modal-header">
                <h5 class="modal-title" id="purchaseModalLabel">Purchase Course</h5>
                <button type="button" class="btn-close-x" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>

            <form id="courseForm" class="form">
                @csrf

                <div class="modal-body" id="modal-body">
                    <div class="intro">
                        <h6>กรอกข้อมูลเพื่อจองคอร์ส / Purchase Information</h6>
                        <p>ระบุข้อมูลของผู้เรียนและผู้ปกครอง พร้อมเลือกสาขาและเวลาที่สะดวก</p>
                    </div>
                    {{-- <div class="text-center">

                        <p class="theme_text">Pls contact Admin / โปรติดต่อ แอดมิน</p>
                        <img src="{{ asset('images/line/qr-line.jpg') }}" alt="">
                        <h3>
                            <a href="https://line.me/ti/p/z8UOvSf1hX" target="_blank" class="theme_btn">Click</a>

                        </h3>

                    </div> --}}
                    @if ($ref)
                        <p>Your referral code: <strong>{{ $ref ?? '' }}</strong></p>
                    @endif
                    <input type="hidden" name="course_id" id="course_id" value="">
                    <input type="hidden" name="ref" value="{{ $ref ?? '' }}">

                    <div class="form-group">
                        <label for="name">Your Parent Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Your Parent Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="telp">Your Parent Phone</label>
                        <input type="text" class="form-control" id="telp" name="telp" required>
                    </div>
                    <div class="form-group">
                        <label for="telp">Appointment date</label>
                        <input type="datetime-local" class="form-control" id="appointment_date" name="appointment_date"
                            required>
                    </div>
                    <div class="divider"></div>

                    <div class="form-group">
                        <label for="name">Student Name</label>
                        <input type="text" class="form-control" id="student_name" name="student_name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Student Nick Name</label>
                        <input type="text" class="form-control" id="student_nickname" name="student_nickname" required>
                    </div>
                    <div class="form-group">
                        <label for="Grade">Grade</label>
                        <select name="grade" id="grade" class="form-control" required>
                            <option value="">Select grade</option>
                            <option value="K1">K1</option>
                            <option value="K2">K2</option>
                            @for ($i = 1; $i <= 6; $i++)
                                <option value="P{{ $i }}">P{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="divider"></div>
                    <span class="text-danger">**เลือก สาขา/วัน/ช่วงเวลา ที่สะดวกเข้าเรียน</span>
                    <div class="form-group">
                        <label>Department </label>
                        <div>
                            <select name="department_id" id="department" class="form-control" required>
                                <option value="">Select department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}
                                        ({{ $department->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-6">
                                {{-- days --}}
                                <label>Days </label>

                                <select name="day" id="day" class="form-control" required>
                                    <option value="">Select day</option>
                                    @foreach ($days as $day)
                                        <option value="{{ $day }}">{{ $day }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label>period</label>

                                <select name="period" id="teaching_period" class="form-control" required>
                                    <option value=""> Period</option>
                                    {{-- @foreach ($teaching_periods as $teaching_period)
                                        <option value="{{ $teaching_period->id }}">{{ $teaching_period->name }}
                                        </option>
                                    @endforeach --}}
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr>

                    <div class="form-group">
                        <label>Parent Type</label>
                        <div>
                            <label>
                                <input type="radio" name="type_parent" value="father" required> Father
                            </label>
                            <label>
                                <input type="radio" name="type_parent" value="mother" required> Mother
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="course_name">Course Name</label>
                        <input type="text" class="form-control" id="course_name" name="course_name" readonly>
                    </div>
                    <div class="form-group">
                        <label for="price">Course Price</label>
                        <input type="number" class="form-control" id="course_price" name="price" readonly>
                    </div>
                </div>
                <div class="modal-footer payment_btn d-flex justify-content-between" id="modal-footer">
                    <button type="button" class="btn-ghost" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="submitForm" class="btn-primary-orange">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
