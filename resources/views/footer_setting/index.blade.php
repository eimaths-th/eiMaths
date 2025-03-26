@extends('layouts.app')

@section('style')
    @include('users.style')
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Footer Setting Management</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        {{-- <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Users Management</li> --}}

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
                            <h3 class="card-title"><i class="fa fa-user"></i>Footer Setting Management</h3>
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
                            <form action="{{ route('footer-setting.createOrUpdate') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="mb-2">Title <span class="text-danger">*</span></label>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <input class="form-control" type="text" name="title" id="Title"
                                                        value="{{ $footer->title ?? '' }}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="mb-2">description <span class="text-danger">*</span></label>
                                            <input class="form-control" type="text" name="description" id="description"
                                                value="{{ $footer->description ?? '' }}" required>
                                        </div>

                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="mb-2">description 2<span class="text-danger">*</span></label>
                                            <input class="form-control" type="text" name="description2"
                                                id="description2" value="{{ $footer->description2 ?? '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        {{-- <div class="form-group">
                                            <label class="mb-2">promptpay_numbers<span
                                                    class="text-danger">*</span></label>
                                            <input class="form-control" type="text" name="promptpay_numbers"
                                                id="promptpay_numbers" value="{{ $qr->promptpay_numbers }}" required>
                                        </div> --}}
                                    </div>

                                    {{-- <div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="mb-2">status<span
                                                    class="text-danger">*</span></label>
                                            <input class="form-control" type="text"
                                                name="status"
                                                value="{{ $qr->status }}" required>
                                        </div>
                                    </div> --}}

                                    <div class="col-lg-3">
                                        <button type="submit" class="btn btn-primary " id="update_qr_payment">
                                            <i class="ti-check"></i> Update
                                        </button>
                                    </div>
                                </div>
                            </form>

                            {{-- qrcode img qrCodeImage  --}}
                            {{-- <img src="{{ $qrCodeImage }}" alt=""> --}}

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>
@endsection

@section('script')
    {{-- @include('footer_setting.script') --}}
@endsection
