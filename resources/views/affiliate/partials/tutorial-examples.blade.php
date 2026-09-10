{{-- Readable examples use the portal's labels and controls without personal records or submittable forms. --}}
@php
    $example_amount = app(\App\Domain\Affiliate\Services\AffiliateConfigurationService::class)->MinimumWithdrawalMinor() / 100;
    $example_format = fn ($value) => number_format($value, 2);
@endphp
@foreach (['link', 'share', 'wallet', 'commission', 'promptpay', 'bank', 'accounts', 'withdraw', 'history'] as $example_key)
    <template id="affiliate-example-{{ $example_key }}">
        <figure class="affiliate-example">
            <figcaption><span class="affiliate-example__badge">{{ __('affiliate_tutorial.labels.example') }}</span><strong>{{ __('affiliate_tutorial.examples.' . $example_key) }}</strong></figcaption>
            <div class="affiliate-example__screen">
                @if ($example_key === 'link')
                    <div class="affiliate-example__note"><b>1</b><span>{{ __('affiliate.dashboard.copy_referral_link') }}</span></div>
                    <div class="affiliate-example__success">✓ {{ __('affiliate_tutorial.examples.copied') }}</div>
                    <div class="affiliate-example__note"><b>2</b><span>LINE / Facebook</span></div>
                    <div class="affiliate-example__message">{{ __('affiliate_tutorial.examples.message') }}<br><span class="affiliate-example__link">[{{ __('affiliate_tutorial.examples.sample_link') }}]</span></div>
                @elseif ($example_key === 'share')
                    <div class="affiliate-example__bar">{{ __('affiliate.media.share_panel_title') }}</div>
                    <p>{{ __('affiliate.media.share_to') }}</p>
                    <div class="affiliate-example__channels"><span><i class="fab fa-facebook" aria-hidden="true"></i> Facebook</span><span><i class="fab fa-line" aria-hidden="true"></i> LINE</span><span><i class="fas fa-share-alt" aria-hidden="true"></i> {{ __('affiliate.media.share_device') }}</span></div>
                    <div class="affiliate-example__action">{{ __('affiliate.media.download') }}</div>
                @elseif ($example_key === 'wallet')
                    <div class="affiliate-example__balance"><span>{{ __('affiliate.dashboard.available_balance') }}</span><strong>{{ $example_format($example_amount * 2) }}</strong></div>
                    <div class="affiliate-example__balance is-reserved"><span>{{ __('affiliate.dashboard.reserved_balance') }}</span><strong>{{ $example_format($example_amount) }}</strong></div>
                    <p class="affiliate-example__caption">{{ __('affiliate_tutorial.examples.amount_note') }}</p>
                @elseif ($example_key === 'commission')
                    @foreach (['PENDING' => 'pending', 'HELD' => 'held', 'AVAILABLE' => 'available', 'REVERSED' => 'reversed'] as $status => $explanation)
                        <div class="affiliate-example__record"><span class="affiliate-status affiliate-status--{{ strtolower($status) }}">{{ __('affiliate.statuses.commission.' . $status) }}</span><span>{{ __('affiliate_tutorial.examples.' . $explanation . '_help') }}</span></div>
                    @endforeach
                @elseif (in_array($example_key, ['promptpay', 'bank'], true))
                    <div class="affiliate-example__bar">{{ __('affiliate.payout_accounts.' . ($example_key === 'promptpay' ? 'promptpay' : 'bank_account')) }}</div>
                    @if ($example_key === 'promptpay')
                        <div class="affiliate-example__field"><span class="affiliate-example__number">1</span><label>{{ __('affiliate.payout_accounts.promptpay_number') }}</label><div>08x-xxx-1234</div></div>
                    @else
                        <div class="affiliate-example__field"><span class="affiliate-example__number">1</span><label>{{ __('affiliate.payout_accounts.bank_name') }}</label><div>{{ __('affiliate_tutorial.examples.bank_name') }}</div></div>
                        <div class="affiliate-example__field"><span class="affiliate-example__number">2</span><label>{{ __('affiliate.payout_accounts.account_name') }}</label><div>{{ __('affiliate_tutorial.examples.account_name') }}</div></div>
                        <div class="affiliate-example__field"><span class="affiliate-example__number">3</span><label>{{ __('affiliate.payout_accounts.account_number') }}</label><div>xxx-x-xx123-4</div></div>
                    @endif
                    <div class="affiliate-example__field"><label>{{ __('affiliate_tutorial.examples.password') }}</label><div>••••••••</div></div>
                    <div class="affiliate-example__action">{{ __('affiliate.payout_accounts.' . ($example_key === 'promptpay' ? 'save_promptpay' : 'save_bank')) }}</div>
                    <div class="affiliate-example__success">✓ {{ __('affiliate_tutorial.examples.saved') }}</div>
                @elseif ($example_key === 'accounts')
                    <div class="affiliate-example__bar">{{ __('affiliate.payout_accounts.saved_accounts') }}</div>
                    <div class="affiliate-example__record"><span>{{ __('affiliate.payout_accounts.promptpay') }}</span><strong>******1234</strong></div>
                    <div class="affiliate-example__success">✓ {{ __('affiliate_tutorial.examples.saved') }}</div>
                @elseif ($example_key === 'withdraw')
                    <div class="affiliate-example__bar">{{ __('affiliate.withdrawals.request_title') }}</div>
                    <p>{{ __('affiliate.withdrawals.minimum', ['amount' => $example_format($example_amount)]) }}</p>
                    <div class="affiliate-example__field"><span class="affiliate-example__number">1</span><label>{{ __('affiliate.withdrawals.amount') }}</label><div>{{ $example_format($example_amount) }}</div></div>
                    <div class="affiliate-example__field"><span class="affiliate-example__number">2</span><label>{{ __('affiliate.withdrawals.payout_account') }}</label><div>{{ __('affiliate.payout_accounts.promptpay') }} — ******1234</div></div>
                    <div class="affiliate-example__action">{{ __('affiliate.withdrawals.request_action') }}</div>
                    <div class="affiliate-example__success">✓ {{ __('affiliate_tutorial.examples.submitted') }}</div>
                    <p class="affiliate-example__caption">{{ __('affiliate_tutorial.examples.amount_note') }}</p>
                @elseif ($example_key === 'history')
                    @foreach (['REQUESTED', 'APPROVED', 'PAID'] as $status)
                        <div class="affiliate-example__record"><b class="affiliate-example__number">{{ $loop->iteration }}</b><span class="affiliate-status affiliate-status--{{ strtolower($status) }}">{{ __('affiliate.statuses.withdrawal.' . $status) }}</span></div>
                    @endforeach
                    <p>{{ __('affiliate.statuses.withdrawal.REJECTED') }} / {{ __('affiliate.statuses.withdrawal.CANCELLED') }}</p>
                    <p class="affiliate-example__caption">{{ __('affiliate_tutorial.steps.history.body') }}</p>
                @endif
            </div>
        </figure>
    </template>
@endforeach
