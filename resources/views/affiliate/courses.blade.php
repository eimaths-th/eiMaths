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

    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="purchaseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="purchaseModalLabel">Purchase Course</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('courses.pending.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Your referral code: <strong>{{ $ref }}</strong></p>
                        <!-- Course details, price, and other information related to the purchase can go here -->

                        <!-- Course selection (hidden input for now) -->
                        <input type="hidden" name="course_id" value="#">
                        <input type="hidden" name="ref" value="{{ $ref }}">

                        <!-- User Details Input -->
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Your Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="telp">Your Phone</label>
                            <input type="text" class="form-control" id="telp" name="telp" required>
                        </div>

                        <!-- Optionally, include a course name or other info -->
                        <div class="form-group">
                            <label for="course_name">Course Name</label>
                            <input type="text" class="form-control" id="course_name" name="course_name" value="#"
                                readonly>
                        </div>
                    </div>
                    <div class="modal-footer payment_btn d-flex justify-content-between">
                        {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> --}}
                        <button type="button" class="theme_line_btn small_btn3" data-bs-dismiss="modal">Close</button>
                        <button class="theme_btn" type="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
        var modal = new bootstrap.Modal(document.getElementById('receiptModal'));
        modal.show(); // Show the modal when needed

        // You can close the modal programmatically like this:
        // modal.hide();
    </script>
@endsection
