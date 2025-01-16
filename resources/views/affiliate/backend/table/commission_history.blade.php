<div role="tabpanel" class="tab-pane fade" id="income_tab">
    <div class="main-title">
        <h3 class="mb-20">Commission History</h3>
    </div>
    <div class="QA_section QA_section_heading_custom check_box_table">
        <div class="QA_table ">
            <div class="">
                <table id="" class="table Crm_table_active3">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Course</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['user_income_data'] as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->created_at->format('d-m-Y') }}</td>
                                <td>{{ number_format($item->commission_amount, 2) }} ฿</td>
                                <td>{{ $item->coursePending ? $item->coursePending->course_name : $item->receipt->contact->level_name }}

                                </td>
                                <td>
                                    @if ($item->status === 'approved')
                                        <span class="badge_1">Approved</span>
                                    @else
                                        <span class="badge_3">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
