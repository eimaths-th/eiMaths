@extends('layouts.app')

@section('style')
    <style>
        .modal-xxl {
            max-width: 1300px !important;
        }
    </style>
@endsection

@section('content')
    {{-- {{ session('errors')->first('error1') }} --}}
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    {{--   <div class="row">
                        @foreach ($data as $rr)
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3 col-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-warehouse"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text text-primary">{{ $rr['name'] }}
                                           In Stock</span>
                                        <span class="info-box-number">{{ number_format($rr['amount'], 2) }}
                                            {{ $rr['unit'] }}</span>
                                    </div>

                                </div>

                            </div>
                        @endforeach

                    </div> --}}

                </div>
            </div>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        {{-- <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Users Management</li> --}}

                        @can('product-edit')
                            <button type="button" class="btn btn-success" id="AddStock">
                                <i class="fa-solid fa-plus"></i> รับเข้า Stock
                            </button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="คุณไม่มีสิทธิ์ในส่วนนี้">
                                <button type="button" class="btn btn-success disabled">
                                    <i class="fa-solid fa-plus"></i> รับเข้า Stock
                                </button>
                            </span>
                        @endcan &nbsp;

                        @can('product-edit')
                            <button type="button" class="btn btn-danger" id="RmStock">
                                <i class="fa-solid fa-minus"></i> จ่ายออก
                            </button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="คุณไม่มีสิทธิ์ในส่วนนี้">
                                <button type="button" class="btn btn-danger disabled">
                                    <i class="fa-solid fa-minus"></i> จ่ายออก
                                </button>
                            </span>
                        @endcan
                    </ol>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        {{-- <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Users Management</li> --}}

                        {{-- btn print product --}}
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#printOptionsModal">
                            <i class="fas fa-print"></i> Print Product
                        </button>

                        <!-- Print Options Modal -->
                        <div class="modal fade" id="printOptionsModal" tabindex="-1" role="dialog"
                            aria-labelledby="printOptionsModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="printOptionsModalLabel">Print Options</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal"
                                            aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form id="printOptionsForm" action="{{ route('products.print') }}" method="GET">
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="centre">Select Centre</label>
                                                <select class="form-control" id="centre" name="centre" required>
                                                    @can('all-centre')
                                                        <option value="all">All Centres</option>
                                                        @foreach ($centre as $dept)
                                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                                        @endforeach
                                                    @else
                                                        <option value="{{ Auth::user()->department->id }}" selected>
                                                            {{ Auth::user()->department->name }}</option>
                                                    @endcan
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Select Code Type</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="code_type"
                                                        id="barcode" value="barcode" checked>
                                                    <label class="form-check-label" for="barcode">
                                                        Barcode
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="code_type"
                                                        id="qrcode" value="qrcode">
                                                    <label class="form-check-label" for="qrcode">
                                                        QR Code
                                                    </label>
                                                </div>
                                            </div>
                                            <input type="hidden" name="type" value="view">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Print</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        &nbsp;
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
                            <button type="button" class="btn btn-danger delete_all_button"><i class="fa fa-trash"></i>
                                Delete
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
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                    title="Collapse">
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

    <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xxl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="userModalLabel"> <i class="fas fa-cubes"></i> Stock History</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table id="users-table" class="table">
                        <thead>
                            <tr>
                                <th>วันที่ทำรายการ</th>
                                <th>สาขาต้นทาง</th>
                                <th>ปลายทาง</th>
                                <th>ชื่อ</th>
                                <th>Accessories</th>
                                <th>รับเข้า</th>
                                <th>จ่ายออก</th>
                                <th>คงเหลือ</th>
                                <th>ผู้ทำรายการ</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('products.create')

    @include('products.edit')

    @include('products.barcode')

    @include('products.qrcode')

    @include('products.plus')

    @include('products.rm')

    {{--  {!! $data->render() !!} --}}
@endsection

@section('script')
    @include('products.script')
@endsection
