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
                                <i class="fa-solid fa-plus"></i> {{ __('products.actions.receive_stock') }}
                            </button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="{{ __('common.feedback.permission_denied') }}">
                                <button type="button" class="btn btn-success disabled">
                                    <i class="fa-solid fa-plus"></i> {{ __('products.actions.receive_stock') }}
                                </button>
                            </span>
                        @endcan &nbsp;

                        @can('product-edit')
                            <button type="button" class="btn btn-danger" id="RmStock">
                                <i class="fa-solid fa-minus"></i> {{ __('products.actions.issue_stock') }}
                            </button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="{{ __('common.feedback.permission_denied') }}">
                                <button type="button" class="btn btn-danger disabled">
                                    <i class="fa-solid fa-minus"></i> {{ __('products.actions.issue_stock') }}
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
                            <i class="fas fa-print"></i> {{ __('products.actions.print_products') }}
                        </button>

                        <!-- Print Options Modal -->
                        <div class="modal fade" id="printOptionsModal" tabindex="-1" role="dialog"
                            aria-labelledby="printOptionsModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="printOptionsModalLabel">{{ __('products.filters.print_options') }}</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal"
                                            aria-label="{{ __('common.actions.close') }}">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form id="printOptionsForm" action="{{ route('products.print') }}" method="GET">
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="centre">{{ __('products.filters.select_centre') }}</label>
                                                <select class="form-control" id="centre" name="centre" required>
                                                    @can('all-centre')
                                                        <option value="all">{{ __('products.filters.all_centres') }}</option>
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
                                                <label>{{ __('products.filters.select_code_type') }}</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="code_type"
                                                        id="barcode" value="barcode" checked>
                                                    <label class="form-check-label" for="barcode">
                                                        {{ __('products.actions.barcode') }}
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="code_type"
                                                        id="qrcode" value="qrcode">
                                                    <label class="form-check-label" for="qrcode">
                                                        {{ __('products.actions.qrcode') }}
                                                    </label>
                                                </div>
                                            </div>
                                            <input type="hidden" name="type" value="view">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">{{ __('common.actions.cancel') }}</button>
                                            <button type="submit" class="btn btn-primary">{{ __('products.actions.print') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        &nbsp;
                        @can('product-create')
                            <button type="button" class="btn btn-success" id="CreateButton">
                                <i class="fas fa-cubes"></i> {{ __('products.actions.add_accessory') }} </button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="{{ __('common.feedback.permission_denied') }}">
                                <button type="button" class="btn btn-success disabled">
                                    <i class="fas fa-cubes"></i> {{ __('products.actions.add_accessory') }}</button>
                            </span>
                        @endcan &nbsp;

                        @can('product-delete')
                            <button type="button" class="btn btn-danger delete_all_button"><i class="fa fa-trash"></i>
                                {{ __('products.actions.delete_all') }}</button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="{{ __('common.feedback.permission_denied') }}">
                                <button type="button" class="btn btn-danger disabled"><i class="fa fa-trash"></i>
                                    {{ __('products.actions.delete_all') }}</button>
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
                            <h3 class="card-title"><i class="fas fa-cubes"></i> {{ __('products.title') }}</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                    title="{{ __('common.actions.collapse') }}">
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
                                            <th>{{ __('products.table.code') }}</th>
                                            <th>{{ __('products.table.branch') }}</th>
                                            <th>{{ __('products.table.name') }}</th>
                                            <th>{{ __('products.table.in_stock') }}</th>
                                            <th>{{ __('products.table.unit') }}</th>
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
                    <h5 class="modal-title" id="userModalLabel"> <i class="fas fa-cubes"></i> {{ __('products.table.stock_history') }}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ __('common.actions.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table id="users-table" class="table">
                        <thead>
                            <tr>
                                <th>{{ __('products.table.transaction_date') }}</th>
                                <th>{{ __('products.table.source_centre') }}</th>
                                <th>{{ __('products.table.destination') }}</th>
                                <th>{{ __('products.table.name') }}</th>
                                <th>{{ __('products.table.accessory') }}</th>
                                <th>{{ __('products.table.received') }}</th>
                                <th>{{ __('products.table.issued') }}</th>
                                <th>{{ __('products.table.remaining') }}</th>
                                <th>{{ __('products.table.operator') }}</th>
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
    <script>
        const product_ui = @json(__('products'));
        const common_ui = @json(__('common'));
    </script>
    @include('products.script')
@endsection
