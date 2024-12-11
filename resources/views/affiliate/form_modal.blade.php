<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="purchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="modal-header">
                <h5 class="modal-title" id="purchaseModalLabel">Purchase Course</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{-- <form id="courseForm">
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
                        <input type="text" class="form-control" id="course_name" name="course_name" value=""
                            readonly>
                    </div>
                </div>
                <div class="modal-footer payment_btn d-flex justify-content-between">
                   <button type="button" class="theme_line_btn small_btn3" data-bs-dismiss="modal">Close</button>
                    <button class="theme_btn" type="submit">Submit</button>
                </div>
            </form> --}}
            <form id="courseForm" class="form">
                @csrf

                <div class="modal-body" id="modal-body">

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
                    <input type="hidden" name="ref"
                        value="{{ $ref ?? '' }}">

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
                </div>
                <div class="modal-footer payment_btn d-flex justify-content-between" id="modal-footer">
                    <button type="button" class="theme_line_btn small_btn3" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="submitForm" class="theme_btn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
