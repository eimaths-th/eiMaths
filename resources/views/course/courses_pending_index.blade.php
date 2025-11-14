{{-- @extends('backend.master') --}}
@extends('layouts.app')
@section('style')
    <style>
        .sms-breadcrumb h1 {
            font-size: 18px;
            margin-bottom: 0;
            color: #415094;
        }


        /* line 506, ../../xampp/htdocs/infixeduB/public/backEnd/scss/admin/_component.scss */

        .sms-breadcrumb .bc-pages a {
            display: inline-block;
            color: #828bb2;
            font-size: 13px;
            position: relative;
            margin-right: 28px;
            -webkit-transition: all 0.4s ease 0s;
            -moz-transition: all 0.4s ease 0s;
            -o-transition: all 0.4s ease 0s;
            transition: all 0.4s ease 0s;
        }

        .product_image_all_div {
            display: flex;
            grid-gap: 10px;
            margin-top: 20px;
            width: 100%;
            /*padding: 15px;*/
            flex-wrap: wrap;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
        }

        .primary-input {
            color: var(--theme-default-color);
            font-size: 13px;
            width: 100%;
            border: 0;
            border-bottom: 1px solid rgba(130, 139, 178, .3);
            background-color: transparent;
            padding: 4px 0 8px;
            position: relative;
            border-radius: 0;
            z-index: 99
        }

        .primary_input_label {
            font-size: 12px;
            text-transform: uppercase;
            color: var(--dynamic-text-color);
            display: block;
            margin-bottom: 6px;
            font-weight: 400
        }

        .primary_input_field {
            border: 1px solid #ECEEF4;
            font-size: 14px;
            color: #415094;
            padding-left: 20px;
            height: 46px;
            border-radius: 5px;
            width: 100%;
            padding-right: 15px;
        }

        .primary_select {
            width: 100%;
            border: 1px solid #ECEEF4;

            border-radius: 3px;
            height: 46px;
            line-height: 44px;
            font-size: 13px;
            color: var(--theme-default-color);
            padding: 0 25px 0 20px;
            font-weight: 300;
            border-radius: 4px
        }

        .primary_select::-moz-placeholder {
            color: var(--theme-default-color);
            font-weight: 300;
            opacity: 1
        }

        .primary_select::placeholder {
            color: var(--theme-default-color);
            font-weight: 300;
            opacity: 1
        }

        .primary_select:after {
            content: "";
            display: block;
            height: 10px;
            margin-top: 0;
            pointer-events: none;
            position: absolute;
            right: 14px;
            top: 10%;
            transition: all .15s ease-in-out;
            width: auto;
            right: 25px;
            content: "\F0D7";
            border: 0;
            font-family: Font Awesome\ 5 Free;
            font-weight: 900;
            color: var(--dynamic-text-color);
            font-size: 14px;
            transform: translateY(-58%) rotate(0deg)
        }

        .primary_select.open:after {
            content: "\F0D8";
            transform: translateY(-58%) rotate(0deg);
            margin: 0
        }

        .primary_select.open,
        .primary_select:active,
        .primary_select:focus {
            border-color: var(--border-hover-color)
        }

        .primary_select .list {
            min-width: 100%;
            width: auto;
            left: 0;
            right: auto;
            background-color: #fff;
            overflow: auto !important;
            border-radius: 0 0 10px 10px;
            margin-top: 1px;
            z-index: 9999 !important;
            box-shadow: var(--selector-shadow)
        }

        .primary_select .list .option {
            color: var(--theme-default-color)
        }

        .primary_select .list .option:hover {
            background-color: var(--backend-main-bg);
            color: var(--backend-primary-color)
        }

        .primary_select .list .option.selected {
            font-weight: 400 !important
        }

        .primary_file_uploader {
            position: relative
        }

        .primary_file_uploader button {
            position: absolute;
            right: 0;
            border: 0;
            top: 7px;
            right: 7px;
            padding: 0;
            background: transparent;
            z-index: 99
        }

        .primary_file_uploader button label {
            margin-bottom: 0
        }

        .primary_file_uploader input {
            border: 1px solid #ECEEF4;
            font-size: 14px;
            color: var(--theme-default-color);
            padding-left: 20px;
            height: 46px;
            border-radius: 4px;
            width: 100%;
            padding-right: 15px;
            padding-bottom: 4px;
            background: transparent
        }

        .primary_file_uploader input:hover {
            border-color: var(--border-hover-color) !important
        }

        .primary_file_uploader input::-moz-placeholder {
            color: var(--theme-default-color)
        }

        .primary_file_uploader input::placeholder {
            color: var(--theme-default-color)
        }

        .primary_file_uploader input:focus {
            border: 1px solid var(--backend-border-color) !important
        }

        .primary-btn {
            display: inline-block;
            color: var(--theme-default-color);
            letter-spacing: 1px;
            font-family: Poppins, sans-serif;
            font-weight: 500;
            padding: 4px 20px;
            outline: none !important;
            text-align: center;
            cursor: pointer;
            text-transform: uppercase;
            border: 0;
            border-radius: 5px;
            position: relative;
            overflow: hidden;
            transition: all .4s ease 0s;
            line-height: 1.4;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .primary-btn.small {
            min-height: 32px
        }

        .primary-btn.form-control {
            background: transparent
        }

        .primary-btn label {
            margin-bottom: 0
        }

        .primary-btn .common-checkbox:checked+label:before {
            color: var(--white);
            top: -13px
        }

        .primary-btn .common-checkbox+label:before {
            border: 1px solid var(--white);
            top: -13px
        }

        .primary-btn span {
            font-weight: 600
        }

        .primary-btn span.pl {
            padding-left: 8px
        }

        .primary-btn span.pr {
            padding-right: 8px
        }

        .primary-btn.small {
            letter-spacing: 1px;
            line-height: 1.4px;
            border-radius: 4px;
            font-weight: 600
        }

        .primary-btn.small:hover {
            color: var(--theme-default-color)
        }

        .primary-btn.medium {
            line-height: 38px !important
        }

        .primary-btn.semi-large {
            line-height: 48px !important
        }

        .primary-btn.large {
            letter-spacing: 1px;
            line-height: 60px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 24px
        }

        .primary-btn.large:hover {
            color: var(--theme-default-color)
        }

        .primary-btn.fix-gr-bg {
            color: var(--white);
            background-size: 200% auto;
            transition: all .4s ease 0s;
            background: var(--button-background)
        }

        .primary-btn.fix-gr-bg:hover {
            background: var(--button-hover);
            color: var(--white);
            box-shadow: none
        }
    </style>

    <style>
        /* line 402, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle {
            display: inline-block;
            height: 20px;
            position: relative;
            width: 36px;
            margin-bottom: 0;
        }

        /* line 409, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle input {
            opacity: 0;
        }

        /* line 412, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle .slider:before {
            bottom: 0px;
            content: "";
            height: 16px;
            left: 0px;
            left: 3px;
            position: absolute;
            -webkit-transition: .4s;
            transition: .4s;
            width: 16px;
            background: #707DB7;
            background: #fff;
            top: 2px;
        }

        /* line 430, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle input:checked+.slider {
            background: #ffb515;
        }

        /* line 434, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle input:checked+.slider:before {
            transform: translateX(14px);
        }

        /* line 441, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle .slider.round {
            border-radius: 34px;
        }

        /* line 445, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle .slider.round:before {
            border-radius: 50%;
        }

        /* line 448, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle .switch_toggle input {
            display: none;
        }

        /* line 451, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle .slider {
            background-color: #292929;
            bottom: 0;
            cursor: pointer;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            transition: .4s;
        }
    </style>
@endsection

@section('content')
    {{-- {!! generateBreadcrumb() !!} --}}
    <section class="content-header">
        <div class="container-fluid">


            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Courses List</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <a class="btn btn-success" href="#" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                            <i class="fa fa-plus"></i> Add Course
                        </a>
                    </ol>

                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">

                {{-- @include('course.course_search') --}}

                <div class="col-lg-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-user"></i>Courses Pennding</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                {{-- <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button> --}}
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">

                                <div class="row">
                                    <div class="col-lg-12">
                                        <!-- table-responsive -->
                                        <div class="">
                                            <table id="Listview"
                                                class="table classList table table-bordered table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">SL</th>
                                                        <th scope="col">ref</th>
                                                        {{-- <th scope="col"> Required Type</th> --}}
                                                        <th scope="col">Name</th>
                                                        <th scope="col">Email</th>
                                                        <th scope="col">Phone</th>
                                                        <th scope="col">Department </th>
                                                        <th scope="col">Appointment Date</th>
                                                        <th scope="col">Student Name</th>
                                                        <th scope="col">grade</th>
                                                        <th scope="col">Type Parent</th>

                                                        <th scope="col">Status</th>
                                                        <th scope="col">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    @include('course.create_new_std')
    @include('course.receipt')
    @include('course.modal_view_slip')

    <!-- Course Pending Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Course Pending Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailsModalBody">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Parent Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Name:</strong></td><td id="detail-name">-</td></tr>
                                <tr><td><strong>Email:</strong></td><td id="detail-email">-</td></tr>
                                <tr><td><strong>Phone:</strong></td><td id="detail-telp">-</td></tr>
                                <tr><td><strong>Type Parent:</strong></td><td id="detail-type_parent">-</td></tr>
                                <tr><td><strong>Ref Code:</strong></td><td id="detail-ref">-</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Student Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Student Name:</strong></td><td id="detail-student_name">-</td></tr>
                                <tr><td><strong>Nickname:</strong></td><td id="detail-student_nickname">-</td></tr>
                                <tr><td><strong>Grade:</strong></td><td id="detail-grade">-</td></tr>
                                <tr><td><strong>Appointment Date:</strong></td><td id="detail-appointment_date">-</td></tr>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Course Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Course Name:</strong></td><td id="detail-course_name">-</td></tr>
                                <tr><td><strong>Price:</strong></td><td id="detail-price">-</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Schedule Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Department:</strong></td><td id="detail-department">-</td></tr>
                                <tr><td><strong>Day:</strong></td><td id="detail-day">-</td></tr>
                                <tr><td><strong>Period:</strong></td><td id="detail-period">-</td></tr>
                            </table>
                        </div>
                    </div>
                    {{-- <hr> --}}
                    <div class="row" style="display: none">
                        <div class="col-md-12">
                            <h6 class="text-primary">System Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>ID:</strong></td><td id="detail-id">-</td></tr>
                                <tr><td><strong>Status:</strong></td><td id="detail-status">-</td></tr>
                                <tr><td><strong>Created At:</strong></td><td id="detail-created_at">-</td></tr>
                                <tr><td><strong>Updated At:</strong></td><td id="detail-updated_at">-</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- @include('contacts.receipt') --}}
@endsection
@section('script')
    <script>
        $(document).ready(function() {

            var token = ''
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(".select2_single").select2({
                maximumSelectionLength: 1,
                allowClear: false,
                //theme: 'bootstrap4'
                placeholder: 'Please select'
            });

            $(".select2_single").on("select2:unselect", function(e) {
                //$('.products').html('');
            });

            $(".select2_singles").select2({
                maximumSelectionLength: 1,
                allowClear: false,
                //theme: 'bootstrap4'
                placeholder: 'Please select'
            });

            $(".select2_singlec").select2({
                maximumSelectionLength: 1,
                allowClear: false,
                //theme: 'bootstrap4'
                placeholder: 'Please select'
            });

            $(".select2_multiple").select2({
                maximumSelectionLength: 2,
                allowClear: false,
                //theme: 'bootstrap4'
                placeholder: 'Please select'
            });

            $(".levels").change(function() {
                let level = $('#AddLevel').val();
                $('#AddTerm').html('');
                $('#AddBook').html('');

                if (level.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('level.find', ['type' => ':type', 'level' => ':level']) }}"
                            .replace(':type', 'add')
                            .replace(':level', level),
                        success: function(res) {
                            $('.terms').html(res.html);
                            $('.terms').prop('disabled', false);
                        }
                    });
                }
            });

            $(".terms").change(function() {
                let level = $('#AddLevel').val();
                let term = $('#AddTerm').val();
                //term.find /term/find/{type}/{level}/{term}
                $('#AddBook').html('');
                if (level.length !== 0 && term.length !== 0) {
                    $.ajax({
                        method: "GET",
                        // url: "term/find/add/" + level + "/" + term,
                        url: "{{ route('term.find', ['type' => ':type', 'level' => ':level', 'term' => ':term']) }}"
                            .replace(':type', 'add')
                            .replace(':level', level)
                            .replace(':term', term),
                        data: {
                            level: $('#AddLevel').val()[0],
                            term: $('#AddTerm').val()[0],
                            level2: $('#AddLevel2').val()[0],
                            term2: $('#AddTerm2').val()[0],
                            _token: token,

                        },
                        async: false,
                        success: function(res) {
                            console.log(res)
                            $('.scoin').val(res.scoin);

                            $('.books').html(res.html);
                            $('.books').prop('disabled', false);
                        }
                    });
                }
            })

            $(".levels2").change(function() {
                let level = $('#AddLevel2').val();
                //console.log(product);
                //alert(product);
                $('#AddTerm2').html('');
                $('#AddBook2').html('');
                if (level.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('level.find', ['type' => ':type', 'level' => ':level']) }}"
                            .replace(':type', 'add')
                            .replace(':level', level),
                        success: function(res) {
                            $('.terms2').html(res.html);
                            //$('.terms').removeAttr('readonly');
                            $('.terms2').prop('disabled', false);
                        }
                    });
                }
            })

            $(".terms2").change(function() {
                let level = $('#AddLevel2').val();
                let term = $('#AddTerm2').val();
                $('#AddBook2').html('');
                if (level.length !== 0 && term.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('term.find', ['type' => ':type', 'level' => ':level', 'term' => ':term']) }}"
                            .replace(':type', 'add')
                            .replace(':level', level)
                            .replace(':term', term),
                        data: {
                            level: $('#AddLevel').val()[0],
                            term: $('#AddTerm').val()[0],
                            level2: $('#AddLevel2').val()[0],
                            term2: $('#AddTerm2').val()[0],
                            _token: token,
                        },
                        async: false,
                        success: function(res) {
                            console.log(res)
                            $('.books2').html(res.html);
                            $('.scoin').val(res.scoin);
                            $('.books2').prop('disabled', false);
                        }
                    });
                }
            })

            $(".elevels").change(function() {
                let level = $('#EditLevel').val();
                //console.log(product);
                //alert(product);
                $('#EditTerm').html('');
                $('#EditBook').html('');
                if (level.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "level/find/add/" + level,
                        success: function(res) {
                            $('.eterms').html(res.html);
                            //$('.terms').removeAttr('readonly');
                            $('.eterms').prop('disabled', false);
                        }
                    });
                }
            })

            $(".eterms").change(function() {
                let level = $('#EditLevel').val();
                let term = $('#EditTerm').val();
                $('#EditBook').html('');
                if (level.length !== 0 && term.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "term/find/add/" + level + "/" + term,

                        async: false,
                        success: function(res) {
                            //console.log(res)
                            $('.ebooks').html(res.html);
                            $('.ebooks').prop('disabled', false);
                        }
                    });
                }
            })

            $(".elevels2").change(function() {
                let level = $('#EditLevel2').val();
                //console.log(product);
                //alert(product);
                $('#EditTerm2').html('');
                $('#EditBook2').html('');
                if (level.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "level/find/add/" + level,
                        success: function(res) {
                            $('.eterms2').html(res.html);
                            //$('.terms').removeAttr('readonly');
                            $('.eterms2').prop('disabled', false);
                        }
                    });
                }
            })

            $(".eterms2").change(function() {
                let level = $('#EditLevel2').val();
                let term = $('#EditTerm2').val();
                $('#EditBook2').html('');
                if (level.length !== 0 && term.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "term/find/add/" + level + "/" + term,

                        async: false,
                        success: function(res) {
                            //console.log(res)
                            $('.ebooks2').html(res.html);
                            $('.ebooks2').prop('disabled', false);
                        }
                    });
                }
            })

            $(".rlevels").change(function() {
                let level = $('#ReceiptLevel').val();
                //console.log(product);
                //alert(product);
                $('#ReceiptTerm').html('');
                $('#ReceiptBook').html('');
                if (level.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('level.find', ['type' => ':type', 'level' => ':level']) }}"
                            .replace(':type', 'add')
                            .replace(':level', level),
                        success: function(res) {
                            $('.rterms').html(res.html);
                            //$('.terms').removeAttr('readonly');
                            $('.rterms').prop('disabled', false);
                        }
                    });
                }
            })

            $(".rterms").change(function() {
                let level = $('#ReceiptLevel').val();
                let term = $('#ReceiptTerm').val();
                $('#ReceiptBook').html('');
                if (level.length !== 0 && term.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('term.find', ['type' => ':type', 'level' => ':level', 'term' => ':term']) }}"
                            .replace(':type', 'add')
                            .replace(':level', level)
                            .replace(':term', term),
                        data: {
                            level: $('#ReceiptLevel').val()[0],
                            term: $('#ReceiptTerm').val()[0],
                            level2: $('#ReceiptLevel2').val()[0],
                            term2: $('#ReceiptTerm2').val()[0],
                            _token: token,
                        },
                        async: false,
                        success: function(res) {
                            //console.log(res)
                            $('.rscoin').val(res.scoin);

                            $('.rbooks').html(res.html);
                            $('.rbooks').prop('disabled', false);
                        }
                    });
                }
            })

            $(".rlevels2").change(function() {
                let level = $('#ReceiptLevel2').val();
                //console.log(product);
                //alert(product);
                $('#ReceiptTerm2').html('');
                $('#ReceiptBook2').html('');
                if (level.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('level.find', ['type' => ':type', 'level' => ':level']) }}"
                            .replace(':type', 'add')
                            .replace(':level', level),
                        success: function(res) {
                            $('.rterms2').html(res.html);
                            //$('.terms').removeAttr('readonly');
                            $('.rterms2').prop('disabled', false);
                        }
                    });
                }
            })

            $(".rterms2").change(function() {
                let level = $('#ReceiptLevel2').val();
                let term = $('#ReceiptTerm2').val();
                $('#ReceiptBook2').html('');
                if (level.length !== 0 && term.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('term.find', ['type' => ':type', 'level' => ':level', 'term' => ':term']) }}"
                            .replace(':type', 'add')
                            .replace(':level', level)
                            .replace(':term', term),
                        data: {
                            level: $('#ReceiptLevel').val()[0],
                            term: $('#ReceiptTerm').val()[0],
                            level2: $('#ReceiptLevel2').val()[0],
                            term2: $('#ReceiptTerm2').val()[0],
                            _token: token,
                        },
                        async: false,
                        success: function(res) {
                            console.log(res)
                            $('.rbooks2').html(res.html);
                            $('.rscoin').val(res.scoin);
                            $('.rbooks2').prop('disabled', false);
                        }
                    });
                }
            })

            $("#ReceiptCentre").change(function() {
                let centre = $('#ReceiptCentre').val();
                if (centre.length !== 0) {
                    $.ajax({
                        method: "GET", //histories.running
                        url: "{{ route('histories.running', ':centre') }}".replace(':centre',
                            centre), // Replace placeholder
                        async: false,
                        success: function(res) {
                            //$('#AddCode').prop('readonly', false);
                            $('#s_student').html(res.html_std);
                        }
                    });
                }
            })

            if ($("#ReceiptCentre").val() !== null) {
                let centre = $('#ReceiptCentre').val();

                if (centre.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('histories.running', ':centre') }}".replace(':centre', centre),
                        async: false,
                        success: function(res) {
                            //$('#AddCode').prop('readonly', false);
                            $('#s_student').html(res.html_std);
                        }
                    });
                }
            }




            $("#AddCentre").change(function() {
                let centre = $('#AddCentre').val();
                //contacts.running
                if (centre) {
                    $.ajax({
                        method: "GET",
                        // method: "GET",
                        url: "{{ route('contacts.running', ':centre') }}".replace(':centre',
                            centre),
                        async: false,
                        success: function(res) {
                            //console.log(res)
                            $('#AddCode').val(res.running);
                        }
                    });
                }
            })
            var table = $('#Listview').DataTable({
                ajax: '',
                serverSide: true,
                processing: true,
                language: {
                    loadingRecords: '&nbsp;',
                    processing: `<div class="spinner-border text-primary"></div>`,
                    "sProcessing": "Processcing...",
                    "sLengthMenu": "Display _MENU_ Row",
                    "sZeroRecords": "No Data Fount",
                    "sInfo": "Display _START_ To _END_ From _TOTAL_ Records",
                    "sInfoEmpty": "Display 0 To 0 From 0 Records",
                    "sInfoFiltered": "(Filters _MAX_ Row)",
                    "sInfoPostFix": "",
                    "sSearch": "Search:",
                    "sUrl": "",
                    "oPaginate": {
                        "sFirst": "First",
                        "sPrevious": "Previous",
                        "sNext": "Next",
                        "sLast": "Last"
                    }
                },
                aaSorting: [
                    [0, "desc"]
                ],
                iDisplayLength: 10,
                lengthMenu: [10, 25, 50, 75, 100],
                stateSave: true,
                autoWidth: false,
                responsive: true,
                sPaginationType: "full_numbers",
                dom: 'T<"clear">lfrtip',
                columns: [{
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false
                    }, // SL
                    {
                        data: 'ref',
                        name: 'ref'
                    }, // Type
                    {
                        data: 'name',
                        name: 'name'
                    }, // CourseTitle
                    {
                        data: 'email',
                        name: 'email'
                    }, // Delivery
                    {
                        data: 'telp',
                        name: 'telp'
                    }, // Delivery
                    {
                        data: 'department',
                        name: 'department'
                    }, // Delivery
                    {
                        data: 'appointment_date',
                        name: 'appointment_date'
                    }, // Delivery
                    {
                        data: 'student_name',
                        name: 'student_name'
                    }, // Delivery
                    {
                        data: 'grade',
                        name: 'grade'
                    }, // Delivery
                    {
                        data: 'type_parent',
                        name: 'type_parent'
                    }, // Delivery
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    }, // Status
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    } // Action
                ],
                createdRow: function(row, data, dataIndex) {

                    var info = table.page.info();
                    $('td:eq(0)', row).html(info.start + dataIndex + 1);

                }
            });





        });

        function showOverlay() {
            $('#overlay').css({
                'display': 'flex',
                'justify-content': 'center', // Center horizontally
                'align-items': 'center' // Center vertically
            });
        }

        function hideOverlay() {
            $('#overlay').css('display', 'none');
        }
        $(document).on('click', '.view-slip-btn', function() {
            var id = $(this).data('id');
            // Send AJAX request to fetch the receipt image

            console.log(id);

            $.ajax({
                url: "{{ route('courses.pending.fetch.receipt', ':id') }}".replace(':id', id),
                method: 'GET',
                success: function(response) {
                    // Assuming your response contains the image path in response.image
                    var imagePath = response.image;

                    // Set the image preview in the modal
                    $('#slip_preview').attr('src', imagePath);

                    // Show the modal
                    $('#ModalViewslip').modal('show');
                },
                error: function(error) {
                    console.error('Error fetching receipt:', error);
                }
            });
        });

        // View Details Button Handler
        $(document).on('click', '.view-detail-btn', function() {
            var id = $(this).data('id');
            console.log('View Details clicked for ID:', id);

            // Show modal first
            $('#detailsModal').modal('show');

            var url = "{{ route('courses.pending.details', ':id') }}".replace(':id', id);
            console.log('AJAX URL:', url);

            // Send AJAX request to fetch course pending details
            $.ajax({
                url: url,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    console.log('AJAX beforeSend: Starting request...');
                    // Clear all fields and show loading
                    $('#detail-name, #detail-email, #detail-telp, #detail-type_parent, #detail-ref').text('Loading...');
                    $('#detail-student_name, #detail-student_nickname, #detail-grade, #detail-appointment_date').text('Loading...');
                    $('#detail-course_name, #detail-price').text('Loading...');
                    $('#detail-department, #detail-day, #detail-period').text('Loading...');
                    $('#detail-id, #detail-status, #detail-created_at, #detail-updated_at').text('Loading...');
                },
                success: function(response) {
                    console.log('AJAX Success Response:', response);
                    if (response.success) {
                        var data = response.data;
                        console.log('Course Pending Data:', data);

                        // Populate parent information
                        $('#detail-name').text(data.name || '-');
                        $('#detail-email').text(data.email || '-');
                        $('#detail-telp').text(data.telp || '-');
                        $('#detail-type_parent').text(data.type_parent || '-');
                        $('#detail-ref').text(data.ref || '-');

                        // Populate student information
                        $('#detail-student_name').text(data.student_name || '-');
                        $('#detail-student_nickname').text(data.student_nickname || '-');
                        $('#detail-grade').text(data.grade || '-');
                        $('#detail-appointment_date').text(data.appointment_date ? new Date(data.appointment_date).toLocaleString() : '-');

                        // Populate course information
                        $('#detail-course_name').text(data.course_name || '-');
                        $('#detail-price').text(data.price ? '฿' + data.price : '-');

                        // Populate schedule information
                        $('#detail-department').text(data.department ? data.department.name : '-');
                        $('#detail-day').text(data.day || '-');
                        $('#detail-period').text(data.period || '-');

                        // Populate system information
                        $('#detail-id').text(data.id || '-');

                        // Status with badge
                        var statusText = '';
                        var statusClass = '';
                        switch(data.status) {
                            case 1: statusText = 'Pending'; statusClass = 'warning'; break;
                            case 2: statusText = 'Accepted'; statusClass = 'success'; break;
                            case 3: statusText = 'Waiting for Payment'; statusClass = 'info'; break;
                            case 4: statusText = 'Success'; statusClass = 'success'; break;
                            case 0: statusText = 'Denied'; statusClass = 'danger'; break;
                            default: statusText = 'Unknown'; statusClass = 'secondary'; break;
                        }
                        $('#detail-status').html('<span class="badge bg-' + statusClass + '">' + statusText + '</span>');

                        $('#detail-created_at').text(data.created_at ? new Date(data.created_at).toLocaleString() : '-');
                        $('#detail-updated_at').text(data.updated_at ? new Date(data.updated_at).toLocaleString() : '-');
                    } else {
                        // Show error in all fields
                        $('#detail-name, #detail-email, #detail-telp, #detail-type_parent, #detail-ref').text('Error');
                        $('#detail-student_name, #detail-student_nickname, #detail-grade, #detail-appointment_date').text('Error');
                        $('#detail-course_name, #detail-price').text('Error');
                        $('#detail-department, #detail-day, #detail-period').text('Error');
                        $('#detail-id, #detail-status, #detail-created_at, #detail-updated_at').text('Error');
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('XHR:', xhr);
                    console.error('Status:', status);
                    console.error('Response Text:', xhr.responseText);
                    // Show error in all fields
                    $('#detail-name, #detail-email, #detail-telp, #detail-type_parent, #detail-ref').text('Error');
                    $('#detail-student_name, #detail-student_nickname, #detail-grade, #detail-appointment_date').text('Error');
                    $('#detail-course_name, #detail-price').text('Error');
                    $('#detail-department, #detail-day, #detail-period').text('Error');
                    $('#detail-id, #detail-status, #detail-created_at, #detail-updated_at').text('Error');
                    alert('Error loading details. Please try again.');
                }
            });
        });
        // $('.view-slip-btn').on('click', function() {
        //         console.log('View Slip Button Clicked');

        //         var slipId = $(this).data('id'); // Get the slip ID from the button
        //         console.log(slipId);

        //         // You can make an AJAX request to fetch the slip image by ID
        //         $.ajax({
        //             url: '/courses/pending/fetch/slip/' +
        //                 slipId, // Replace with your route to fetch the image
        //             method: 'GET',
        //             success: function(response) {
        //                 // Assuming your response contains the image path in response.image
        //                 var imagePath = response.image;

        //                 // Set the image preview in the modal
        //                 $('#slip_preview').attr('src', imagePath);

        //                 // Show the modal
        //                 $('#ModalViewSlip').modal('show');
        //             },
        //             error: function(error) {
        //                 console.error('Error fetching slip:', error);
        //             }
        //         });
        //     });
        $(document).on('click', '.confirm-btn', function() {
            var id = $(this).data('id');
            // Send AJAX request to handle confirmation (you may need to define a route for confirmation)
            $.ajax({
                url: "{{ route('courses.pending.confirm', ':id') }}".replace(':id', id),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    status: 2, // For example, update status to "confirmed" (you can define the status you want)
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message); // Show confirmation message
                        // Optionally, refresh the table or update the UI
                        $('#Listview').DataTable().ajax.reload();

                    }
                },
                error: function() {
                    alert('An error occurred');
                }
            });
        });

        $(document).on('click', '.cancel-btn', function() {
            var id = $(this).data('id');
            // Send AJAX request to handle cancellation
            $.ajax({
                url: "{{ route('courses.pending.cancel', ':id') }}".replace(':id', id),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    status: 0, // For example, update status to "cancelled"
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message); // Show cancel message
                        // Optionally, refresh the table or update the UI
                        $('#Listview').DataTable().ajax.reload();

                    }
                },
                error: function() {
                    alert('An error occurred');
                }
            });
        });

        $(document).on('click', '.reset-btn', function() {
            var id = $(this).data('id');
            // Send AJAX request to handle cancellation
            $.ajax({
                url: "{{ route('courses.pending.reset', ':id') }}".replace(':id', id),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    status: 0, // For example, update status to "cancelled"
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message); // Show cancel message
                        // Optionally, refresh the table or update the UI
                        $('#Listview').DataTable().ajax.reload();

                    }
                },
                error: function() {
                    alert('An error occurred');
                }
            });
        });

        // daterange();

        $.datepicker.setDefaults($.datepicker.regional['en']);
        $(".AddDate").datepicker({
            /*  onSelect: function() {
                 table.draw();
             }, */
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            yearRange: '1980:2050',
            autoclose: true
        });

        let id;



        $(document).on('click', '.ModalNewStd', async function(e) {
            e.preventDefault();

            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            console.log($('#AddCentre').val());

            let id = $(this).data('id');

            try {
                const res = await $.ajax({
                    url: "{{ route('courses.pending.new.student', ':id') }}".replace(':id', id),
                    method: 'GET'
                });

                $('#Addref').val(res.data.ref);
                $('#courses_pending_id').val(res.data.id);
                if (res.data && res.data.ref) {
                    $('#ref').html('<h4> Referent code : ' + res.data.ref + '</h4>');
                } else {
                    $('#ref').html('');
                }

                if (res.data.type_parent && res.data.name && res.data.email && res.data.telp) {
                    if (res.data.type_parent === 'mother') {
                        $('#getmName').val(res.data.name);
                        $('#getmEmail').val(res.data.email);
                        $('#getmTelephone').val(res.data.telp);
                    } else {
                        $('#getfName').val(res.data.name);
                        $('#getfEmail').val(res.data.email);
                        $('#getfTelephone').val(res.data.telp);
                    }
                } else {
                    console.error('Invalid data:', res.data);
                }

                let addCentre = $('#AddCentre').val();
                const runningRes = await $.ajax({
                    method: "GET",
                    url: "{{ route('contacts.running', ':centre') }}".replace(':centre', addCentre)
                });

                $('#AddCode').val(runningRes.running);
                $('#AddTerm').prop('disabled', true);
                $('#AddBook').prop('disabled', true);
                $('#AddTerm2').prop('disabled', true);
                $('#AddBook2').prop('disabled', true);

                $('#CreateModalNewStd').modal('show');
            } catch (error) {
                console.error('Error:', error);
            }
        });

        var token = ''
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#SubmitCreateForm').click(function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();


            var selectedValue1 = $('#AddLevel').val()[0];
            var selectedValue2 = $('#AddLevel2').val()[0];

            if (selectedValue1 && selectedValue1.trim() !== '') {
                $("#AddLevel").removeClass("is-invalid");
                $("#AddLevel").addClass("is-valid");
            } else {
                $("#AddLevel").addClass("is-invalid");
                $("#AddLevel").removeClass("is-valid");
                toastr.error('Please Select Start Level', {
                    timeOut: 5000
                });
                return false;
            }

            if (selectedValue2 < selectedValue1) {
                toastr.error("Cannot select 'To level' less than 'Start level'", {
                    timeOut: 5000
                });
                return false;
            }

            $.ajax({
                url: "{{ route('courses.pending.new.student.store') }}",
                method: 'post',
                data: {
                    ref: $('#Addref').val(),
                    courses_pending_id: $('#courses_pending_id').val(),

                    centre: $('#AddCentre').val()[0],
                    code: $('#AddCode').val(),
                    name: $('#AddName').val(),
                    nickname: $('#AddNickname').val(),
                    gender: $("input[name='gender']:checked").val(),
                    birth_date: $('#AddBirthDate').val(),
                    start_date: $('#AddDate').val(),
                    //start_term: $('#AddSTerm').val()[0],
                    school: $('#AddSchool').val(),
                    level: $('#AddLevel').val()[0],
                    term: $('#AddTerm').val()[0],
                    bookuse: $('#AddBook').val()[0],
                    level2: $('#AddLevel2').val()[0],
                    term2: $('#AddTerm2').val()[0],
                    bookuse2: $('#AddBook2').val()[0],
                    discontinued: 0,
                    //ddate: $('#AddDDate').val(),
                    //reason: $('#AddReason').val(),
                    postcode: $('#AddPostcode').val(),
                    address: $('#AddAddress').val(),
                    telephone: $('#AddTelephone').val(),
                    father_name: $('#getfName').val(),
                    father_email: $('#getfEmail').val(),
                    father_mobile: $('#getfTelephone').val(),
                    mother_name: $('#getmName').val(),
                    mother_email: $('#getmEmail').val(),
                    mother_mobile: $('#getmTelephone').val(),
                    _token: token,
                },
                success: function(result) {
                    // console.log(result);
                    if (result.errors) {
                        $('.alert-danger').html('');
                        $.each(result.errors, function(key, value) {
                            $('.alert-danger').show();
                            $('.alert-danger').append('<strong><li>' + value +
                                '</li></strong>');
                        });
                    } else {
                        $('.alert-danger').hide();
                        $('.alert-success').show();
                        $('.alert-success').append('<strong><li>' + result.success +
                            '</li></strong>');
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        $('#Listview').DataTable().ajax.reload();
                        $('#AddSTerm').val(null).trigger("change");
                        $('#AddBook').val(null).trigger("change");
                        $('#AddLevel').val(null).trigger("change")
                        $('#AddTerm').val(null).trigger("change")
                        $('.form').trigger('reset');
                        $('#Listview').DataTable().ajax.reload();
                        $('#CreateModalNewStd').modal('hide');

                        showOverlay();

                        var oid = result.oid;
                        var url = "{{ route('receipts_pending') }}?new=" +
                            encodeURIComponent(oid);
                        window.location.href = url;
                    }
                }
            });
        });



        $(document).on('click', '.ModalNewReciept', async function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            $('#custom-tabs-one-home-tab-receipt').tab('show');
            //$('#AddSTerm').val(null).trigger("change");
            $('#AddBook').val(null).trigger("change");
            $('#AddLevel').val(null).trigger("change");
            $('#AddTerm').val(null).trigger("change");
            $('#AddBook2').val(null).trigger("change");
            $('#AddLevel2').val(null).trigger("change")
            $('#AddTerm2').val(null).trigger("change");
            let id = $(this).data('id');

            try {
                const res = await $.ajax({
                    url: "{{ route('courses.pending.new.student', ':id') }}".replace(':id', id),
                    method: 'GET'
                });
                console.log(res);
                if (res.data.ref) {

                    getCentre(res.centre_id);
                    $('#ReceiptCentre').html(res.htmlOptions);
                    $('#getRef').val(res.data.ref);
                    $('#getRef').prop('readonly', true);
                } else {
                    console.log('false  New Receipt');

                    $('#getRef').val(''); // Clear the value if ref is null or undefined
                    $('#getRef').prop('readonly', false); // Make editable if ref is null or undefined
                }
                $('#courses_pending_id').val(res.data.id);

                $('#AddTerm').prop('disabled', true);
                $('#AddBook').prop('disabled', true);
                $('#AddTerm2').prop('disabled', true);
                $('#AddBook2').prop('disabled', true);
                $('#ReceiptModal').modal('show');
            } catch (error) {
                console.error('Error:', error);
            }

            $(document).on('change', '#s_student', function() {
                let student_id = $('#s_student').val();
                $.ajax({
                    // url: "{{ route('courses.pending.new.student', ':id') }}".replace(':id', id),
                    url: "{{ route('courses.pending.getCheckRefStudnet') }}",
                    method: 'GET',
                    data: {
                        id: id,
                        student_id: student_id,
                    },
                    success: function(response) {
                        console.log(response);

                        if (response.success) {

                        } else {
                            alert(response.message);
                            toastr.error(response.message, {
                                timeOut: 10000
                            }); // Make editable if ref is null or undefined

                        }
                        $('#getRef').val(response.ref);
                        $('#getRef').prop('readonly', true);
                    }
                });


            });

        });



        function getCentre(centre) {
            // console.log(centre);

            if (centre) {
                $.ajax({
                    method: "GET",
                    url: "{{ route('histories.running', ':centre') }}".replace(':centre', centre),
                    async: false,
                    success: function(res) {
                        //$('#AddCode').prop('readonly', false);
                        $('#s_student').html(res.html_std);
                    }
                });
            }

        }



        $('#ReceiptCreateForm').click(function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();


            var selectedValue1 = $('#ReceiptLevel').val()[0];
            var selectedValue2 = $('#ReceiptLevel2').val()[0];

            if (selectedValue1 && selectedValue1.trim() !== '') {
                $("#ReceiptLevel").removeClass("is-invalid");
                $("#ReceiptLevel").addClass("is-valid");
            } else {
                $("#ReceiptLevel").addClass("is-invalid");
                $("#ReceiptLevel").removeClass("is-valid");
                toastr.error('Please Select Start Level', {
                    timeOut: 5000
                });
                return false;
            }

            if (selectedValue2 < selectedValue1) {
                toastr.error("Cannot select 'To level' less than 'Start level'", {
                    timeOut: 5000
                });
                return false;
            }

            $.ajax({
                url: "{{ route('contacts.receipt') }}",
                method: 'post',
                data: {
                    centre: $('#ReceiptCentre').val()[0],
                    ref: $('#getRef').val(),
                    courses_pending_id: $('#courses_pending_id').val(),
                    student: $('#s_student').val()[0],
                    level: $('#ReceiptLevel').val()[0],
                    term: $('#ReceiptTerm').val()[0],
                    bookuse: $('#ReceiptBook').val()[0],
                    level2: $('#ReceiptLevel2').val()[0],
                    term2: $('#ReceiptTerm2').val()[0],
                    bookuse2: $('#ReceiptBook2').val()[0],
                    _token: token,
                },
                success: function(result) {
                    console.log(result);
                    if (result.errors) {
                        $('.alert-danger').html('');
                        $.each(result.errors, function(key, value) {
                            $('.alert-danger').show();
                            $('.alert-danger').append('<strong><li>' + value +
                                '</li></strong>');
                        });
                    } else {
                        $('.alert-danger').hide();
                        $('.alert-success').show();
                        $('.alert-success').append('<strong><li>' + result.success +
                            '</li></strong>');
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        $('#Listview').DataTable().ajax.reload();
                        $('#ReceiptBook').val(null).trigger("change");
                        $('#ReceiptLevel').val(null).trigger("change")
                        $('#ReceiptTerm').val(null).trigger("change")
                        $('.form').trigger('reset');
                        $('#ReceiptModal').modal('hide');

                        showOverlay();

                        var oid = result.oid;
                        var url = "{{ route('receipts_pending') }}?new=" +
                            encodeURIComponent(oid);
                        window.location.href = url;
                    }
                }
            });
        });
    </script>
    {{-- <script>
        $(document).ready(function() {

            function showOverlay() {
                $('#overlay').css({
                    'display': 'flex',
                    'justify-content': 'center', // Center horizontally
                    'align-items': 'center' // Center vertically
                });
            }

            function hideOverlay() {
                $('#overlay').css('display', 'none');
            }

            $(".delete_all_button").click(function() {
                var len = $('input[name="table_records[]"]:checked').length;
                if (len > 0) {
                    if (confirm("Confirm data deletion?")) {
                        $('form#delete_all').submit();
                    }
                } else {
                    alert("Please select items to delete.");
                }
            });

            $(".export_button").click(function() {
                var len = $('input[name="table_records[]"]:checked').length;
                if (len > 0) {
                    if (confirm("Click OK to Export?")) {
                        $('form#delete_all').attr('action',
                            '{{ route('contact.export') }}'); //this fails silently
                        $('form#delete_all').submit();
                    }
                } else {
                    alert("Please Select Record For Export");
                }

            });

            $('#check-all').click(function() {
                $(':checkbox.flat').prop('checked', this.checked);
            });
            //

            const discontinuedCheckbox = $('#ecustomCheckbox1');
            const editDDateInput = $('#EditDDate');
            const editReasonInput = $('#EditReason');
            const sreasonSection = $('#sreason');

            // Add an event listener to the checkbox using jQuery
            discontinuedCheckbox.on('change', function() {
                // Enable/disable the inputs based on the checkbox state
                editDDateInput.prop('disabled', !this.checked);
                editReasonInput.prop('disabled', !this.checked);
                if (this.checked) {
                    sreasonSection.removeClass('d-none');
                    if (editDDateInput.val() === "" || editDDateInput.val() ===
                        null) {
                        const currentDate = new Date().toISOString().split('T')[0];
                        $('#EditDDate').val(currentDate);
                    }
                } else {
                    sreasonSection.addClass('d-none');
                    $('#EditDDate').val('');
                    $('#EditReason').val('');
                }
            });

            $('#ddl_discontinueReason').on('change', function() {
                const selectedReason = $(this).val();
                $('#EditReason').val(selectedReason);
            });


            $(".select2_single").select2({
                maximumSelectionLength: 1,
                allowClear: false,
                //theme: 'bootstrap4'
                placeholder: 'Please select'
            });

            $(".select2_single").on("select2:unselect", function(e) {
                //$('.products').html('');
            });

            $(".select2_singles").select2({
                maximumSelectionLength: 1,
                allowClear: false,
                //theme: 'bootstrap4'
                placeholder: 'Please select'
            });

            $(".select2_singlec").select2({
                maximumSelectionLength: 1,
                allowClear: false,
                //theme: 'bootstrap4'
                placeholder: 'Please select'
            });

            $(".select2_multiple").select2({
                maximumSelectionLength: 2,
                allowClear: false,
                //theme: 'bootstrap4'
                placeholder: 'Please select'
            });

            var startDate;
            var endDate;

            function datesearch() {
                var currentDate = moment();
                // Set the start date to 7 days before today
                startDate = moment(currentDate).subtract(365, 'days').format('YYYY-MM-DD');
                // Set the end date to the end of the current month
                endDate = moment(currentDate).endOf('year').format('YYYY-MM-DD');
            }

            function retrieveFieldValues() {
                var saveddateStart = localStorage.getItem('dateStart');
                var savedSearchType = localStorage.getItem('searchType');
                var savedKeyword = localStorage.getItem('keyword');

                // Set field values from local storage
                if (saveddateStart) {
                    var dateParts = saveddateStart.split(' - ');
                    startDate = dateParts[0];
                    endDate = dateParts[1];
                } else {
                    datesearch();
                }
                if (savedSearchType) {
                    $('#search_type').val(savedSearchType);
                }
                if (savedKeyword) {
                    $('#keyword').val(savedKeyword);
                }
            }

            // Call the function to set initial field values on page load
            retrieveFieldValues();

            let daterange = () => {
                $('#reservation').daterangepicker({
                    startDate: startDate,
                    endDate: endDate,
                    showDropdowns: true,
                    linkedCalendars: false,
                    locale: {
                        format: 'YYYY-MM-DD'
                    }
                });

                // Apply the custom date range filter on input change
                $('#reservation').on('apply.daterangepicker', function() {
                    table.draw();
                    storeFieldValues();
                });
            }

            daterange();

            $.datepicker.setDefaults($.datepicker.regional['en']);
            $(".AddDate").datepicker({
                /*  onSelect: function() {
                     table.draw();
                 }, */
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '1980:2050',
                autoclose: true
            });

            //$.noConflict();
            var token = ''
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(".levels").change(function() {
                let level = $('#AddLevel').val();
                //console.log(product);
                //alert(product);
                $('#AddTerm').html('');
                $('#AddBook').html('');
                if (level.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "level/find/add/" + level,
                        success: function(res) {
                            $('.terms').html(res.html);
                            //$('.terms').removeAttr('readonly');
                            $('.terms').prop('disabled', false);
                        }
                    });
                }
            })

            $(".terms").change(function() {
                let level = $('#AddLevel').val();
                let term = $('#AddTerm').val();
                $('#AddBook').html('');
                if (level.length !== 0 && term.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "term/find/add/" + level + "/" + term,
                        data: {
                            level: $('#AddLevel').val()[0],
                            term: $('#AddTerm').val()[0],
                            level2: $('#AddLevel2').val()[0],
                            term2: $('#AddTerm2').val()[0],
                            _token: token,
                        },
                        async: false,
                        success: function(res) {
                            //console.log(res)
                            $('.scoin').val(res.scoin);

                            $('.books').html(res.html);
                            $('.books').prop('disabled', false);
                        }
                    });
                }
            })

            $(".levels2").change(function() {
                let level = $('#AddLevel2').val();
                //console.log(product);
                //alert(product);
                $('#AddTerm2').html('');
                $('#AddBook2').html('');
                if (level.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "level/find/add/" + level,
                        success: function(res) {
                            $('.terms2').html(res.html);
                            //$('.terms').removeAttr('readonly');
                            $('.terms2').prop('disabled', false);
                        }
                    });
                }
            })

            $(".terms2").change(function() {
                let level = $('#AddLevel2').val();
                let term = $('#AddTerm2').val();
                $('#AddBook2').html('');
                if (level.length !== 0 && term.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "term/find/add/" + level + "/" + term,
                        data: {
                            level: $('#AddLevel').val()[0],
                            term: $('#AddTerm').val()[0],
                            level2: $('#AddLevel2').val()[0],
                            term2: $('#AddTerm2').val()[0],
                            _token: token,
                        },
                        async: false,
                        success: function(res) {
                            //console.log(res)
                            $('.books2').html(res.html);
                            $('.scoin').val(res.scoin);
                            $('.books2').prop('disabled', false);
                        }
                    });
                }
            })

            $(".elevels").change(function() {
                let level = $('#EditLevel').val();
                //console.log(product);
                //alert(product);
                $('#EditTerm').html('');
                $('#EditBook').html('');
                if (level.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "level/find/add/" + level,
                        success: function(res) {
                            $('.eterms').html(res.html);
                            //$('.terms').removeAttr('readonly');
                            $('.eterms').prop('disabled', false);
                        }
                    });
                }
            })

            $(".eterms").change(function() {
                let level = $('#EditLevel').val();
                let term = $('#EditTerm').val();
                $('#EditBook').html('');
                if (level.length !== 0 && term.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "term/find/add/" + level + "/" + term,

                        async: false,
                        success: function(res) {
                            //console.log(res)
                            $('.ebooks').html(res.html);
                            $('.ebooks').prop('disabled', false);
                        }
                    });
                }
            })

            $(".elevels2").change(function() {
                let level = $('#EditLevel2').val();
                //console.log(product);
                //alert(product);
                $('#EditTerm2').html('');
                $('#EditBook2').html('');
                if (level.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "level/find/add/" + level,
                        success: function(res) {
                            $('.eterms2').html(res.html);
                            //$('.terms').removeAttr('readonly');
                            $('.eterms2').prop('disabled', false);
                        }
                    });
                }
            })

            $(".eterms2").change(function() {
                let level = $('#EditLevel2').val();
                let term = $('#EditTerm2').val();
                $('#EditBook2').html('');
                if (level.length !== 0 && term.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "term/find/add/" + level + "/" + term,

                        async: false,
                        success: function(res) {
                            //console.log(res)
                            $('.ebooks2').html(res.html);
                            $('.ebooks2').prop('disabled', false);
                        }
                    });
                }
            })

            $(".rlevels").change(function() {
                let level = $('#ReceiptLevel').val();
                //console.log(product);
                //alert(product);
                $('#ReceiptTerm').html('');
                $('#ReceiptBook').html('');
                if (level.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "level/find/add/" + level,
                        success: function(res) {
                            $('.rterms').html(res.html);
                            //$('.terms').removeAttr('readonly');
                            $('.rterms').prop('disabled', false);
                        }
                    });
                }
            })

            $(".rterms").change(function() {
                let level = $('#ReceiptLevel').val();
                let term = $('#ReceiptTerm').val();
                $('#ReceiptBook').html('');
                if (level.length !== 0 && term.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "term/find/add/" + level + "/" + term,
                        data: {
                            level: $('#ReceiptLevel').val()[0],
                            term: $('#ReceiptTerm').val()[0],
                            level2: $('#ReceiptLevel2').val()[0],
                            term2: $('#ReceiptTerm2').val()[0],
                            _token: token,
                        },
                        async: false,
                        success: function(res) {
                            //console.log(res)
                            $('.rscoin').val(res.scoin);

                            $('.rbooks').html(res.html);
                            $('.rbooks').prop('disabled', false);
                        }
                    });
                }
            })

            $(".rlevels2").change(function() {
                let level = $('#ReceiptLevel2').val();
                //console.log(product);
                //alert(product);
                $('#ReceiptTerm2').html('');
                $('#ReceiptBook2').html('');
                if (level.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "level/find/add/" + level,
                        success: function(res) {
                            $('.rterms2').html(res.html);
                            //$('.terms').removeAttr('readonly');
                            $('.rterms2').prop('disabled', false);
                        }
                    });
                }
            })

            $(".rterms2").change(function() {
                let level = $('#ReceiptLevel2').val();
                let term = $('#ReceiptTerm2').val();
                $('#ReceiptBook2').html('');
                if (level.length !== 0 && term.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "term/find/add/" + level + "/" + term,
                        data: {
                            level: $('#ReceiptLevel').val()[0],
                            term: $('#ReceiptTerm').val()[0],
                            level2: $('#ReceiptLevel2').val()[0],
                            term2: $('#ReceiptTerm2').val()[0],
                            _token: token,
                        },
                        async: false,
                        success: function(res) {
                            console.log(res)
                            $('.rbooks2').html(res.html);
                            $('.rscoin').val(res.scoin);
                            $('.rbooks2').prop('disabled', false);
                        }
                    });
                }
            })

            $("#ReceiptCentre").change(function() {
                let centre = $('#ReceiptCentre').val();
                if (centre.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "histories/running/" + centre,
                        async: false,
                        success: function(res) {
                            //$('#AddCode').prop('readonly', false);
                            $('#s_student').html(res.html_std);
                        }
                    });
                }
            })

            if ($("#ReceiptCentre").val() !== null) {
                let centre = $('#ReceiptCentre').val();
                if (centre.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "histories/running/" + centre,
                        async: false,
                        success: function(res) {
                            //$('#AddCode').prop('readonly', false);
                            $('#s_student').html(res.html_std);
                        }
                    });
                }
            }

            // var table = $('#Listview1').DataTable(table_option);

            $('#SearchButtons').on('click', function() {
                var searchType = $('#search_type').val();
                var keyword = $('#keyword').val();

                if (searchType !== '' && keyword !== '') {
                    // Clear the previous search and any custom filters
                    table.search('').draw();
                    $.fn.dataTable.ext.search.pop(); // Remove the custom date range filter

                    // Apply the new search based on searchType and keyword
                    if (searchType === '1') {
                        table.column(2).search(keyword).draw();
                    } else if (searchType === '2') {
                        table.column(3).search(keyword).draw();
                    } else if (searchType === '3') {
                        table.column(14).search(keyword).draw();
                    } else if (searchType === '4') {
                        table.column(15).search(keyword).draw();
                    } else if (searchType === '5') {
                        table.column(10).search(keyword).draw();
                    } else if (searchType === '6') {
                        table.column(11).search(keyword).draw();
                    }
                } else {
                    // Handle the case where searchType or keyword is empty
                    toastr.error('Please input Search Type and Keyword', {
                        timeOut: 5000
                    });
                }
            });

            // Attach event handler to a button or element to trigger the reset
            $('#resetSearchButton').on('click', async function() {
                localStorage.removeItem('dateStart');
                localStorage.removeItem('searchType');
                localStorage.removeItem('keyword');

                // Set field values to empty
                $('#search_type').val('');
                $('#keyword').val('');

                $('#Listview').html('');

                // Clear DataTable state
                if (table) {
                    table.state.clear();
                    await table.destroy();
                }

                // Set the date range back to its default
                var currentDate = moment();
                var startDate = moment(currentDate).subtract(365, 'days').format('YYYY-MM-DD');
                var endDate = moment(currentDate).endOf('year').format('YYYY-MM-DD');

                daterange();

                table = $('#Listview').DataTable(table_option);
                table.draw();
            });

            function storeFieldValues() {
                var dateStart = $('#reservation').val();
                var searchType = $('#search_type').val();
                var keyword = $('#keyword').val();

                // Store values in local storage
                localStorage.setItem('dateStart', dateStart);
                localStorage.setItem('searchType', searchType);
                localStorage.setItem('keyword', keyword);
            }

            // Attach event handlers to the fields to store values when they change
            $('#search_type').on('change', storeFieldValues);
            $('#keyword').on('input', storeFieldValues);

            $("#AddCentre").change(function() {
                let centre = $('#AddCentre').val();
                if (centre.length !== 0) {
                    $.ajax({
                        method: "GET",
                        url: "contacts/running/" + centre,
                        async: false,
                        success: function(res) {

                            $('#AddCode').val(res.running);
                        }
                    });
                }
            })

            $(document).on('click', '#CreateDemoButton', async function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                $('#custom-tabs-one-home-tab').tab('show');
                //$('#AddSTerm').val(null).trigger("change");
                var addCentre = $('#AddCentre').val();
                var url = '{{ route('demo.code') }}';

                await $.ajax({
                    method: "GET",
                    url: url,
                    success: function(res) {
                        console.log(res);
                        $('#AddDemoCode').val(res.demoCode);
                        $('#AddDemoSchool').val('eiMaths');
                        $('#AddDemoName').val(res.demoCode);
                        $('#AddDemoNickname').val(res.demoCode);

                    }
                });
                $('#CreateDemoModal').modal('show');
            });

            $('#SubmitDemoCreateForm').click(function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                var url = "{{ route('demo.store') }}";
                $.ajax({
                    url: url,
                    method: 'post',
                    data: {
                        code: $('#AddDemoCode').val(),
                        school: $('#AddDemoSchool').val(),
                        name: $('#AddDemoName').val(),
                        nickname: $('#AddDemoNickname').val(),
                        password: $('#AddDemoPassword').val(),
                        password_confirmation: $('#AddDemoConfPassword').val(),
                        _token: token,
                    },
                    success: function(result) {
                        console.log(result);
                        if (result.errors) {
                            $('.alert-danger').html('');
                            $.each(result.errors, function(key, value) {
                                $('.alert-danger').show();
                                $('.alert-danger').append('<strong><li>' + value +
                                    '</li></strong>');
                            });
                        } else {
                            $('.alert-danger').hide();
                            $('.alert-success').show();
                            $('.alert-success').append('<strong><li>' + result.success +
                                '</li></strong>');
                            toastr.success(result.success, {
                                timeOut: 5000
                            });
                            $('#Listview').DataTable().ajax.reload();

                            $('.form').trigger('reset');
                            $('#CreateDemoModal').modal('hide');

                            // showOverlay();

                        }
                    }
                });
            });

            $(document).on('click', '#CreateButton', async function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                $('#custom-tabs-one-home-tab').tab('show');
                //$('#AddSTerm').val(null).trigger("change");
                $('#AddBook').val(null).trigger("change");
                $('#AddLevel').val(null).trigger("change");
                $('#AddTerm').val(null).trigger("change");
                $('#AddBook2').val(null).trigger("change");
                $('#AddLevel2').val(null).trigger("change")
                $('#AddTerm2').val(null).trigger("change")

                var addCentre = $('#AddCentre').val();

                await $.ajax({
                    method: "GET",
                    url: "contacts/running/" + addCentre,
                    success: function(res) {
                        //console.log(res)
                        $('#AddCode').val(res.running);
                        $('#AddTerm').prop('disabled', true);
                        $('#AddBook').prop('disabled', true);
                        $('#AddTerm2').prop('disabled', true);
                        $('#AddBook2').prop('disabled', true);
                    }
                });
                $('#CreateModal').modal('show');
            });


            $(document).on('click', '#ReceiptButton', async function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                $('#custom-tabs-one-home-tab-receipt').tab('show');
                //$('#AddSTerm').val(null).trigger("change");
                $('#AddBook').val(null).trigger("change");
                $('#AddLevel').val(null).trigger("change");
                $('#AddTerm').val(null).trigger("change");
                $('#AddBook2').val(null).trigger("change");
                $('#AddLevel2').val(null).trigger("change")
                $('#AddTerm2').val(null).trigger("change");

                $('#ReceiptModal').modal('show');
            });

            // Create product Ajax request.
            $('#SubmitCreateForm').click(function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                var selectedValue1 = $('#AddLevel').val()[0];
                var selectedValue2 = $('#AddLevel2').val()[0];

                if (selectedValue1 && selectedValue1.trim() !== '') {
                    $("#AddLevel").removeClass("is-invalid");
                    $("#AddLevel").addClass("is-valid");
                } else {
                    $("#AddLevel").addClass("is-invalid");
                    $("#AddLevel").removeClass("is-valid");
                    toastr.error('Please Select Start Level', {
                        timeOut: 5000
                    });
                    return false;
                }

                if (selectedValue2 < selectedValue1) {
                    toastr.error("Cannot select 'To level' less than 'Start level'", {
                        timeOut: 5000
                    });
                    return false;
                }

                $.ajax({
                    url: "{{ route('contacts.store') }}",
                    method: 'post',
                    data: {
                        centre: $('#AddCentre').val()[0],
                        code: $('#AddCode').val(),
                        name: $('#AddName').val(),
                        nickname: $('#AddNickname').val(),
                        gender: $("input[name='gender']:checked").val(),
                        birth_date: $('#AddBirthDate').val(),
                        start_date: $('#AddDate').val(),
                        //start_term: $('#AddSTerm').val()[0],
                        school: $('#AddSchool').val(),
                        level: $('#AddLevel').val()[0],
                        term: $('#AddTerm').val()[0],
                        bookuse: $('#AddBook').val()[0],
                        level2: $('#AddLevel2').val()[0],
                        term2: $('#AddTerm2').val()[0],
                        bookuse2: $('#AddBook2').val()[0],
                        discontinued: 0,
                        //ddate: $('#AddDDate').val(),
                        //reason: $('#AddReason').val(),
                        postcode: $('#AddPostcode').val(),
                        address: $('#AddAddress').val(),
                        telephone: $('#AddTelephone').val(),
                        father_name: $('#AddfName').val(),
                        father_email: $('#AddfEmail').val(),
                        father_mobile: $('#AddfTelephone').val(),
                        mother_name: $('#AddmName').val(),
                        mother_email: $('#AddmEmail').val(),
                        mother_mobile: $('#AddmTelephone').val(),
                        _token: token,
                    },
                    success: function(result) {
                        console.log(result);
                        if (result.errors) {
                            $('.alert-danger').html('');
                            $.each(result.errors, function(key, value) {
                                $('.alert-danger').show();
                                $('.alert-danger').append('<strong><li>' + value +
                                    '</li></strong>');
                            });
                        } else {
                            $('.alert-danger').hide();
                            $('.alert-success').show();
                            $('.alert-success').append('<strong><li>' + result.success +
                                '</li></strong>');
                            toastr.success(result.success, {
                                timeOut: 5000
                            });
                            $('#Listview').DataTable().ajax.reload();
                            $('#AddSTerm').val(null).trigger("change");
                            $('#AddBook').val(null).trigger("change");
                            $('#AddLevel').val(null).trigger("change")
                            $('#AddTerm').val(null).trigger("change")
                            $('.form').trigger('reset');
                            $('#CreateModal').modal('hide');

                            showOverlay();

                            var oid = result.oid;
                            var url = "{{ route('receipts_pending') }}?new=" +
                                encodeURIComponent(oid);
                            window.location.href = url;
                        }
                    }
                });
            });

            let id;
            $(document).on('click', '.btn-show', function(e) {
                e.preventDefault();

                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                $('#custom-tabs-one-homee-tab').tab('show');
                id = $(this).data('id');

                $.ajax({
                    url: "contacts/course/" + id,
                    method: 'GET',
                    success: function(res) {

                        console.log(res);
                        $('#CourseTable').html(res);
                        $('#CourseModal').modal('show');
                    }
                });

            })

            $(document).on('click', '#getEditData', function(e) {
                e.preventDefault();

                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                $('#custom-tabs-one-homee-tab').tab('show');
                id = $(this).data('id');
                console.log(id);

                $.ajax({
                    url: "contacts/edit/" + id,
                    method: 'GET',
                    success: function(res) {
                        $('#EditCentre').val(res.data.centre).change();
                        $('#EditName').val(res.data.name);
                        $('#EditNickname').val(res.data.nickname);
                        $('#EditCode').val(res.data.code);
                        $('#EditDate').val(res.data.start_date);
                        $('#EditBirthDate').val(res.data.birth_date);
                        $('#EditSchool').val(res.data.school);
                        //$('#EditSTerm').val(res.data.start_term).trigger('change.select2');
                        $('#EditLevel').val(res.datas[0].level_id).trigger('change.select2');
                        $('#EditTerm').val(res.datas[0].term_id).trigger('change.select2');
                        $('#EditBook').val(res.datas[0].product_id).change();

                        if (res.data.order_status == 1) {
                            //$('#EditLevel').prop('disabled', true);
                            //$('#EditLevel2').prop('disabled', true);
                        }

                        if (res.datas[1] && res.datas[1].hasOwnProperty('level_id') && res
                            .datas[1].level_id !== '') {
                            $('#EditLevel2').val(res.datas[1].level_id).trigger(
                                'change.select2');
                            $('#EditTerm2').val(res.datas[1].term_id).trigger('change.select2');
                            $('#EditBook2').val(res.datas[1].product_id).change();
                        }

                        if (res.data.discontinued == 1) {
                            $('#ecustomCheckbox1').prop('checked', true);
                            $('#EditDDate').prop('disabled', false);
                            $('#EditReason').prop('disabled', false);
                            $('#sreason').removeClass('d-none');
                            $('#EditDDate').val(res.data.discontinued_date);
                        } else {
                            $('#ecustomCheckbox1').prop('checked', false);
                            $('#EditDDate').prop('disabled', true);
                            $('#EditReason').prop('disabled', true);
                            $('#sreason').addClass('d-none');
                            $('#EditDDate').val('');
                        }

                        if (res.data.gender == 1) {
                            $("#eradioSuccess1").prop("checked", true);
                        } else if (res.data.gender == 2) {
                            $("#eradioSuccess2").prop("checked", true);
                        }

                        $('#EditReason').val(res.data.discontinued_reason);

                        $('#EditPostcode').val(res.data.postcode);
                        $('#EditAddress').val(res.data.address);
                        $('#EditTelephone').val(res.data.telephone);

                        $('#EditfName').val(res.data.father_name);
                        $('#EditfEmail').val(res.data.father_email);
                        $('#EditfTelephone').val(res.data.father_mobile);
                        $('#EditmName').val(res.data.mother_name);
                        $('#EditmEmail').val(res.data.mother_email);
                        $('#EditmTelephone').val(res.data.mother_mobile);

                        $('#EditTerm').prop('disabled', true);
                        $('#EditBook').prop('disabled', true);
                        $('#EditTerm2').prop('disabled', true);
                        $('#EditBook2').prop('disabled', true);

                        $('#EditModalBody').html(res.html);
                        $('#EditModal').modal('show');
                    }
                });

            })

            $('#SubmitEditForm').click(function(e) {
                if (!confirm("Confirm the action?")) return;
                e.preventDefault();

                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                var selectedValue1 = $('#EditLevel').val()[0];
                var selectedValue2 = $('#EditLevel2').val()[0];

                if (selectedValue1 && selectedValue1.trim() !== '') {
                    $("#EditLevel").removeClass("is-invalid");
                    $("#EditLevel").addClass("is-valid");
                } else {
                    $("#EditLevel").addClass("is-invalid");
                    $("#EditLevel").removeClass("is-valid");
                    toastr.error('Please Select Start Level', {
                        timeOut: 5000
                    });
                    return false;
                }

                if (selectedValue2 < selectedValue1) {
                    toastr.error("Cannot select 'To level' less than 'Start level'", {
                        timeOut: 5000
                    });
                    return false;
                }


                $.ajax({
                    url: "contacts/save/" + id,
                    method: 'PUT',
                    data: {
                        centre: $('#EditCentre').val()[0],
                        code: $('#EditCode').val(),
                        name: $('#EditName').val(),
                        nickname: $('#EditNickname').val(),
                        gender: $("input[name='egender']:checked").val(),
                        birth_date: $('#EditBirthDate').val(),
                        start_date: $('#EditDate').val(),
                        //start_term: $('#EditSTerm').val()[0],
                        school: $('#EditSchool').val(),
                        level: $('#EditLevel').val()[0],
                        term: $('#EditTerm').val()[0],
                        bookuse: $('#EditBook').val()[0],
                        level2: $('#EditLevel2').val()[0],
                        term2: $('#EditTerm2').val()[0],
                        bookuse2: $('#EditBook2').val()[0],
                        discontinued: $('#ecustomCheckbox1').is(':checked') ? 1 : 0,
                        discontinued_date: $('#EditDDate').val(),
                        discontinued_reason: $('#EditReason').val(),
                        postcode: $('#EditPostcode').val(),
                        address: $('#EditAddress').val(),
                        telephone: $('#EditTelephone').val(),
                        father_name: $('#EditfName').val(),
                        father_email: $('#EditfEmail').val(),
                        father_mobile: $('#EditfTelephone').val(),
                        mother_name: $('#EditmName').val(),
                        mother_email: $('#EditmEmail').val(),
                        mother_mobile: $('#EditmTelephone').val(),
                    },

                    success: function(result) {
                        //console.log(result);
                        if (result.errors) {
                            $('.alert-danger').html('');
                            $.each(result.errors, function(key, value) {
                                $('.alert-danger').show();
                                $('.alert-danger').append('<strong><li>' + value +
                                    '</li></strong>');
                            });
                        } else {
                            var ostatus = result.discontinued;
                            if (ostatus == 1) {
                                var url = "{{ route('discontinued') }}";
                                window.location.href = url;
                            } else {
                                $('.alert-danger').hide();
                                $('.alert-success').show();
                                $('.alert-success').append('<strong><li>' + result.success +
                                    '</li></strong>');
                                $('#EditModal').modal('hide');
                                toastr.success(result.success, {
                                    timeOut: 5000
                                });
                                $('#Listview').DataTable().ajax.reload();
                            }

                        }
                    }
                });
            });

            // Create product Ajax request.
            $('#ReceiptCreateForm').click(function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();


                var selectedValue1 = $('#ReceiptLevel').val()[0];
                var selectedValue2 = $('#ReceiptLevel2').val()[0];

                if (selectedValue1 && selectedValue1.trim() !== '') {
                    $("#ReceiptLevel").removeClass("is-invalid");
                    $("#ReceiptLevel").addClass("is-valid");
                } else {
                    $("#ReceiptLevel").addClass("is-invalid");
                    $("#ReceiptLevel").removeClass("is-valid");
                    toastr.error('Please Select Start Level', {
                        timeOut: 5000
                    });
                    return false;
                }

                if (selectedValue2 < selectedValue1) {
                    toastr.error("Cannot select 'To level' less than 'Start level'", {
                        timeOut: 5000
                    });
                    return false;
                }

                $.ajax({
                    url: "{{ route('contacts.receipt') }}",
                    method: 'post',
                    data: {
                        centre: $('#ReceiptCentre').val()[0],
                        student: $('#s_student').val()[0],
                        level: $('#ReceiptLevel').val()[0],
                        term: $('#ReceiptTerm').val()[0],
                        bookuse: $('#ReceiptBook').val()[0],
                        level2: $('#ReceiptLevel2').val()[0],
                        term2: $('#ReceiptTerm2').val()[0],
                        bookuse2: $('#ReceiptBook2').val()[0],
                        _token: token,
                    },
                    success: function(result) {
                        console.log(result);
                        if (result.errors) {
                            $('.alert-danger').html('');
                            $.each(result.errors, function(key, value) {
                                $('.alert-danger').show();
                                $('.alert-danger').append('<strong><li>' + value +
                                    '</li></strong>');
                            });
                        } else {
                            $('.alert-danger').hide();
                            $('.alert-success').show();
                            $('.alert-success').append('<strong><li>' + result.success +
                                '</li></strong>');
                            toastr.success(result.success, {
                                timeOut: 5000
                            });
                            $('#Listview').DataTable().ajax.reload();
                            $('#ReceiptBook').val(null).trigger("change");
                            $('#ReceiptLevel').val(null).trigger("change")
                            $('#ReceiptTerm').val(null).trigger("change")
                            $('.form').trigger('reset');
                            $('#ReceiptModal').modal('hide');

                            showOverlay();

                            var oid = result.oid;
                            var url = "{{ route('receipts_pending') }}?new=" +
                                encodeURIComponent(oid);
                            window.location.href = url;
                        }
                    }
                });
            });

            $(document).on('click', '.btn-delete', function() {
                if (!confirm("Confirm the action?")) return;

                var rowid = $(this).data('rowid')
                var el = $(this)
                if (!rowid) return;


                $.ajax({
                    //type: "POST",
                    method: 'DELETE',
                    dataType: 'JSON',
                    url: "contacts/destroy/",
                    data: {
                        id: rowid,
                        //_method: 'delete',
                        _token: token
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.success) {
                            toastr.success(data.message, {
                                timeOut: 5000
                            });
                            table.row(el.parents('tr'))
                                .remove()
                                .draw();
                        }
                    }
                }); //end ajax
            })

        });
    </script> --}}
@endsection
