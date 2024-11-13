@extends('backend.master')
@section('table')
    @php
        $table_name = 'checkouts';
    @endphp
    {{ $table_name }}
@stop

@section('mainContent')

    {!! generateBreadcrumb() !!}

    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            <div class="row justify-content-between">
                <div class="col-lg-12">
                    <div class="white_box mb_30">
                        <div class="white_box_tittle list_header main-title mb-0">
                            <h3 class="mb-0">{{ __('courses.Advanced Filter') }} </h3>
                        </div>
                        <form action="{{ route('filterSearch') }}" method="POST">
                            @csrf
                            <div class="row">

                                <div class="col-lg-4 mt-30">
                                    <select class="primary_select" name="methods">
                                        <option data-display="{{ __('common.Select') }} {{ __('payment.Method') }}"
                                            value="">{{ __('common.Select') }} {{ __('payment.Method') }}</option>
                                        <option value="all" selected>{{ __('payment.All') }}</option>
                                        @foreach ($gateways as $gateway)
                                            @if ($gateway->method != 'Bank Payment')
                                                <option value="{{ $gateway->method }}"
                                                    @if (isset($_POST['methods']) && $_POST['methods'] == $gateway->method) selected @endif>
                                                    {{ $gateway->method }}
                                                </option>
                                            @endif
                                        @endforeach

                                    </select>
                                </div>
                                <div class="col-xl-4 col-md-4 col-lg-4">
                                    <div class="primary_input mb-15">
                                        <label class="primary_input_label"
                                            for="startDate">{{ __('common.Start Date') }}</label>
                                        <div class="primary_datepicker_input">
                                            <div class="g-0  input-right-icon">
                                                <div class="col">
                                                    <div class="">
                                                        <input placeholder="{{ __('common.Date') }}"
                                                            class="primary_input_field primary-input date form-control"
                                                            id="startDate" type="text" name="start_date"
                                                            value="@if (isset($_POST['start_date'])) {{ $_POST['start_date'] }} @else{{ date('m/d/Y') }} @endif"
                                                            autocomplete="off">
                                                    </div>
                                                </div>
                                                <button class="" type="button">
                                                    <i class="ti-calendar" id="start-date-icon"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4 col-lg-4">
                                    <div class="primary_input mb-15">
                                        <label class="primary_input_label"
                                            for="admissionDate">{{ __('common.End Date') }}</label>
                                        <div class="primary_datepicker_input">
                                            <div class="g-0  input-right-icon">
                                                <div class="col">
                                                    <div class="">
                                                        <input placeholder="{{ __('common.Date') }}"
                                                            class="primary_input_field primary-input date form-control"
                                                            id="admissionDate" type="text" name="end_date"
                                                            value="@if (isset($_POST['end_date'])) {{ $_POST['end_date'] }} @else{{ date('m/d/Y') }} @endif"
                                                            autocomplete="off">
                                                    </div>
                                                </div>
                                                <button class="" type="button">
                                                    <i class="ti-calendar" id="admission-date-icon"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-12 mt-20">
                                    <div class="search_course_btn text-end">
                                        <button type="submit"
                                            class="primary-btn radius_30px   fix-gr-bg">{{ __('courses.Filter') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="white-box">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-12 col-md-12">
                                <div class="main-title">
                                    {{-- <h3 class="mb-0">{{ __('payment.Received Online') }}</h3> --}}
                                    <h3>COD Payment</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- </div> -->
                <style>
                    .hiddenRow {
                        padding: 0 !important;
                        /* display: none; */
                        /* To initially hide the row if needed */
                    }

                    .table-no-lines {
                        border-collapse: collapse;
                        width: 100%;
                    }

                    .table-no-lines th,
                    .table-no-lines td {
                        border: none;
                        /* Remove borders */
                        padding: 10px;
                        /* Add padding if needed */
                        text-align: left;
                    }

                    .table-no-lines th {
                        font-weight: bold;
                    }

                    .custom-badge {
                        padding-left: 5px;
                        padding-right: 5px;
                        padding-top: 5px;
                        padding-bottom: 5px;
                        font-size: 10px;
                    }

                    .modal img {
                        max-width: 100%;
                        height: auto;
                    }
                </style>
                <div class="QA_section QA_section_heading_custom check_box_table mt-20">
                    <div class="QA_table pt-3">

                        <table id="lms_table" class="table Crm_table_active3">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    {{-- <th scope="col">ID</th> --}}
                                    <th scope="col">{{ __('payment.Transaction') }}</th>
                                    <th scope="col">{{ __('common.User') }}</th>
                                    <th scope="col">{{ __('payment.Request Date') }}</th>
                                    <th scope="col">{{ __('payment.Total') }} {{ __('payment.Amount') }}</th>
                                    {{-- <th scope="col">{{ __('common.Paid') }} {{ __('payment.Amount') }}</th> --}}
                                    {{-- <th scope="col"> {{ __('tax.TAX') }}</th> --}}
                                    <th scope="col">{{ __('payment.Payment') }} {{ __('payment.Method') }}</th>
                                    <th scope="col">Status Slip</th>
                                    <th>Slip</th>
                                    <th></th>
                                    {{-- <th scope="col">{{__('common.Status')}}</th> --}}
                                </tr>
                            </thead>
                            @php
                                $loopss = 1; // Default empty class

                            @endphp
                            <tbody>
                                @foreach ($codCheckouts as $log)
                                    @php
                                        $text = '';
                                        $class = ''; // Default empty class
                                        if ($log->approve_slip == 1) {
                                            $text = 'Pending';
                                            $class = 'warning'; // Assign class for Pending
                                        } elseif ($log->approve_slip == 0) {
                                            $text = 'No Slip';
                                            $class = 'secondary'; // Assign a class for No Slip
                                        } elseif ($log->approve_slip == 2) {
                                            $text = 'Success';
                                            $class = 'success'; // Assign class for Success
                                        } elseif ($log->approve_slip == 3) {
                                            $text = 'Denied';
                                            $class = 'danger'; // Assign class for Denied
                                        } elseif ($log->approve_slip == 4) {
                                            # code...
                                            $class = 'danger';
                                            $text = ' Incomplete Payment';
                                        } elseif ($log->approve_slip == 5) {
                                            # code...
                                            $class = 'danger';
                                            $text = 'Over';
                                        } else {
                                            $text = 'Error';
                                            $class = 'danger'; // Assign a class for unknown states
                                        }
                                    @endphp
                                    <input type="hidden" name="checkoutId" id="checkoutId" value="{{ $log->id }}">
                                    <tr class="accordion-toggle" {{-- data-toggle="collapse" data-target="#codPayments_{{ $log->id }}" > --}} data-bs-toggle="modal"
                                        data-bs-target="#viewSlipModal_{{ $log->id }}">
                                        {{-- <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewSlipModal_{{ $payment->id }}"> --}}
                                        <th scope="row">{{ $loopss++ }}</th>
                                        {{-- <th scope="row">{{ $log->id }}</th> --}}
                                        <th scope="row">{{ $log->tracking }}</th>
                                        <td>{{ $log->user->name }}</td>
                                        <td>{{ $log->dateFormat }}</td>
                                        <td class="text-center">{{ getPriceFormat($log->price, false) }}</td>
                                        <td class="text-center">{{ $log->payment_method }}</td>
                                        <td class="text-center">
                                            <!-- Status handling -->
                                            <span
                                                class="badge border border-{{ $class }} text-{{ $class }} rounded custom-badge">
                                                <i class="fa fa-circle fs-10px fa-fw me-1"></i> {{ $text }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $log->codPayments->count() }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary">View Slip</button>
                                        </td>
                                    </tr>

                                    {{-- @if ($log->codPayments->count() > 0)
                                        <tr>
                                            <td colspan="8" class="hiddenRow">
                                                <div class="collapse" id="codPayments_{{ $log->id }}">
                                                    <table class="table-no-lines">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">{{ __('Payment ID') }}</th>
                                                                <th scope="col">{{ __('Amount') }}</th>
                                                                <th scope="col">{{ __('Status') }}</th>
                                                                <th scope="col">{{ __('Created At') }}</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $loops = 1;
                                                            @endphp
                                                            @foreach ($log->codPayments as $payment)
                                                                <tr>
                                                                    <td>{{ $loops }}</td>
                                                                    <td>{{ getPriceFormat($payment->amount, false) }}</td>
                                                                    <td>
                                                                        @php
                                                                            $loops++;
                                                                            $text = '';
                                                                            $class = '';
                                                                            if ($payment->slip_check == 1) {
                                                                                $text = 'Pending';
                                                                                $class = 'warning';
                                                                            } elseif ($payment->slip_check == 2) {
                                                                                $text = 'success';
                                                                                $class = 'success';
                                                                            } elseif ($payment->slip_check == 3) {
                                                                                $text = 'Denied';
                                                                                $class = 'danger';
                                                                            } else {
                                                                                $text = 'error';
                                                                            }
                                                                        @endphp
                                                                        <span
                                                                            class="badge border border-{{ $class }} text-{{ $class }} rounded custom-badge d-inline-flex align-items-center">
                                                                            <i class="fa fa-circle fs-10px fa-fw me-1"></i>
                                                                            {{ $text }}
                                                                        </span>
                                                                    </td>
                                                                    <td>{{ $payment->created_at }}</td>
                                                                    <td>
                                                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewSlipModal_{{ $payment->id }}">
                                                                            View
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                                <div class="modal fade" id="viewSlipModal_{{ $payment->id }}" tabindex="-1"
                                                                    aria-labelledby="viewSlipModalLabel_{{ $payment->id }}" aria-hidden="true">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title"
                                                                                    id="viewSlipModalLabel_{{ $payment->id }}">
                                                                                    Slip for Payment ID:
                                                                                    {{ $payment->id }}
                                                                                </h5>
                                                                                <button type="button" class="btn-close"
                                                                                    data-bs-dismiss="modal"
                                                                                    aria-label="Close"> </button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <!-- Slip Image -->
                                                                                @if ($payment->slip_path)
                                                                                    <img src="{{ asset($payment->slip_path) }}" alt="Slip Image" class="img-fluid">
                                                                                @else
                                                                                    <p>No slip uploaded.</p>
                                                                                @endif
                                                                            </div>

                                                                            <div class="modal-footer">
                                                                                <!-- Accept Button -->
                                                                                <button class="btn btn-success" onclick="updateSlipStatus({{ $payment->id }}, 2)">Accept</button>

                                                                                <!-- Deny Button -->

                                                                                <button class="btn btn-danger deny-btn"
                                                                                    data-id="{{ $payment->id }}">Deny</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif --}}
                                    <div class="modal fade" id="viewSlipModal_{{ $log->id }}" tabindex="-1"
                                        aria-labelledby="viewSlipModalLabel_{{ $log->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="viewSlipModalLabel_{{ $log->id }}">
                                                        Slip for Payment ID:
                                                        {{ $log->id }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"> </button>
                                                </div>

                                                @foreach ($log->codPayments as $payment)
                                                    @php
                                                        // $loops++;
                                                        $text = '';
                                                        $class = '';
                                                        if ($payment->slip_check == 1) {
                                                            $text = 'Pending';
                                                            $class = 'warning';
                                                        } elseif ($payment->slip_check == 2) {
                                                            $text = 'success';
                                                            $class = 'success';
                                                        } elseif ($payment->slip_check == 3) {
                                                            $text = 'Denied';
                                                            $class = 'danger';
                                                        } else {
                                                            $text = 'error';
                                                        }
                                                    @endphp
                                                    <div class="modal-body">
                                                        <!-- Slip Image -->
                                                        <p>Total : {{ number_format((float) $payment->checkout->price) }} ฿
                                                        </p>
                                                        <p>Paid : {{ number_format((float) $payment->paid) }} ฿</p>
                                                        <h3>
                                                            status :
                                                            <span
                                                                class="text-{{ $class }}">{{ $text }}</span>
                                                        </h3>
                                                        @if ($payment->slip_path)
                                                            <div class="text-center mb-3">
                                                                <img src="{{ asset($payment->slip_path) }}"
                                                                    alt="Slip Image" width= "300px" class="img-fluid">
                                                            </div>
                                                        @else
                                                            <p>No slip uploaded.</p>
                                                        @endif

                                                        @if ($payment->slip_check == 3)
                                                            <h3 class="mt-3">
                                                                Reason :
                                                                {{ $payment->denial_reason }}

                                                            </h3>
                                                        @else
                                                        @endif

                                                    </div>

                                                    <div class="modal-footer">
                                                        <!-- Accept Button -->

                                                        <button class="btn btn-success"
                                                            onclick="updateSlipStatus({{ $payment->id }}, 2)">Accept</button>

                                                        <!-- Deny Button -->

                                                        <button class="btn btn-danger deny-btn"
                                                            data-id="{{ $payment->id }}">Deny</button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                            <!-- Collapsible Submenu for cod_payments -->
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <script>
        $('.accordion-toggle').click(function() {
            var target = $(this).data('target');
            $(target).collapse('toggle');
        });
    </script>
    <script>
        let checkoutId = $('#checkoutId').val();

        function updateSlipStatus(paymentId, status) {
            $.ajax({
                url: "{{ route('CODSlip.update', '') }}/" + paymentId, // Construct the URL dynamically
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}', // Pass the CSRF token
                    slip_check: status // Pass the status (2 for Accept, 3 for Deny)
                },
                success: function(response) {
                    // console.log(response);
                    // console.log(paymentId);
                    // console.log(checkoutId);

                    $('#statusBadge_' + paymentId).text(status === 2 ? 'Success' : 'Denied')
                        .removeClass('text-warning text-danger')
                        .addClass(status === 2 ? 'text-success' : 'text-danger');
                    // $('$viewSlipModal_' + paymentId).modal('hide');
                    // $('#lms_table').load(location.href + " #lms_table");
                    // alert(response.message); // Example: Show success message
                    toastr.success(response.message, 'Success', {
                        closeButton: true,
                        progressBar: true,
                        timeOut: 5000,

                    });
                    $('#viewSlipModal_' + checkoutId).modal('hide');

                    // if (status == 2) {
                    //     toastr.success('Payment accepted successfully!');

                    // } else if (status == 3) {
                    //     toastr.error('Payment denied!');
                    // }
                },
                error: function(xhr) {
                    // Handle error
                    alert('An error occurred while updating the slip status.');
                }
            });
        }
    </script>
    <script>
        $(document).ready(function() {
            // When the Deny button is clicked
            $('.deny-btn').on('click', function() {
                var paymentId = $(this).data('id');
                $('#paymentId').val(paymentId); // Set the payment ID in a hidden input
                $('#denyReasonModal').modal('show'); // Show the modal
            });

            // When the confirm button is clicked in the modal
            $('#confirmDeny').on('click', function() {
                var paymentId = $('#paymentId').val();
                var reason = $('#denyReason').val();

                if (!reason.trim()) {
                    toastr.error('Please provide a reason for denial.');
                    return; // Do not proceed if the reason is empty
                }

                // Send the AJAX request with the denial reason
                $.ajax({
                    // url: '/path-to-your-route/' + paymentId, // Your route here
                    url: "{{ route('CODSlip.update', '') }}/" +
                        paymentId, // Construct the URL dynamically
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}', // CSRF token
                        slip_check: 3, // Deny
                        reason: reason // Denial reason
                    },
                    success: function(response) {
                        if (response.success) {
                            // Close the modal
                            $('#denyReasonModal').modal('hide');
                            $('#viewSlipModal_' + paymentId).modal('hide');

                            // Show success notification
                            toastr.error('Payment denied! Reason: ' + reason);

                            // Reload the table or update UI as needed
                            // $('#lms_table').DataTable().ajax.reload();
                        } else {
                            toastr.error('An error occurred.');
                        }
                    },
                    error: function() {
                        toastr.error('An error occurred during the request.');
                    }
                });
            });
        });
    </script>
    <div class="modal fade" id="denyReasonModal" tabindex="-1" aria-labelledby="denyReasonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="denyReasonModalLabel">Provide a Reason for Denial</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <textarea id="denyReason" class="form-control" rows="3" placeholder="Enter the reason for denial..."></textarea>
                    <input type="hidden" id="paymentId" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="confirmDeny">Deny</button>
                </div>
            </div>
        </div>
    </div>


@endsection
{{-- <tbody>
                                @foreach ($codCheckouts as $log)
                                    <tr>
                                        <th scope="row">
                                            {{ @$log->tracking }}
                                        </th>
                                        <td>{{ @$log->user->name }}</td>
                                        <td>{{ @$log->dateFormat }}</td>
                                        <td> {{ getPriceFormat($log->price, false) }}</td>
                                        <td>{{ getPriceFormat($log->purchase_price, false) }}</td>
                                        <td>{{ getPriceFormat($log->tax, false) }}</td>
                                        <td> {{ @$log->payment_method }}</td>

                                    </tr>
                                @endforeach

                            </tbody> --}}
