@extends('layouts.app')

@section('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    @include('users.style')

    <style>
        /* line 402, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle {
            display: inline-block;
            height: 20px;
            position: relative;
            width: 36px;
            margin-bottom: 0;
        }

        /* line 409, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle input {
            opacity: 0;
        }

        /* line 412, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle .slider:before {
            bottom: 0px;
            content: "";
            height: 16px;
            left: 0px;
            left: 3px;
            position: absolute;
            -webkit-transition: .4s;
            transition: .4s;
            width: 16px;
            background: #707DB7;
            background: #fff;
            top: 2px;
        }

        /* line 430, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle input:checked+.slider {
            background: #ffb515;
        }

        /* line 434, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle input:checked+.slider:before {
            transform: translateX(14px);
        }

        /* line 441, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle .slider.round {
            border-radius: 34px;
        }

        /* line 445, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle .slider.round:before {
            border-radius: 50%;
        }

        /* line 448, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle .switch_toggle input {
            display: none;
        }

        /* line 451, G:/laragon/www/CRM_frontend/Admin_crm/public/frontend/scss/_predefine.scss */
        .switch_toggle .slider {
            background-color: #292929;
            bottom: 0;
            cursor: pointer;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            transition: .4s;
        }
    </style>
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Promotion Image Management</h4>
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
                            <h3 class="card-title"><i class="fa fa-user"></i>Promotion Image Management</h3>
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
                            <form action="{{ route('promotion-img.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="mb-2">add image <span class="text-danger">*</span></label>
                                            <input class="form-control" type="file" name="image" id="image"
                                                required>
                                        </div>

                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="mb-2">Title <span class="text-danger">*</span></label>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <input class="form-control" type="text" name="title" id="Title"
                                                        value="" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="mb-2">Url <span class="text-danger">*</span></label>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <input class="form-control" type="text" name="url" id="url"
                                                        value="">
                                                </div>
                                            </div>
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
                    {{-- <div class="card card-primary">
                        <div class="card-body">
                            <h3>Promotion Images</h3>
                            <div class="row">
                                @foreach ($promotion_images as $image)
                                    <div class="col-md-3 mb-4">
                                        <div class="card">
                                            <img src="{{ asset($image->img) }}" alt="Promotion Image" class="card-img-top" style="width: 100%; height: 200px; object-fit: cover;">
                                            <div class="card-body text-center">
                                                <form action="{{ route('promotion-img.destroy', $image->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div> --}}

                    {{-- <div class="card card-primary">
                        <div class="card-body">
                            <h3>Promotion Images</h3>
                            <div class="row">
                                @foreach ($promotion_images as $image)
                                    <div class="col-md-3 mb-4">
                                        <div class="card position-relative">
                                            <img src="{{ asset($image->img) }}" alt="Promotion Image" class="card-img-top"
                                                style="width: 100%; height: 200px; object-fit: cover;">

                                            <!-- Delete Button Positioned at the Top Right -->
                                            <div class="position-absolute top-0 end-0 p-2">
                                                <form action="{{ route('promotion-img.destroy', $image->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash-alt"></i>
                                                        <!-- You can use FontAwesome icon for trash -->
                                                    </button>
                                                </form>
                                            </div>

                                            <div class="card-body text-center">
                                                <!-- Card content can be added here -->
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div> --}}


                    <div class="card card-primary">
                        <div class="card-body">
                            <h3>Promotion Images</h3>
                            <div class="row">
                                @foreach ($promotion_images as $image)
                                    <div class="col-md-3 mb-4">
                                        <div class="card position-relative">
                                            <img src="{{ asset($image->img) }}" alt="Promotion Image" class="card-img-top"
                                                style="width: 100%; height: 200px; object-fit: cover;">

                                            <!-- Delete Button Positioned at the Top Right -->
                                            <div class="position-absolute top-0 end-0 p-2">
                                                <!-- Disable delete button if the image is enabled -->
                                                @if ($image->status == 'enabled')
                                                    <button type="button" class="btn btn-danger btn-sm" disabled>
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                @else
                                                    <form action="{{ route('promotion-img.destroy', $image->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>

                                            <!-- Radio Button to Enable Image -->
                                            <div class="card-body text-center">
                                                <label class="switch_toggle">
                                                    <input type="checkbox" class="status_enable_disable" id="enable_{{ $image->id }}" name="enabled_image"
                                                        value="{{ $image->id }}" {{ $image->status == 'enabled' ? 'checked' : '' }}>
                                                    <span class="slider round"></span>

                                                </label>

                                            </div>

                                            {{-- <label class="switch_toggle">
                                                <input type="checkbox" class="{{ $status_enable_disable }}" data-id="{{ $query->id }}" {{ $checked }}>
                                                <i class="slider round"></i>
                                            </label> --}}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Field to Update the Status via Ajax -->
                    <input type="hidden" id="enabled_image_id" value="{{ old('enabled_image_id') }}">
                </div>



            </div>

        </div>

        </div>

    </section>
@endsection

@section('script')
    {{-- @include('footer_setting.script') --}}
    <script>
        $(document).ready(function() {
            // When a radio button is selected, update the status of the images
            $('.status_enable_disable').change(function(e) {
                e.preventDefault();
                let selectedImageId = $(this).val();
                let isChecked = $(this).is(':checked');

                // Send the selected image ID via AJAX to update the status
                $.ajax({
                    url: '/promotion-images/update-status', // Your endpoint to update the image status
                    method: 'POST',
                    data: {
                        id: selectedImageId,
                        status: isChecked ? 'enabled' : 'disabled',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // On success, reload the page to reflect changes
                        toastr.success(response.message, {
                            timeOut: 5000
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('An error occurred while updating the image status.');
                    }
                });
            });
        });
    </script>
@endsection
