@extends('affiliate.frontend.main')
@section('style')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        #loading {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            z-index: 9999;
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
@endsection
@section('content')
    <div id="loading" style="display: none;">
        <div class="spinner"></div>
        <p class="text-white">Sending email...</p>
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

    <x-course-page-section :request="$request" :categories="$categories" :languages="$languages" />

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
            background: linear-gradient(45deg, rgba(255,255,255,0.05) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.05) 75%);
            background-size: 40px 40px;
            animation: footerPattern 20s linear infinite;
        }
        @keyframes footerPattern {
            0% { transform: translateX(0); }
            100% { transform: translateX(40px); }
        }
        .premium-footer .footer-content {
            position: relative;
            z-index: 2;
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.08);
            border-radius: 20px 20px 0 0;
            margin: 0 -15px;
            padding: 3rem 2rem 2rem;
        }
        .premium-footer .footer-logo {
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.2));
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
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .premium-footer .footer-text {
            color: rgba(255,255,255,0.9);
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
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            color: #fff;
            font-size: 1.4rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            border: 2px solid rgba(255,255,255,0.2);
        }
        .premium-footer .social-link:hover {
            transform: translateY(-5px) scale(1.1);
            background: rgba(255,255,255,0.25);
            border-color: rgba(255,255,255,0.4);
            color: #fff;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
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
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            border: none;
            margin: 2rem 0 1.5rem;
        }
        .premium-footer .copyright {
            text-align: center;
            color: rgba(255,255,255,0.8);
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

    <footer class="premium-footer py-5">
        <div class="container">
            <div class="footer-content">
                <div class="row g-4">
                    <!-- Column 1: Logo & About -->
                    <div class="col-lg-4 text-center text-lg-start">
                        <img src="{{ asset('images/logo.png') }}" alt="eiMaths-TH Logo" class="footer-logo mb-3" style="width: 50%; max-width: 180px; height: auto;">
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
                            <a href="https://www.facebook.com/eimaths.th/?locale=th_TH" target="_blank" class="social-link facebook" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://www.youtube.com/channel/UCiD8a78ZN6SNQViVmG1St0A" target="_blank" class="social-link youtube" title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a href="https://www.tiktok.com/@eimaths.th" target="_blank" class="social-link tiktok" title="TikTok">
                                <i class="fab fa-tiktok"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <hr class="footer-divider">

                <!-- Copyright -->
                <div class="copyright">
                    <p class="mb-0">© {{ date('Y') }} eiMaths-TH. All Rights Reserved. | ระบบการเรียนการสอนคณิตศาสตร์ออนไลน์</p>
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
                .catch(error => console.error('Error:', error));
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
