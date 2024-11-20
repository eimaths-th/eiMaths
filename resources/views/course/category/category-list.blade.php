{{-- @extends('backend.master') --}}
@extends('layouts.app')
@section('style')
    {{-- @include('affiliate.backend.my_affiliate-head') --}}
    {{-- <link rel="stylesheet" href="{{ asset('backend/css/backend_style.css') }}{{ assetVersion() }}" /> --}}

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
                        <div class="card-header">
                            <div class="card-title">
                                <h3 class="card-title"><i class="fa fa-user"></i> Add New Category</h3>

                                {{-- @if (isset($edit))
                                    @if (permissionCheck('categories.store'))
                                        <a href="{{ route('course.category') }}"
                                            class="primary-btn small fix-gr-bg ml-4 d-flex justify-content-center align-items-center"
                                            style="line-height: 25px;" title="{{ __('courses.Add New') }}">+</a>
                                    @endif
                                @endif --}}
                            </div>
                        </div>

                        {{-- <div class="row pt-0">
                            @if (isModuleActive('FrontendMultiLang'))
                                <ul class="nav nav-tabs no-bottom-border  mt-sm-md-20 mb-10 ml-3" role="tablist">
                                    @foreach ($LanguageList as $key => $language)
                                        <li class="nav-item">
                                            <a class="nav-link  @if (auth()->user()->language_code == $language->code) active @endif"
                                                href="#element{{ $language->code }}" role="tab"
                                                data-bs-toggle="tab">{{ $language->native }} </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div> --}}

                        {{-- <form action="{{ route('course.category.update') }}" method="POST" id="category-form"  name="category-form" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="{{ $edit->id }}"> --}}
                        @if ($message = Session::get('success'))
                            <script>
                                toastr.success('{{ $message }}', {
                                    timeOut: 5000
                                });
                            </script>
                        @endif
                        <form action="{{ route('categories.store') }}" method="POST" id="category-form"
                            name="category-form" enctype="multipart/form-data">
                            @csrf

                            <div class="card-body">
                                <div class="tab-content">
                                    @foreach ($LanguageList as $key => $language)
                                        <div role="tabpanel"
                                            class="tab-pane fade @if (auth()->user()->language_code == $language->code) show active @endif"
                                            id="element{{ $language->code }}">

                                            @php
                                                $fields = [
                                                    'name' => __('Name'),
                                                    'description' => __('Description'),
                                                ];
                                            @endphp

                                            @foreach ($fields as $fieldKey => $fieldLabel)
                                                <div class="row">
                                                    <div class="col-xl-12">
                                                        <div class="primary_input mb-3">
                                                            <label class="primary_input_label"
                                                                for="{{ $fieldKey }}Input">
                                                                {{ $fieldLabel }}
                                                                @if ($fieldKey === 'name')
                                                                    <strong class="text-danger">*</strong>
                                                                @endif
                                                            </label>
                                                            <input name="{{ $fieldKey }}[{{ $language->code }}]"
                                                                id="{{ $fieldKey }}Input"
                                                                class="primary_input_field {{ $fieldKey }} {{ $errors->has($fieldKey) ? 'is-invalid' : '' }}"
                                                                placeholder="{{ $fieldLabel }}" type="text"
                                                                value="{{ isset($edit) ? $edit->getTranslation($fieldKey, $language->code) : old($fieldKey . '.' . $language->code) }}"
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
                                    @endforeach
                                </div>

                                <div class="row">

                                    <div class="col-xl-12 ">
                                        <div class="primary_input mb-3">
                                            <label class="primary_input_label"
                                                for="parent">{{ __('common.Parent') }}</label>
                                            <select class="primary_select mb-25" name="parent" id="parent">
                                                <option value="">{{ __('common.None') }}</option>
                                                @foreach ($categories as $category)
                                                    @if (isset($edit) && $category->id == $edit->id)
                                                        @php
                                                            continue;
                                                        @endphp
                                                    @endif
                                                    <option value="{{ $category->id }}"
                                                        {{ isset($edit) ? ($edit->parent_id == $category->id ? 'selected' : old('parent')) : old('parent') }}>
                                                        {{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-xl-12">
                                        <div class="primary_input mb-3">
                                            <label class="primary_input_label"
                                                for="position_order">{{ __('courses.Position Order') }}</label>
                                            <select class="primary_select mb-25" name="position_order" id="position_order">
                                                @for ($i = 1; $i <= $max_id; $i++)
                                                    <option value="{{ $i }}"
                                                        {{ isset($edit) ? ($edit->position_order == $i ? 'selected' : old('position_order')) : old('position_order') }}>
                                                        {{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-xl-12">
                                        <div class="primary_input mb-3">
                                            <label class="primary_input_label"
                                                for="status">{{ __('courses.Status') }}</label>
                                            <select class="primary_select mb-25" name="status" id="status">
                                                <option value="1"
                                                    {{ isset($edit) ? ($edit->status == 1 ? 'selected' : '') : '' }}>
                                                    {{ __('common.Active') }}</option>
                                                <option value="0"
                                                    {{ isset($edit) ? ($edit->status == 0 ? 'selected' : '') : '' }}>
                                                    {{ __('common.Inactive') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 mt-10">
                                        <div class="mb-15">
                                            <x-upload-file name="image" type="image"
                                                media_id="{{ isset($edit) ? $edit->image_media?->media_id : '' }}"
                                                label="{{ __('frontendmanage.Icon') }}"
                                                note="{{ __('courses.Recommended size 200px x 200px') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="mb-15">
                                            <x-upload-file type="image" name="thumbnail"
                                                media_id="{{ isset($edit) ? $edit->thumbnail_media?->media_id : '' }}"
                                                label="{{ __('courses.Thumbnail Image') }} "
                                                note="{{ __('courses.Recommended size 1140px x 300px') }}" />
                                        </div>
                                        {{--                                                    <div class="primary_input mb-15"> --}}
                                        {{--                                                        <label class="primary_input_label" --}}
                                        {{--                                                               for="">{{ __('courses.Thumbnail Image') }}  </label> --}}
                                        {{--                                                        <div class="primary_file_uploader"> --}}
                                        {{--                                                            <input class="primary-input filePlaceholder courseLabel2" --}}
                                        {{--                                                                   type="text" --}}
                                        {{--                                                                   id=" " --}}
                                        {{--                                                                   value="{{isset($edit)?$edit->thumbnail:''}}" --}}
                                        {{--                                                                   placeholder="{{__('setting.Browse file')}}" --}}
                                        {{--                                                                   readonly="" {{$errors->has('thumbnail') ? 'autofocus' : ''}}> --}}
                                        {{--                                                            <div class="multi_btn"> --}}
                                        {{--                                                                <button class="" type="button"> --}}
                                        {{--                                                                    <label class="primary-btn small fix-gr-bg" --}}
                                        {{--                                                                           for="document_file_2">{{ __('common.Browse') }}</label> --}}
                                        {{--                                                                    <input type="file" --}}
                                        {{--                                                                           class="d-none fileUpload courseLabel2" --}}
                                        {{--                                                                           name="thumbnail" --}}
                                        {{--                                                                           id="document_file_2"> --}}
                                        {{--                                                                </button> --}}

                                        {{--                                                                <button type="button" data-table="categories" --}}
                                        {{--                                                                        data-name="thumbnail" --}}
                                        {{--                                                                        data-id="{{isset($edit)?$edit->id:''}}" --}}
                                        {{--                                                                        data-label=".courseLabel2" --}}
                                        {{--                                                                        class="primary-btn small fix-gr-bg px-3 removeImage"> --}}
                                        {{--                                                                    x --}}
                                        {{--                                                                </button> --}}
                                        {{--                                                            </div> --}}
                                        {{--                                                        </div> --}}
                                        {{--                                                        <p class="image_size">{{__('courses.Recommended size 1140px x 300px')}}</p> --}}
                                        {{--                                                    </div> --}}

                                    </div>
                                    @php
                                        $tooltip = '';
                                        if (permissionCheck('course.category.store')) {
                                            $tooltip = '';
                                        } else {
                                            $tooltip = trans('courses.You have no permission to add');
                                        }
                                    @endphp
                                    <div class="col-lg-12 text-center">
                                        <div class="d-flex justify-content-center pt_20">
                                            <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip"
                                                title="{{ @$tooltip }}" id="save_button_parent">
                                                <i class=" fa fa-check "></i>
                                                @if (!isset($edit))
                                                    {{ __('common.Save') }}
                                                @else
                                                    {{ __('common.Update') }}
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
                                            <th scope="col">Name</th>
                                            <th scope="col">Parent</th>
                                            <th scope="col">Position ID</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Icon</th>
                                            <th scope="col">Thumbnail Image</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($categories as $key => $category)
                                            <tr>
                                                <td>{{ ++$key }}</td>
                                                <td> {{ checkParent($category) }} {{ @$category->name }}</td>
                                                <td>{{ @$category->parent->name }}</td>
                                                <td>{{ @$category->position_order }}</td>
                                                <td>{{ @$category->description }}</td>
                                                <td>
                                                    <div>
                                                        <img style="width: 70px !important;"
                                                            src="@if (isset($category->image)) {{ url(@$category->image) }} @endif"
                                                            alt="" class="img img-responsive m-2">
                                                    </div>
                                                </td>

                                                <td>
                                                    <img src="@if (isset($category->thumbnail)) {{ url(@$category->thumbnail) }} @endif"
                                                        alt="" class="img img-responsive m-2"
                                                        style="width: 70px !important; ">
                                                </td>
                                                <td class="nowrap">
                                                    @php
                                                        if (isModuleActive('Organization')) {
                                                            $org_id = $category->organization_id;
                                                        } else {
                                                            $org_id = null;
                                                        }

                                                    @endphp
                                                    {{-- <x-status :org="$org_id" :id="$category->id" :status="$category->status"
                                                        :route="'course.category.status_update'"></x-status> --}}
                                                    <x-backend.status :org="$org_id" :id="$category->id" :status="$category->status"
                                                        :route="'category.status_update'" />
                                                </td>

                                                <td>
                                                    @php
                                                        $hasPermission = true;
                                                        $user = \Illuminate\Support\Facades\Auth::user();
                                                        if ($user->role_id == 2 && $user->id != $category->user_id) {
                                                            $hasPermission = false;
                                                        }
                                                    @endphp
                                                    <div class="dropdown CRM_dropdown">
                                                        <button class="btn btn-secondary dropdown-toggle" type="button"
                                                            id="dropdownMenu1{{ @$category->id }}"
                                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                            Select
                                                        </button>


                                                        <div class="dropdown-menu dropdown-menu-right"
                                                            aria-labelledby="dropdownMenu1{{ @$category->id }}">
                                                            {{-- @if (permissionCheck('course.category.edit') && orgPermission($category->organization_id) && $hasPermission) --}}
                                                            {{-- <a class="dropdown-item edit_brand"  href="{{ route('course.category.edit', $category->id) }}">{{ __('common.Edit') }}</a> --}}
                                                            <a class="dropdown-item edit_brand"
                                                                href="#">{{ __('common.Edit') }}</a>
                                                            {{-- @endif --}}
                                                            {{-- @if (permissionCheck('course.category.delete') && orgPermission($category->organization_id) && $hasPermission) --}}
                                                            {{-- <a onclick="confirm_modal('{{ route('course.category.delete', $category->id) }}');" class="dropdown-item edit_brand">{{ __('common.Delete') }}</a> --}}
                                                            <a onclick="confirm_modal('#');"
                                                                class="dropdown-item edit_brand">{{ __('common.Delete') }}</a>
                                                            {{-- @endif --}}
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
@endsection
@section('script')
    <script src="{{ asset('backend/js/category.js') }}{{ assetVersion() }}"></script>
    {{-- @include('affiliate.backend.my_affiliate-script') --}}
    <script>
       
        $(document).ready(function() {
            // $('#lms_table1').DataTable({
            //     processing: true, // Shows loading indicator
            //     serverSide: true, // Enable server-side processing
            //     // ajax: '',

            //     language: {
            //         loadingRecords: '&nbsp;',
            //         processing: `<div class="spinner-border text-primary"></div>`,
            //         sProcessing: "Processing...",
            //         sLengthMenu: "Display _MENU_ Rows",
            //         sZeroRecords: "No Data Found",
            //         sInfo: "Showing _START_ to _END_ of _TOTAL_ records",
            //         sInfoEmpty: "Showing 0 to 0 of 0 records",
            //         sInfoFiltered: "(filtered from _MAX_ total records)",
            //         sSearch: "Search:",
            //         oPaginate: {
            //             sFirst: "First",
            //             sPrevious: "Previous",
            //             sNext: "Next",
            //             sLast: "Last"
            //         }
            //     }
            // });
            $("#lms_table1").DataTable({
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                pageLength: 10, // Default number of rows per page
                order: [
                    [0, 'asc']
                ], // Default sorting (by first column)
                columnDefs: [{
                        orderable: false,
                        targets: [5, 6, 8]
                    } // Disable sorting for specific columns
                ],
                language: {
                    search: "Filter records:", // Customize search box text
                    lengthMenu: "Show _MENU_ entries per page"
                }
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

        });
    </script>
@endsection
