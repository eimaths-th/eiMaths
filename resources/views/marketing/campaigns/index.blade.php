@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Marketing Campaigns</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('marketing.campaigns.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Campaign
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Segment</th>
                                <th>Schedule</th>
                                <th>Status</th>
                                <th>Target</th>
                                <th>Sent</th>
                                <th>Failed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $campaign)
                            <tr>
                                <td>{{ $campaign->title }}</td>
                                <td>
                                    <small>
                                        Level: {{ $campaign->segment_level ?? 'All' }}<br>
                                        Interest: {{ $campaign->segment_interest ?? 'All' }}<br>
                                        Status: {{ $campaign->segment_status ?? 'All' }}
                                    </small>
                                </td>
                                <td>{{ $campaign->schedule_at ? \Carbon\Carbon::parse($campaign->schedule_at)->format('Y-m-d H:i') : '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $campaign->status == 'completed' ? 'success' : ($campaign->status == 'scheduled' ? 'info' : ($campaign->status == 'draft' ? 'secondary' : ($campaign->status == 'processing' ? 'primary' : 'warning'))) }}">
                                        {{ ucfirst($campaign->status) }}
                                    </span>
                                </td>
                                <td>{{ $campaign->target_count }}</td>
                                <td>{{ $campaign->sent_count }}</td>
                                <td>{{ $campaign->failed_count }}</td>
                                <td>
                                    <a href="{{ route('marketing.campaigns.edit', $campaign) }}" class="btn btn-sm btn-info">Edit/View</a>
                                    @if(!in_array($campaign->status, ['processing', 'completed']))
                                    <form action="{{ route('marketing.campaigns.destroy', $campaign) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirm delete?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No campaigns found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $campaigns->links() }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
