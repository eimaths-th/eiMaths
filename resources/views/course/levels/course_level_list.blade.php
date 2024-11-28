a{{-- @extends('backend.master') --}}
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
    $table_name = 'categories';
@endphp
@section('table')
    {{ $table_name }}
@endsection
@section('content')
    {{-- @include('backend.partials.alertMessage') --}}
    @php
        $LanguageList = getLanguageList();
    @endphp
    {!! generateBreadcrumb() !!}

    <section class="content-header">
        <div class="container-fluid">


            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Category</h1>
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
            <div class="row">

                <div class="col-xl-3">
                    <div class="card card-primary">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">
                                <h3 class="card-title">

                                    @if (!isset($edit))
                                        Add New Level
                                    @else
                                        Update Level
                                    @endif
                                </h3>

                            </div>
                            @if (isset($edit))
                                <a class="" href="{{ route('categories.index') }}"
                                    title="{{ __('courses.Add New') }}">+</a>
                            @endif
                        </div>

                        @if (Session::has('toastr'))
                            <script>
                                {!! Session::get('toastr') !!}
                            </script>
                        @endif
                        {{-- @if ($message = Session::get('success'))
                            <script>
                                toastr.success('{{ $message }}', {
                                    timeOut: 5000
                                });
                            </script>
                        @endif --}}
                        @if (isset($edit))
                            <form action="{{ route('course.level.update') }}" method="POST" id="category-update-form"
                                name="category-form" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="{{ $edit->id }}">
                            @else
                                <form id="category-form" data-store-url="{{ route('course.level.store') }}"
                                    data-csrf-token="{{ csrf_token() }}">
                        @endif

                        @csrf

                        <div class="card-body">
                            <div class="tab-content">

                                @php
                                    $fields = [
                                        'title' => __('Name'),
                                    ];
                                @endphp

                                @foreach ($fields as $fieldKey => $fieldLabel)
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="primary_input mb-3">
                                                <label class="primary_input_label" for="{{ $fieldKey }}Input">
                                                    {{ $fieldLabel }}
                                                    @if ($fieldKey === 'name')
                                                        <strong class="text-danger">*</strong>
                                                    @endif
                                                </label>
                                                <input name="{{ $fieldKey }}[{{ auth()->user()->language_code }}]"
                                                    id="{{ $fieldKey }}Input" autocomplete="off"
                                                    class="primary_input_field text-dark {{ $fieldKey }} {{ $errors->has($fieldKey) ? 'is-invalid' : '' }}"
                                                    placeholder="{{ $fieldLabel }}" type="text"
                                                    value="{{ isset($edit) ? $edit->getTranslation($fieldKey, auth()->user()->language_code) : old($fieldKey . '.' . auth()->user()->language_code) }}"
                                                    @if ($errors->has($fieldKey)) autofocus @endif>

                                                @if ($errors->has($fieldKey))
                                                    <span class="invalid-feedback d-block mb-10" role="alert">
                                                        <strong>{{ $errors->first($fieldKey) }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                            </div>

                            <div class="row">
                                <div class="col-lg-12 text-center">
                                    <div class="d-flex justify-content-center pt_20">
                                        <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip"
                                            title="{{ @$tooltip }}" id="save_button_parent">
                                            <i class=" fa fa-check "></i>
                                            @if (!isset($edit))
                                                Save
                                            @else
                                                Update
                                            @endif
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        </form>
                    </div>
                </div>

                <div class="col-xl-9">
                    <div class="card card-primary">
                        <div class="card-header">

                            <div class="card-title">
                                <h3 class="card-title"><i class="fa fa-user"></i> Category List</h3>

                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="lms_table1" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">SL</th>
                                            <th scope="col">Title</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($levels as $key => $level)
                                            <tr>
                                                <th class="m-2">{{ $key + 1 }}</th>
                                                <td>{{ @$level->title }}</td>
                                                <td class="nowrap level_status" data-statue_value="{{ $level->status }}">

                                                    <x-backend.status :id="$level->id" :status="$level->status" :route="'course.level.update.status'">
                                                    </x-backend.status>
                                                </td>
                                                <td>
                                                    <div class="dropdown CRM_dropdown">
                                                        <button class="btn btn-secondary dropdown-toggle" type="button"
                                                            id="dropdownMenu1{{ @$level->id }}" data-bs-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false">
                                                            Select
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right"
                                                            aria-labelledby="dropdownMenu1{{ @$level->id }}">
                                                            <a class="dropdown-item edit_brand"
                                                                href="{{ route('course.level.edit', @$level->id) }}"
                                                                {{-- href="#" --}}>Edit</a>
                                                            <a onclick="confirm_modal('{{ route('course.level.destroy', @$level->id) }}');"
                                                                {{-- onclick="#" --}}
                                                                class="dropdown-item edit_brand">Delete</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <input type="hidden" name="status_route" class="status_route" value="#">
    {{-- <input type="hidden" name="status_route" class="status_route" value="{{ route('course.category.status_update') }}"> --}}
    @include('backend.partials.delete_modal')

    @if (Session::has('message'))
        <script type="text/javascript">
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
            };
            @if (Session::get('alert-type') == 'success')
                toastr.success("{{ Session::get('message') }}");
            @elseif (Session::get('alert-type') == 'error')
                toastr.error("{{ Session::get('message') }}");
            @elseif (Session::get('alert-type') == 'warning')
                toastr.warning("{{ Session::get('message') }}");
            @elseif (Session::get('alert-type') == 'info')
                toastr.info("{{ Session::get('message') }}");
            @endif
        </script>
    @endif
@endsection
@section('script')
    {{-- <script src="{{ asset('backend/js/category.js') }}{{ assetVersion() }}"></script> --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> --}}

    {{-- @include('affiliate.backend.my_affiliate-script') --}}
    <script>
        $(document).ready(function() {
            $("#lms_table1").DataTable({
                "pageLength": 10,
                "responsive": true,
                "lengthChange": true,
                "autoWidth": false,
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });
    </script>
    
    {{-- ceate data--}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('category-form');

            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent traditional form submission

                const storeUrl = form.getAttribute('data-store-url');
                const csrfToken = form.getAttribute('data-csrf-token');

                const formData = new FormData(form);
                const payload = {};

                // Convert formData to JSON
                formData.forEach((value, key) => {
                    const match = key.match(/(.+?)\[(.+?)\]/); // Handles keys like 'title[en]'
                    if (match) {
                        const baseKey = match[1];
                        const langKey = match[2];
                        payload[baseKey] = payload[baseKey] || {};
                        payload[baseKey][langKey] = value;
                    } else {
                        payload[key] = value;
                    }
                });

                fetch(storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toastr.success(data.message, 'Success');
                            form.reset(); // Reset the form if needed
                            location.reload()

                        } else {
                            toastr.error(data.message || 'An error occurred.', 'Error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        toastr.error('An unexpected error occurred.', 'Error');
                    });
            });
        });
    </script>

    {{-- update status --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.status_enable_disable');

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const id = this.value;
                    const status = this.checked ? 1 : 0;
                    const route = "{{ route('course.level.update_status') }}";

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

    {{-- update data --}}
    <script>
        document.getElementById('category-update-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const formData = new FormData(this);
            const updateUrl = this.action;
            const options = {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            };
            fetch(updateUrl, options)
                .then(response => response.json()) // Parse the JSON response
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        setTimeout(() => location.reload(),
                            2000);
                    } else {
                        toastr.error('Failed to update course level.', 'Error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('An error occurred while updating the course level.', 'Error');
                });
        });
    </script>

    {{-- delete --}}
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
