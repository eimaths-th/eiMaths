@inject('recaptcha_verifier', 'App\Support\Recaptcha\RecaptchaVerifier')

@if ($recaptcha_verifier->IsEnabled())
    <input type="hidden" name="recaptcha_token" value="">
    <p style="margin: 12px 0 0; font-size: 13px; color: #64748b; text-align: center;">{{ __('students.login.recaptcha_notice') }}</p>

    @once
        @push('scripts')
            <script src="https://www.google.com/recaptcha/api.js?render={{ urlencode($recaptcha_verifier->SiteKey()) }}" async defer></script>
            <script>
                (function () {
                    const RECAPTCHA_TIMEOUT_MILLISECONDS = 10000;
                    const RECAPTCHA_SITE_KEY = @json($recaptcha_verifier->SiteKey());

                    function RequestToken(recaptcha_action) {
                        return new Promise(function (resolve) {
                            const timeout_id = setTimeout(function () {
                                resolve('');
                            }, RECAPTCHA_TIMEOUT_MILLISECONDS);

                            function FinishWithToken(recaptcha_token) {
                                clearTimeout(timeout_id);
                                resolve(recaptcha_token || '');
                            }

                            function FinishWithoutToken() {
                                FinishWithToken('');
                            }

                            try {
                                grecaptcha.ready(function () {
                                    try {
                                        Promise.resolve(grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: recaptcha_action }))
                                            .then(FinishWithToken, FinishWithoutToken);
                                    } catch (execute_error) {
                                        FinishWithoutToken();
                                    }
                                });
                            } catch (ready_error) {
                                FinishWithoutToken();
                            }
                        });
                    }

                    document.querySelectorAll('form[data-recaptcha-action]').forEach(function (protected_form) {
                        const token_input = protected_form.querySelector('input[name="recaptcha_token"]');
                        let is_token_pending = false;

                        protected_form.addEventListener('submit', function (submit_event) {
                            submit_event.preventDefault();
                            if (is_token_pending) {
                                return;
                            }
                            is_token_pending = true;

                            RequestToken(protected_form.dataset.recaptchaAction).then(function (recaptcha_token) {
                                token_input.value = recaptcha_token;
                                protected_form.submit();
                            });
                        });

                        window.addEventListener('pageshow', function (page_show_event) {
                            if (!page_show_event.persisted) {
                                return;
                            }
                            is_token_pending = false;
                            token_input.value = '';
                        });
                    });
                })();
            </script>
        @endpush
    @endonce
@endif
