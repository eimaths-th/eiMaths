@extends('layouts.app')

@section('style')
    @include('orders.style')
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
                        {{-- <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Users Management</li> --}}
                        {{-- @can('order-create')
                            <button type="button" class="btn btn-success" id="CreateButton">
                                <i class="fa-solid fa-cart-shopping"></i> Create Order</button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="You Not Have Permission">
                                <button type="button" class="btn btn-success disabled">
                                    <i class="fa-solid fa-cart-shopping"></i> Create Order </button>
                            </span>
                        @endcan &nbsp;

                        @can('order-delete')
                            <button type="button" class="btn btn-danger delete_all_button"><i class="fa fa-trash"></i> Delete
                                All</button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="You Not Have Permission">
                                <button type="button" class="btn btn-danger disabled"><i class="fa fa-trash"></i>
                                    Delete All</button>
                            </span>
                        @endcan --}}
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
                            <h3 class="card-title"> <i class="fa-solid fa-cart-shopping"></i> New Student Orders</h3>
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
                            <form method="post" action="{{ route('orders.destroy_all') }}" name="delete_all"
                                id="delete_all">
                                @csrf
                                @method('POST')
                                <table id="Listview" class="display nowrap table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%"><input type="checkbox" id="check-all" class="flat"></th>
                                            <th>Centre</th>
                                            <th>Order Date</th>
                                            <th>Order Number</th>
                                            <th>Student Code</th>
                                            <th>Student Name</th>
                                            <th>Item</th>
                                            <th>Amount</th>
                                            {{-- <th>ราคาต่อหน่วย</th> --}}
                                            <th>Price</th>
                                            <th width="120px"></th>
                                            <th>More</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                    <tfoot >
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th align="right"></th>
                                            <th align="right">Total</th>
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

    </section>


    @include('orders.create')

    @include('orders.edit')

    @include('orders.print')

    @include('orders.log')

    {{--  {!! $data->render() !!} --}}
@endsection

@section('script')
    @include('orders.script')
    @include('orders.dropzone')
@endsection
