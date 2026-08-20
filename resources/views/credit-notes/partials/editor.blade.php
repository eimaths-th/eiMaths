@php
    $selected_items = $credit_note->Items->keyBy(fn ($item) => ($item->source_type?->value ?? '') . ':' . $item->source_id);
    $buyer_vat_registered = (string) old('buyer_is_vat_registered', $credit_note->buyer_is_vat_registered ? '1' : '0');
    $buyer_tax_fields_are_visible = $buyer_vat_registered === '1';
    $buyer_branch_type = (string) old('buyer_branch_type', $credit_note->buyer_branch_type?->value ?? 'head_office');
    $selected_reason_code = (string) old('reason_code', $credit_note->reason_code?->value ?? '');
    $default_adjustment_event_date = optional($credit_note->adjustment_event_date)->format('Y-m-d')
        ?? now((string) config('app.timezone', 'Asia/Bangkok'))->toDateString();
    $adjustment_event_date = old('adjustment_event_date', $default_adjustment_event_date);
@endphp

<form id="credit-note-editor" method="POST" action="{{ route('credit-notes.update', $credit_note) }}" novalidate>
    @csrf
    @method('PUT')
    <input type="hidden" name="lock_version" value="{{ $credit_note->lock_version }}">

    <div class="credit-note-form-section">
        <div class="credit-note-section-heading">
            <div>
                <span class="credit-note-section-kicker">01 / Buyer profile</span>
                <h3>{{ __('credit-note.fields.buyer_name') }}</h3>
            </div>
            <i class="fas fa-user-tie" aria-hidden="true"></i>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="buyer_name">{{ __('credit-note.fields.buyer_name') }} <span class="text-danger" aria-hidden="true">*</span></label>
                <input id="buyer_name" name="buyer_name" class="form-control" value="{{ old('buyer_name', $credit_note->buyer_name) }}" required autocomplete="organization">
            </div>
            <div class="form-group col-md-6">
                <label for="buyer_is_vat_registered">VAT registered</label>
                <select id="buyer_is_vat_registered" name="buyer_is_vat_registered" class="form-control" required aria-controls="buyer-tax-id-field buyer-branch-type-field buyer-branch-code-field" aria-expanded="{{ $buyer_tax_fields_are_visible ? 'true' : 'false' }}">
                    <option value="1" @selected($buyer_vat_registered === '1')>Yes</option>
                    <option value="0" @selected($buyer_vat_registered === '0')>No</option>
                </select>
            </div>
            <div class="form-group col-md-12">
                <label for="buyer_address">{{ __('credit-note.fields.buyer_address') }}</label>
                <textarea id="buyer_address" name="buyer_address" class="form-control" rows="3">{{ old('buyer_address', $credit_note->buyer_address) }}</textarea>
            </div>
            <div id="buyer-tax-id-field" class="form-group col-md-4" data-buyer-tax-field aria-hidden="{{ $buyer_tax_fields_are_visible ? 'false' : 'true' }}" @if (!$buyer_tax_fields_are_visible) hidden @endif>
                <label for="buyer_tax_id">{{ __('credit-note.fields.buyer_tax_id') }} <span class="text-danger" aria-hidden="true">*</span></label>
                <input id="buyer_tax_id" name="buyer_tax_id" class="form-control" value="{{ old('buyer_tax_id', $credit_note->buyer_tax_id) }}" inputmode="numeric" data-required-when-vat-registered @required($buyer_tax_fields_are_visible) @disabled(!$buyer_tax_fields_are_visible)>
            </div>
            <div id="buyer-branch-type-field" class="form-group col-md-4" data-buyer-tax-field aria-hidden="{{ $buyer_tax_fields_are_visible ? 'false' : 'true' }}" @if (!$buyer_tax_fields_are_visible) hidden @endif>
                <label for="buyer_branch_type">Buyer branch <span class="text-danger" aria-hidden="true">*</span></label>
                <select id="buyer_branch_type" name="buyer_branch_type" class="form-control" data-required-when-vat-registered @required($buyer_tax_fields_are_visible) @disabled(!$buyer_tax_fields_are_visible)>
                    <option value="head_office" @selected($buyer_branch_type === 'head_office')>Head Office</option>
                    <option value="branch" @selected($buyer_branch_type === 'branch')>Branch</option>
                </select>
            </div>
            <div id="buyer-branch-code-field" class="form-group col-md-4" data-buyer-tax-field aria-hidden="{{ $buyer_tax_fields_are_visible ? 'false' : 'true' }}" @if (!$buyer_tax_fields_are_visible) hidden @endif>
                <label for="buyer_branch_code">Branch code <span class="text-danger" aria-hidden="true">*</span></label>
                <input id="buyer_branch_code" name="buyer_branch_code" class="form-control" value="{{ old('buyer_branch_code', $credit_note->buyer_branch_code) }}" pattern="[0-9]{5}" inputmode="numeric" data-required-when-vat-registered @required($buyer_tax_fields_are_visible) @disabled(!$buyer_tax_fields_are_visible)>
            </div>
        </div>
    </div>

    <div class="credit-note-form-section">
        <div class="credit-note-section-heading">
            <div>
                <span class="credit-note-section-kicker">02 / Adjustment reason</span>
                <h3>{{ __('credit-note.fields.reason') }}</h3>
            </div>
            <i class="fas fa-clipboard-check" aria-hidden="true"></i>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="reason_code">{{ __('credit-note.fields.reason') }} <span class="text-danger" aria-hidden="true">*</span></label>
                <select id="reason_code" name="reason_code" class="form-control" required>
                    @foreach (\App\Domain\CreditNote\Enums\CreditNoteReasonCode::cases() as $reason)
                        <option value="{{ $reason->value }}" @selected($selected_reason_code === $reason->value)>
                            {{ str_replace('_', ' ', ucfirst($reason->value)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="adjustment_event_date">{{ __('credit-note.fields.adjustment_event_date') }} <span class="text-danger" aria-hidden="true">*</span></label>
                <input id="adjustment_event_date" name="adjustment_event_date" type="date" class="form-control" value="{{ $adjustment_event_date }}" required>
            </div>
            <div class="form-group col-md-6">
                <label for="reason_details">{{ __('credit-note.fields.reason_details') }}</label>
                <textarea id="reason_details" name="reason_details" class="form-control" rows="3">{{ old('reason_details', $credit_note->reason_details) }}</textarea>
            </div>
            <div class="form-group col-md-6">
                <label for="late_issue_reason">Late issue reason <span class="text-muted">(if applicable)</span></label>
                <textarea id="late_issue_reason" name="late_issue_reason" class="form-control" rows="3">{{ old('late_issue_reason', $credit_note->late_issue_reason) }}</textarea>
            </div>
        </div>
    </div>

    <div class="credit-note-form-section credit-note-source-section">
        <div class="credit-note-section-heading">
            <div>
                <span class="credit-note-section-kicker">03 / Source receipt</span>
                <h3>Source receipt lines</h3>
            </div>
            <i class="fas fa-receipt" aria-hidden="true"></i>
        </div>
        <p class="credit-note-section-help">Enter a positive credit amount only for lines to include. Leave other lines blank.</p>
        <div class="table-responsive">
            <table class="table table-hover credit-note-source-table">
                <caption class="sr-only">Source receipt lines available for credit</caption>
                <thead>
                    <tr>
                        <th scope="col">Line</th>
                        <th scope="col" class="text-right">Original</th>
                        <th scope="col">Tax treatment</th>
                        <th scope="col" class="credit-note-entry-column">Credit quantity</th>
                        <th scope="col" class="credit-note-entry-column">Credit amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (($source_snapshot?->lines ?? []) as $index => $source_line)
                        @php
                            $source_key = $source_line->source_type->value . ':' . $source_line->source_id;
                            $selected_item = $selected_items->get($source_key);
                            $line_is_complete = $source_line->has_complete_source_values && $source_line->line_type !== null && $source_line->tax_treatment !== null && $source_line->vat_rate_basis_points !== null;
                            $credit_amount_default = $selected_item === null ? '' : number_format(((int) $selected_item->credit_amount_minor) / 100, 2, '.', '');
                            $credit_quantity_default = $selected_item?->credit_quantity === null ? '' : (string) $selected_item->credit_quantity;
                            $credit_amount = old("items.{$index}.credit_amount", $credit_amount_default);
                            $credit_quantity = old("items.{$index}.credit_quantity", $credit_quantity_default);
                        @endphp
                        <tr @class(['credit-note-source-row-incomplete' => !$line_is_complete])>
                            <td>
                                <span class="credit-note-line-number">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="credit-note-line-description">{{ $source_line->description }}</span>
                                <input type="hidden" name="items[{{ $index }}][source_type]" value="{{ $source_line->source_type->value }}">
                                <input type="hidden" name="items[{{ $index }}][source_id]" value="{{ $source_line->source_id }}">
                            </td>
                            <td class="text-right text-nowrap">{{ number_format($source_line->original_line_total_minor / 100, 2, '.', ',') }}</td>
                            <td>
                                @if ($line_is_complete)
                                    <span class="credit-note-tax-label">{{ $source_line->tax_treatment->value }}</span>
                                    <small class="d-block text-muted">{{ number_format($source_line->vat_rate_basis_points / 100, 2, '.', '') }}% VAT</small>
                                @else
                                    <span class="badge badge-warning">Incomplete source metadata</span>
                                @endif
                            </td>
                            <td>
                                <label class="sr-only" for="credit_quantity_{{ $index }}">Credit quantity for line {{ $index + 1 }}</label>
                                <input id="credit_quantity_{{ $index }}" name="items[{{ $index }}][credit_quantity]" class="form-control form-control-sm" value="{{ $credit_quantity }}" inputmode="decimal" placeholder="Optional" @disabled(!$line_is_complete)>
                            </td>
                            <td>
                                <label class="sr-only" for="credit_amount_{{ $index }}">Credit amount for line {{ $index + 1 }}</label>
                                <div class="input-group input-group-sm">
                                    <input id="credit_amount_{{ $index }}" name="items[{{ $index }}][credit_amount]" class="form-control text-right" value="{{ $credit_amount }}" inputmode="decimal" placeholder="0.00" @disabled(!$line_is_complete)>
                                    <div class="input-group-append"><span class="input-group-text">THB</span></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if (($source_snapshot?->lines ?? []) === [])
                        <tr><td colspan="5" class="text-center text-muted py-4">No source receipt lines are available.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    @include('credit-notes.partials.totals', ['credit_note' => $credit_note])

    <div class="credit-note-form-actions">
        <p id="credit-note-editor-feedback" class="mb-0 text-muted" aria-live="polite"></p>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1" aria-hidden="true"></i>{{ __('credit-note.actions.save') }}
        </button>
    </div>
</form>

<div class="credit-note-next-actions">
    @if (config('financial-documents.credit_note.financial_actions_enabled') && auth()->user()?->can('credit-note-issue'))
        <form method="POST" action="{{ route('credit-notes.issue', $credit_note) }}">
            @csrf
            <input type="hidden" name="lock_version" value="{{ $credit_note->lock_version }}">
            <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
            <button type="submit" class="btn btn-success"><i class="fas fa-check-circle mr-1" aria-hidden="true"></i>{{ __('credit-note.actions.issue') }}</button>
        </form>
    @endif
    @if (config('financial-documents.credit_note.financial_actions_enabled') && auth()->user()?->can('credit-note-cancel'))
        <form method="POST" action="{{ route('credit-notes.cancel', $credit_note) }}" class="credit-note-cancel-form">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
            <label for="draft-cancellation-reason" class="sr-only">Cancellation reason</label>
            <input id="draft-cancellation-reason" name="reason" class="form-control" required maxlength="2000" placeholder="Cancellation reason">
            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-ban mr-1" aria-hidden="true"></i>{{ __('credit-note.actions.cancel') }}</button>
        </form>
    @endif
</div>
