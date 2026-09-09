@extends('layouts.app')

@section('content')
<main class="affiliate-portal" aria-labelledby="affiliate-employee-access-title">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 id="affiliate-employee-access-title" class="mb-0">{{ __('affiliate.employee_access.title') }}</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @if (session('status'))
                <div class="alert alert-success" role="status" aria-live="polite">
                    {{ session('status') }}
                </div>
            @endif

            <section class="card card-primary affiliate-card" aria-labelledby="affiliate-employee-access-card-title">
                <div class="card-header">
                    <h2 id="affiliate-employee-access-card-title" class="card-title">{{ __('affiliate.employee_access.status_region') }}</h2>
                </div>

                <div class="card-body">
                    <p>{{ __('affiliate.employee_access.introduction') }}</p>

                    <div class="affiliate-status-panel" role="status" aria-live="polite" aria-label="{{ __('affiliate.employee_access.status_region') }}">
                        @switch($affiliate_profile?->status?->value)
                            @case('PENDING_EMAIL')
                                <p class="mb-3">{{ __('affiliate.employee_access.pending_email') }}</p>
                                @include('affiliate.employee-access-request-form')
                                @break

                            @case('PENDING_APPROVAL')
                                <p class="mb-0">{{ __('affiliate.employee_access.pending_approval') }}</p>
                                @break

                            @case('ACTIVE')
                                <p class="mb-3">{{ __('affiliate.employee_access.active') }}</p>
                                <a class="btn btn-primary" href="{{ route('affiliate.dashboard') }}">
                                    {{ __('affiliate.employee_access.open_dashboard') }}
                                </a>
                                @break

                            @case('SUSPENDED')
                                <p class="mb-2">{{ __('affiliate.employee_access.suspended') }}</p>
                                <p class="mb-0">{{ __('affiliate.employee_access.contact_administrator') }}</p>
                                @break

                            @case('DEACTIVATED')
                                <p class="mb-2">{{ __('affiliate.employee_access.deactivated') }}</p>
                                <p class="mb-0">{{ __('affiliate.employee_access.contact_administrator') }}</p>
                                @break

                            @default
                                <p class="mb-3">{{ __('affiliate.employee_access.no_profile') }}</p>
                                @include('affiliate.employee-access-request-form')
                        @endswitch
                    </div>
                </div>

                <div class="card-footer">
                    <span class="text-muted">{{ __('affiliate.employee_access.status_region') }}</span>
                </div>
            </section>
        </div>
    </section>
</main>
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('css/affiliate-portal.css') }}?v={{ filemtime(public_path('css/affiliate-portal.css')) }}">
@endsection
