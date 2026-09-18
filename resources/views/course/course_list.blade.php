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
                    <h1>{{ __('course.courses.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <a class="btn btn-success" href="#" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                            <i class="fa fa-plus"></i> {{ __('course.courses.add_course') }}
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
                                    {{ __('course.courses.list_title') }}
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
                                                        <th scope="col">{{ __('course.courses.table.serial') }}</th>
                                                        <th scope="col">{{ __('course.courses.table.type') }}</th>
                                                        {{-- <th scope="col"> Required Type</th> --}}
                                                        <th scope="col">{{ __('course.courses.table.course_title') }}</th>
                                                        <th scope="col">{{ __('course.courses.table.delivery') }}</th>
                                                        <th scope="col">{{ __('course.courses.table.category') }}</th>
                                                        <th scope="col">{{ __('course.courses.table.instructor') }}</th>
                                                        {{-- <th scope="col">Lesson</th> --}}
                                                        {{-- <th scope="col">Enrolled</th> --}}
                                                        <th scope="col">{{ __('course.courses.table.price') }}</th>
                                                        <th scope="col">{{ __('course.courses.table.view_scope') }}</th>
                                                        <th scope="col">{{ __('course.courses.table.status') }}</th>
                                                        <th scope="col">{{ __('course.courses.table.action') }}</th>
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
                                <h4 class="modal-title">{{ __('course.courses.form.edit_title') }}</h4>
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
                                                            {{ __('course.courses.form.type') }}</label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="radio" class="common-radio type1" id="type_edit_1"
                                                            name="type" value="1">
                                                        <label for="type_edit_1">{{ __('course.courses.form.course') }}</label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="radio" class="common-radio type2" id="type_edit_2"
                                                            name="type" value="2">
                                                        <label for="type_edit_2">{{ __('course.courses.form.quiz') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-xl-6 dripCheck">
                                            <div class="primary_input mb-25">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label class="primary_input_label" for="    ">
                                                            {{ __('course.courses.form.drip_content') }}</label>
                                                    </div>

                                                    <div class="col-md-6">

                                                        <input type="radio" class="common-radio drip0" id="drip_edit_0"
                                                            name="drip" value="0"
                                                            {{ @$course->drip == 0 ? 'checked' : '' }}>
                                                        <label for="drip_edit_0">{{ __('course.courses.form.no') }}</label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="radio" class="common-radio drip1" id="drip_edit_1"
                                                            name="drip" value="1"
                                                            {{ @$course->drip == 1 ? 'checked' : '' }}>
                                                        <label for="drip_edit_1">{{ __('course.courses.form.yes') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label" for="title">{{ __('course.courses.form.title') }}
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
                                                    for="about">{{ __('course.courses.form.requirements') }} </label>
                                                <textarea class="lms_summernote" name="requirements" id="requirementsEdit" cols="30" rows="10"> </textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="primary_input mb-35">
                                                <label class="primary_input_label"
                                                    for="about">{{ __('course.courses.form.description') }}</label>
                                                <textarea class="lms_summernote" name="about" id="aboutEdit" cols="30" rows="10"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="primary_input mb-35">
                                                <label class="primary_input_label"
                                                    for="about">{{ __('course.courses.form.outcomes') }} </label>
                                                <textarea class="lms_summernote" name="outcomes" id="outcomesEdit" cols="30" rows="10"> </textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">

                                        <div class="col-xl-6 courseBox">
                                            <select class="primary_select edit_category_id" name="category"
                                                {{ $errors->has('category') ? 'autofocus' : '' }}>
                                                <option
                                                    data-display="{{ __('course.courses.form.select') }} {{ __('course.courses.form.category') }}"
                                                    value="">{{ __('course.courses.form.select') }} {{ __('course.courses.form.category') }}
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
                                                    data-display="{{ __('course.courses.form.select') }} {{ __('course.courses.form.sub_category') }}"
                                                    value="">{{ __('course.courses.form.select') }}
                                                    {{ __('course.courses.form.sub_category') }}

                                                </option>


                                            </select>
                                        </div>
                                        <div class="col-xl-6 mt-30 quizBox" style="display: none">
                                            <select class="primary_select" name="quiz" id="quiz_edit_id"
                                                {{ $errors->has('quiz') ? 'autofocus' : '' }}>
                                                <option data-display="{{ __('course.courses.form.select') }} {{ __('course.courses.form.quiz') }}"
                                                    value="">{{ __('course.courses.form.select') }} {{ __('course.courses.form.quiz') }}
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
                                                    data-display="{{ __('course.courses.form.select') }} {{ __('course.courses.form.level') }}"
                                                    value="">{{ __('course.courses.form.select') }} {{ __('course.courses.form.level') }}
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
                                                    data-display="{{ __('course.courses.form.select') }} {{ __('course.courses.form.language') }}"
                                                    value="">{{ __('course.courses.form.select') }}
                                                    {{ __('course.courses.form.language') }}
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
                                                    for="">{{ __('course.courses.form.duration') }}
                                                    ({{ __('course.courses.form.in_minute') }})
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
                                                <label>{{ __('course.courses.form.top_course') }}</label>
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
                                                <label>{{ __('course.courses.form.free_course') }}</label>
                                            </div>
                                        </div>
                                        <div class="col-xl-4" id="edit_price_div">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label"
                                                    for="">{{ __('course.courses.form.price') }}</label>
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
                                                <label>{{ __('course.courses.form.discounted_course') }}</label>
                                            </div>
                                        </div>

                                        <div class="col-xl-4" id="edit_discount_price_div">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label"
                                                    for="">{{ __('course.courses.form.discount') }}
                                                    {{ __('course.courses.form.price') }}</label>
                                                <input class="primary_input_field editDiscount" name="discount_price"
                                                    id="editDiscountPrice" placeholder="-" type="text">
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row mt-20 videoOption">
                                        <div class="col-xl-4 mt-25">
                                            <select class="primary_select category_id " name="host"
                                                id="editCourseHost">
                                                <option data-display="{{ __('course.courses.form.course_overview_host') }} *"
                                                    value="">{{ __('course.courses.form.course_overview_host') }}
                                                </option>

                                                <option value="Youtube">
                                                    {{ __('course.courses.form.youtube') }}
                                                </option>
                                                <option value="Vimeo">
                                                    {{ __('course.courses.form.vimeo') }}
                                                </option>
                                                @if (isModuleActive('AmazonS3'))
                                                    <option value="AmazonS3">
                                                        {{ __('course.courses.form.amazon_s3') }}
                                                    </option>
                                                @endif

                                                <option value="Self">
                                                    {{ __('course.courses.form.self_host') }}
                                                </option>


                                            </select>
                                        </div>
                                        <div class="col-xl-8 ">
                                            <div class="input-effect videoUrl"
                                                style="display:@if ((isset($course) && @$course->host != 'Youtube') || !isset($course)) none @endif">
                                                <label>{{ __('course.courses.form.video_url') }}
                                                    <span class="required_mark">*</span></label>
                                                <input id="couseEditViewUrl"
                                                    class="primary_input_field youtubeVideo name{{ $errors->has('trailer_link') ? ' is-invalid' : '' }}"
                                                    type="text" name="trailer_link"
                                                    placeholder="{{ __('course.courses.form.video_url') }}" autocomplete="off"
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
                                                        for="">{{ __('course.courses.form.vimeo_video') }}</label>
                                                    <select class="primary_select vimeoVideo" name="vimeo"
                                                        id="viemoEditCourse">
                                                        <option
                                                        data-display="{{ __('course.courses.form.select') }} {{ __('course.courses.form.video') }}"
                                                        value="">{{ __('course.courses.form.select') }}
                                                            {{ __('course.courses.form.video') }}
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
                                                            for="">{{ __('course.courses.form.video_file') }}</label>
                                                        <div class="primary_file_uploader">
                                                            {{-- <input
                                                             class="primary-input filePlaceholder"
                                                             type="text"

                                                             placeholder="{{ __('course.courses.form.browse_video_file') }}"
                                                             readonly="">
                                                         <button class="" type="button">
                                                             <label
                                                                 class="primary-btn small fix-gr-bg"
                                                                 for="document_file_edit">{{ __('course.courses.form.browse') }}</label>
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
                                                    for="">{{ __('course.courses.form.course_thumbnail') }}
                                                    ({{ __('course.courses.form.max_image_size') }})
                                                    *</label>
                                                <div class="primary_file_uploader">
                                                    <input class="primary-input filePlaceholder" type="text"
                                                        id=""
                                                        placeholder="{{ __('course.courses.form.browse_image_file') }}"
                                                        readonly="" {{ $errors->has('image') ? 'autofocus' : '' }}>
                                                    <button class="" type="button">
                                                        <label class="primary-btn small fix-gr-bg"
                                                            for="document_file_1_edit_">{{ __('course.courses.form.browse') }}</label>
                                                        <input type="file" class="d-none fileUpload" name="image"
                                                            id="document_file_1_edit_">
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @if (\Illuminate\Support\Facades\Auth::user()->subscription_api_status == 1)
                                            <div class="col-xl-6">
                                                <label class="primary_input_label"
                                                    for="">{{ __('course.courses.form.subscription_list') }}
                                                </label>
                                                <select class="primary_select" id="subscriptionEdit"
                                                    name="subscription_list"
                                                    {{ $errors->has('subscription_list') ? 'autofocus' : '' }}>
                                                    <option
                                                        data-display="{{ __('course.courses.form.select') }} {{ __('course.courses.form.subscription_list') }}"
                                                        value="">{{ __('course.courses.form.select') }}
                                                        {{ __('course.courses.form.subscription_list') }}

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
                                                    for="">{{ __('course.courses.form.meta_keywords') }}</label>
                                                <input class="primary_input_field" name="meta_keywords" id="editMetaKey"
                                                    placeholder="-" type="text">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label"
                                                    for="">{{ __('course.courses.form.meta_description') }}</label>
                                                <textarea id="editMetaDetails" class="primary_input_field" name="meta_description" style="height: 200px"
                                                    rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 text-center pt_15">
                                        <div class="d-flex justify-content-center">
                                            <button class="primary-btn semi_large2  fix-gr-bg" id="save_button_parent"
                                                type="submit"><i class="ti-check"></i> {{ __('course.courses.form.update_course') }}
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

    <script>
        const course_ui = {
            datatable: @json(__('common.datatable')),
            messages: @json(__('course.courses.messages')),
        };
    </script>

    <script src="{{ asset('/Modules/CourseSetting/Resources/assets/js/course.js') }}"></script>


    <script>
        $(document).ready(function() {
            var table = $('#Listview').DataTable({
                ajax: '',
                serverSide: true,
                processing: true,
                language: {
                    loadingRecords: course_ui.datatable.loading_records,
                    processing: `<div class="spinner-border text-primary"></div>`,
                    "sProcessing": course_ui.datatable.processing,
                    "sLengthMenu": course_ui.datatable.length_menu,
                    "sZeroRecords": course_ui.datatable.zero_records,
                    "sInfo": course_ui.datatable.info,
                    "sInfoEmpty": course_ui.datatable.info_empty,
                    "sInfoFiltered": course_ui.datatable.info_filtered,
                    "sInfoPostFix": course_ui.datatable.info_postfix,
                    "sSearch": course_ui.datatable.search,
                    "sUrl": course_ui.datatable.url,
                    "oPaginate": {
                        "sFirst": course_ui.datatable.first,
                        "sPrevious": course_ui.datatable.previous,
                        "sNext": course_ui.datatable.next,
                        "sLast": course_ui.datatable.last
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
                        name: 'category.name',
                        orderable: false,
                        // searchable: false
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
                        toastr.success(course_ui.messages.status_updated);
                    } else {
                        toastr.error(course_ui.messages.status_update_failed);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    toastr.error(course_ui.messages.status_update_error);
                },
            });
        });
    </script>
    <script>
        function courseTranslatedValue(value) {
            if (!value) return '';
            if (typeof value === 'string') return value;
            return value.en || Object.values(value)[0] || '';
        }

        async function courseFetchJson(response) {
            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                const validationMessages = data.errors ? Object.values(data.errors).flat().join(' ') : '';
                throw new Error(
                    validationMessages || data.message || course_ui.messages.http_error.replace(':status', response.status)
                );
            }

            return data;
        }

        function previewCourseImage(input, preview, missingWarning = null) {
            const file = input.files && input.files[0];
            if (!file) return;

            const allowedTypes = ['image/jpeg', 'image/png'];
            const allowedName = /\.(?:jpe?g|png)$/i.test(file.name);
            if ((!allowedTypes.includes(file.type) && !allowedName) || file.size > 2 * 1024 * 1024) {
                input.value = '';
                toastr.error(course_ui.messages.image_type_error);
                return;
            }

            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
            if (missingWarning) missingWarning.classList.add('d-none');
        }

        document.getElementById('courseImage').addEventListener('change', function() {
            previewCourseImage(this, document.getElementById('newCourseImagePreview'));
        });

        document.getElementById('editCourseImage').addEventListener('change', function() {
            previewCourseImage(
                this,
                document.getElementById('courseImagePreview'),
                document.getElementById('courseImageMissingWarning')
            );
        });

        document.getElementById('addCourseForm').addEventListener('submit', async function(event) {
            event.preventDefault(); // Prevent the default form submission

            const formData = new FormData(this);
            const submitButton = document.querySelector('[form="addCourseForm"][type="submit"]');
            submitButton.disabled = true;

            try {
                const response = await fetch('{{ route('course.store') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await courseFetchJson(response);

                $('#Listview').DataTable().ajax.reload();
                toastr.success(data.message, { timeOut: 5000 });
                this.reset();
                document.getElementById('newCourseImagePreview').classList.add('d-none');
                bootstrap.Modal.getInstance(document.getElementById('addCourseModal'))?.hide();
            } catch (error) {
                toastr.error(error.message, { timeOut: 7000 });
            } finally {
                submitButton.disabled = false;
            }
        });
    </script>

    <script>
        document.addEventListener('click', async function(event) {
            const editButton = event.target.closest('.edit-btn');
            if (editButton) {
                const courseId = editButton.getAttribute('data-id');
                // console.log(courseId); // Should log the course ID
                // Remaining logic...

                const editUrl = "{{ route('course.edit', ['id' => ':id']) }}".replace(':id', courseId);

                try {
                    const response = await fetch(editUrl, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await courseFetchJson(response);

                    if (data.success) {
                            document.getElementById('editCourseForm').reset();
                            document.getElementById('editCourseId').value = data.course.id;
                            document.getElementById('editCourseTitle').value = data.display_title || courseTranslatedValue(data.course.title);
                            document.getElementById('editCategoryId').value = data.course.category_id;
                            // Add other fields as needed
                            // Populate mode of delivery (set the selected option based on course data)
                            document.getElementById('editDeliveryMode').value = data.course.mode_of_delivery;

                            // Populate course price
                            document.getElementById('editCoursePrice').value = data.course.price;

                            // Populate status (radio buttons)
                            if (Number(data.course.status) === 1) {
                                document.getElementById('editStatusActive').checked = true;
                                document.getElementById('editStatusInactive').checked = false;
                            } else {
                                document.getElementById('editStatusActive').checked = false;
                                document.getElementById('editStatusInactive').checked = true;
                            }

                            document.getElementById('editCourseAbout').value = courseTranslatedValue(data.course.about);
                            document.getElementById('editCourseOutcomes').value = data.course.affiliate_outcomes || '';
                            document.getElementById('editCourseRequirements').value = data.course.affiliate_requirements || '';

                            const imageElement = document.getElementById('courseImagePreview');
                            imageElement.src = data.image_url;
                            imageElement.classList.remove('d-none');
                            document.getElementById('courseImageMissingWarning').classList.toggle('d-none', data.image_exists);
                            document.getElementById('editCourseImage').value = '';

                            // Show the modal
                            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editCourseModal'));
                            modal.show();
                    }
                } catch (error) {
                    toastr.error(error.message, { timeOut: 7000 });
                }
            }
        });

        document.getElementById('editCourseForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            const courseId = document.getElementById('editCourseId').value;
            const updateUrl = "{{ route('course.update', ['id' => ':id']) }}".replace(':id', courseId);
            const formData = new FormData(this);
            formData.set('_method', 'PUT');
            const submitButton = document.querySelector('[form="editCourseForm"][type="submit"]');
            submitButton.disabled = true;

            try {
                const response = await fetch(updateUrl, {
                    method: 'POST', // Use POST and spoof PUT
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await courseFetchJson(response);

                $('#Listview').DataTable().ajax.reload(null, false);
                toastr.success(data.message, { timeOut: 5000 });
                bootstrap.Modal.getInstance(document.getElementById('editCourseModal'))?.hide();
            } catch (error) {
                toastr.error(error.message, { timeOut: 7000 });
            } finally {
                submitButton.disabled = false;
            }
        });

        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('delete-btn')) {

                // Use event.target to get the clicked element
                const courseId = event.target.getAttribute('data-id');
                const destroyUrl = "{{ route('course.destroy', ['id' => ':id']) }}".replace(':id', courseId);


                if (confirm(course_ui.messages.confirm_delete)) {
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
                                toastr.error(course_ui.messages.delete_error, {
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
                                toastr.success(course_ui.messages.status_updated);
                            } else {
                                toastr.error(course_ui.messages.status_update_failed);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
    <script>
        function confirm_modal(deleteUrl) {
            if (confirm(course_ui.messages.confirm_delete)) {
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
