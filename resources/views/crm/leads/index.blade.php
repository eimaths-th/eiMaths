@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>CRM Leads</h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-check"></i> Lead List</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('crm.leads.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search parent, phone, LINE, student">
                        </div>
                        <div class="col-md-2 mb-2">
                            <select name="status" class="form-control">
                                <option value="">All statuses</option>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <select name="source" class="form-control">
                                <option value="">All sources</option>
                                @foreach($sources as $source)
                                    <option value="{{ $source }}" @selected(request('source') === $source)>{{ $source }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <select name="campaign" class="form-control">
                                <option value="">All campaigns</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign }}" @selected(request('campaign') === $campaign)>{{ $campaign }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <select name="staff_id" class="form-control">
                                <option value="">All staff</option>
                                @foreach($staffUsers as $staff)
                                    <option value="{{ $staff->id }}" @selected((string) request('staff_id') === (string) $staff->id)>{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <select name="created_from" class="form-control">
                                <option value="">All channels</option>
                                @foreach($createdFromOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(request('created_from') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 mb-2">
                            <button type="submit" class="btn btn-primary btn-block">Filter</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                        </div>
                        <div class="col-md-2 mb-2">
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('crm.leads.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Parent</th>
                                <th>Phone</th>
                                <th>LINE ID</th>
                                <th>Channel</th>
                                <th>Student</th>
                                <th>Grade</th>
                                <th>Pain Point</th>
                                <th>Interest</th>
                                <th>Source</th>
                                <th>Campaign</th>
                                <th>Status</th>
                                <th>Staff</th>
                                <th>Created</th>
                                <th>Follow-up</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leads as $lead)
                                <tr>
                                    <td>{{ $lead->parent_name }}</td>
                                    <td>{{ $lead->phone }}</td>
                                    <td>{{ $lead->line_id ?: '-' }}</td>
                                    <td>
                                        @if($lead->line_user_id)
                                            <span class="badge badge-success">LINE OA</span>
                                            <div class="small mt-1">{{ $lead->line_display_name ?: $lead->line_user_id }}</div>
                                            <div class="text-muted small">{{ $lead->created_from_label }}</div>
                                        @else
                                            <span class="badge badge-light">{{ $lead->created_from_label }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $lead->student_name }}</td>
                                    <td>{{ $lead->student_grade }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($lead->pain_point, 50) }}</td>
                                    <td>{{ $lead->interest ?: '-' }}</td>
                                    <td>{{ $lead->utm_source ?: $lead->last_touch_source ?: '-' }}</td>
                                    <td>{{ $lead->utm_campaign ?: '-' }}</td>
                                    <td><span class="badge badge-info">{{ $lead->status_label }}</span></td>
                                    <td>{{ $lead->assignedStaff->name ?? '-' }}</td>
                                    <td>{{ optional($lead->created_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ optional($lead->next_follow_up_at)->format('Y-m-d H:i') ?: '-' }}</td>
                                    <td>
                                        <a href="{{ route('crm.leads.show', $lead) }}" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="text-center">No leads found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $leads->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
