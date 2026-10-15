@extends('layouts.app')

@section('style')
    @include('contacts.style')
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
                        {{-- <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Users Management</li> --}}


                        <button type="button" class="btn btn-info export_button" id="export_button"><i
                                class="fa-solid fa-file-export"></i>
                            {{ __('common.actions.export_excel') }} </button>
                        &nbsp;
                        @can('student-create')
                            <button type="button" class="btn btn-success" id="CreateButton"><i
                                    class="fas fa-graduation-cap"></i>
                                {{ __('students.actions.new_student') }} </button> &nbsp;
                            &nbsp;<button type="button" class="btn btn-success" id="ReceiptButton"><i
                                    class="fa-solid fa-file-invoice-dollar"></i>
                                {{ __('students.actions.new_receipt') }} </button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="{{ __('common.feedback.permission_denied') }}">
                                <button type="button" class="btn btn-info" id="export_button"><i
                                        class="fa-solid fa-file-export"></i> {{ __('common.actions.export_excel') }} </button>
                                <button type="button" class="btn btn-success disabled">
                                    <i class="fas fa-graduation-cap"></i> {{ __('students.actions.new_student') }} </button>
                                &nbsp;<button type="button" class="btn btn-success" disabled><i
                                        class="fa-solid fa-file-invoice-dollar"></i>
                                    {{ __('students.actions.new_receipt') }} </button>
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
                            <h3 class="card-title"><i class="fas fa-graduation-cap"></i> {{ __('students.titles.list') }}</h3>
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
                            @if ($shipment_context)
                                @php
                                    $shipment_context_url = auth()->user()->isHq()
                                        ? route('book-shipments.show', ['book_shipment' => $shipment_context->id])
                                        : route('incoming-book-shipments.show', ['book_shipment' => $shipment_context->id]);
                                @endphp
                                <div class="alert alert-info" role="status">
                                    <strong>{{ __('students.shipment.shipment') }} {{ $shipment_context->shipment_number }}</strong>
                                    <span class="ml-2">
                                        {{ __('students.shipment.destination') }}
                                        {{ $shipment_context->destination_centre_name ?: ($shipment_context->DestinationCentre?->name ?: __('common.fields.centre')) }}
                                    </span>
                                    <a
                                        class="btn btn-sm btn-outline-primary ml-2"
                                        href="{{ $shipment_context_url }}"
                                    >
                                        {{ __('students.shipment.open') }}
                                    </a>
                                </div>
                            @endif

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
                                                    {{-- <option value="">Referal Code</option> --}}
                                                    <option value="2">{{ __('common.fields.student_name') }}</option>
                                                    <option value="7">{{ __('common.fields.nickname') }}</option>
                                                    <option value="3">{{ __('common.fields.father_name') }}</option>
                                                    <option value="4">{{ __('common.fields.mother_name') }}</option>
                                                    <option value="5">{{ __('common.fields.father_mobile') }}</option>
                                                    <option value="6">{{ __('common.fields.mother_mobile') }}</option>
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
                            <div class="row mt-2">
                                <div class="col-xs-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                    <strong><i class="fas fa-clipboard-check"></i> {{ __('students.filters.course_status') }}:</strong>
                                        <select id="renewal-status-filter" class="form-control">
                                            <option value="">{{ __('students.filters.all_students') }}</option>
                                            <option value="pending">{{ __('students.renewal.pending') }}</option>
                                            <option value="active">{{ __('students.renewal.active') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row ">
                                <div class="col-xs-12 col-sm-12 col-md-12" id="Listviewd">
                                    <form method="post" action="{{ route('contact.export') }}" name="export_students"
                                        id="export_students">
                                        @csrf
                                        @method('POST')
                                        <table id="Listview"
                                            class="display nowrap table table-bordered table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="90px"><input type="checkbox" id="check-all"
                                                            class="flat">
                                                    </th>
                                                    <th>{{ __('students.table.centre') }}</th>
                                                    <th>{{ __('students.table.code') }}</th>
                                                    <th>{{ __('students.table.referral') }}</th>
                                                    <th>{{ __('students.table.student_name') }}</th>
                                                    <th>{{ __('students.table.register_date') }}</th>
                                                    <th>{{ __('students.table.start_end_date') }}</th>
                                                    <th>{{ __('students.table.remaining') }}</th>
                                                    <th>{{ __('students.table.level') }}</th>
                                                    <th>{{ __('students.table.term') }}</th>
                                                    <th>{{ __('students.table.book_use') }} </th>
                                                    <th>{{ __('students.table.book_receipt') }}</th>
                                                    {{-- <th>Start Term</th> --}}
                                                    <th {{-- class="none" --}}>{{ __('students.table.telephone') }}</th>
                                                    <th>{{ __('students.table.father_mobile') }}</th>
                                                    <th>{{ __('students.table.mother_mobile') }}</th>
                                                    <th class="none">{{ __('students.table.father_email') }}</th>
                                                    <th class="none">{{ __('students.table.mother_email') }}</th>
                                                    <th class="none">{{ __('students.table.father_name') }}</th>
                                                    <th class="none">{{ __('students.table.mother_name') }}</th>
                                                    <th class="none" width="180px"></th>
                                                    <th>{{ __('students.table.more') }}</th>
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

    @include('contacts.course')

    @include('contacts.fast-history')

    @include('contacts.discontinue')

    @include('contacts.create')

    @include('contacts.create_demo')

    @include('contacts.edit')

    @include('contacts.receipt')

    @include('contacts.book-receipts.modal')

    {{--  {!! $data->render() !!} --}}
@endsection

@section('script')
    @include('contacts.script')
    @include('contacts.book-receipts.script')
@endsection
