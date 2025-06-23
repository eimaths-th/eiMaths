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
                        {{-- <li class="breadcrumb-item"><a href="#">Home</a></li> --}}
                        <li class="breadcrumb-item active">Affiliate Configurations</li>
                        {{-- @can('user-create')
                            <button type="button" class="btn btn-success" id="CreateButton">
                                <i class="fas fa-user"></i> Add User </button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="You Not Have Permission">
                                <button type="button" class="btn btn-success disabled">
                                    <i class="fas fa-user"></i> Add User </button>
                            </span>
                        @endcan &nbsp;

                        @can('user-delete')
                            <button type="button" class="btn btn-danger delete_all_button"><i class="fa fa-trash"></i> Delete All</button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="You Not Have Permission">
                                <button type="button" class="btn btn-danger disabled"><i
                                        class="fa fa-trash"></i> Delete All</button>
                            </span>
                        @endcan --}}
                    </ol>

                </div>
            </div>
        </div>
    </section>

    {{-- <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-user"></i>Affiliate Configurations</h3>
                            <div class="card-tools">
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
                            <form>
                                <div class="row">
                                    @php
                                        $keyLabels = [
                                            'min_withdraw' => 'จำนวนเงินถอนขั้นต่ำ (฿)',
                                            'balance_add_account_after_days' => 'ถอนเงินได้หลังจาก',
                                            'commission_amount' => 'เปอร์เซ็นต์ค่าคอมมิชชั่น',
                                        ];
                                        $keyLabels2 = [
                                            'min_withdraw' => ' บาท',
                                            'balance_add_account_after_days' => ' วัน',
                                            'commission_amount' => ' %',
                                        ];
                                    @endphp

                                    @foreach ($aff_config as $config)
                                        @if (array_key_exists($config->key, $keyLabels))
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label class="mb-2">{{ $keyLabels[$config->key] }}
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="row">
                                                        <div class="col-lg-10">
                                                            <input class="form-control" type="number"
                                                                id="{{ $config->key }}" name="{{ $config->key }}"
                                                                autocomplete="off" value="{{ $config->value }}">
                                                        </div>
                                                        <div class="col-lg-2">
                                                            <label class="mb-2">{{ $keyLabels2[$config->key] }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    <div class="col-lg-3">

                                    </div>
                                    <div class="col-lg-3">
                                        <button type="submit" class="btn btn-primary" id="updateCommissionConfig">
                                            <i class="ti-check"></i>
                                            update
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </section> --}}

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 col-sm-6 col-lg-6  col-xl-3 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fa-solid fa-graduation-cap"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Student</span>
                            <span class="info-box-number" id="">{{ $count_std }} คน</span>
                            <span class="info-box-number" id="">{{ $results2 }} คอร์ส</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-user"></i>Affiliate Configurations</h3>
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
                            {{--  --}}
                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif
                            <form action="{{ route('commission.updateConfigComission') }}" method="POST">
                                @csrf
                                <div class="row">
                                    @foreach ($aff_comission as $comission)
                                        <input type="hidden" name="id[]" value="{{ $comission->id }}">

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="mb-2">Course per Month <span
                                                        class="text-danger">*</span></label>
                                                <div class="row">
                                                    <div class="col-lg-10">
                                                        <input class="form-control" type="number"
                                                            name="course_per_month[{{ $comission->id }}]"
                                                            value="{{ $comission->course_per_month }}" required>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <label class="mb-2">คน</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label class="mb-2">Low <span class="text-danger">*</span></label>
                                                        <input class="form-control" type="number"
                                                            name="user_per_course_low[{{ $comission->id }}]"
                                                            value="{{ $comission->user_per_course_low }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label class="mb-2">High <span
                                                                class="text-danger">*</span></label>
                                                        <input class="form-control" type="number"
                                                            name="user_per_course_high[{{ $comission->id }}]"
                                                            value="{{ $comission->user_per_course_high }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="mb-2">Commission per course 10 percent <span
                                                        class="text-danger">*</span></label>
                                                <input class="form-control" type="number"
                                                    name="comission_per_course_10_percent[{{ $comission->id }}]"
                                                    value="{{ $comission->comission_per_course_10_percent }}" required>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="mb-2">Commission per course 15 percent <span
                                                        class="text-danger">*</span></label>
                                                <input class="form-control" type="number"
                                                    name="comission_per_course_15_percent[{{ $comission->id }}]"
                                                    value="{{ $comission->comission_per_course_15_percent }}" required>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="col-lg-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti-check"></i> Update
                                        </button>
                                    </div>
                                </div>
                            </form>

                        </div>

                    </div>
                </div>

            </div>

        </div>

    </section>

    {{--  {!! $data->render() !!} --}}
@endsection

@section('script')
    @include('affiliate.config.script')
@endsection
