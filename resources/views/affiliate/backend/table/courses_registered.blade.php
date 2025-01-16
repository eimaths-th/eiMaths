<div role="tabpanel" class="tab-pane fade" id="courses_registered_tab">
    <div class="main-title">
        <h3 class="mb-20">Courses Registered</h3>
    </div>
    <div class="QA_section QA_section_heading_custom check_box_table">
        <div class="QA_table ">
            <div class="">
                <table id="" class="table Crm_table_active3">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Date</th>
                            <th>Referent Code</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Telp</th>
                            <th>Course Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['courses_pennding'] as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->created_at->format('d-m-Y')  ?? 'N/A'}}</td>
                                <td>{{ $item->ref  ?? 'N/A'}}</td>
                                <td>{{ $item->name ?? 'N/A'}} </td>
                                <td>{{ $item->email ?? 'N/A'}} </td>
                                <td>{{ $item->telp ?? 'N/A'}} </td>
                                <td>{{ $item->course_name ?? 'N/A'}} </td>
                                <td>
                                    @if ($item->status === '4')
                                        <span class="badge_1">Success</span>
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
