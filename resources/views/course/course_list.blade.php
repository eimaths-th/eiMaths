{{-- @extends('backend.master') --}}
@extends('layouts.app')
@section('style')
    {{-- @include('affiliate.backend.my_affiliate-head') --}}
    {{-- <link rel="stylesheet" href="{{ asset('backend/css/backend_style.css') }}{{ assetVersion() }}" /> --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

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
    {{-- <style>
        .CRM_dropdown.dropdown .dropdown-toggle {
            background: transparent;
            color: var(--theme-default-color);
            font-size: 12px;
            font-weight: 500;
            border: 1px solid var(--backend-primary-color);
            border-radius: 32px;
            padding: 2px 40px 5px 23px;
            text-transform: uppercase;
            overflow: hidden;
            transition: .3s;
            height: 32px
        }

        @media only screen and (min-width:992px) and (max-width:1400px) {
            .CRM_dropdown.dropdown .dropdown-toggle {
                padding: 2px 30px 5px 23px
            }
        }

        .CRM_dropdown.dropdown .dropdown-toggle:after {
            content: "\E62A";
            font-family: themify;
            border: none;
            border-top: 0;
            font-size: 12px;
            position: relative;
            top: 3px;
            left: 0;
            font-weight: 400;
            transition: .3s
        }

        .CRM_dropdown.dropdown .dropdown-toggle:focus,
        .CRM_dropdown.dropdown .dropdown-toggle:hover {
            color: #fff;
            border: 1px solid transparent;
            box-shadow: none !important
        }

        .CRM_dropdown.dropdown .dropdown-menu {
            border-radius: 5px 5px 10px 10px;
            border: 0;
            padding: 15px 0;
            box-shadow: var(--selector-shadow);
            margin-top: 5px
        }

        .CRM_dropdown.dropdown .dropdown-menu.full_width {
            min-width: 110px
        }

        .CRM_dropdown.dropdown .dropdown-menu .dropdown-item {
            color: var(--dynamic-text-color);
            text-align: right;
            font-size: 12px;
            padding: 4px 1.5rem;
            text-transform: uppercase;
            cursor: pointer
        }

        .CRM_dropdown.dropdown .dropdown-menu .dropdown-item:hover {
            color: var(--theme-default-color)
        }

        .CRM_dropdown.dropdown .dropdown-menu .dropdown-item .active,
        .CRM_dropdown.dropdown .dropdown-menu .dropdown-item:active {
            color: var(--theme-default-color);
            background-color: #f8f9fa
        }

        .CRM_dropdown.dropdown.CRM_dropdown_default button {
            padding: 0 !important;
            border: 0 !important;
            text-transform: capitalize;
            font-size: 13px;
            text-align: left;
            overflow: visible
        }

        .CRM_dropdown.dropdown.CRM_dropdown_default button:hover {
            background: transparent;
            color: var(--theme-default-color)
        }

        .CRM_dropdown.dropdown.CRM_dropdown_default button:focus,
        .CRM_dropdown.dropdown.CRM_dropdown_default button:hover {
            border: 0;
            box-shadow: none !important;
            background: transparent;
            color: var(--theme-default-color)
        }

        .CRM_dropdown.dropdown.CRM_dropdown_default button:after {
            content: "\F0D7";
            font-family: Font Awesome\ 5 free;
            border: none;
            border-top: 0;
            font-size: 12px;
            position: relative;
            top: 3px;
            left: 0;
            font-weight: 900;
            transition: .3s;
            position: absolute;
            right: 0;
            left: auto;
            top: 4px
        }

        .CRM_dropdown.dropdown.CRM_dropdown_default .dropdown-item {
            text-transform: capitalize !important;
            text-align: left
        }

        .dropdown-menu.option_width_8 {
            min-width: 150px
        }
    </style>
    <style>
        .dropdown-toggle {
            white-space: nowrap;
        }

        .dropdown-toggle::after {
            display: inline-block;
            margin-left: 0.255em;
            vertical-align: 0.255em;
            content: "";
            border-top: 0.3em solid;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
        }

        .dropdown-toggle:empty::after {
            margin-left: 0;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            display: none;
            float: left;
            min-width: 10rem;
            padding: 0.5rem 0;
            margin: 0.125rem 0 0;
            font-size: 1rem;
            color: #212529;
            text-align: left;
            list-style: none;
            background-color: #ffbb01;
            background-clip: padding-box;
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 0.25rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.175);
        }

        .dropdown-menu-left {
            right: auto;
            left: 0;
        }

        .dropdown-menu-right {
            right: 0;
            left: auto;
        }
    </style> --}}
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

@php
    $table_name = 'courses';

    $category = request()->get('category');
    $type = request()->get('type');
    $instructor = request()->get('instructor');
    $status = request()->get('search_status');
    $search_required_type = request()->get('search_required_type');
    $search_delivery_mode = request()->get('search_delivery_mode');
    $url = '';
    // route('getAllCourseData') .
    // '?search_status=' .
    // $status .
    // '&category=' .
    // $category .
    // '&type=' .
    // $type .
    // '&instructor=' .
    // $instructor .
    // '&required_type=' .
    // $search_required_type .
    // '&mode_of_delivery=' .
    // $search_delivery_mode
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

                    </ol>

                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card card-primary">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">{{ __('courses.Advanced Filter') }} </h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('course.list') }}" method="GET">
                                <div class="row">

                                    <div class="col-lg-3 mt-20">

                                        <label class="primary_input_label"
                                            for="category">{{ __('courses.Category') }}</label>
                                        <select class="primary_select" name="category" id="category">
                                            <option data-display="{{ __('common.Select') }} {{ __('courses.Category') }}"
                                                value="">{{ __('common.Select') }} {{ __('courses.Category') }}
                                            </option>
                                            @foreach ($categories as $category)
                                                @if ($category->parent_id == 0)
                                                    @include('backend.categories._single_select_option', [
                                                        'category' => $category,
                                                        'level' => 1,
                                                    ])
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3 mt-20">

                                        <label class="primary_input_label" for="type">{{ __('courses.Type') }}</label>
                                        <select class="primary_select" name="type" id="type">
                                            <option data-display="{{ __('common.Select') }} {{ __('courses.Type') }}"
                                                value="">{{ __('common.Select') }} {{ __('courses.Type') }}</option>
                                            <option value="1"
                                                {{ isset($category_type) ? ($category_type == 1 ? 'selected' : '') : '' }}>
                                                {{ __('courses.Course') }}</option>
                                            <option value="2"
                                                {{ isset($category_type) ? ($category_type == 2 ? 'selected' : '') : '' }}>
                                                {{ __('quiz.Quiz') }}</option>
                                        </select>

                                    </div>
                                    <div class="col-lg-3 mt-20">

                                        <label class="primary_input_label"
                                            for="instructor">{{ __('courses.Instructor') }}</label>
                                        <select class="primary_select" name="instructor" id="instructor">
                                            <option
                                                data-display="{{ __('common.Select') }} {{ __('courses.Instructor') }}"
                                                value="">{{ __('common.Select') }} {{ __('courses.Instructor') }}
                                            </option>
                                            {{-- @foreach ($instructors as $instructor)
                                                <option value="{{ $instructor->id }}"
                                                    {{ isset($category_instructor) ? ($category_instructor == $instructor->id ? 'selected' : '') : '' }}>
                                                    {{ @$instructor->name }} </option>
                                            @endforeach --}}
                                        </select>

                                    </div>

                                    <div class="col-lg-3 mt-20">

                                        <label class="primary_input_label" for="status">{{ __('common.Status') }}</label>
                                        <select class="primary_select" name="search_status" id="status">
                                            <option data-display="{{ __('common.Select') }} {{ __('common.Status') }}"
                                                value="">{{ __('common.Select') }} {{ __('common.Status') }}
                                            </option>
                                            <option value="Active"
                                                {{ isset($category_status) ? ($category_status == 'Active' ? 'selected' : '') : 'selected' }}>
                                                {{ __('courses.Active') }} </option>
                                            <option value="Inactive"
                                                {{ isset($category_status) ? ($category_status == 'Inactive' ? 'selected' : '') : '' }}>
                                                {{ __('common.Inactive') }} </option>
                                        </select>

                                    </div>
                                    {{-- @if (isModuleActive('Org')) --}}
                                    {{-- <div class="col-lg-3 mt-20">
                                        <label class="primary_input_label"
                                            for="search_required_type">{{ __('courses.Required Type') }}</label>
                                        <select class="primary_select" name="search_required_type"
                                            id="search_required_type">
                                            <option
                                                data-display="{{ __('common.Select') }} {{ __('courses.Required Type') }}"
                                                value="">{{ __('common.Select') }} {{ __('courses.Required Type') }}
                                            </option>
                                            <option value="Compulsory"
                                                {{ isset($search_required_type) ? ($search_required_type == 'Compulsory' ? 'selected' : '') : '' }}>
                                                {{ __('courses.Compulsory') }} </option>
                                            <option value="Open"
                                                {{ isset($search_required_type) ? ($search_required_type == 'Open' ? 'selected' : '') : '' }}>
                                                {{ __('courses.Open') }}</option>
                                        </select>

                                    </div>

                                    <div class="col-lg-3 mt-20">

                                        <label class="primary_input_label"
                                            for="status">{{ __('courses.Delivery Mode') }}</label>
                                        <select class="primary_select" name="search_delivery_mode" id="status">
                                            <option
                                                data-display="{{ __('common.Select') }} {{ __('courses.Delivery Mode') }}"
                                                value="">{{ __('common.Select') }} {{ __('courses.Delivery Mode') }}
                                            </option>
                                            <option value="1"
                                                {{ isset($search_delivery_mode) ? ($search_delivery_mode == '1' ? 'selected' : '') : '' }}>
                                                {{ __('courses.Online') }} </option>
                                            <option value="3"
                                                {{ isset($search_delivery_mode) ? ($search_delivery_mode == '3' ? 'selected' : '') : '' }}>
                                                {{ __('courses.Offline') }}</option>
                                        </select>

                                    </div> --}}
                                    {{-- @endif --}}
                                    <div class="col-12 mt-2">
                                        <button type="submit" class="btn btn-primary radius_30px   fix-gr-bg">
                                            <span class="ti-search pe-2"></span>

                                            {{ __('courses.Filter') }} </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

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
                            <a class="btn btn-primary" href="#">
                                {{-- href="{{ route('course.store') }}"> --}}
                                <i class="fa fa-plus"></i> Add Course
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">

                                <div class="row">
                                    <div class="col-lg-12">
                                        <!-- table-responsive -->
                                        <div class="">
                                            <table id="lms_table" class="table classList">
                                                <thead>
                                                    <tr>
                                                        <th scope="col"> SL</th>
                                                        <th scope="col"> Type</th>
                                                        <th scope="col"> Required Type</th>
                                                        <th scope="col">CourseTitle</th>
                                                        <th scope="col">Delivery</th>
                                                        <th scope="col">Category</th>
                                                        <th scope="col">Instructor</th>
                                                        <th scope="col">Lesson</th>
                                                        <th scope="col">Enrolled</th>
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
    {{-- @include('backend.partials.delete_modal') --}}
