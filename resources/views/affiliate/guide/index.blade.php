@extends('layouts.app')

@section('content')
<main class="affiliate-portal" aria-labelledby="affiliate-guide-title">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 id="affiliate-guide-title" class="mb-0">{{ __('affiliate.guide.title') }}</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('affiliate.partials.navigation')

            <section class="affiliate-guide-hero mb-4" aria-labelledby="affiliate-guide-hero-title">
                <div class="affiliate-guide-hero-content">
                    <p class="affiliate-guide-eyebrow mb-2">{{ __('affiliate.guide.eyebrow') }}</p>
                    <h2 id="affiliate-guide-hero-title">{{ __('affiliate.guide.intro_title') }}</h2>
                    <p class="mb-3">{{ __('affiliate.guide.intro') }}</p>
                    <a class="btn btn-light" href="{{ route('affiliate.guide.document') }}" target="_blank" rel="noopener">
                        <i class="fas fa-file-pdf mr-1" aria-hidden="true"></i>{{ __('affiliate.guide.open_document') }}
                    </a>
                </div>
                <div class="affiliate-guide-hero-mark" aria-hidden="true">
                    <i class="fas fa-graduation-cap"></i>
                    <span>eiMaths</span>
                </div>
            </section>

            <div class="row">
                <div class="col-lg-7 mb-4">
                    <section class="card card-primary affiliate-card h-100" aria-labelledby="affiliate-guide-about-title">
                        <div class="card-header"><h2 id="affiliate-guide-about-title" class="card-title">{{ __('affiliate.guide.about_title') }}</h2></div>
                        <div class="card-body">
                            <p>{{ __('affiliate.guide.about') }}</p>
                            <dl class="affiliate-guide-facts mb-0">
                                <div>
                                    <dt>{{ __('affiliate.guide.company_facts.programs') }}</dt>
                                    <dd>{{ __('affiliate.guide.company_facts.programs_value') }}</dd>
                                </div>
                                <div>
                                    <dt>{{ __('affiliate.guide.company_facts.founded') }}</dt>
                                    <dd>{{ __('affiliate.guide.company_facts.founded_value') }}</dd>
                                </div>
                                <div>
                                    <dt>{{ __('affiliate.guide.company_facts.thailand_branches') }}</dt>
                                    <dd>{{ __('affiliate.guide.company_facts.thailand_branches_value') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </section>
                </div>
                <div class="col-lg-5 mb-4">
                    <section class="affiliate-guide-payment h-100" aria-labelledby="affiliate-guide-payment-title">
                        <div class="affiliate-guide-payment-icon" aria-hidden="true"><i class="fas fa-shield-alt"></i></div>
                        <div>
                            <h2 id="affiliate-guide-payment-title">{{ __('affiliate.guide.payment_notice_title') }}</h2>
                            <p class="mb-0">{{ __('affiliate.guide.payment_notice') }}</p>
                        </div>
                    </section>
                </div>
            </div>

            <section class="card card-primary affiliate-card mb-4" aria-labelledby="affiliate-guide-activities-title">
                <div class="card-header"><h2 id="affiliate-guide-activities-title" class="card-title">{{ __('affiliate.guide.what_we_do_title') }}</h2></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <ul class="affiliate-guide-list mb-0">
                                @foreach (__('affiliate.guide.what_we_do') as $activity)
                                    <li><i class="fas fa-check" aria-hidden="true"></i><span>{{ $activity }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-lg-6">
                            <h3 class="h5">{{ __('affiliate.guide.affiliate_role_title') }}</h3>
                            <ul class="affiliate-guide-list mb-0">
                                @foreach (__('affiliate.guide.affiliate_role') as $role)
                                    <li><i class="fas fa-user-check" aria-hidden="true"></i><span>{{ $role }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card card-primary affiliate-card mb-4" aria-labelledby="affiliate-guide-process-title">
                <div class="card-header"><h2 id="affiliate-guide-process-title" class="card-title">{{ __('affiliate.guide.process_title') }}</h2></div>
                <div class="card-body">
                    <ol class="affiliate-guide-process mb-0">
                        @foreach (__('affiliate.guide.process') as $step)
                            <li><span class="affiliate-guide-process-number" aria-hidden="true">{{ $loop->iteration }}</span><span>{{ $step }}</span></li>
                        @endforeach
                    </ol>
                </div>
            </section>

            <section class="card card-primary affiliate-card mb-4" aria-labelledby="affiliate-guide-document-title">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h2 id="affiliate-guide-document-title" class="card-title mb-2 mb-md-0">{{ __('affiliate.guide.document_title') }}</h2>
                    <a class="btn btn-light btn-sm" href="{{ route('affiliate.guide.document') }}" download>
                        <i class="fas fa-download mr-1" aria-hidden="true"></i>{{ __('affiliate.guide.download_document') }}
                    </a>
                </div>
                <div class="card-body">
                    <p>{{ __('affiliate.guide.document_description') }}</p>
                    <div class="affiliate-guide-document-frame">
                        <iframe src="{{ route('affiliate.guide.document') }}" title="{{ __('affiliate.guide.document_title') }}" loading="lazy"></iframe>
                    </div>
                </div>
                <div class="card-footer"><p class="mb-0">{{ __('affiliate.guide.contact') }}</p></div>
            </section>
        </div>
    </section>
</main>
@endsection

@section('style')
<link rel="stylesheet" href="{{ asset('css/affiliate-portal.css') }}">
@endsection
