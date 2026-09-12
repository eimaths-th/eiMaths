<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('affiliate.registration.verification_expired_title') }} | eiMaths</title>
    <link rel="icon" href="{{ asset('images/cropped-footer-logo-32x32.png') }}" type="image/png">
    <style>
        :root {
            color-scheme: light;
            --brand: #f36c21;
            --brand-dark: #d8510b;
            --ink: #263548;
            --muted: #667085;
            --line: #e8eaed;
            --soft-orange: #fff4eb;
            --page: #f7f8fa;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px 18px;
            color: var(--ink);
            background:
                radial-gradient(circle at 10% 8%, rgba(243, 108, 33, 0.12), transparent 28rem),
                radial-gradient(circle at 92% 90%, rgba(138, 175, 61, 0.12), transparent 25rem),
                var(--page);
            font-family: "Leelawadee UI", "Noto Sans Thai", Tahoma, Arial, sans-serif;
        }

        .verification-card {
            width: min(100%, 570px);
            overflow: hidden;
            border: 1px solid rgba(38, 53, 72, 0.08);
            border-radius: 24px;
            background: var(--white);
            box-shadow: 0 24px 60px rgba(38, 53, 72, 0.12);
        }

        .brand-bar {
            height: 6px;
            background: linear-gradient(90deg, var(--brand), #ffb13b 62%, #8aaf3d);
        }

        .verification-content {
            padding: 42px 44px 40px;
            text-align: center;
        }

        .logo {
            width: 164px;
            height: auto;
            margin-bottom: 26px;
        }

        .status-icon {
            width: 78px;
            height: 78px;
            display: grid;
            place-items: center;
            margin: 0 auto 20px;
            border-radius: 50%;
            color: var(--brand);
            background: var(--soft-orange);
        }

        .status-icon svg {
            width: 38px;
            height: 38px;
        }

        .status-label {
            display: inline-flex;
            align-items: center;
            margin-bottom: 14px;
            padding: 7px 13px;
            border-radius: 999px;
            color: var(--brand-dark);
            background: var(--soft-orange);
            font-size: 14px;
            font-weight: 700;
        }

        h1 {
            margin: 0 0 14px;
            font-size: clamp(26px, 5vw, 34px);
            line-height: 1.25;
            letter-spacing: -0.02em;
        }

        .description {
            max-width: 450px;
            margin: 0 auto;
            color: var(--muted);
            font-size: 17px;
            line-height: 1.75;
        }

        .action-panel {
            margin-top: 28px;
            padding-top: 28px;
            border-top: 1px solid var(--line);
        }

        .action-hint {
            margin: 0 0 16px;
            color: var(--ink);
            font-size: 15px;
            line-height: 1.65;
        }

        .resend-form {
            margin: 0;
        }

        .primary-action,
        .secondary-action {
            min-height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform 160ms ease, box-shadow 160ms ease, background 160ms ease;
        }

        .primary-action {
            width: 100%;
            padding: 13px 22px;
            border: 0;
            color: var(--white);
            background: var(--brand);
            box-shadow: 0 10px 22px rgba(243, 108, 33, 0.24);
        }

        .primary-action:hover {
            transform: translateY(-1px);
            background: var(--brand-dark);
            box-shadow: 0 12px 26px rgba(243, 108, 33, 0.3);
        }

        .primary-action:focus-visible,
        .secondary-action:focus-visible {
            outline: 3px solid rgba(243, 108, 33, 0.28);
            outline-offset: 3px;
        }

        .secondary-action {
            margin-top: 18px;
            color: var(--muted);
        }

        .secondary-action:hover {
            color: var(--brand-dark);
        }

        .security-note {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin: 26px 0 0;
            padding: 13px 15px;
            border-radius: 12px;
            color: var(--muted);
            background: #f8f9fb;
            font-size: 13px;
            line-height: 1.55;
            text-align: left;
        }

        .security-note svg {
            width: 17px;
            min-width: 17px;
            margin-top: 2px;
            color: #8aaf3d;
        }

        @media (max-width: 575px) {
            body {
                padding: 18px 12px;
            }

            .verification-card {
                border-radius: 18px;
            }

            .verification-content {
                padding: 32px 22px 28px;
            }

            .logo {
                width: 142px;
                margin-bottom: 22px;
            }

            .description {
                font-size: 16px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .primary-action,
            .secondary-action {
                transition: none;
            }
        }
    </style>
</head>
<body>
    <main class="verification-card" aria-labelledby="verification-expired-title">
        <div class="brand-bar" aria-hidden="true"></div>
        <div class="verification-content">
            <img class="logo" src="{{ asset('images/Eimaths-Logo-Without-Halo.png') }}" alt="eiMaths">

            <div class="status-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M12 7v5l3 2"></path>
                </svg>
            </div>

            <div class="status-label">{{ __('affiliate.registration.verification_expired_badge') }}</div>
            <h1 id="verification-expired-title">{{ __('affiliate.registration.verification_expired_title') }}</h1>
            <p class="description">
                {{ __('affiliate.registration.verification_expired_message', ['minutes' => $expiration_minutes]) }}
            </p>

            <div class="action-panel">
                @if ($resend_route !== null)
                    <p class="action-hint">{{ __('affiliate.registration.verification_expired_resend_hint') }}</p>
                    <form id="resend-verification-form" class="resend-form" method="POST" action="{{ $resend_route }}">
                        @csrf
                        <button class="primary-action" type="submit">
                            {{ __('affiliate.registration.resend_verification') }}
                        </button>
                    </form>
                @else
                    <p class="action-hint">{{ __('affiliate.registration.verification_expired_login_hint') }}</p>
                    <a class="primary-action" href="{{ route('login') }}">
                        {{ __('affiliate.registration.go_to_login') }}
                    </a>
                @endif

                <a class="secondary-action" href="{{ route('home') }}">
                    {{ __('affiliate.registration.back_to_home') }}
                </a>
            </div>

            <p class="security-note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <path d="m9 12 2 2 4-4"></path>
                </svg>
                <span>{{ __('affiliate.registration.verification_security_note') }}</span>
            </p>
        </div>
    </main>
</body>
</html>
