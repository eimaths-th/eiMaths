@extends('layouts.app')

@section('style')
    @include('histories.style')
@endsection

@section('content')

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    {{-- <h4>Users & Roles Management</h4> --}}
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('navigation.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('students.titles.histories') }}</li>
                    </ol>

                </div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-6">
                    {{-- <h4>Users & Roles Management</h4> --}}
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        @can('student-create')
                            <button type="button" class="btn btn-success" id="CreateButton">
                                <i class="fas fa-graduation-cap"></i>{{ __('students.actions.create_history') }}</button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="{{ __('common.feedback.permission_denied') }}">
                                <button type="button" class="btn btn-success disabled">
                                    <i class="fas fa-graduation-cap"></i>{{ __('students.actions.create_history') }}</button>
                            </span>
                        @endcan &nbsp;

                        @can('student-delete')
                            <button type="button" class="btn btn-danger delete_all_button"><i class="fa fa-trash"></i> {{ __('common.actions.delete_all') }}</button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="{{ __('common.feedback.permission_denied') }}">
                                <button type="button" class="btn btn-danger disabled"><i class="fa fa-trash"></i>
                                    {{ __('common.actions.delete_all') }}</button>
                            </span>
                        @endcan
                    </ol>

                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="overlay" id="overlay">
                {{-- <i class="fas fa-2x fa-sync-alt fa-spin"></i> --}}
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-graduation-cap"></i> {{ __('students.titles.histories') }}</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                    <i class="fas fa-expand"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="{{ __('common.actions.close') }}">
                                    <i class="fas fa-minus"></i>
                                </button>
                                {{-- <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button> --}}
                            </div>
                        </div>

                        <div class="card-body">
                            @if ($message = Session::get('success'))
                                {{--  <div class="alert alert-success">
                                    <p>{{ $message }}</p>
                                </div> --}}
                                <script>
                                    toastr.success('{{ $message }}', {
                                        timeOut: 5000
                                    });
                                </script>
                            @endif
                            <div class="row ">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="row float-lg-left">
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <strong>
                                                    {{ __('students.filters.start_date') }}:</strong>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="far fa-calendar-alt"></i>
                                                        </span>
                                                    </div>
                                                    <input type="text" class="form-control float-right" id="reservation">
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="row float-lg-right">

                                        <div class="col-xs-4 col-sm-4 col-md-4">
                                            <div class="form-group">
                                                <strong><i class="fa-solid fa-clipboard-question"></i>
                                                    {{ __('students.filters.search_type') }}:</strong>
                                                <select style="width: 100%;" class="form-control" id="search_type"
                                                    name="search_type">
                                                    <option value="">{{ __('students.filters.please_select') }}</option>
                                                    <option value="1">{{ __('common.fields.student_code') }}</option>
                                                    <option value="2">{{ __('common.fields.student_name') }}</option>

                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4">
                                            <div class="form-group">
                                                <strong><i class="fa-regular fa-keyboard"></i> {{ __('common.fields.keyword') }}:</strong>
                                                {!! Form::text('keyword', null, ['id' => 'keyword', 'placeholder' => __('common.fields.keyword'), 'class' => 'form-control']) !!}
                                            </div>
                                        </div>
                                        <div class="col-xs-2 col-sm-2 col-md-2">
                                            <div class="form-group">
                                                <strong>&nbsp;</strong>
                                                <button type="button" class="form-control btn bg-gradient-success"
                                                    id="SearchButtons">
                                                    <i class="fas fa-search"></i></button>

                                            </div>
                                        </div>
                                        <div class="col-xs-2 col-sm-2 col-md-2" style="align-items: flex-end;">
                                            <div class="form-group">
                                                <strong>&nbsp;</strong>

                                                <button type="button" class="form-control btn bg-gradient-warning"
                                                    id="resetSearchButton"><i class="fa-solid fa-rotate"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row ">
                                <div class="col-xs-12 col-sm-12 col-md-12" id="Listviewd">
                                    <form method="post" action="{{ route('histories.destroy_all') }}" name="delete_all"
                                        id="delete_all">
                                        @csrf
                                        @method('POST')
                                        <table id="Listview"
                                            class="display nowrap table table-bordered table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="5%">
                                                        <input type="checkbox" id="check-all" class="flat">
                                                    </th>
                                                    <th>{{ __('common.fields.date') }} </th>
                                                    <th>{{ __('students.history.start_end_time') }} </th>
                                                    <th width="1%" class="text-center">{{ __('common.fields.centre') }}</th>
                                                    <th width="1%" class="text-center">{{ __('common.fields.student_code') }}</th>
                                                    <th>{{ __('common.fields.student_name') }}</th>
                                                    <th width="1%">{{ __('common.fields.level') }}</th>
                                                    <th width="1%">{{ __('common.fields.term') }}</th>
                                                    <th>{{ __('common.fields.book') }} </th>



                                                    {{-- <th width="1%">Remain</th> --}}
                                                    <th>{{ __('students.history.course_start_end_date') }}</th>
                                                    {{-- <th></th> --}}

                                                    {{-- <th>Start Term</th> --}}
                                                    <th>{{ __('students.history.comment') }}</th>

                                                    <th class="none" width="180px"></th>
                                                    <th>{{ __('students.history.more') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

    @include('histories.create')

    @include('histories.view')
    @include('histories.edit')
    @include('histories.log')


    {{--  {!! $data->render() !!} --}}
@endsection

@section('script')
    @include('histories.script')
@endsection
