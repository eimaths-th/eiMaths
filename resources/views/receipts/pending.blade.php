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
                            <h3 class="card-title"><i class=" fa-solid fa-circle-pause"></i>  Pending Receipt
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                    <i class="fas fa-expand"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
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
                                                    Date Filter:</strong>
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
                                                    Search Type:</strong>
                                                <select style="width: 100%;" class="form-control" id="search_type"
                                                    name="search_type">
                                                    <option value="">Please Select</option>
                                                    <option value="1">Student Code</option>
                                                    <option value="2">Student Name</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4">
                                            <div class="form-group">
                                                <strong><i class="fa-regular fa-keyboard"></i> Keyword:</strong>
                                                {!! Form::text('keyword', null, ['id' => 'keyword', 'placeholder' => 'keyword', 'class' => 'form-control']) !!}
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
                                    <form method="post" action="{{ route('orders.destroy_all') }}" name="delete_all"
                                        id="delete_all">
                                        @csrf
                                        @method('POST')
                                        <table id="Listview"
                                            class="display nowrap table table-bordered table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="5%"><input type="checkbox" id="check-all" class="flat">
                                                    </th>
                                                    <th {{-- data-priority="6" --}}>Centre</th>
                                                    <th {{-- data-priority="2" --}}>Date</th>
                                                    <th>Referent Code</th>
                                                    <th {{-- data-priority="4" --}}>Student Code</th>
                                                    <th {{-- data-priority="5" --}}>Student Name</th>
                                                    <th>Level</th>
                                                    <th>Bookuses</th>
                                                    {{-- <th>Amount</th> --}}
                                                    {{-- <th>ราคาต่อหน่วย</th> --}}
                                                    <th>Term & Book Fee</th>
                                                    {{-- <th>StartTerm</th> --}}
                                                    <th width="120px" {{-- data-priority="3" --}}></th>
                                                    <th>More</th>
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
                                                    <th style="text-align: right;">Total</th>
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
    @include('receipts.pscript')
    @include('receipts.dropzone')
@endsection
