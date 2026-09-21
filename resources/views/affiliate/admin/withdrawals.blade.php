@extends('layouts.app')

@section('content')
<main class="container-fluid py-4" aria-labelledby="affiliate-withdrawals-title">
    <h1 id="affiliate-withdrawals-title">{{ __('affiliate-management.withdrawals.title') }}</h1>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p>{{ __('affiliate-management.withdrawals.description') }}</p>

    <div class="table-responsive">
        <table class="table table-striped" aria-describedby="affiliate-withdrawals-title">
            <thead>
                <tr>
                    <th scope="col">{{ __('affiliate-management.withdrawals.columns.id') }}</th>
                    <th scope="col">{{ __('affiliate-management.withdrawals.columns.affiliate') }}</th>
                    <th scope="col">{{ __('affiliate-management.withdrawals.columns.email') }}</th>
                    <th scope="col">{{ __('affiliate-management.withdrawals.columns.payout_account') }}</th>
                    <th scope="col">{{ __('affiliate-management.withdrawals.columns.amount') }}</th>
                    <th scope="col">{{ __('affiliate-management.withdrawals.columns.status') }}</th>
                    <th scope="col">{{ __('affiliate-management.withdrawals.columns.payment_reference') }}</th>
                    <th scope="col">{{ __('affiliate-management.withdrawals.columns.requested_at') }}</th>
                    <th scope="col">{{ __('affiliate-management.withdrawals.columns.actions') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($withdrawals as $withdrawal)
                <tr>
                    <td>{{ $withdrawal->id }}</td>
                    <td>{{ optional($withdrawal->AffiliateProfile?->User)->name ?: '—' }}</td>
                    <td>{{ optional($withdrawal->AffiliateProfile?->User)->email ?: '—' }}</td>
                    <td>{{ $withdrawal->payout_type?->value ? __('affiliate-management.withdrawals.payout_types.' . $withdrawal->payout_type->value) : '—' }} — {{ optional($withdrawal->AffiliatePayoutAccount)->masked_label ?: __('affiliate-management.withdrawals.payout_types.snapshot') }}</td>
                    <td>{{ number_format($withdrawal->amount_minor / 100, 2) }}</td>
                    <td>{{ __('affiliate-management.withdrawals.statuses.' . $withdrawal->status->value) }}</td>
                    <td>{{ $withdrawal->payment_reference ?? '—' }}</td>
                    <td>{{ optional($withdrawal->requested_at)->toDateTimeString() }}</td>
                    <td>
                        <div class="d-flex flex-wrap" style="gap: .5rem;">
                            @if ($withdrawal->status->value === 'REQUESTED' && auth()->user()->can('affiliate-withdrawal-approve'))
                                <form method="POST" action="{{ route('affiliate.withdrawals.admin.approve', $withdrawal) }}">
                                    @csrf
                                    <input type="hidden" name="transition_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                                    <button type="submit" class="btn btn-sm btn-success">{{ __('affiliate-management.withdrawals.actions.approve') }}</button>
                                </form>
                            @endif

                            @if (in_array($withdrawal->status->value, ['REQUESTED', 'APPROVED'], true) && auth()->user()->can('affiliate-withdrawal-approve'))
                                <form method="POST" action="{{ route('affiliate.withdrawals.admin.reject', $withdrawal) }}" class="d-flex" style="gap: .25rem;">
                                    @csrf
                                    <input type="hidden" name="transition_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                                    <input type="text" name="reason" class="form-control form-control-sm" placeholder="{{ __('affiliate-management.withdrawals.fields.reason') }}" aria-label="{{ __('affiliate-management.withdrawals.fields.reason') }}" required>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('affiliate-management.withdrawals.actions.reject') }}</button>
                                </form>
                            @endif

                            @if ($withdrawal->status->value === 'APPROVED' && auth()->user()->can('affiliate-withdrawal-pay'))
                                <form method="POST" action="{{ route('affiliate.withdrawals.admin.pay', $withdrawal) }}" class="d-flex flex-wrap" style="gap: .25rem;">
                                    @csrf
                                    <input type="hidden" name="transition_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                                    <input type="text" name="payment_reference" class="form-control form-control-sm" placeholder="{{ __('affiliate-management.withdrawals.fields.bank_reference') }}" aria-label="{{ __('affiliate-management.withdrawals.fields.bank_reference') }}" maxlength="255" required>
                                    <button type="submit" class="btn btn-sm btn-primary">{{ __('affiliate-management.withdrawals.actions.mark_paid') }}</button>
                                </form>
                            @endif

                            @if ($withdrawal->status->value === 'APPROVED' && auth()->user()->can('affiliate-payout-details-view'))
                                <div class="payout-details-reveal" data-payout-details-container>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        data-reveal-payout-details
                                        data-reveal-url="{{ route('affiliate.withdrawals.admin.payout-details.reveal', $withdrawal) }}"
                                        aria-controls="payout-details-{{ $withdrawal->id }}"
                                    >{{ __('affiliate-management.withdrawals.actions.view_payout_details') }}</button>
                                    <div id="payout-details-{{ $withdrawal->id }}" class="mt-2" data-payout-details-panel hidden role="status" aria-live="polite">
                                        <p class="mb-1"><strong>{{ __('affiliate-management.withdrawals.fields.payout_type') }}:</strong> <span data-payout-field="payout_type"></span></p>
                                        <p class="mb-1" data-payout-field-row="promptpay_identifier"><strong>{{ __('affiliate-management.withdrawals.fields.promptpay_identifier') }}:</strong> <span data-payout-field="promptpay_identifier"></span></p>
                                        <p class="mb-1" data-payout-field-row="bank_name"><strong>{{ __('affiliate-management.withdrawals.fields.bank') }}:</strong> <span data-payout-field="bank_name"></span></p>
                                        <p class="mb-1" data-payout-field-row="account_name"><strong>{{ __('affiliate-management.withdrawals.fields.account_name') }}:</strong> <span data-payout-field="account_name"></span></p>
                                        <p class="mb-1" data-payout-field-row="account_number"><strong>{{ __('affiliate-management.withdrawals.fields.account_number') }}:</strong> <span data-payout-field="account_number"></span></p>
                                        <p class="mb-1 text-danger" data-payout-details-error hidden>{{ __('affiliate-management.withdrawals.errors.unavailable') }}</p>
                                        <button type="button" class="btn btn-sm btn-outline-dark" data-hide-payout-details>{{ __('affiliate-management.withdrawals.actions.hide_details') }}</button>
                                    </div>
                                </div>
                            @endif

                            @if (!in_array($withdrawal->status->value, ['REQUESTED', 'APPROVED'], true))
                                <span aria-hidden="true">—</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9">{{ __('affiliate-management.withdrawals.empty') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $withdrawals->links() }}
</main>

<script>
    (() => {
        const csrf_token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const ClearPayoutDetails = (panel) => {
            panel.querySelectorAll('[data-payout-field]').forEach((field) => {
                field.textContent = '';
            });
            panel.querySelectorAll('[data-payout-field-row]').forEach((row) => {
                row.hidden = true;
            });
        };

        const HidePayoutDetails = (panel, reveal_button) => {
            ClearPayoutDetails(panel);
            panel.hidden = true;
            const error = panel.querySelector('[data-payout-details-error]');
            if (error) {
                error.hidden = true;
            }
            reveal_button.hidden = false;
        };

        const ShowPayoutDetails = (panel, reveal_button, payload) => {
            ClearPayoutDetails(panel);
            panel.querySelector('[data-payout-field="payout_type"]').textContent = payload.payout_type || '';
            Object.entries(payload.details || {}).forEach(([field_name, field_value]) => {
                const field = panel.querySelector(`[data-payout-field="${field_name}"]`);
                const row = panel.querySelector(`[data-payout-field-row="${field_name}"]`);
                if (field && row) {
                    field.textContent = String(field_value);
                    row.hidden = false;
                }
            });
            panel.hidden = false;
            reveal_button.hidden = true;
        };

        document.addEventListener('click', async (event) => {
            if (!(event.target instanceof Element)) {
                return;
            }

            const reveal_button = event.target.closest('[data-reveal-payout-details]');
            if (reveal_button) {
                const container = reveal_button.closest('[data-payout-details-container]');
                const panel = container?.querySelector('[data-payout-details-panel]');
                const error = panel?.querySelector('[data-payout-details-error]');
                if (!panel || !error) {
                    return;
                }

                error.hidden = true;
                reveal_button.disabled = true;
                try {
                    const response = await fetch(reveal_button.dataset.revealUrl, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrf_token,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const payload = await response.json();
                    if (!response.ok || !payload.data) {
                        throw new Error(@json(__('affiliate-management.withdrawals.errors.fetch_failed')));
                    }
                    ShowPayoutDetails(panel, reveal_button, payload.data);
                } catch (error_value) {
                    ClearPayoutDetails(panel);
                    error.hidden = false;
                    reveal_button.hidden = false;
                } finally {
                    reveal_button.disabled = false;
                }
                return;
            }

            const hide_button = event.target.closest('[data-hide-payout-details]');
            if (hide_button) {
                const container = hide_button.closest('[data-payout-details-container]');
                const panel = container?.querySelector('[data-payout-details-panel]');
                const reveal_button = container?.querySelector('[data-reveal-payout-details]');
                if (panel && reveal_button) {
                    HidePayoutDetails(panel, reveal_button);
                }
            }
        });
    })();
</script>
@endsection
