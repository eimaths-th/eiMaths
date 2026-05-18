@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Marketing Campaigns</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('marketing-campaigns.create') }}" class="btn btn-primary">Create Campaign</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Schedule At</th>
                            <th>Target Segment</th>
                            <th>Stats (Target/Sent/Fail)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaigns as $campaign)
                            <tr>
                                <td>{{ $campaign->id }}</td>
                                <td>{{ $campaign->title }}</td>
                                <td>
                                    <span class="badge bg-{{ $campaign->status == 'completed' ? 'success' : ($campaign->status == 'scheduled' ? 'info' : 'secondary') }}">
                                        {{ ucfirst($campaign->status) }}
                                    </span>
                                </td>
                                <td>{{ $campaign->schedule_at ? \Carbon\Carbon::parse($campaign->schedule_at)->format('Y-m-d H:i') : '-' }}</td>
                                <td>
                                    @if($campaign->segment_level) Level: {{ $campaign->segment_level }}<br> @endif
                                    @if($campaign->segment_interest) Interest: {{ $campaign->segment_interest }}<br> @endif
                                    @if($campaign->segment_status) Status: {{ $campaign->segment_status }} @endif
                                </td>
                                <td>{{ $campaign->target_count }} / {{ $campaign->sent_count }} / {{ $campaign->failed_count }}</td>
                                <td>
                                    <a href="{{ route('marketing-campaigns.edit', $campaign->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('marketing-campaigns.destroy', $campaign->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No campaigns found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
