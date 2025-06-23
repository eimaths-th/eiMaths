@extends('layouts.app')

@section('style')
    @include('users.style')
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
                        <button id="approveSelectedBtn" class="btn btn-success ms-3 me-3">Approve All Selected</button>&nbsp;
                        <button id="rejectSelectedBtn" class="btn btn-danger">Reject All Selected</button>
                    </ol>

                </div>
            </div>
        </div>
    </section>

    <section class="content">


        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-user"></i> Affiliate Withdraw</h3>
                            <div class="card-tools">
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
                            <form>
                                @csrf
                                @method('POST')
                                <table id="Listview" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="check-all" class="flat"></th>
                                            <th>Date</th>
                                            <th>Affilirate Name</th>
                                            <th>Withdraw Amount</th>
                                            <th>Payment Type</th>
                                            <th>Account</th>
                                            <th>Status</th>
                                            <th>Confirmed By</th>
                                            <th>Confirm Date</th>
                                            <th>Rejected Date</th>
                                            <th>Rejected Date</th>
                                            <th width="120px">Action</th>
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

    </section>

    {{--  {!! $data->render() !!} --}}
    @include('config_withdraw.template_modal')
@endsection



@section('script')
    @include('config_withdraw.script')
@endsection