@endsection
@section('script')
    {{-- <script src="{{ asset('backend/js/category.js') }}{{ assetVersion() }}"></script> --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> --}}

    {{-- @include('affiliate.backend.my_affiliate-script') --}}

    <script src="{{ asset('/Modules/CourseSetting/Resources/assets/js/course.js') }}"></script>


    <script>
        dataTableOptions.serverSide = true
        dataTableOptions.processing = true
        dataTableOptions.ajax = '{!! $url !!}';
        dataTableOptions.columns = [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'type',
                name: 'type'
            },
            @if (isModuleActive('Org'))
                {
                    data: 'required_type',
                    name: 'required_type'
                },
            @endif {
                data: 'title',
                name: 'title'
            },
            {
                data: 'mode_of_delivery',
                name: 'mode_of_delivery'
            },
            {
                data: 'category',
                name: 'category.name'
            },
            @if (!isModuleActive('Org'))
                {
                    data: 'quiz',
                    name: 'quiz.title'
                },
            @endif {
                data: 'user',
                name: 'user.name'
            },

            {
                data: 'lessons',
                name: 'lessons'
            },
            {
                data: 'enrolled_users',
                name: 'enrolled_users'
            },
            @if (showEcommerce())
                {
                    data: 'price',
                    name: 'price'
                },
            @endif {
                data: 'scope',
                name: 'scope'
            },
            {
                data: 'status',
                name: 'search_status',
                orderable: false,
                searchable: false
            },
            {
                data: 'action',
                name: 'action',
                orderable: false
            },

        ];

        let table = $('.classList').DataTable(dataTableOptions);
    </script>
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
