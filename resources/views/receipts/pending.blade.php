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
                            <h3 class="card-title"><i class=" fa-solid fa-circle-pause"></i>  {{ __('receipts.pending.title') }}
                            </h3>
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
                            <div class="row ">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="row float-lg-left">
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <strong>
                                                    {{ __('receipts.pending.filter.date') }}</strong>
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
                                                    {{ __('receipts.pending.filter.search_type') }}</strong>
                                                <select style="width: 100%;" class="form-control" id="search_type"
                                                    name="search_type">
                                                    <option value="">{{ __('receipts.pending.filter.please_select') }}</option>
                                                    <option value="1">{{ __('receipts.pending.filter.student_code') }}</option>
                                                    <option value="2">{{ __('receipts.pending.filter.student_name') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4">
                                            <div class="form-group">
                                                <strong><i class="fa-regular fa-keyboard"></i> {{ __('receipts.pending.filter.keyword') }}</strong>
                                                {!! Form::text('keyword', null, ['id' => 'keyword', 'placeholder' => __('receipts.pending.filter.keyword_placeholder'), 'class' => 'form-control']) !!}
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
                                                    <th {{-- data-priority="6" --}}>{{ __('receipts.pending.table.centre') }}</th>
                                                    <th {{-- data-priority="2" --}}>{{ __('receipts.pending.table.date') }}</th>
                                                    <th>{{ __('receipts.pending.table.referent_code') }}</th>
                                                    <th {{-- data-priority="4" --}}>{{ __('receipts.pending.table.student_code') }}</th>
                                                    <th {{-- data-priority="5" --}}>{{ __('receipts.pending.table.student_name') }}</th>
                                                    <th>{{ __('receipts.pending.table.level') }}</th>
                                                    <th>{{ __('receipts.pending.table.bookuses') }}</th>
                                                    {{-- <th>Amount</th> --}}
                                                    {{-- <th>ราคาต่อหน่วย</th> --}}
                                                    <th>{{ __('receipts.pending.table.term_book_fee') }}</th>
                                                    {{-- <th>StartTerm</th> --}}
                                                    <th width="120px" {{-- data-priority="3" --}}></th>
                                                    <th>{{ __('receipts.pending.table.more') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th></th>
                                                    <th></th>
                                                    {{-- <th></th> --}}
                                                    <th></th>
                                                    <th></th>
                                                    {{-- <th></th> --}}
                                                    {{-- <th></th> --}}
                                                    <th align="right"></th>
                                                    <th align="right"></th>
                                                    <th align="right"></th>
                                                    <th style="text-align: right;">{{ __('receipts.pending.table.total') }}</th>
                                                    <th align="right"></th>
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

    @include('receipts.edit')

    @include('receipts.log')

    {{--  {!! $data->render() !!} --}}
@endsection

@section('script')
    <script>
        const receipt_pending_ui = {
            datatable: @json(__('common.datatable')),
            messages: @json(__('receipts.pending.messages')),
        };
        const receipt_upload_ui = @json(__('receipts.upload'));
    </script>
    @include('receipts.pscript')
    @include('receipts.dropzone')
@endsection
