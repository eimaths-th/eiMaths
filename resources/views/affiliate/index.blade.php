@extends('affiliate.frontend.main')
@section('style')
    <style>
        .affiliate-capabilities {
            max-width: 760px;
            margin: 32px auto 0;
            padding: 20px 24px;
            border-left: 4px solid var(--system_primery_color);
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.88);
            text-align: left;
        }

        .affiliate-capabilities h2 {
            margin: 0 0 8px;
            color: var(--system_secendory_color);
            font-size: 22px;
            font-weight: 700;
            line-height: 1.4;
        }

        .affiliate_bradcam_area .breadcam_text .affiliate-capabilities p {
            margin: 0;
            color: #5b5675;
            font-size: 16px;
            font-weight: 400;
            line-height: 1.7;
        }

        @media only screen and (max-width: 767px) {
            .affiliate-capabilities {
                margin-top: 24px;
                padding: 18px 20px;
            }

            .affiliate-capabilities h2 {
                font-size: 20px;
            }
        }
    </style>
@endsection
@section('content')
    @if (request('ref'))
        @php
            $ref = request('ref');
            // dd(request('ref'), $ref);
        @endphp
    @else
        @php
            $ref = '';
            // dd(request('ref'), $ref);
        @endphp
    @endif
    <div id="content-area">
        <div class="full-page" data-type="component-text" data-preview="" data-aoraeditor-title="All Section"
            data-aoraeditor-categories="Affiliate Page">
            <div class="row">
                <div class="col-sm-12 ui-resizable" data-type="container-content">
                    <div data-aoraeditor-categories="Affiliate Page" data-aoraeditor-title="Affiliate Title"
                        data-preview="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/title.png') }}"
                        data-type="component-text">
                        <div class="affiliate_bradcam_area">
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-10 offset-lg-1">
                                        <div class="breadcam_text text-center"><br><span><span
                                                    style="font-size:28px;">{{ __('affiliate-management.website.hero_title') }}</span></span>
                                            <h4><strong>{{ __('affiliate-management.website.hero_subtitle', ['rate' => $affiliate_policy['commission_rate_percent']]) }}</strong></h4>
                                            <p>{{ __('affiliate-management.website.hero_description') }}</p>
                                            <a class="theme_btn" href="{{ route('affiliate.registration') }}">
                                                {{-- <a class="theme_btn" href="https://ecommerce.eimaths-th.com/affiliate/registration"> --}}
                                                {{ __('affiliate-management.website.join_action') }}</a>
                                            <section class="affiliate-capabilities"
                                                aria-labelledby="affiliate-capabilities-title">
                                                <h2 id="affiliate-capabilities-title">
                                                    {{ __('affiliate.landing.capabilities_title') }}</h2>
                                                @if ($affiliate_capabilities['analytics'])
                                                    <p>{{ __('affiliate.landing.analytics_enabled') }}</p>
                                                @endif
                                                @if ($affiliate_capabilities['media'])
                                                    <p>{{ __('affiliate.landing.media_enabled') }}</p>
                                                @endif
                                                @if (! $affiliate_capabilities['analytics'] && ! $affiliate_capabilities['media'])
                                                    <p>{{ __('affiliate.landing.analytics_unavailable_guidance') }}</p>
                                                @endif
                                            </section>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 ui-resizable" data-type="container-content">
                    <div data-aoraeditor-categories="Affiliate Page" data-aoraeditor-title="Affiliate Commission"
                        data-preview="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/commission_section.png') }}"
                        {{-- data-preview="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/commission_section.png" --}} data-type="component-text">
                        <div class="lms_section">
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-xl-10">
                                        <div class="commision_box">
                                            <div alt=""
                                                class="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/round.svg') }}">
                                                {{-- class="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/round.svg"> --}}
                                                &nbsp;</div>
                                            <div class="commision_left">
                                                <h4>{{ __('affiliate-management.website.commission_title') }}</h4>
                                                <p class="rate_text">{{ __('affiliate-management.website.eligible_tuition') }}</p>
                                                <h3>{{ $affiliate_policy['commission_rate_percent'] }}%</h3>
                                                <p class="Sale_text">{{ __('affiliate-management.website.order_rate') }}</p><a class="theme_btn small_btn2"
                                                    {{-- href="https://ecommerce.eimaths-th.com/my-affiliate"> --}} href="{{ route('affiliate.login') }}">
                                                    &nbsp;{{ __('affiliate-management.website.create_link') }}</a>
                                                <p class="discription_text">{{ __('affiliate-management.website.commission_description') }}</p>
                                            </div>
                                            <div class="commision_right flex-fill">
                                                <div class="commision_payment_lists">
                                                    <div class="commision_payment_list">
                                                        <div class="thumb"><img alt=""
                                                                src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_1.svg') }}">
                                                            {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_1.svg"> --}}
                                                        </div>
                                                        <div class="comission_payment_text">
                                                            <h4>{{ __('affiliate-management.website.payment_title') }}</h4>
                                                            <p>{{ __('affiliate-management.website.minimum_withdrawal', ['amount' => $affiliate_policy['minimum_withdrawal_baht']]) }}
                                                                @if ($affiliate_policy['is_withdrawal_approval_required'])
                                                                    {{ __('affiliate-management.website.approval_required') }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="commision_payment_list">
                                                        <div class="thumb"><img alt=""
                                                                src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_2.svg') }}">
                                                            {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_2.svg"> --}}
                                                        </div>
                                                        <div class="comission_payment_text">
                                                            <h4>{{ __('affiliate-management.website.rate_title') }}</h4>
                                                            <p>{{ __('affiliate-management.website.rate_description') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="commision_payment_list">
                                                        <div class="thumb"><img alt=""
                                                                src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_3.svg') }}">
                                                            {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/wallet_3.svg"> --}}
                                                        </div>
                                                        <div class="comission_payment_text">
                                                            <h4>{{ __('affiliate-management.website.schedule_title') }}</h4>
                                                            <p>{{ __('affiliate-management.website.schedule_description', ['days' => $affiliate_policy['commission_holding_days']]) }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <style type="text/css" data-type="component-text">
                        .about .mt-40 {
                            margin-top: 35px;
                        }

                        @media only screen and (max-width: 767px) {
                            .about .mt-40 {
                                margin-top: 0
                            }
                        }

                        .counter {
                            padding: 0 32px
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter {
                                padding: 0
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .counter {
                                padding: 0
                            }
                        }

                        .counter #counters {
                            position: relative;
                            z-index: 1
                        }

                        .counter #counters .row {
                            margin-top: -36px
                        }

                        @media (min-width: 480px) {
                            .counter #counters .col-sm-6 {
                                width: 50%
                            }
                        }

                        .counter-shape {
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            --width: 255px;
                            width: var(--width);
                            height: var(--width);
                            border-radius: 100%;
                            background-color: var(--system_primery_color)
                        }

                        @media only screen and (max-width: 767px) {
                            .counter-shape {
                                --width: 175px
                            }
                        }

                        .counter-shape::before,
                        .counter-shape::after {
                            content: "";
                            width: 100%;
                            height: 100%;
                            border-radius: 100%;
                            border: 1px dashed var(--system_primery_color);
                            position: absolute;
                            top: 0;
                            left: 0;
                            opacity: .3;
                        }

                        html[dir=rtl] .counter-shape::before,
                        html[dir=rtl] .counter-shape::after {
                            left: auto;
                            right: 0;
                        }

                        .counter-shape::before {
                            transform: scale(1.3)
                        }

                        .counter-shape::after {
                            transform: scale(1.75)
                        }

                        .counter-item {
                            padding: 40px;
                            border-radius: 24px;
                            position: relative;
                            z-index: 1;
                            background-color: #fff;
                            margin-top: 36px;
                            max-width: 270px;
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .counter-item {
                                padding: 30px
                            }
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter-item {
                                padding: 30px
                            }
                        }

                        @media only screen and (max-width: 767px) {
                            .counter-item {
                                margin-top: 24px !important;
                                padding: 24px;
                            }
                        }

                        @media only screen and (max-width: 479px) {
                            .counter-item {
                                max-width: 100%;
                            }
                        }

                        .counter-item-icon {
                            --icon: 80px;
                            width: var(--icon);
                            height: var(--icon);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            border-radius: 100%;
                            margin-bottom: 14px
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .counter-item-icon {
                                margin-bottom: 10px
                            }
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter-item-icon {
                                --icon: 70px
                            }
                        }

                        @media only screen and (max-width: 767px) {
                            .counter-item-icon {
                                --icon: 60px
                            }
                        }

                        .counter-item-icon>* {
                            width: 36px
                        }

                        @media only screen and (max-width: 767px) {
                            .counter-item-icon>* {
                                width: 24px
                            }
                        }

                        .counter-item label {
                            display: inline-flex
                        }

                        .counter-item h4 {
                            font-size: 32px;
                            line-height: 1.25;
                            line-height: 1;
                            margin-bottom: 5px
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter-item h4 {
                                font-size: 28px
                            }
                        }

                        @media only screen and (min-width: 768px) and (max-width: 991px) {
                            .counter-item h4 {
                                font-size: 26px
                            }
                        }

                        @media only screen and (max-width: 767px) {
                            .counter-item h4 {
                                font-size: 24px
                            }
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .counter-item h4 {
                                font-size: 21px
                            }
                        }

                        .counter-item p {
                            font-size: 20px;
                            line-height: 1.5;
                            color: currentColor;
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter-item p {
                                font-size: 18px
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .counter-item p {
                                font-size: 18px
                            }
                        }

                        .about-content {
                            position: relative;
                            padding-left: 50px
                        }

                        html[dir=rtl] .about-content {
                            padding-left: 0;
                            padding-right: 50px;
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .about-content {
                                padding-left: 20px
                            }

                            html[dir=rtl] .about-content {
                                padding-left: 0;
                                padding-right: 20px;
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .about-content {
                                margin-top: 40px;
                                padding-bottom: 20px;
                                padding-left: 0
                            }

                            html[dir=rtl] .about-content {
                                padding-right: 0;
                            }
                        }

                        @media only screen and (max-width: 767px) {
                            .about-content {
                                margin-top: 26px
                            }
                        }

                        .about-content::after {
                            content: "";
                            width: 100%;
                            height: 100%;
                            border-radius: 100%;
                            background-color: rgba(255, 255, 255, 0.2);
                            position: absolute;
                            top: 0;
                            left: 0;
                            z-index: -1;
                            filter: blur(150px)
                        }

                        html[dir=rtl] .about-content::after {
                            left: auto;
                            right: 0;
                        }

                        .about-content h3 {
                            font-size: 48px;
                            line-height: 1.25;
                            font-weight: 600;
                            margin-bottom: 24px
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .about-content h3 {
                                font-size: 42px
                            }
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .about-content h3 {
                                font-size: 36px
                            }
                        }

                        @media only screen and (min-width: 768px) and (max-width: 991px) {
                            .about-content h3 {
                                font-size: 32px
                            }
                        }

                        @media only screen and (max-width: 767px) {
                            .about-content h3 {
                                font-size: 28px
                            }
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .about-content h3 {
                                margin-bottom: 18px
                            }
                        }

                        @media only screen and (min-width: 768px) and (max-width: 991px) {
                            .about-content h3 {
                                font-size: 28px
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .about-content h3 {
                                margin-bottom: 16px
                            }
                        }

                        .about-content p {
                            color: var(--system_paragraph_color);
                            line-height: 1.5;
                            margin-bottom: 26px
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .about-content p {
                                margin-bottom: 16px
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .about-content p {
                                margin-bottom: 14px
                            }
                        }

                        @media only screen and (max-width: 479px) {
                            .about-content p {
                                font-size: 14px;
                                line-height: 1.683
                            }
                        }

                        .about-content ul {
                            width: 56%
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .about-content ul {
                                width: 66%
                            }
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .about-content ul {
                                width: 66%
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .about-content ul {
                                width: 100%;
                                margin-top: 20px
                            }
                        }

                        .about-content ul li {
                            color: var(--system_paragraph_color);
                            --width: 24px
                        }

                        .about-content ul li:not(:last-child) {
                            margin-bottom: 20px
                        }

                        .about-content ul li .icon {
                            color: #38485C;
                            width: var(--width);
                            height: var(--width);
                            flex: 0 0 auto;
                            position: relative;
                            top: 3px
                        }

                        .about-content ul li .icon img,
                        .about-content ul li .icon>* {
                            width: 100%;
                            height: 100%;
                            object-fit: contain;
                            position: absolute;
                            top: 0;
                            left: 0;
                            z-index: -1
                        }

                        html[dir=rtl] .about-content ul li .icon img,
                        html[dir=rtl] .about-content ul li .icon>* {
                            left: auto;
                            right: 0;
                        }

                        .about-content ul li .icon svg path {
                            fill: #CEE8FF;
                            stroke: var(--system_primery_color)
                        }

                        .about-content ul li .content {
                            width: calc(100% - var(--width));
                            flex: 0 0 auto;
                            padding-left: 18px
                        }

                        html[dir=rtl] .about-content ul li .content {
                            padding-left: 0;
                            padding-right: 18px;
                        }

                        .about-content a {
                            --btn-padding-y: 9px;
                            min-width: 120px
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .about-content a {
                                margin-top: 10px
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .about-content a {
                                margin-top: 10px
                            }
                        }
                    </style>
                    <div class="row">
                        <div class="col-sm-12 ui-resizable" data-type="container-content">
                            <div class="text-center" data-type="component-text">
                                <div class="photo-panel"><img class="w-60 h-60 img-cover"
                                        src="{{ asset('uploads/main/images/15-09-2024/66e7009b50afa.png') }}"
                                        {{-- src="https://ecommerce.eimaths-th.com/public/uploads/main/images/15-09-2024/66e7009b50afa.png" --}} style="height: 80%; width: 80%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="about section-margin-lg" data-type="component-text">
                        <div class="container">
                            <div class="row align-items-center">
                                <div class="col-lg-6">
                                    <div>
                                        <div class="row" id="counters">
                                            <div class="counter-shape">&nbsp;</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="about-content">
                                        <h4>&nbsp;</h4>
                                        <h4>&nbsp;</h4>
                                        <h4>&nbsp;</h4>
                                        <h4><strong>{{ __('affiliate-management.website.about_title') }}</strong></h4>
                                        <p>{{ __('affiliate-management.website.about_description') }}</p>
                                        <ul class="mb-4 mb-lg-5">
                                            <li class="d-flex flex-wrap"><span class="icon text-primary"><svg fill="none"
                                                        height="26" viewBox="0 0 26 26" width="26"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M13 25C19.6274 25 25 19.6274 25 13C25 6.37258 19.6274 1 13 1C6.37258 1 1 6.37258 1 13C1 19.6274 6.37258 25 13 25Z"
                                                            fill="#CEE8FF" stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"></path>
                                                        <path
                                                            d="M8.20312 14.2002L11.694 17.8002C13.3367 13.0814 14.7048 11.0108 17.8031 8.2002"
                                                            stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"></path>
                                                    </svg> </span> <span class="content"><span
                                                        style="color: var(--system_paragraph_color); background-color: var(--bs-body-bg); font-family: var(--font_family2); font-size: var(--bs-body-font-size); text-align: var(--bs-body-text-align);">{{ __('affiliate-management.website.dashboard_description') }}</span></span>
                                            </li>
                                            <li class="d-flex flex-wrap"><span class="icon text-primary"><svg
                                                        fill="none" height="26" viewBox="0 0 26 26" width="26"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M13 25C19.6274 25 25 19.6274 25 13C25 6.37258 19.6274 1 13 1C6.37258 1 1 6.37258 1 13C1 19.6274 6.37258 25 13 25Z"
                                                            fill="#CEE8FF" stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"></path>
                                                        <path
                                                            d="M8.20312 14.2002L11.694 17.8002C13.3367 13.0814 14.7048 11.0108 17.8031 8.2002"
                                                            stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"></path>
                                                    </svg> </span> <span class="content"><span
                                                        style="color: var(--system_paragraph_color); background-color: var(--bs-body-bg); font-family: var(--font_family2); font-size: var(--bs-body-font-size); text-align: var(--bs-body-text-align);">{{ __('affiliate-management.website.terms_description') }}</span></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 ui-resizable" data-type="container-content">
                    <div data-aoraeditor-categories="Affiliate Page"
                        data-aoraeditor-title="Affiliate How Does Work Section"
                        data-preview="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/how_work_section.png') }}"
                        {{-- data-preview="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/how_work_section.png" --}} data-type="component-text">
                        <div class="lms_section section_padding2 pb-0">
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-xl-10">
                                        <div class="work_title_area">
                                            <div class="section__title4 "><span class="subheading_text">{{ __('affiliate-management.website.steps_title') }}&nbsp;<span
                                                        style="color: var(--system_primery_color); background-color: var(--bs-body-bg); font-family: var(--font_family2); text-align: var(--bs-body-text-align);">?</span></span>
                                                <h4><strong>{{ __('affiliate-management.website.tracking_title') }}</strong></h4>
                                            </div>
                                            <div class="work_title_desc">
                                                <p>{{ __('affiliate-management.website.tracking_description') }}</p>
                                                <p>{{ __('affiliate-management.website.tracking_accounts') }}</p>
                                            </div>
                                        </div>
                                        <div class="row justify-content-center">
                                            <div class="col-xl-4 col-lg-4 col-md-6">
                                                <div class="process_widget_box text-center mb_30">
                                                    <div class="icon"><img alt=""
                                                            src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_1.svg') }}">
                                                        {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_1.svg"> --}}
                                                    </div>
                                                    <h4>{{ __('affiliate-management.website.step_register') }}</h4>
                                                    <p>{{ __('affiliate-management.website.step_register_text') }}</p>
                                                    <p>&nbsp;</p>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-6">
                                                <div class="process_widget_box text-center mb_30">
                                                    <div class="icon"><img alt=""
                                                            src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_2.svg') }}">
                                                        {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_2.svg"> --}}
                                                    </div>
                                                    <h4>{{ __('affiliate-management.website.step_verify') }}</h4>
                                                    <p>{{ __('affiliate-management.website.step_verify_text') }}</p>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-6">
                                                <div class="process_widget_box text-center mb_30">
                                                    <div class="icon"><img alt=""
                                                            src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_3.svg') }}">
                                                        {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/process_icon_3.svg"> --}}
                                                    </div>
                                                    <h4>{{ __('affiliate-management.website.step_active') }}</h4>
                                                    <p>{{ __('affiliate-management.website.step_active_text') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 ui-resizable" data-type="container-content">
                    <div data-aoraeditor-categories="Affiliate Page"
                        data-aoraeditor-title="Affiliate Rules & Requirement Section"
                        data-preview="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/rules_section.png') }}"
                        {{-- data-preview="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/rules_section.png" --}} data-type="component-text">
                        <div class="lms_section section_padding2 affiliate_bg">
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-xl-6 col-lg-8 col-md-8">
                                        <div class="section__title4 text-center margin_52"><span
                                                class="subheading_text">{{ __('affiliate-management.website.guidelines_label') }}</span>
                                            <h3>{{ __('affiliate-management.website.guidelines_title') }}
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="row justify-content-center">
                                    <div class="col-xl-10">
                                        <div class="Requirements_boxs mb_80">
                                            <div class="single_Requirements_box">
                                                <div class="Requirements_box_head">
                                                    <h3>{{ __('affiliate-management.website.do_title') }}</h3>
                                                </div>
                                                <div class="Requirements_box_body">
                                                    <ul>
                                                        <li>
                                                            <div class="icon"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg"> --}}
                                                            </div>
                                                            <p>{{ __('affiliate-management.website.do_items.0') }}</p>
                                                        </li>
                                                        <li>
                                                            <div class="icon"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg"> --}}
                                                            </div>
                                                            <p>{{ __('affiliate-management.website.do_items.1') }}</p>
                                                        </li>
                                                        <li>
                                                            <div class="icon"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg"> --}}
                                                            </div>
                                                            <p>{{ __('affiliate-management.website.do_items.2') }}</p>
                                                        </li>
                                                        <li>
                                                            <div class="icon"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg"> --}}
                                                            </div>
                                                            <p>{{ __('affiliate-management.website.do_items.3') }}</p>
                                                        </li>
                                                        <li>
                                                            <div class="icon"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/green_check.svg"> --}}
                                                            </div>
                                                            <p>{{ __('affiliate-management.website.do_items.4') }}</p>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="single_Requirements_box">
                                                <div class="Requirements_box_head style2">
                                                    <h3>{{ __('affiliate-management.website.dont_title') }}</h3>
                                                </div>
                                                <div class="Requirements_box_body">
                                                    <ul>
                                                        <li>
                                                            <div class="icon"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/red_cross.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/red_cross.svg"> --}}
                                                            </div>
                                                            <p>{{ __('affiliate-management.website.dont_items.0') }}</p>
                                                        </li>
                                                        <li>
                                                            <div class="icon"><img alt=""
                                                                    src="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/red_cross.svg') }}">
                                                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/red_cross.svg"> --}}
                                                            </div>
                                                            <p>{{ __('affiliate-management.website.dont_items.1') }}</p>
                                                        </li>
                                                        <li>
                                                            <div class="icon">&nbsp;</div>
                                                        </li>
                                                        <li>
                                                            <div class="icon">&nbsp;</div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="apply_btn text-center mb_30"><a
                                                class="theme_btn min_windth_200 text-center" {{-- href="https://ecommerce.eimaths-th.com/affiliate/registration"> --}}
                                                href=" {{ route('affiliate.registration') }} ">
                                                {{ __('affiliate-management.website.join_action') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 ui-resizable" data-type="container-content">
                    <div data-type="component-text">
                        <div class="breadcrumb_area bradcam_bg_1 position-relative">
                            <div class="breadcrumb_img w-100 h-100 position-absolute bottom-0 left-0 "><img alt=""
                                    class="w-100 h-100 img-cover"
                                    src="{{ asset('frontend/infixlmstheme/img/banner/bradcam_bg_1.jpg') }}">
                                {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/banner/bradcam_bg_1.jpg"> --}}
                            </div>
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-9 offset-lg-1">
                                        <div class="breadcam_wrap"><span>{{ __('affiliate-management.website.contact_label') }}</span>
                                            <h3>{{ __('affiliate-management.website.contact_title') }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- test contact --}}

                    {{-- end test contact --}}
                    <style type="text/css" data-type="component-text">
                        .bg-deep-green {
                            background-color: #6CB552 !important
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter .row {
                                margin-top: -70px
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .counter .row {
                                margin-top: -70px
                            }
                        }

                        @media only screen and (max-width: 767px) {
                            .counter>.container {
                                max-width: 92%
                            }
                        }

                        .counter-item {
                            padding: 40px;
                            border-radius: 40px;
                            position: relative;
                            z-index: 1;
                            max-width: 300px;
                        }

                        html[dir=rtl] .counter-item::before,
                        html[dir=rtl] .counter-item::after {
                            left: auto;
                            right: 0;
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .counter-item {
                                padding: 30px
                            }
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter-item {
                                margin-top: 70px
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .counter-item {
                                margin-top: 70px
                            }
                        }

                        .counter-item::before,
                        .counter-item::after {
                            content: "";
                            width: 100%;
                            height: 100%;
                            border-radius: 40px;
                            position: absolute;
                            top: 0;
                            left: 0;
                            transform: rotate(5deg);
                            z-index: -1;
                            background-color: #EEE6F5;
                            opacity: .4
                        }

                        html[dir=rtl] .counter-item::before,
                        html[dir=rtl] .counter-item::after {
                            left: auto;
                            right: 0;
                        }

                        .counter-item::after {
                            z-index: -2;
                            opacity: .3;
                            transform: rotate(10deg)
                        }

                        .counter-item.bg-primary::before,
                        .counter-item.bg-primary::after {
                            background-color: var(--system_primery_color)
                        }

                        .counter-item.bg-deep-green::before,
                        .counter-item.bg-deep-green::after {
                            background-color: #6CB552
                        }

                        .counter-item.bg-orange::before,
                        .counter-item.bg-orange::after {
                            background-color: #FEB74C
                        }

                        .counter-item.bg-green::before,
                        .counter-item.bg-green::after {
                            background-color: #16CE8C
                        }

                        .counter-item-icon {
                            margin-bottom: 1rem
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .counter-item-icon {
                                margin-bottom: 10px
                            }
                        }

                        .counter-item label {
                            display: inline-flex
                        }

                        .counter-item h4 {
                            margin-bottom: 16px;
                            font-size: 24px;
                            line-height: 1
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter-item h4 {
                                font-size: 22px
                            }
                        }

                        @media only screen and (max-width: 991px) {
                            .counter-item h4 {
                                font-size: 20px
                            }
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .counter-item h4 {
                                margin-bottom: 10px;
                                font-size: 21px
                            }
                        }

                        .counter-item h4 span {
                            display: block;
                            font-size: 36px;
                            line-height: 1.52778;
                            opacity: .8
                        }

                        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
                            .counter-item h4 span {
                                font-size: 32px
                            }
                        }

                        @media only screen and (min-width: 992px) and (max-width: 1279px) {
                            .counter-item h4 span {
                                font-size: 30px
                            }
                        }

                        @media only screen and (min-width: 768px) and (max-width: 991px) {
                            .counter-item h4 span {
                                font-size: 28px
                            }
                        }

                        @media only screen and (max-width: 767px) {
                            .counter-item h4 span {
                                font-size: 24px
                            }
                        }

                        .counter-item p {
                            color: #fff;
                        }
                    </style>
                    <div class="about section-margin" data-type="component-text">
                        <div class="container">
                            <div class="row" id="counters">
                                <div class="col-xl-3 col-md-6">
                                    <div class="counter-item bg-primary text-white">
                                        <div class="counter-item-icon"><svg fill="none" height="37"
                                                viewBox="0 0 37 37" width="37" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M18.5726 34.4966C16.7247 34.4966 16.7091 33.1522 15.1171 32.0052C12.3478 30.0101 8.09093 27.6623 4.70159 27.2026C4.09404 27.1354 3.53292 26.8455 3.12654 26.3889C2.72016 25.9323 2.49731 25.3413 2.50102 24.73V4.82601C2.501 4.46888 2.57834 4.11597 2.72773 3.79158C2.87712 3.46719 3.09501 3.17901 3.36641 2.94687C3.63303 2.71896 3.9451 2.55037 4.28187 2.45233C4.61865 2.35428 4.97244 2.329 5.31973 2.37819C10.4238 3.2253 15.093 5.76898 18.5726 9.59803V34.4966Z"
                                                    stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3"></path>
                                                <path
                                                    d="M18.5724 34.4966C20.4203 34.4966 20.4359 33.1522 22.0279 32.0052C24.7973 30.0101 29.0541 27.6623 32.4434 27.2026C33.051 27.1354 33.6121 26.8455 34.0185 26.3889C34.4249 25.9323 34.6477 25.3413 34.644 24.73V4.82601C34.644 4.46888 34.5667 4.11597 34.4173 3.79158C34.2679 3.46719 34.05 3.17901 33.7786 2.94687C33.512 2.71896 33.1999 2.55037 32.8631 2.45233C32.5264 2.35428 32.1726 2.329 31.8253 2.37819C26.7212 3.2253 22.052 5.76898 18.5724 9.59803V34.4966Z"
                                                    stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3"></path>
                                            </svg></div>
                                        <h4 class="currentColor"><span class="d-flex align-items-center">1</span>
                                            {{ __('affiliate-management.website.policy_cards.0.title') }}</h4>
                                        <p>{{ __('affiliate-management.website.policy_cards.0.description') }}</p>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="counter-item bg-deep-green text-white">
                                        <div class="counter-item-icon"><svg fill="none" height="37"
                                                viewBox="0 0 40 37" width="40" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M27.1709 18.7017C30.9915 18.7017 34.0887 15.6045 34.0887 11.7839C34.0887 8.82843 32.2354 6.30583 29.6275 5.31494"
                                                    stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3"></path>
                                                <path
                                                    d="M15.5303 17.3302C19.6718 17.3302 23.0292 13.9729 23.0292 9.83137C23.0292 5.68987 19.6718 2.33252 15.5303 2.33252C11.3888 2.33252 8.03149 5.68987 8.03149 9.83137C8.03149 13.9729 11.3888 17.3302 15.5303 17.3302Z"
                                                    stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3"></path>
                                                <path
                                                    d="M27.0165 27.8659C30.6846 30.3089 28.2077 35.0208 23.8006 35.0208H7.25036C2.8432 35.0208 0.36634 30.3089 4.0344 27.8659C7.32457 25.6746 11.2759 24.3975 15.5255 24.3975C19.7751 24.3975 23.7264 25.6746 27.0165 27.8659Z"
                                                    stroke="white" stroke-width="3"></path>
                                                <path
                                                    d="M36.6195 28.4199L35.788 29.6684L36.6195 28.4199ZM23.1628 33.5205C22.3344 33.5205 21.6628 34.192 21.6628 35.0205C21.6628 35.8489 22.3344 36.5205 23.1628 36.5205V33.5205ZM34.2513 25.4271C33.4917 25.0965 32.6079 25.4443 32.2774 26.2039C31.9468 26.9635 32.2946 27.8473 33.0542 28.1779L34.2513 25.4271ZM23.1628 36.5205H33.6527V33.5205H23.1628V36.5205ZM33.6527 36.5205C36.2184 36.5205 38.3575 35.1415 39.2822 33.2141C39.7481 32.2428 39.9061 31.1179 39.6022 30.0064C39.2959 28.8864 38.5558 27.9073 37.451 27.1715L35.788 29.6684C36.3752 30.0594 36.6185 30.4688 36.7084 30.7977C36.8007 31.1351 36.7678 31.5195 36.5773 31.9165C36.189 32.726 35.1528 33.5205 33.6527 33.5205V36.5205ZM33.0542 28.1779C34.0124 28.5949 34.9267 29.0947 35.788 29.6684L37.451 27.1715C36.4436 26.5005 35.3735 25.9155 34.2513 25.4271L33.0542 28.1779Z"
                                                    fill="white"></path>
                                            </svg></div>
                                        <h4 class="currentColor"><span class="d-flex align-items-center"><span
                                                    class="counter p-0 m-0 ">2</span></span>{{ __('affiliate-management.website.policy_cards.1.title') }}</h4>
                                        <p>{{ __('affiliate-management.website.policy_cards.1.description') }}</p>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="counter-item bg-orange text-white">
                                        <div class="counter-item-icon"><svg fill="none" height="40"
                                                viewBox="0 0 40 40" width="40" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M20 38C29.9411 38 38 29.9411 38 20C38 10.0589 29.9411 2 20 2C10.0589 2 2 10.0589 2 20C2 29.9411 10.0589 38 20 38Z"
                                                    stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3"></path>
                                                <path
                                                    d="M8.23064 26.9231C9.51592 26.9231 10.7486 26.4125 11.6574 25.5037C12.5662 24.5948 13.0768 23.3622 13.0768 22.0769V17.9231C13.0768 16.6378 13.5874 15.4051 14.4962 14.4963C15.405 13.5875 16.6377 13.0769 17.9229 13.0769C19.2082 13.0769 20.4409 12.5663 21.3497 11.6575C22.2585 10.7487 22.7691 9.51604 22.7691 8.23076V2.21168C21.8665 2.07231 20.9417 2 20 2C10.0589 2 2 10.0589 2 20C2 22.4533 2.49078 24.7919 3.37953 26.9231H8.23064Z"
                                                    stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3"></path>
                                                <path
                                                    d="M37.9978 19.722C36.6117 19.0032 35.0748 18.6238 33.5134 18.6152H27.615C26.3297 18.6152 25.097 19.1258 24.1882 20.0346C23.2794 20.9435 22.7688 22.1761 22.7688 23.4614C22.7688 24.7467 23.2794 25.9793 24.1882 26.8881C25.097 27.797 26.3297 28.3075 27.615 28.3075C28.533 28.3075 29.4135 28.6722 30.0626 29.3214C30.7118 29.9706 31.0765 30.851 31.0765 31.7691V34.1783H31.0904C35.2707 30.9039 37.9663 25.8208 37.9996 20.1069V19.8928C37.9992 19.8358 37.9986 19.7789 37.9978 19.722Z"
                                                    stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3"></path>
                                            </svg></div>
                                        <h4 class="currentColor"><span class="d-flex align-items-center">3</span>
                                            {{ __('affiliate-management.website.policy_cards.2.title') }}</h4>
                                        <p>{{ __('affiliate-management.website.policy_cards.2.description') }}</p>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="counter-item bg-green text-white">
                                        <div class="counter-item-icon"><svg fill="none" height="36"
                                                viewBox="0 0 40 36" width="40" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M20.25 6.86275L19.0872 7.8104C19.3721 8.15992 19.7991 8.36275 20.25 8.36275C20.7009 8.36275 21.1279 8.15992 21.4127 7.8104L20.25 6.86275ZM2.25 11.9473L0.750164 11.9251C0.750055 11.9325 0.75 11.9399 0.75 11.9473H2.25ZM38.25 11.9473H39.75C39.75 11.9399 39.7499 11.9325 39.7498 11.9251L38.25 11.9473ZM3.74984 11.9695C3.80301 8.37207 5.86454 5.0043 8.66757 3.69812C10.0351 3.06085 11.6143 2.89066 13.3317 3.4358C15.0693 3.98734 17.0448 5.30437 19.0872 7.8104L21.4127 5.91511C19.1068 3.08573 16.6732 1.34896 14.2393 0.576395C11.7853 -0.202583 9.43065 0.032806 7.40043 0.978862C3.40788 2.83934 0.817805 7.34926 0.750164 11.9251L3.74984 11.9695ZM20.25 32.6024C20.1152 32.6024 19.7289 32.5356 19.0552 32.2606C18.4195 32.0012 17.6408 31.6037 16.7661 31.0708C15.018 30.0057 12.967 28.449 11.0133 26.5203C7.06408 22.6216 3.75 17.4461 3.75 11.9473H0.75C0.75 18.6163 4.71522 24.5183 8.90565 28.6552C11.0218 30.7443 13.2557 32.4449 15.2052 33.6327C16.1793 34.2262 17.1026 34.704 17.9217 35.0383C18.7029 35.3571 19.5244 35.6024 20.25 35.6024V32.6024ZM39.7498 11.9251C39.6822 7.34925 37.0921 2.83934 33.0995 0.978861C31.0693 0.0328063 28.7147 -0.202582 26.2606 0.576394C23.8267 1.34896 21.3932 3.08573 19.0872 5.91511L21.4127 7.8104C23.4551 5.30437 25.4307 3.98734 27.1683 3.4358C28.8856 2.89066 30.4648 3.06085 31.8324 3.69812C34.6354 5.0043 36.697 8.37207 36.7502 11.9695L39.7498 11.9251ZM20.25 35.6024C20.9755 35.6024 21.797 35.3571 22.5782 35.0383C23.3973 34.704 24.3206 34.2262 25.2948 33.6327C27.2443 32.4449 29.4782 30.7443 31.5943 28.6552C35.7848 24.5184 39.75 18.6163 39.75 11.9473H36.75C36.75 17.4461 33.4359 22.6216 29.4867 26.5203C27.533 28.449 25.482 30.0057 23.7338 31.0708C22.8592 31.6037 22.0805 32.0012 21.4447 32.2606C20.771 32.5356 20.3848 32.6024 20.25 32.6024V35.6024Z"
                                                    fill="white"></path>
                                            </svg></div>
                                        <h4 class="currentColor"><span class="d-flex align-items-center"><span
                                                    class="counter p-0 m-0 ">4</span></span>{{ __('affiliate-management.website.policy_cards.3.title') }}
                                        </h4>
                                        <p>{{ __('affiliate-management.website.policy_cards.3.description') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modules_area section__padding_custom" data-type="component-text">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-6 col-md-8">
                                    <h3>&nbsp;</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="modile_main_wrap">
                                        <div class="single_module text-center">
                                            <p style="text-align: left;"><a href="javascript:void(0)">&nbsp;</a>{{ __('affiliate-management.website.policy_sections.0', [
                                                'days' => $affiliate_policy['attribution_duration_days'],
                                                'rate' => $affiliate_policy['commission_rate_percent'],
                                                'holding' => $affiliate_policy['commission_holding_days'],
                                                'minimum' => $affiliate_policy['minimum_withdrawal_baht'],
                                                'approval' => $affiliate_policy['is_withdrawal_approval_required'] ? __('affiliate-management.website.approval_required') : '',
                                            ]) }}</p>
                                        </div>
                                        <div class="single_module text-center">
                                            <div style="text-align: left;">{{ __('affiliate-management.website.policy_sections.1') }}</div>
                                        </div>
                                        <div class="single_module text-center" style="text-align: left;">
                                            <div style="text-align: left;">{{ __('affiliate-management.website.policy_sections.2') }}</div>
                                        </div>
                                        <div class="single_module text-center">
                                            <p style="text-align: left;">{{ __('affiliate-management.website.policy_sections.3') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 ui-resizable" data-type="container-content">
                    <div data-aoraeditor-categories="Affiliate Page"
                        data-aoraeditor-title="Affiliate Frequently Asked Questions Section"
                        data-preview="{{ asset('frontend/infixlmstheme/img/snippets/preview/affiliate/frequntlya_ask_section.png') }}"
                        {{-- data-preview="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/snippets/preview/affiliate/frequntlya_ask_section.png" --}} data-type="component-text">
                        <div class="affliate_faq-section">
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-xl-6 col-lg-8 col-md-8">
                                        <div class="section__title4 text-center margin_52"><span
                                                class="subheading_text">{{ __('affiliate-management.website.faq_label') }}</span>
                                            <h4><strong>{{ __('affiliate-management.website.faq_title') }}</strong></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xl-4 col-lg-4 col-md-6">
                                        <div class="affiliate_faq_box mb_25">
                                            <h4>{{ __('affiliate-management.website.faq_items.0.title') }}</h4>
                                            <p>{{ __('affiliate-management.website.faq_items.0.description') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6">
                                        <div class="affiliate_faq_box mb_25">
                                            <h4>{{ __('affiliate-management.website.faq_items.1.title') }}</h4>
                                            <p>{{ __('affiliate-management.website.faq_items.1.description') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6">
                                        <div class="affiliate_faq_box mb_25">
                                            <h4>{{ __('affiliate-management.website.faq_items.2.title') }}</h4>
                                            <p>{{ __('affiliate-management.website.faq_items.2.description') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6">
                                        <div class="affiliate_faq_box mb_25">
                                            <h4>{{ __('affiliate-management.website.faq_items.3.title') }}</h4>
                                            <p>{{ __('affiliate-management.website.faq_items.3.description') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6">
                                        <div class="affiliate_faq_box mb_25">
                                            <h4>{{ __('affiliate-management.website.faq_items.4.title') }}</h4>
                                            <p>{{ __('affiliate-management.website.faq_items.4.description') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-6">
                                        <div class="affiliate_faq_box mb_25">
                                            <h4>{{ __('affiliate-management.website.faq_items.5.title') }}</h4>
                                            <p>{{ __('affiliate-management.website.faq_items.5.description') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div data-type="component-text">
                    <div class="breadcrumb_area bradcam_bg_1 position-relative">
                        <div class="breadcrumb_img w-100 h-100 position-absolute bottom-0 left-0 "><img alt=""
                                class="w-100 h-100 img-cover"
                                src="{{ asset('frontend/infixlmstheme/img/banner/bradcam_bg_1.jpg') }}">
                            {{-- src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/banner/bradcam_bg_1.jpg"> --}}
                        </div>
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-9 offset-lg-1">
                                    <div class="breadcam_wrap"><span>{{ __('affiliate-management.website.contact_us') }}</span>
                                        <h3>{{ __('affiliate-management.website.contact_title_short') }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="contact_section " data-type="component-text">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-xl-10">
                                <div class="contact_address">
                                    <div class="row justify-content-center">
                                        <div class="col-xl-12">
                                            <div class="row justify-content-between">
                                                <div class="col-lg-5">
                                                    <div class="contact_info mb_30">
                                                        <h4 class="mb-0">{{ __('affiliate-management.website.contact_info') }}</h4>
                                                        <div class="address_lines">
                                                            <div class="single_address_line d-flex">
                                                                <div class="address_info">
                                                                    <p>The Crystal SB ราชพฤกษ์ ชั้น 3, 555/9
                                                                        อำเภอบางกรวย จังหวัดนนทบุรี 11130</p>
                                                                </div>
                                                            </div>
                                                            <div class="single_address_line d-flex">
                                                                <div class="address_info">
                                                                    <p>0 616-208-666</p>
                                                                </div>
                                                            </div>
                                                            <div class="single_address_line d-flex">
                                                                <div class="address_info">
                                                                    <p>info@eimaths-th.com</p>
                                                                    <p>{{ __('affiliate-management.website.contact_email_hint') }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
@endsection
