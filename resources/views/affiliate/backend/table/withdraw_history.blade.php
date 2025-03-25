<div role="tabpanel" class="tab-pane fade" id="transaction_tab">
    <div class="main-title">
        <h3 class="mb-20">Withdraw History</h3>
    </div>
    <div class="QA_section QA_section_heading_custom check_box_table">
        <div class="QA_table ">
            <div class="">
                <table id="" class="table Crm_table_active3">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Request Date</th>
                            <th>Amount</th>
                            <th>Payment Type</th>
                            <th>Status</th>
                            <th>Confirm Date</th>
                            <th>Action</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['user_transaction_data'] as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ showDate($item->request_date) }}</td>
                                <td>{{ showPrice($item->withdraw_amount) }}
                                </td>
                                <td>
                                    @if ($item->payment_type == 1)
                                        <span class="badge_5">PromptPay</span>
                                    @elseif($item->payment_type == 2)
                                        <span class="badge_5">Bank Account</span>
                                    @else
                                        <span class="badge_5"> error </span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->status == 0)
                                        <span class="badge_3">Pending</span>
                                    @elseif($item->status == 1)
                                        <span class="badge_1">Done</span>
                                    @else
                                        <span class="badge_4">Cancel</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- {{ $item->confirm_date ?? ($item->reject_date ?? 'N/A') }} --}}
                                    {{ $item->confirm_date ?? ($item->reject_date ?? 'N/A') }}
                                </td>

                                <td>
                                    {{-- <div class="dropdown CRM_dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button"
                                            id="dropdownMenu2" data-bs-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            Select
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                                            <a href="#"
                                                    class="dropdown-item edit_row"
                                                    data-id="{{ $item->id }}">Edit</a>
                                                <a href="#"
                                                    class="dropdown-item delete_row"
                                                    data-id="{{ $item->id }}"
                                                    type="button">Delete</a>
                                        </div>
                                    </div> --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
