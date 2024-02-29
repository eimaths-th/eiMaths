@extends('layouts.app')

@section('style')
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

                        @can('product-create')
                            <button type="button" class="btn btn-success" id="CreateButton">
                                <i class="fas fa-cubes"></i> Add Acceseries </button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="คุณไม่มีสิทธิ์ในส่วนนี้">
                                <button type="button" class="btn btn-success disabled">
                                    <i class="fas fa-cubes"></i> Add Acceseries</button>
                            </span>
                        @endcan &nbsp;

                        @can('product-delete')
                            <button type="button" class="btn btn-danger delete_all_button"><i class="fa fa-trash"></i> Delete
                                All</button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="คุณไม่มีสิทธิ์ในส่วนนี้">
                                <button type="button" class="btn btn-danger disabled"><i class="fa fa-trash"></i> Delete
                                    All</button>
                            </span>
                        @endcan
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
                            <h3 class="card-title"><i class="fas fa-cubes"></i> Education Acceseries</h3>
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
                            <form method="post" action="{{ route('products.destroy_all') }}" name="delete_all"
                                id="delete_all">
                                @csrf
                                @method('POST')
                                <table id="Listview" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="check-all" class="flat"></th>
                                            <th>Code</th>
                                            <th>Branch</th>
                                            <th>Name</th>
                                            <th>In Stock</th>
                                            <th>Unit</th>
                                            <th width="320px"></th>
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


    @include('products.create')

    @include('products.edit')

    @include('products.barcode')

    @include('products.qrcode')

    {{--  {!! $data->render() !!} --}}
@endsection

@section('script')
    @include('products.script')
@endsection
