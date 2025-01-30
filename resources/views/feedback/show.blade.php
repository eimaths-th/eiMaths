@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Feedback View Details</h1>

                    {{-- <h4>Users & Roles Management</h4> --}}
                </div>
                <div class="col-sm-6">
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-user"></i>View Feedback</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                {{-- <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                            <i class="fas fa-times"></i>
                        </button> --}}
                            </div>
                        </div>

                        <div class="card-body">
                            <h3>{{ $feedback->title }}</h3>
                            <p><strong>Description:</strong> {{ $feedback->description }}</p>
                            <p><strong>Category:</strong> {{ $feedback->category }}</p>
                            <p><strong>Status:</strong> {{ $feedback->status }}</p>
                            <p><strong>Priority:</strong> {{ $feedback->priority }}</p>
                            <p><strong>Submitted by:</strong> {{ $feedback->user->name ?? 'Anonymous' }}</p>
                            <p><strong>Admin Comments:</strong> {{ $feedback->admin_comments ?? 'None' }}</p>

                            @if ($feedback->attachment_path)
                                <p><strong>Attachment:</strong>
                                    <a href="{{ asset('storage/' . $feedback->attachment_path) }}" target="_blank">View
                                        Attachment</a>
                                </p>
                            @endif

                            @if (auth()->user()->hasRole('SystemAdmin'))
                                <!-- Assuming you're using roles -->
                                <div class="row">
                                    <div class="col-lg-4">
                                        <form action="{{ route('feedback.updateStatus', $feedback->id) }}" method="POST">
                                            @csrf
                                            <label for="status">Update Status:</label>
                                            <select class="form-control mb-3" name="status">
                                                <option value="New" {{ $feedback->status === 'New' ? 'selected' : '' }}>
                                                    New
                                                </option>
                                                <option value="In Progress"
                                                    {{ $feedback->status === 'In Progress' ? 'selected' : '' }}>In Progress
                                                </option>
                                                <option value="Resolved"
                                                    {{ $feedback->status === 'Resolved' ? 'selected' : '' }}>
                                                    Resolved</option>
                                            </select>

                                            <label for="admin_comments">Admin Comments:</label>
                                            <textarea class="form-control mb-3" name="admin_comments">{{ $feedback->admin_comments }}</textarea>

                                            <button class="btn btn-success mb-3" type="submit">Update Feedback</button>
                                        </form>
                                    </div>
                                </div>
                            @endif

                            <a class="btn btn-secondary" href="{{ route('feedback.index') }}">Back to Feedback List</a>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>
@endsection
