@extends('layouts.app')

@section('style')
    @include('receipts.style')
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid">


            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4></h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <button type="button" class="btn btn-info export_button" id="export_button"><i
                                class="fa-solid fa-file-export"></i>
                            {{ __('common.actions.export_excel') }} </button>
                        &nbsp;
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="overlay" id="overlay">
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            @switch(request('type'))
                                @case(0)
                                    <h3 class="card-title"><i class="fa-solid fa-children"></i> {{ __('receipts.list.titles.new_student') }}</h3>
                                @break

                                @case(1)
                                    <h3 class="card-title"><i class="fa-solid fa-children"></i> {{ __('receipts.list.titles.term_student') }}</h3>
                                @break

                                @default
                                    <h3 class="card-title"><i class="fa-solid fa-children"></i> {{ __('receipts.list.titles.default') }}</h3>
                            @endswitch
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                    <i class="fas fa-expand"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="{{ __('common.actions.collapse') }}">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            @if ($message = Session::get('success'))
                                <script>
                                    toastr.success('{{ $message }}', {
                                        timeOut: 5000
                                    });
                                </script>
                            @endif
                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <script>
                                        toastr.error(@json($error, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE), {
                                            timeOut: 8000,
                                            closeButton: true,
                                            progressBar: true
                                        });
                                    </script>
                                @endforeach
                            @endif
                            <div class="row ">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="row float-lg-left">
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <strong>
                                                    {{ __('receipts.list.filter.date') }}</strong>
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
                                                    {{ __('receipts.list.filter.search_type') }}</strong>
                                                <select style="width: 100%;" class="form-control" id="search_type"
                                                    name="search_type">
                                                    <option value="">{{ __('receipts.list.filter.please_select') }}</option>
                                                    <option value="1">{{ __('receipts.list.filter.student_code') }}</option>
                                                    <option value="2">{{ __('receipts.list.filter.student_name') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4">
                                            <div class="form-group">
                                                <strong><i class="fa-regular fa-keyboard"></i> {{ __('receipts.list.filter.keyword') }}</strong>
                                                {!! Form::text('keyword', null, ['id' => 'keyword', 'placeholder' => __('receipts.list.filter.keyword_placeholder'), 'class' => 'form-control']) !!}
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
                                    <form method="post" action="{{ route('reciept.export') }}" name="delete_all"
                                        id="delete_all">
                                        @csrf
                                        @method('POST')
                                        <table id="Listview"
                                            class="display nowrap table table-bordered table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="5%"><input type="checkbox" id="check-all" class="flat">
                                                    </th>
                                                    <th>{{ __('receipts.list.table.centre') }}</th>
                                                    <th>{{ __('receipts.list.table.referent_code') }}</th>
                                                    <th>{{ __('receipts.list.table.student_code') }}</th>
                                                    <th>{{ __('receipts.list.table.student_name') }}</th>
                                                    <th>{{ __('receipts.list.table.receipt_number') }}</th>
                                                    <th>{{ __('receipts.list.table.payment_date') }}</th>
                                                    <th>{{ __('receipts.list.table.level') }}</th>
                                                    <th>{{ __('receipts.list.table.book_use') }}</th>
                                                    <th>{{ __('receipts.list.table.total_fee') }}</th>
                                                    {{--  <th>StartTerm</th> --}}
                                                    <th width="120px"></th>
                                                    <th>{{ __('receipts.list.table.more') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th align="right"></th>
                                                    <th align="right"></th>
                                                    <th align="right"></th>
                                                    <th align="right">{{ __('receipts.list.table.total') }}</th>
                                                    <th align="center"></th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
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
    @include('receipts.print')
    @include('receipts.edit_receipt')
@endsection

@section('script')
    <script>
        const receipt_list_ui = {
            datatable: @json(__('common.datatable')),
            messages: @json(__('receipts.list.messages')),
        };
        const receipt_upload_ui = @json(__('receipts.upload'));
    </script>
    @include('receipts.script')
    @include('receipts.dropzone')
@endsection
