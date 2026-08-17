@extends('affiliate.auth.layouts.app')
@section('title', 'Waiting for Approval')
@section('content')

    <style>
        .affiliate-waiting-page .error_wrapper {
            padding: clamp(140px, 18vh, 220px) 24px clamp(160px, 20vh, 220px);
        }

        .affiliate-waiting-page .error_wrapper_info {
            max-width: 760px;
            margin: 0 auto;
        }

        .affiliate-waiting-page .error_wrapper_info h3 {
            margin-bottom: 12px;
        }

        .affiliate-waiting-page .error_wrapper_info h2 {
            margin-bottom: 0;
        }

        .affiliate-resend-panel {
            max-width: 560px;
            margin: 26px auto 0;
            padding: 20px 24px 22px;
            border: 1px solid rgba(32, 46, 59, 0.12);
            border-radius: 10px;
            background: #fff;
        }

        .affiliate-resend-panel p {
            margin: 0 0 18px;
            line-height: 1.6;
        }

        .affiliate-resend-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }

        .affiliate-resend-form .form-group {
            width: 100%;
            max-width: 420px;
            margin: 0;
            text-align: left;
        }

        .affiliate-resend-form .form-control {
            min-height: 44px;
        }

        .affiliate-resend-form .theme_btn {
            min-width: 220px;
            margin: 0;
        }

        .affiliate-resend-form .alert {
            width: 100%;
            margin: 0 0 2px;
        }

        @media (max-width: 575.98px) {
            .affiliate-waiting-page .error_wrapper {
                padding-right: 16px;
                padding-left: 16px;
            }

            .affiliate-resend-panel {
                padding-right: 16px;
                padding-left: 16px;
            }
        }
    </style>

    <div class="affiliate-waiting-page">
        <div class="error_wrapper">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-xl-12">
                    <div class="error_wrapper_info text-center">
                        <div class="thumb">
                            <img src="{{ asset('/public/infixlmstheme/')}}/img/banner/error_thumb.png" alt="">
                        </div>
                        <h3>{{ __('frontend.Thanks For Registration') }} !!!</h3>
                        <h2>
                            {{ __('frontend.Please Wait For Approval') }}
                        </h2>
                        @if (session('affiliate_email_verified'))
                            <div class="affiliate-resend-panel alert alert-success" role="status">
                                {{ __('affiliate.registration.verification_completed') }}
                            </div>
                        @elseif ($show_verified_waiting_message ?? false)
                            <div class="affiliate-resend-panel alert alert-success" role="status">
                                {{ __('affiliate.registration.verification_completed') }}
                            </div>
                        @endif
                        @if ($show_resend_verification ?? false)
                            <div class="affiliate-resend-panel">
                                @if (session('affiliate_verification_sent'))
                                    <div class="alert alert-success" role="status">
                                        {{ __('affiliate.registration.verification_sent') }}
                                    </div>
                                @endif
                                <p>{{ __('affiliate.registration.verification_pending') }}</p>
                                <form id="resend-verification-form" class="affiliate-resend-form" method="POST" action="{{ route('affiliate.verification.resend') }}">
                                    @csrf
                                    <button type="submit" class="btn theme_btn">
                                        {{ __('affiliate.registration.resend_verification') }}
                                    </button>
                                </form>
                            </div>
                        @elseif ($show_email_resend_fallback ?? false)
                            <div class="affiliate-resend-panel">
                                @if (session('affiliate_verification_sent'))
                                    <div class="alert alert-success" role="status">
                                        {{ __('affiliate.registration.verification_sent') }}
                                    </div>
                                @endif
                                <p>{{ __('affiliate.registration.email_fallback') }}</p>
                                <form id="resend-verification-form" class="affiliate-resend-form" method="POST" action="{{ route('affiliate.verification.resend') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label for="affiliate-verification-email">
                                            {{ __('affiliate.registration.email') }}
                                        </label>
                                        <input
                                            id="affiliate-verification-email"
                                            name="email"
                                            type="email"
                                            class="form-control"
                                            value="{{ old('email') }}"
                                            autocomplete="email"
                                            required
                                        >
                                        @error('email')
                                            <div class="text-danger mt-1" role="alert">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn theme_btn">
                                        {{ __('affiliate.registration.resend_verification') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                        <br>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

@endsection
