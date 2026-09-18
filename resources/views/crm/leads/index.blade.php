@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('crm.leads.title') }}</h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-check"></i> {{ __('crm.leads.list_title') }}</h3>
            </div>
            <div class="card-body">
                <!-- Advanced Filter Box -->
                <div class="p-3 bg-light rounded border mb-4 shadow-sm">
                    <form method="GET" action="{{ route('crm.leads.index') }}">
                        <div class="row">
                            <!-- Row 1 -->
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">{{ __('crm.leads.filters.search_keyword') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-left-0" placeholder="{{ __('crm.leads.filters.search_placeholder') }}">
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">{{ __('crm.leads.filters.status') }}</label>
                                <select name="status" class="form-control">
                                    <option value="">{{ __('crm.leads.filters.all_statuses') }}</option>
                                    @foreach($statuses as $value => $label)
                                        @php
                                            $status_option_key = 'crm.leads.statuses.' . $value;
                                            $status_option_translation = __($status_option_key);
                                            $status_option_label = $status_option_translation !== $status_option_key ? $status_option_translation : $label;
                                        @endphp
                                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $status_option_label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">{{ __('crm.leads.filters.channel') }}</label>
                                <select name="created_from" class="form-control">
                                    <option value="">{{ __('crm.leads.filters.all_channels') }}</option>
                                    @foreach($createdFromOptions as $value => $label)
                                        @php
                                            $created_from_option_key = 'crm.leads.created_from.' . $value;
                                            $created_from_option_translation = __($created_from_option_key);
                                            $created_from_option_label = $created_from_option_translation !== $created_from_option_key ? $created_from_option_translation : $label;
                                        @endphp
                                        <option value="{{ $value }}" @selected(request('created_from') === $value)>{{ $created_from_option_label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">{{ __('crm.leads.filters.campaign') }}</label>
                                <select name="campaign" class="form-control">
                                    <option value="">{{ __('crm.leads.filters.all_campaigns') }}</option>
                                    @foreach($campaigns as $campaign)
                                        <option value="{{ $campaign }}" @selected(request('campaign') === $campaign)>{{ $campaign }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">{{ __('crm.leads.filters.staff_in_charge') }}</label>
                                <select name="staff_id" class="form-control">
                                    <option value="">{{ __('crm.leads.filters.all_staff') }}</option>
                                    @foreach($staffUsers as $staff)
                                        <option value="{{ $staff->id }}" @selected((string) request('staff_id') === (string) $staff->id)>{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Row 2 -->
                            <div class="col-md-2 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">{{ __('crm.leads.filters.source') }}</label>
                                <select name="source" class="form-control">
                                    <option value="">{{ __('crm.leads.filters.all_sources') }}</option>
                                    @foreach($sources as $source)
                                        <option value="{{ $source }}" @selected(request('source') === $source)>{{ $source }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">{{ __('crm.leads.filters.line_source') }}</label>
                                <select name="line_source" class="form-control">
                                    <option value="">{{ __('crm.leads.filters.all_line_sources') }}</option>
                                    @foreach($lineSources as $lineSource)
                                        <option value="{{ $lineSource }}" @selected(request('line_source') === $lineSource)>{{ $lineSource }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @php
                                $dateFromFormatted = request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y') : '';
                                $dateToFormatted = request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y') : '';
                            @endphp
                            <div class="col-md-2 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">{{ __('crm.leads.filters.created_from') }}</label>
                                <input type="hidden" name="date_from" id="date_from" value="{{ request('date_from') }}">
                                <div class="input-group">
                                    <input type="text" id="date_from_visible" value="{{ $dateFromFormatted }}" class="form-control bg-white" placeholder="{{ __('crm.leads.filters.date_placeholder') }}" autocomplete="off">
                                    <div class="input-group-append" style="cursor: pointer;" onclick="$('#date_from_visible').focus();">
                                        <span class="input-group-text bg-white"><i class="far fa-calendar-alt text-muted"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">{{ __('crm.leads.filters.created_to') }}</label>
                                <input type="hidden" name="date_to" id="date_to" value="{{ request('date_to') }}">
                                <div class="input-group">
                                    <input type="text" id="date_to_visible" value="{{ $dateToFormatted }}" class="form-control bg-white" placeholder="{{ __('crm.leads.filters.date_placeholder') }}" autocomplete="off">
                                    <div class="input-group-append" style="cursor: pointer;" onclick="$('#date_to_visible').focus();">
                                        <span class="input-group-text bg-white"><i class="far fa-calendar-alt text-muted"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2 flex-grow-1"><i class="fas fa-filter mr-1"></i> {{ __('crm.leads.filters.filter') }}</button>
                                <a href="{{ route('crm.leads.index') }}" class="btn btn-secondary flex-grow-1"><i class="fas fa-undo mr-1"></i> {{ __('crm.leads.filters.reset') }}</a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <label class="form-label font-weight-bold text-muted small mb-1">{{ __('crm.leads.filters.tracking_creator') }}</label>
                                <select name="tracking_creator_id" class="form-control">
                                    <option value="">{{ __('crm.leads.filters.all_creators') }}</option>
                                    @foreach($trackingCreators as $creator)
                                        <option value="{{ $creator->id }}" @selected((string) request('tracking_creator_id') === (string) $creator->id)>{{ $creator->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(request('tracking_link_id'))
                                <input type="hidden" name="tracking_link_id" value="{{ request('tracking_link_id') }}">
                                <div class="col-md-5 col-sm-6 mb-3 d-flex align-items-end">
                                    <div class="alert alert-info py-2 mb-0 w-100">
                                        {{ __('crm.leads.filters.tracking_context', ['id' => request('tracking_link_id')]) }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- Leads Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover text-sm">
                        <thead class="bg-light">
                            <tr>
                                <th>{{ __('crm.leads.table.parent_student') }}</th>
                                <th>{{ __('crm.leads.table.contact_info') }}</th>
                                <th>{{ __('crm.leads.table.channel') }}</th>
                                <th>{{ __('crm.leads.table.problem') }}</th>
                                <th>{{ __('crm.leads.table.source') }}</th>
                                <th>{{ __('crm.leads.table.status') }}</th>
                                <th>{{ __('crm.leads.table.staff') }}</th>
                                <th>{{ __('crm.leads.table.timeline') }}</th>
                                <th style="width: 80px;">{{ __('crm.leads.table.action') }}</th>
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
                                    $status_key = 'crm.leads.statuses.' . $lead->lead_status;
                                    $status_translation = __($status_key);
                                    $status_label = $status_translation !== $status_key ? $status_translation : $lead->status_label;
                                    $created_from_key = 'crm.leads.created_from.' . $lead->created_from;
                                    $created_from_translation = __($created_from_key);
                                    $created_from_label = $created_from_translation !== $created_from_key ? $created_from_translation : $lead->created_from_label;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $lead->parent_name }}</div>
                                        @if($lead->student_name || $lead->student_grade)
                                            <div class="small text-muted mt-1">
                                                <i class="fas fa-child text-xs text-muted mr-1"></i> {{ $lead->student_name ?: __('crm.leads.table.no_student') }}
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
                                            <div class="text-muted small mt-1" style="font-size: 85%;">{{ $created_from_label }}</div>
                                        @else
                                            <span class="badge badge-light border">{{ $created_from_label }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lead->pain_point)
                                            <span title="{{ $lead->pain_point }}">{{ \Illuminate\Support\Str::limit($lead->pain_point, 60) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $sourceVal = $lead->utm_source ?: $lead->last_touch_source ?: '';
                                            $sourceLower = strtolower($sourceVal);
                                        @endphp
                                        @if($sourceLower === 'facebook')
                                            <span class="badge text-white px-2 py-1.5 font-weight-normal" style="background-color: #3b5998 !important; font-size: 85%;">
                                                <i class="fab fa-facebook-f mr-1"></i> Facebook
                                            </span>
                                        @elseif($sourceLower === 'tiktok')
                                            <span class="badge text-white px-2 py-1.5 font-weight-normal" style="background-color: #010101 !important; font-size: 85%;">
                                                <i class="fab fa-tiktok mr-1"></i> TikTok
                                            </span>
                                        @elseif($sourceLower === 'line')
                                            <span class="badge text-white px-2 py-1.5 font-weight-normal" style="background-color: #06C755 !important; font-size: 85%;">
                                                <i class="fab fa-line mr-1"></i> LINE
                                            </span>
                                        @elseif($sourceLower === 'google')
                                            <span class="badge text-white px-2 py-1.5 font-weight-normal" style="background-color: #db4437 !important; font-size: 85%;">
                                                <i class="fab fa-google mr-1"></i> Google
                                            </span>
                                        @elseif($sourceLower === 'youtube')
                                            <span class="badge text-white px-2 py-1.5 font-weight-normal" style="background-color: #ff0000 !important; font-size: 85%;">
                                                <i class="fab fa-youtube mr-1"></i> YouTube
                                            </span>
                                        @elseif($sourceLower === 'instagram')
                                            <span class="badge text-white px-2 py-1.5 font-weight-normal" style="background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%) !important; font-size: 85%;">
                                                <i class="fab fa-instagram mr-1"></i> Instagram
                                            </span>
                                        @elseif($sourceVal)
                                            <span class="badge badge-secondary px-2 py-1.5 font-weight-normal" style="font-size: 85%;">
                                                <i class="fas fa-link mr-1"></i> {{ ucfirst($sourceVal) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                        @if($lead->tracking_link_name)
                                            <div class="small mt-2 font-weight-bold text-primary" title="{{ __('crm.leads.table.tracking_code', ['code' => $lead->tracking_link_code]) }}">
                                                <i class="fas fa-link mr-1"></i>{{ $lead->tracking_link_name }}
                                            </div>
                                            <div class="small text-muted">
                                                {{ __('crm.leads.table.by') }} {{ $lead->tracking_link_creator_name ?: __('crm.leads.table.system') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusColor }} px-2 py-1.5">{{ $status_label }}</span>
                                    </td>
                                    <td>
                                        @if($lead->assignedStaff)
                                            <span class="text-dark"><i class="fas fa-user-tie text-muted mr-1"></i> {{ $lead->assignedStaff->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small text-nowrap"><strong class="text-muted">{{ __('crm.leads.table.created') }}</strong> {{ optional($lead->created_at)->format('Y-m-d H:i') }}</div>
                                        @if($lead->next_follow_up_at)
                                            <div class="small text-nowrap mt-1 text-danger font-weight-bold">
                                                <i class="far fa-clock mr-1"></i> {{ __('crm.leads.table.follow_up') }} {{ $lead->next_follow_up_at->format('Y-m-d H:i') }}
                                            </div>
                                        @else
                                            <div class="small text-muted mt-1"><i class="far fa-clock mr-1"></i> {{ __('crm.leads.table.follow_up') }} -</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('crm.leads.show', $lead) }}" class="btn btn-sm btn-outline-primary btn-block"><i class="fas fa-eye mr-1"></i> {{ __('crm.leads.table.view') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">{{ __('crm.leads.table.no_leads') }}</td>
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

@section('script')
<script>
    $(document).ready(function() {
        $('#date_from_visible').datepicker({
            dateFormat: 'dd/mm/yy',
            altField: '#date_from',
            altFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
        
        $('#date_to_visible').datepicker({
            dateFormat: 'dd/mm/yy',
            altField: '#date_to',
            altFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
        
        // Clear hidden input when user clears visible input manually
        $('#date_from_visible').on('input change', function() {
            if (!$(this).val()) {
                $('#date_from').val('');
            }
        });
        
        $('#date_to_visible').on('input change', function() {
            if (!$(this).val()) {
                $('#date_to').val('');
            }
        });
    });
</script>
@endsection
