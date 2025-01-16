@extends('affiliate.frontend.main')
@section('style')
@endsection
@section('content')
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
@endsection
@section('script')
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script> --}}

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

                    // Populate the form fields inside the modal
                    $('#purchaseModalLabel').text(title);
                    $('#course_id').val(courseId);
                    $('#course_name').val(title);

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

                        $('.form').trigger('reset');
                        $('#receiptModal').modal('hide');

                        $('#modalLineContact').modal('show');
                        
                        $('#downloadQrButton').off('click').on('click', function() {
                            // Assuming the QR code image URL is available in the response
                            const qrUrl =
                            "{{ asset('images/line/qr-line.jpg') }}"; // Replace with your actual QR URL from the response
                            const link = document.createElement('a');
                            link.href = qrUrl;
                            link.download = 'qr_code.png'; // Set the filename for download
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
@endsection
