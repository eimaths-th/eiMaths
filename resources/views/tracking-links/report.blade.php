@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Tracking Link Report</h1>
            </div>
            <div class="col-sm-6">
                <a href="{{ route('tracking-links.index') }}" class="btn btn-primary float-sm-right">
                    <i class="fas fa-link mr-1"></i> Tracking Link Generator
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-body">
                <form method="GET" action="{{ route('tracking-links.report') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-3">
                            <label>Link creator</label>
                            <select name="creator_id" class="form-control">
                                <option value="">All creators</option>
                                @foreach($creators as $creator)
                                    <option value="{{ $creator->id }}" @selected((string) request('creator_id') === (string) $creator->id)>{{ $creator->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Lead created from</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Lead created to</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3 d-flex">
                            <button class="btn btn-primary flex-grow-1 mr-2"><i class="fas fa-filter mr-1"></i> Filter</button>
                            <a href="{{ route('tracking-links.report') }}" class="btn btn-secondary flex-grow-1">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            @foreach([
                ['label' => 'Tracking Links', 'value' => $summary['links'], 'icon' => 'fa-link', 'color' => 'info'],
                ['label' => 'Tracked Leads', 'value' => $summary['tracked_leads'], 'icon' => 'fa-users', 'color' => 'primary'],
                ['label' => 'Active Leads', 'value' => $summary['active_leads'], 'icon' => 'fa-user-check', 'color' => 'warning'],
                ['label' => 'Enrolled', 'value' => $summary['enrolled_leads'], 'icon' => 'fa-graduation-cap', 'color' => 'success'],
            ] as $card)
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-{{ $card['color'] }}">
                        <div class="inner"><h3>{{ number_format($card['value']) }}</h3><p>{{ $card['label'] }}</p></div>
                        <div class="icon"><i class="fas {{ $card['icon'] }}"></i></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="alert alert-light border">
            <strong>Attribution coverage:</strong> {{ number_format($summary['attribution_rate'], 1) }}%
            ของ CRM Leads ในช่วงเวลาที่เลือกสามารถระบุ Tracking Link ได้
        </div>

        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Results by Link</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Link</th>
                            <th>Created by</th>
                            <th>Source / Medium</th>
                            <th>Campaign</th>
                            <th class="text-center">Leads</th>
                            <th class="text-center">Active</th>
                            <th class="text-center">Enrolled</th>
                            <th>Latest lead</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($links as $link)
                            <tr class="{{ $link->trashed() ? 'text-muted' : '' }}">
                                <td>
                                    <strong>{{ $link->name }}</strong>
                                    @if($link->trashed())<span class="badge badge-secondary">Archived</span>@endif
                                    <div class="small text-muted">{{ $link->tracking_code }}</div>
                                </td>
                                <td>{{ $link->creator->name ?? $link->created_by_name ?? 'System' }}</td>
                                <td>{{ $link->utm_source }} / {{ $link->utm_medium }}</td>
                                <td>
                                    {{ $link->utm_campaign }}
                                    @if($link->utm_content)<div class="small text-muted">{{ $link->utm_content }}</div>@endif
                                </td>
                                <td class="text-center"><strong>{{ number_format($link->leads_count) }}</strong></td>
                                <td class="text-center">{{ number_format($link->active_leads_count) }}</td>
                                <td class="text-center">{{ number_format($link->enrolled_leads_count) }}</td>
                                <td>{{ $link->latest_lead_at ? \Carbon\Carbon::parse($link->latest_lead_at)->format('Y-m-d H:i') : '-' }}</td>
                                <td>
                                    <a href="{{ route('crm.leads.index', ['tracking_link_id' => $link->id]) }}" class="btn btn-sm btn-outline-primary">
                                        View Leads
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No tracking links found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $links->links() }}</div>
        </div>
    </div>
</section>
@endsection
