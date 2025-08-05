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
                            Export Excel </button>
                        &nbsp;
                        @can('student-create')
                            <button type="button" class="btn btn-success" id="CreateButton"><i
                                    class="fas fa-graduation-cap"></i>
                                New Student </button> &nbsp;
                            <button type="button" class="btn btn-success" id="CreateDemoButton"><i
                                    class="fas fa-graduation-cap"></i>
                                New Demo Student </button>
                            &nbsp;<button type="button" class="btn btn-success" id="ReceiptButton"><i
                                    class="fa-solid fa-file-invoice-dollar"></i>
                                New Receipt </button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="You Not Have Permission">
                                <button type="button" class="btn btn-info" id="export_button"><i
                                        class="fa-solid fa-file-export"></i> Export Excel </button>
                                <button type="button" class="btn btn-success disabled">
                                    <i class="fas fa-graduation-cap"></i> New Student </button>
                                &nbsp;<button type="button" class="btn btn-success" disabled><i
                                        class="fa-solid fa-file-invoice-dollar"></i>
                                    New Receipt </button>
                            </span>
                        @endcan &nbsp;

                        @can('student-delete')
                            <button type="button" class="btn btn-danger delete_all_button"><i class="fa fa-trash"></i>
                                Delete
                                All</button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="You Not Have Permission">
                                <button type="button" class="btn btn-danger disabled"><i class="fa fa-trash"></i>
                                    Delete All</button>
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
                            <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Student List</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                    <i class="fas fa-expand"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
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
                                                    Start Date Filter:</strong>
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
                                                    {{-- <option value="">Referal Code</option> --}}
                                                    <option value="2">Student Name</option>
                                                    <option value="7">Student Nick Name</option>
                                                    <option value="3">Father Name</option>
                                                    <option value="4">Mother Name</option>
                                                    <option value="5">Father Mobile</option>
                                                    <option value="6">Mother Mobile</option>
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
                                    <form method="post" action="{{ route('contacts.destroy_all') }}" name="delete_all"
                                        id="delete_all">
                                        @csrf
                                        @method('POST')
                                        <table id="Listview"
                                            class="display nowrap table table-bordered table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="90px"><input type="checkbox" id="check-all"
                                                            class="flat">
                                                    </th>
                                                    <th>Centre</th>
                                                    <th>Code</th>
                                                    <th>Referal</th>
                                                    <th>Student Name</th>
                                                    <th>Register Date</th>
                                                    <th>Start Date - End Date</th>
                                                    <th>Remaining</th>
                                                    <th>Level</th>
                                                    <th>Term</th>
                                                    <th>BookUse </th>
                                                    {{-- <th>Start Term</th> --}}
                                                    <th {{-- class="none" --}}>Telephone</th>
                                                    <th>Father Mobile</th>
                                                    <th>Mother Mobile</th>
                                                    <th class="none">Father Email</th>
                                                    <th class="none">Mother Email</th>
                                                    <th class="none">Father Name</th>
                                                    <th class="none">Mother Name</th>
                                                    <th class="none" width="180px"></th>
                                                    <th>More</th>
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

    @include('contacts.create')

    @include('contacts.create_demo')

    @include('contacts.edit')

    @include('contacts.receipt')

    {{--  {!! $data->render() !!} --}}
@endsection

@section('script')
    @include('contacts.script')
@endsection
