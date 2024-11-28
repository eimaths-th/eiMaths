<div role="tabpanel" class="tab-pane fade" id="income_tab">
    <div class="main-title">
        <h3 class="mb-20">Commission History</h3>
    </div>
    <div class="QA_section QA_section_heading_custom check_box_table">
        <div class="QA_table ">
            <div class="">
                <table id="lms_table" class="table Crm_table_active3">
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
                                <td>{{ showDate($item->date) }}</td>
                                <td>{{ showPrice($item->amount) }}</td>
                                <td>{{ $item->course ? $item->course->title : '' }}
                                </td>
                                <td>
                                    @if ($item->status == 0)
                                        <span
                                            class="badge_3">{{ __('affiliate.Pending') }}</span>
                                    @else
                                        <span
                                            class="badge_1">{{ __('affiliate.Done') }}</span>
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