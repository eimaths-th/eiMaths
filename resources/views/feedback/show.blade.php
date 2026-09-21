@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('feedback-management.detail.page_title') }}</h1>

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
                            <h3 class="card-title"><i class="fa fa-user"></i>{{ __('feedback-management.detail.card_title') }}</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="{{ __('feedback-management.detail.collapse') }}">
                                    <i class="fas fa-minus"></i>
                                </button>
                                {{-- <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                            <i class="fas fa-times"></i>
                        </button> --}}
                            </div>
                        </div>

                        <div class="card-body">
                            <h3>{{ $feedback->title }}</h3>
                            <p><strong>{{ __('feedback-management.detail.fields.description') }}</strong> {{ $feedback->description }}</p>
                            <p><strong>{{ __('feedback-management.detail.fields.category') }}</strong> {{ __('feedback-management.categories.' . strtolower($feedback->category)) }}</p>
                            <p><strong>{{ __('feedback-management.detail.fields.status') }}</strong> {{ __('feedback-management.status.' . strtolower(str_replace(' ', '_', $feedback->status))) }}</p>
                            <p><strong>{{ __('feedback-management.detail.fields.priority') }}</strong> {{ __('feedback-management.priorities.' . strtolower($feedback->priority)) }}</p>
                            <p><strong>{{ __('feedback-management.detail.fields.submitted_by') }}</strong> {{ $feedback->user->name ?? __('feedback-management.detail.anonymous') }}</p>
                            <p><strong>{{ __('feedback-management.detail.fields.admin_comments') }}</strong> {{ $feedback->admin_comments ?? __('feedback-management.detail.none') }}</p>

                            @if ($feedback->attachment_path)
                                <p><strong>{{ __('feedback-management.detail.fields.attachment') }}</strong>
                                    <a href="{{ asset('storage/' . $feedback->attachment_path) }}" target="_blank">{{ __('feedback-management.detail.view_attachment') }}</a>
                                </p>
                            @endif

                            @if (auth()->user()->hasRole('SystemAdmin'))
                                <!-- Assuming you're using roles -->
                                <div class="row">
                                    <div class="col-lg-4">
                                        <form action="{{ route('feedback.updateStatus', $feedback->id) }}" method="POST">
                                            @csrf
                                            <label for="status">{{ __('feedback-management.detail.fields.update_status') }}</label>
                                            <select class="form-control mb-3" name="status">
                                                <option value="New" {{ $feedback->status === 'New' ? 'selected' : '' }}>
                                                    {{ __('feedback-management.detail.status_options.new') }}
                                                </option>
                                                <option value="In Progress"
                                                    {{ $feedback->status === 'In Progress' ? 'selected' : '' }}>{{ __('feedback-management.detail.status_options.in_progress') }}
                                                </option>
                                                <option value="Resolved"
                                                    {{ $feedback->status === 'Resolved' ? 'selected' : '' }}>{{ __('feedback-management.detail.status_options.resolved') }}</option>
                                            </select>

                                            <label for="admin_comments">{{ __('feedback-management.detail.fields.admin_comments') }}</label>
                                            <textarea class="form-control mb-3" name="admin_comments">{{ $feedback->admin_comments }}</textarea>

                                            <button class="btn btn-success mb-3" type="submit">{{ __('feedback-management.detail.update') }}</button>
                                        </form>
                                    </div>
                                </div>
                            @endif

                            <a class="btn btn-secondary" href="{{ route('feedback.index') }}">{{ __('feedback-management.detail.back') }}</a>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>
@endsection
