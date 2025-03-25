@extends('affiliate.frontend.main')
@section('style')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

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


    {{-- <div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="receiptModalLabel">Purchase Receipt</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Your referral code: <strong>{{ $ref }}</strong></p>
                    <!-- Add more details related to the purchase here -->
                    <p>Course details, price, and other information related to the purchase can go here.</p>
                </div>
                
            </div>
        </div>
    </div> --}}
    {{-- @include('affiliate.modal_line_contact') --}}
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <!-- Column 1: Logo & About -->
                <div class="col-md-4 text-center text-md-start">
                    <img src="{{ asset('images/logo.png') }}" alt="eiMaths-TH Logo" style="width: 40%; height: auto;">
                    {{-- <h5 class="mt-2">eiMaths-TH</h5> --}}
                    <p class="text-white">Providing quality content and services since 2024.</p>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="col-md-4">
                    <!-- footer.courses.header2 -->
                    <h5>ประกาศ</h5>
                    <!-- footer.courses.content2 -->
                    ราคาคอร์สเรียนไม่รวมค่าแรกเข้า และ ค่าอุปกรณ์การเรียน
                    <br>
                </div>

                <!-- Column 3: Social Media -->
                <div class="col-md-4 text-center text-md-end">
                    <h5>Follow Us</h5>
                    <a href="https://www.facebook.com/eimaths.th/?locale=th_TH" target="_blank" class="text-white me-2">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="https://www.youtube.com/channel/UCiD8a78ZN6SNQViVmG1St0A" target="_blank"
                        class="text-white me-2">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://www.tiktok.com/@eimaths.th" target="_blank" class="text-white me-2">
                        <i class="fa-brands fa-tiktok"></i>
                    </a>
                </div>
            </div>

            <hr class="mt-4">

            <!-- Copyright -->
            <div class="text-center">
                <p>© {{ date('Y') }} eiMaths-TH. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    {{-- <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <img src="your-logo.png" alt="Logo" style="width: 150px; height: auto;">
            <p class="mt-2">© 2024 EIMaths. All Rights Reserved.</p>
            <div class="social-icons">
                <a href="https://www.tiktok.com/@eimaths.th" class="text-white me-2">
                    <i class="fab fa-tiktok"></i>
                </a>
                <a href="https://www.facebook.com/eimaths" class="text-white me-2">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="https://www.instagram.com/eimaths" class="text-white me-2">
                    <i class="fab fa-instagram"></i>
                </a>
            </div>
        </div>
    </footer> --}}
    @include('affiliate.frontend.promotion_modal')
@endsection
@section('script')
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script> --}}
    <script>
        $(document).ready(function() {
            $('#promoModal').modal('show');
            
            // setTimeout(function() {
            //     $('#promoModal').modal('show');
            // }, 2000); // Show after 2 seconds
        });
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
            // Listen for clicks on modal trigger buttons
            document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
                button.addEventListener('click', (e) => {
                    const courseName = button.getAttribute('data-course-name');
                    const link = e.target.closest('[data-bs-toggle="modal"]');
                    const title = link.getAttribute('data-title');
                    const courseId = link.getAttribute('data-course-id');
                    const price = link.getAttribute('data-price');

                    // Populate the form fields inside the modal
                    $('#purchaseModalLabel').text(title);
                    $('#course_id').val(courseId);
                    $('#course_name').val(title);
                    $('#course_price').val(price);

                    $('#receiptModal').modal('show');

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
                        $('#receiptModal').modal('hide');
                        const coursePending_id = data.coursePending_id;
                        $('#coursePending_id').val(coursePending_id);
                        // Show the modal
                        $('#modalLineContact').modal('show');

                        // Update the QR code image source
                        $('#qrCodePayment').attr('src', data.qrCodePayment);

                        // Handle QR Code download
                        $('#downloadQrButton').off('click').on('click', function() {
                            const qrUrl = data.qrCodePayment;
                            const link = document.createElement('a');
                            link.href = qrUrl;
                            link.download = 'qr_code.png';
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        });

                        $('.modal-backdrop').removeClass('modal-backdrop');
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
                                            title: "Email Sent!",
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
