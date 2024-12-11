{{-- @extends('backend.master') --}}
@extends('layouts.app')
@section('style')
    {{-- @include('affiliate.backend.my_affiliate-head') --}}
    {{-- <link rel="stylesheet" href="{{ asset('backend/css/backend_style.css') }}{{ assetVersion() }}" /> --}}

    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script> --}}
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">

@endsection

@php
    $table_name = 'courses';

    $category = request()->get('category');
    $type = request()->get('type');
    $instructor = request()->get('instructor');
    $status = request()->get('search_status');
    $search_required_type = request()->get('search_required_type');
    $search_delivery_mode = request()->get('search_delivery_mode');
    $url =
        route('course.data') .
        '?search_status=' .
        $status .
        '&category=' .
        $category .
        '&type=' .
        $type .
        '&instructor=' .
        $instructor .
        '&required_type=' .
        $search_required_type .
        '&mode_of_delivery=' .
        $search_delivery_mode;
    $text = 'All';

@endphp
@section('table')
    {{ $table_name }}
@endsection
@section('content')
    {!! generateBreadcrumb() !!}
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
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">
                                <h3 class="card-title">
                                    {{ $title ?? '' }}
                                    Course List
                                </h3>
                            </div>
                            <style>
                                .card-header .btn {
                                    margin-left: auto;
                                    /* Ensures the button aligns to the far right */
                                }
                            </style>
                            {{-- href="{{ route('course.store') }}"> --}}

                        </div>
                        <div class="card-body">
                            <div class="table-responsive">

                                <div class="row">
                                    <div class="col-lg-12">
                                        <!-- table-responsive -->
                                        <div class="">
                                            <table id="Listview" class="table classList">
                                                <thead>
                                                    <tr>
                                                        <th scope="col"> SL</th>
                                                        <th scope="col"> Type</th>
                                                        {{-- <th scope="col"> Required Type</th> --}}
                                                        <th scope="col">CourseTitle</th>
                                                        <th scope="col">Delivery</th>
                                                        <th scope="col">Category</th>
                                                        <th scope="col">Instructor</th>
                                                        {{-- <th scope="col">Lesson</th> --}}
                                                        {{-- <th scope="col">Enrolled</th> --}}
                                                        <th scope="col">Price</th>
                                                        <th scope="col">View Scope</th>
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

                <div class="modal fade admin-query" id="editCourse">
                    <div class="modal-dialog modal_1000px modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">{{ __('common.Edit') }} {{ __('quiz.Topic') }} </h4>
                                <button type="button" class="close " data-bs-dismiss="modal">
                                    <i class="ti-close "></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="#" method="POST" {{-- <form action="{{ route('AdminUpdateCourse') }}" method="POST" --}} enctype="multipart/form-data"
                                    id="courseEditForm">
                                    @csrf
                                    <div class="row">
                                        <div class="col-xl-6 ">
                                            <div class="primary_input mb-25">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label class="primary_input_label" for="    ">
                                                            {{ __('courses.Type') }}</label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="radio" class="common-radio type1" id="type_edit_1"
                                                            name="type" value="1">
                                                        <label for="type_edit_1">{{ __('courses.Course') }}</label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="radio" class="common-radio type2" id="type_edit_2"
                                                            name="type" value="2">
                                                        <label for="type_edit_2">{{ __('quiz.Quiz') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-xl-6 dripCheck">
                                            <div class="primary_input mb-25">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label class="primary_input_label" for="    ">
                                                            {{ __('common.Drip Content') }}</label>
                                                    </div>

                                                    <div class="col-md-6">

                                                        <input type="radio" class="common-radio drip0" id="drip_edit_0"
                                                            name="drip" value="0"
                                                            {{ @$course->drip == 0 ? 'checked' : '' }}>
                                                        <label for="drip_edit_0">{{ __('common.No') }}</label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="radio" class="common-radio drip1" id="drip_edit_1"
                                                            name="drip" value="1"
                                                            {{ @$course->drip == 1 ? 'checked' : '' }}>
                                                        <label for="drip_edit_1">{{ __('common.Yes') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label" for="title">{{ __('quiz.Topic') }}
                                                    {{ __('common.Title') }}
                                                    *</label>
                                                <input class="primary_input_field" name="title" id="title"
                                                    placeholder="-" type="text"
                                                    {{ $errors->has('title') ? 'autofocus' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="id" class="course_id" id="editCourseId"
                                        value="">

                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="primary_input mb-35">
                                                <label class="primary_input_label"
                                                    for="about">{{ __('courses.Course') }}
                                                    {{ __('courses.Requirements') }} </label>
                                                <textarea class="lms_summernote" name="requirements" id="requirementsEdit" cols="30" rows="10"> </textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="primary_input mb-35">
                                                <label class="primary_input_label"
                                                    for="about">{{ __('courses.Course') }}
                                                    {{ __('courses.Description') }}</label>
                                                <textarea class="lms_summernote" name="about" id="aboutEdit" cols="30" rows="10"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="primary_input mb-35">
                                                <label class="primary_input_label"
                                                    for="about">{{ __('courses.Course') }}
                                                    {{ __('courses.Outcomes') }} </label>
                                                <textarea class="lms_summernote" name="outcomes" id="outcomesEdit" cols="30" rows="10"> </textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">

                                        <div class="col-xl-6 courseBox">
                                            <select class="primary_select edit_category_id" name="category"
                                                {{ $errors->has('category') ? 'autofocus' : '' }}>
                                                <option
                                                    data-display="{{ __('common.Select') }} {{ __('quiz.Category') }}"
                                                    value="">{{ __('common.Select') }} {{ __('quiz.Category') }}
                                                    *
                                                </option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}">{{ @$category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-xl-6 courseBox" id="edit_subCategoryDiv{{ @$course->id }}">
                                            <select class="primary_select " name="sub_category" id="edit_subcategory_id"
                                                {{ $errors->has('sub_category') ? 'autofocus' : '' }}>
                                                <option
                                                    data-display="{{ __('common.Select') }} {{ __('courses.Sub Category') }}"
                                                    value="">{{ __('common.Select') }}
                                                    {{ __('courses.Sub Category') }}

                                                </option>


                                            </select>
                                        </div>
                                        <div class="col-xl-6 mt-30 quizBox" style="display: none">
                                            <select class="primary_select" name="quiz" id="quiz_edit_id"
                                                {{ $errors->has('quiz') ? 'autofocus' : '' }}>
                                                <option data-display="{{ __('common.Select') }} {{ __('quiz.Quiz') }}"
                                                    value="">{{ __('common.Select') }} {{ __('quiz.Quiz') }}
                                                    *
                                                </option>
                                                {{-- @foreach ($quizzes as $quiz)
                                                    <option value="{{ $quiz->id }}">{{ @$quiz->title }} </option>
                                                @endforeach --}}
                                            </select>
                                        </div>

                                        <div class="col-xl-4 mt-30 makeResize">
                                            <select class="primary_select" id="levelEdit" name="level"
                                                {{ $errors->has('level') ? 'autofocus' : '' }}>
                                                <option
                                                    data-display="{{ __('common.Select') }} {{ __('courses.Level') }}"
                                                    value="">{{ __('common.Select') }} {{ __('courses.Level') }}
                                                    *
                                                </option>
                                                @foreach ($levels as $level)
                                                    <option value="{{ $level->id }}">
                                                        {{ $level->title }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>
                                        <div class="col-xl-4 mt-30 makeResize" id="">
                                            <select class="primary_select mb_30" name="language" id="languageEdit"
                                                {{ $errors->has('language') ? 'autofocus' : '' }}>
                                                <option
                                                    data-display="{{ __('common.Select') }} {{ __('courses.Language') }}"
                                                    value="">{{ __('common.Select') }}
                                                    {{ __('courses.Language') }}
                                                    *
                                                </option>

                                                @foreach ($languages as $language)
                                                    <option value="{{ $language->id }}">{{ $language->native }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-xl-4 makeResize">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label"
                                                    for="">{{ __('common.Duration') }}
                                                    ({{ __('common.In Minute') }})
                                                    *</label>
                                                <input class="primary_input_field" id="durationEdit" name="duration"
                                                    placeholder="-" min="0" step="any" type="number"
                                                    {{ $errors->has('duration') ? 'autofocus' : '' }}>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row d-none">
                                        <div class="col-lg-6">
                                            <div class="checkbox_wrap d-flex align-items-center">
                                                <label for="course_1" class="switch_toggle">
                                                    <input type="checkbox" id="edit_course_1">
                                                    <i class="slider round"></i>
                                                </label>
                                                <label>{{ __('courses.This course is a top course') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-20">
                                        <div class="col-lg-6">
                                            <div class="checkbox_wrap d-flex align-items-center mt-40">
                                                <label for="editCourseFree" class="switch_toggle">
                                                    <input type="checkbox" class="edit_course_2" name="is_free"
                                                        value="1" id="editCourseFree">
                                                    <i class="slider round"></i>
                                                </label>
                                                <label>{{ __('courses.This course is a free course') }}</label>
                                            </div>
                                        </div>
                                        <div class="col-xl-4" id="edit_price_div">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label"
                                                    for="">{{ __('courses.Price') }}</label>
                                                <input class="primary_input_field" name="price" id="priceEdit"
                                                    placeholder="-" value="" type="text">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-20 editDiscountDiv">
                                        <div class="col-lg-6">
                                            <div class="checkbox_wrap d-flex align-items-center mt-40">
                                                <label for="editCourseDiscount" class="switch_toggle">
                                                    <input type="checkbox" class="edit_course_3" name="is_discount"
                                                        value="1" id="editCourseDiscount">
                                                    <i class="slider round"></i>
                                                </label>
                                                <label>{{ __('courses.This course has discounted price') }}</label>
                                            </div>
                                        </div>

                                        <div class="col-xl-4" id="edit_discount_price_div">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label"
                                                    for="">{{ __('courses.Discount') }}
                                                    {{ __('courses.Price') }}</label>
                                                <input class="primary_input_field editDiscount" name="discount_price"
                                                    id="editDiscountPrice" placeholder="-" type="text">
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row mt-20 videoOption">
                                        <div class="col-xl-4 mt-25">
                                            <select class="primary_select category_id " name="host"
                                                id="editCourseHost">
                                                <option data-display="{{ __('courses.Course overview host') }} *"
                                                    value="">{{ __('courses.Course overview host') }}
                                                </option>

                                                <option value="Youtube">
                                                    {{ __('courses.Youtube') }}
                                                </option>
                                                <option value="Vimeo">
                                                    {{ __('courses.Vimeo') }}
                                                </option>
                                                @if (isModuleActive('AmazonS3'))
                                                    <option value="AmazonS3">
                                                        {{ __('courses.Amazon S3') }}
                                                    </option>
                                                @endif

                                                <option value="Self">
                                                    {{ __('courses.Self Host') }}
                                                </option>


                                            </select>
                                        </div>
                                        <div class="col-xl-8 ">
                                            <div class="input-effect videoUrl"
                                                style="display:@if ((isset($course) && @$course->host != 'Youtube') || !isset($course)) none @endif">
                                                <label>{{ __('courses.Video URL') }}
                                                    <span class="required_mark">*</span></label>
                                                <input id="couseEditViewUrl"
                                                    class="primary_input_field youtubeVideo name{{ $errors->has('trailer_link') ? ' is-invalid' : '' }}"
                                                    type="text" name="trailer_link"
                                                    placeholder="{{ __('courses.Video URL') }}" autocomplete="off"
                                                    value=" " {{ $errors->has('trailer_link') ? 'autofocus' : '' }}>
                                                <span class="focus-border"></span>
                                                @if ($errors->has('trailer_link'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('trailer_link') }}</strong>
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="row  vimeoUrl" id=""
                                                style="display: @if ((isset($course) && @$course->host != 'Vimeo') || !isset($course)) none @endif">
                                                <div class="col-lg-12" id="">
                                                    <label class="primary_input_label"
                                                        for="">{{ __('courses.Vimeo Video') }}</label>
                                                    <select class="primary_select vimeoVideo" name="vimeo"
                                                        id="viemoEditCourse">
                                                        <option
                                                            data-display="{{ __('common.Select') }} {{ __('courses.Video') }}"
                                                            value="">{{ __('common.Select') }}
                                                            {{ __('courses.Video') }}
                                                        </option>
                                                        @if (isset($video_list))
                                                            @foreach ($video_list as $video)
                                                                <option value="{{ @$video['uri'] }}">
                                                                    {{ @$video['name'] }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    @if ($errors->has('vimeo'))
                                                        <span class="invalid-feedback invalid-select" role="alert">
                                                            <strong>{{ $errors->first('vimeo') }}</strong>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="row  videofileupload" id=""
                                                style="display: @if ((isset($course) && (@$course->host == 'Vimeo' || @$course->host == 'Youtube')) || !isset($course)) none @endif">

                                                <div class="col-xl-12">
                                                    <div class="primary_input">
                                                        <label class="primary_input_label"
                                                            for="">{{ __('courses.Video File') }}</label>
                                                        <div class="primary_file_uploader">
                                                            {{-- <input
                                                             class="primary-input filePlaceholder"
                                                             type="text"

                                                             placeholder="{{__('courses.Browse Video file')}}"
                                                             readonly="">
                                                         <button class="" type="button">
                                                             <label
                                                                 class="primary-btn small fix-gr-bg"
                                                                 for="document_file_edit">{{__('common.Browse') }}</label>
                                                             <input type="file"
                                                                    class="d-none fileUpload"
                                                                    name="file"
                                                                    id="document_file_edit">
                                                         </button>

                                                         @if ($errors->has('file'))
                                                             <span
                                                                 class="invalid-feedback invalid-select"
                                                                 role="alert">
                                         <strong>{{ $errors->first('file') }}</strong>
                                     </span>
                                                         @endif --}}
                                                            <input type="file" class="filepond" name="file">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-20">


                                        <div class="col-xl-6">
                                            <div class="primary_input mb-35">
                                                <label class="primary_input_label"
                                                    for="">{{ __('courses.Course Thumbnail') }}
                                                    ({{ __('common.Max Image Size 1MB') }})
                                                    *</label>
                                                <div class="primary_file_uploader">
                                                    <input class="primary-input filePlaceholder" type="text"
                                                        id=""
                                                        placeholder="{{ __('courses.Browse Image file') }}"
                                                        readonly="" {{ $errors->has('image') ? 'autofocus' : '' }}>
                                                    <button class="" type="button">
                                                        <label class="primary-btn small fix-gr-bg"
                                                            for="document_file_1_edit_">{{ __('common.Browse') }}</label>
                                                        <input type="file" class="d-none fileUpload" name="image"
                                                            id="document_file_1_edit_">
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @if (\Illuminate\Support\Facades\Auth::user()->subscription_api_status == 1)
                                            <div class="col-xl-6">
                                                <label class="primary_input_label"
                                                    for="">{{ __('newsletter.Subscription List') }}
                                                </label>
                                                <select class="primary_select" id="subscriptionEdit"
                                                    name="subscription_list"
                                                    {{ $errors->has('subscription_list') ? 'autofocus' : '' }}>
                                                    <option
                                                        data-display="{{ __('common.Select') }} {{ __('newsletter.Subscription List') }}"
                                                        value="">{{ __('common.Select') }}
                                                        {{ __('newsletter.Subscription List') }}

                                                    </option>
                                                    @foreach ($sub_lists as $list)
                                                        <option value="{{ $list['id'] }}">
                                                            {{ $list['name'] }}
                                                        </option>
                                                    @endforeach

                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="row">


                                        <div class="col-xl-12">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label"
                                                    for="">{{ __('courses.Meta keywords') }}</label>
                                                <input class="primary_input_field" name="meta_keywords" id="editMetaKey"
                                                    placeholder="-" type="text">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label"
                                                    for="">{{ __('courses.Meta description') }}</label>
                                                <textarea id="editMetaDetails" class="primary_input_field" name="meta_description" style="height: 200px"
                                                    rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 text-center pt_15">
                                        <div class="d-flex justify-content-center">
                                            <button class="primary-btn semi_large2  fix-gr-bg" id="save_button_parent"
                                                type="submit"><i class="ti-check"></i> {{ __('common.Update') }}
                                                {{ __('courses.Course') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    @include('course.coures_modal_create_form')
    @include('course.coures_modal_edit_form')
    {{-- @include('backend.partials.delete_modal') --}}
    @php
        // dd($url);
    @endphp
@endsection
@section('script')
    {{-- <script src="{{ asset('backend/js/category.js') }}{{ assetVersion() }}"></script> --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> --}}

    {{-- @include('affiliate.backend.my_affiliate-script') --}}

    <script src="{{ asset('/Modules/CourseSetting/Resources/assets/js/course.js') }}"></script>


    <script>
        $(document).ready(function() {
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
                        data: 'type',
                        name: 'type'
                    }, // Type
                    {
                        data: 'title',
                        name: 'title'
                    }, // CourseTitle
                    {
                        data: 'mode_of_delivery',
                        name: 'mode_of_delivery'
                    }, // Delivery
                    {
                        data: 'category',
                        name: 'category',
                        orderable: false,
                        searchable: false
                    }, // Category
                    {
                        data: 'user',
                        name: 'user',
                        orderable: false,
                        searchable: false
                    }, // Instructor
                    // {
                    //     data: 'lessons',
                    //     name: 'lessons',
                    //     orderable: false,
                    //     searchable: false
                    // }, // Lesson
                    // {
                    //     data: 'enrolled_users',
                    //     name: 'enrolled_users',
                    //     orderable: false,
                    //     searchable: false
                    // }, // Enrolled
                    {
                        data: 'price',
                        name: 'price',
                        orderable: false,
                        searchable: false
                    }, // Price
                    {
                        data: 'scope',
                        name: 'scope'
                    }, // View Scope
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
                    // Sequential numbering
                    var info = table.page.info();
                    $('td:eq(0)', row).html(info.start + dataIndex + 1);
                }
            });
        });
    </script>
    <script>
        $(document).on('change', '.status_enable_disable', function() {
            let courseId = $(this).data('id');
            let status = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: '{{ route('courses.updateStatus') }}', // Update with your route
                type: 'POST',
                data: {
                    id: courseId,
                    status: status,
                    _token: '{{ csrf_token() }}', // CSRF token for security
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Status updated successfully.');
                    } else {
                        toastr.error('Failed to update status.');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    toastr.error('An error occurred while updating the status.');
                },
            });
        });
    </script>
    <script>
        document.getElementById('addCourseForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Gather form data
            const formData = new FormData(this);

            // Send data via AJAX (example using Fetch API)
            fetch('{{ route('course.store') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#Listview').DataTable().ajax.reload();
                        toastr.success(data.message, {
                            timeOut: 5000
                        });

                        $('.form').trigger('reset');

                        $('#addCourseModal').modal('hide');
                        // alert('Course added successfully!');
                        // location.reload(); // Optionally reload the page or update the UI dynamically
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
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('edit-btn')) {
                const courseId = event.target.getAttribute('data-id');
                // console.log(courseId); // Should log the course ID
                // Remaining logic...

                const editUrl = "{{ route('course.edit', ['id' => ':id']) }}".replace(':id', courseId);

                fetch(editUrl, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            $('#editCourseId').val(data.course.id);
                            document.getElementById('editCourseTitle').value = data.course.title.en;
                            document.getElementById('editCategoryId').value = data.course.category_id;
                            // Add other fields as needed
                            // Populate mode of delivery (set the selected option based on course data)
                            document.getElementById('editDeliveryMode').value = data.course.mode_of_delivery;

                            // Populate course price
                            document.getElementById('editCoursePrice').value = data.course.price;

                            // Populate status (radio buttons)
                            if (data.course.status === 1) {
                                document.getElementById('edittStatusActive').checked = true;
                                document.getElementById('editStatusInactive').checked = false;
                            } else {
                                document.getElementById('edittStatusActive').checked = false;
                                document.getElementById('editStatusInactive').checked = true;
                            }

                            // Handle course image (if any image exists)
                            const imageElement = document.getElementById('courseImagePreview');
                            if (data.course.image) {
                                const imagePath = data.course.image.replace('public/', '');
                                const imageUrl = "{{ asset('') }}" + imagePath;
                                imageElement.src = imageUrl;


                            } else {
                                imageElement.src = ''; // Clear image if none exists
                            }

                            // Populate hidden fields
                            document.getElementById('editLevel').value = data.course.level || '1';
                            document.getElementById('editPublish').value = data.course.publish || '1';
                            document.getElementById('editLang_id').value = data.course.lang_id || '19';
                            document.getElementById('editUser_id').value = data.course.user_id || '';
                            document.getElementById('editType').value = data.course.type || '1';
                            document.getElementById('editScope').value = data.course.scope || '1';
                            document.getElementById('editShow_mode_of_delivery').value = data.course
                                .show_mode_of_delivery || '1';

                            // Show the modal
                            const modal = new bootstrap.Modal(document.getElementById('editCourseModal'));
                            modal.show();
                        } else {
                            alert('Failed to fetch course details.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });

        document.getElementById('editCourseForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const courseId = document.getElementById('editCourseId').value;
            const updateUrl = "{{ route('course.update', ['id' => ':id']) }}".replace(':id', courseId);
            const formData = new FormData(this);

            fetch(updateUrl, {
                    method: 'POST', // Use POST and spoof PUT
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-HTTP-Method-Override': 'PUT', // Spoof method
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('HTTP error ' + response.status);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        $('#Listview').DataTable().ajax.reload();
                        toastr.success(data.message, {
                            timeOut: 5000
                        });
                        $('.form').trigger('reset');

                        $('#editCourseModal').modal('hide');
                        // location.reload();
                    } else {
                        // alert('Failed to update course: ' + (data.message || 'Unknown error'));
                        $('#Listview').DataTable().ajax.reload();
                        toastr.error(data.message, {
                            timeOut: 5000
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('delete-btn')) {

                // Use event.target to get the clicked element
                const courseId = event.target.getAttribute('data-id');
                const destroyUrl = "{{ route('course.destroy', ['id' => ':id']) }}".replace(':id', courseId);


                if (confirm('Are you sure you want to delete this course?')) {
                    fetch(destroyUrl, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                $('#Listview').DataTable().ajax.reload();
                                toastr.success(data.message, {
                                    timeOut: 5000
                                });
                                // alert('Course deleted successfully!');
                                // location.reload(); // Refresh the page or update the UI dynamically
                            } else {
                                toastr.error('Error deleting the course.', {
                                    timeOut: 5000
                                });
                            }
                        })
                        .catch(error => console.error('Error:', error));
                }
            }
        });
    </script>
    {{-- <script>
        // dataTableOptions.serverSide = true
        // dataTableOptions.processing = true
        // dataTableOptions.ajax = '{!! $url !!}';
        // dataTableOptions.columns = [{
        //         data: 'DT_RowIndex',
        //         name: 'id'
        //     },
        //     {
        //         data: 'type',
        //         name: 'type'
        //     },

        //     {
        //         data: 'title',
        //         name: 'title'
        //     },
        //     {
        //         data: 'mode_of_delivery',
        //         name: 'mode_of_delivery'
        //     },
        //     {
        //         data: 'category',
        //         name: 'category.name'
        //     },

        //     {
        //         data: 'user',
        //         name: 'user.name'
        //     },

        //     {
        //         data: 'lessons',
        //         name: 'lessons'
        //     },
        //     {
        //         data: 'enrolled_users',
        //         name: 'enrolled_users'
        //     },

        //     {
        //         data: 'price',
        //         name: 'price'
        //     },
        //     {
        //         data: 'scope',
        //         name: 'scope'
        //     },
        //     {
        //         data: 'status',
        //         name: 'search_status',
        //         orderable: false,
        //         searchable: false
        //     },
        //     {
        //         data: 'action',
        //         name: 'action',
        //         orderable: false
        //     },

        // ];

        // let table = $('.classList').DataTable(dataTableOptions);
    </script> --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.status_enable_disable');

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const id = this.value;
                    const status = this.checked ? 1 : 0;
                    const route = "{{ route('category.status_update') }}";

                    fetch(route, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id,
                                status
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.message === 'success') {
                                toastr.success('Status updated successfully!');
                            } else {
                                toastr.error('Failed to update status. Please try again.');
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
    <script>
        function confirm_modal(deleteUrl) {
            if (confirm("Are you sure you want to delete this category?")) {
                fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    // .then(data => {
                    //     if (data.success) {
                    //         alert(data.message);
                    //         location.reload(); // Reload the page to reflect changes

                    //     } else {
                    //         alert(data.message);
                    //     }
                    // })
                    .then(data => {
                        if (data.success) {
                            toastr.success(data.message);
                            setTimeout(() => location.reload(),
                                2000); // Optional: delay reload for a smoother experience
                        } else {
                            toastr.error(data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }
    </script>
@endsection
