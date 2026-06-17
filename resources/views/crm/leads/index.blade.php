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
                <!-- Advanced Filter Box -->
                <div class="p-3 bg-light rounded border mb-4 shadow-sm">
                    <form method="GET" action="{{ route('crm.leads.index') }}">
                        <div class="row">
                            <!-- Row 1 -->
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">Search Keyword</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-left-0" placeholder="Parent, phone, LINE, student...">
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All statuses</option>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">Channel</label>
                                <select name="created_from" class="form-control">
                                    <option value="">All channels</option>
                                    @foreach($createdFromOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(request('created_from') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">Campaign</label>
                                <select name="campaign" class="form-control">
                                    <option value="">All campaigns</option>
                                    @foreach($campaigns as $campaign)
                                        <option value="{{ $campaign }}" @selected(request('campaign') === $campaign)>{{ $campaign }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">Staff In Charge</label>
                                <select name="staff_id" class="form-control">
                                    <option value="">All staff</option>
                                    @foreach($staffUsers as $staff)
                                        <option value="{{ $staff->id }}" @selected((string) request('staff_id') === (string) $staff->id)>{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Row 2 -->
                            <div class="col-md-2 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">Source</label>
                                <select name="source" class="form-control">
                                    <option value="">All sources</option>
                                    @foreach($sources as $source)
                                        <option value="{{ $source }}" @selected(request('source') === $source)>{{ $source }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">LINE Source</label>
                                <select name="line_source" class="form-control">
                                    <option value="">All LINE sources</option>
                                    @foreach($lineSources as $lineSource)
                                        <option value="{{ $lineSource }}" @selected(request('line_source') === $lineSource)>{{ $lineSource }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">Created From</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">Created To</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                            </div>
                            <div class="col-md-4 col-sm-12 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2 flex-grow-1"><i class="fas fa-filter mr-1"></i> Filter</button>
                                <a href="{{ route('crm.leads.index') }}" class="btn btn-secondary flex-grow-1"><i class="fas fa-undo mr-1"></i> Reset</a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Leads Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover text-sm">
                        <thead class="bg-light">
                            <tr>
                                <th>Parent / Student</th>
                                <th>Contact Info</th>
                                <th>Channel</th>
                                <th>Needs & Interest</th>
                                <th>Marketing</th>
                                <th>Status</th>
                                <th>Staff</th>
                                <th>Timeline</th>
                                <th style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leads as $lead)
                                @php
                                    $badgeColors = [
                                        'line_login_pending' => 'badge-secondary',
                                        'new_lead' => 'badge-primary',
                                        'registered' => 'badge-info',
                                        'waiting_for_contact' => 'badge-warning',
                                        'contacted' => 'badge-info bg-teal',
                                        'trial_scheduled' => 'badge-primary bg-purple',
                                        'trial_completed' => 'badge-success',
                                        'enrolled' => 'badge-success',
                                        'not_interested' => 'badge-danger',
                                        'cannot_contact' => 'badge-danger',
                                    ];
                                    $statusColor = $badgeColors[$lead->lead_status] ?? 'badge-info';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $lead->parent_name }}</div>
                                        @if($lead->student_name || $lead->student_grade)
                                            <div class="small text-muted mt-1">
                                                <i class="fas fa-child text-xs text-muted mr-1"></i> {{ $lead->student_name ?: 'N/A' }}
                                                @if($lead->student_grade)
                                                    <span class="badge badge-secondary ml-1" style="font-size: 80%;">{{ $lead->student_grade }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-nowrap"><i class="fas fa-phone-alt text-muted mr-1 text-xs"></i> {{ $lead->phone }}</div>
                                        <div class="small text-muted mt-1 text-nowrap">
                                            <i class="fab fa-line {{ $lead->line_id ? 'text-success' : 'text-muted' }} mr-1"></i> 
                                            {{ $lead->line_id ?: '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($lead->line_user_id)
                                            <span class="badge badge-success mb-1"><i class="fab fa-line mr-1"></i> LINE OA</span>
                                            @if($lead->line_display_name)
                                                <div class="small font-weight-bold text-truncate" style="max-width: 150px;" title="{{ $lead->line_display_name }}">{{ $lead->line_display_name }}</div>
                                            @endif
                                            @if($lead->line_source)
                                                <div class="small text-muted text-truncate" style="max-width: 150px;" title="{{ $lead->line_source }}">{{ $lead->line_source }}</div>
                                            @endif
                                            <div class="text-muted small mt-1" style="font-size: 85%;">{{ $lead->created_from_label }}</div>
                                        @else
                                            <span class="badge badge-light border">{{ $lead->created_from_label }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lead->interest)
                                            <div class="small"><strong>Interest:</strong> {{ $lead->interest }}</div>
                                        @endif
                                        @if($lead->pain_point)
                                            <div class="small mt-1 text-muted" title="{{ $lead->pain_point }}">
                                                <strong>Problem:</strong> {{ \Illuminate\Support\Str::limit($lead->pain_point, 60) }}
                                            </div>
                                        @endif
                                        @if(!$lead->interest && !$lead->pain_point)
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lead->utm_source || $lead->last_touch_source)
                                            <div class="small"><strong>Src:</strong> {{ $lead->utm_source ?: $lead->last_touch_source }}</div>
                                        @endif
                                        @if($lead->utm_campaign)
                                            <div class="small text-muted"><strong>Camp:</strong> {{ $lead->utm_campaign }}</div>
                                        @endif
                                        @if(!$lead->utm_source && !$lead->last_touch_source && !$lead->utm_campaign)
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusColor }} px-2 py-1.5">{{ $lead->status_label }}</span>
                                    </td>
                                    <td>
                                        @if($lead->assignedStaff)
                                            <span class="text-dark"><i class="fas fa-user-tie text-muted mr-1"></i> {{ $lead->assignedStaff->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small text-nowrap"><strong class="text-muted">Created:</strong> {{ optional($lead->created_at)->format('Y-m-d H:i') }}</div>
                                        @if($lead->next_follow_up_at)
                                            <div class="small text-nowrap mt-1 text-danger font-weight-bold">
                                                <i class="far fa-clock mr-1"></i> Follow-up: {{ $lead->next_follow_up_at->format('Y-m-d H:i') }}
                                            </div>
                                        @else
                                            <div class="small text-muted mt-1"><i class="far fa-clock mr-1"></i> Follow-up: -</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('crm.leads.show', $lead) }}" class="btn btn-sm btn-outline-primary btn-block"><i class="fas fa-eye mr-1"></i> View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No leads found.</td>
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
