<style>
    @include('credit-notes.partials.document-styles')
</style>

@php
    $receipt_issuer_name = 'บริษัท เมต้า โนวเลจ จำกัด(สำนักงานใหญ่)';
    $receipt_issuer_address = "555 / 9 หมู่ 1 ตำบลบางขนุน อำเภอบางกรวย จังหวัดนนทบุรี 11130";
    $receipt_issuer_tax_id = '0125565026368';
    $receipt_issuer_phone = '061 620 8666';
    $use_receipt_issuer_branding = !filled($credit_note->issuer_name)
        && !filled(config('financial-documents.credit_note.issuer.legal_name'))
        && !filled($credit_note->issuer_address)
        && !filled(config('financial-documents.credit_note.issuer.legal_address'))
        && !filled($credit_note->issuer_tax_id)
        && !filled(config('financial-documents.credit_note.issuer.tax_id'));
    $issuer_name = filled($credit_note->issuer_name) ? $credit_note->issuer_name : config('financial-documents.credit_note.issuer.legal_name');
    $issuer_address = filled($credit_note->issuer_address) ? $credit_note->issuer_address : config('financial-documents.credit_note.issuer.legal_address');
    $issuer_tax_id = filled($credit_note->issuer_tax_id) ? $credit_note->issuer_tax_id : config('financial-documents.credit_note.issuer.tax_id');
    $issuer_name = filled($issuer_name) ? $issuer_name : $receipt_issuer_name;
    $issuer_address = filled($issuer_address) ? $issuer_address : $receipt_issuer_address;
    $issuer_tax_id = filled($issuer_tax_id) ? $issuer_tax_id : $receipt_issuer_tax_id;

    $buyer_is_vat_registered = $credit_note->buyer_is_vat_registered === true;
    $buyer_address = filled($credit_note->buyer_address)
        ? $credit_note->buyer_address
        : $credit_note->original_billing_details_text;
    $status = $credit_note->status?->value ?? 'draft';
    $status_label = __('credit-note.statuses.' . $status);
    $reason_code = $credit_note->reason_code?->value;
    $reason_is_custom = $reason_code === 'other';
    $reason_label = $reason_is_custom && filled($credit_note->reason_details)
        ? (string) $credit_note->reason_details
        : ($reason_code ? __('credit-note.reasons.' . $reason_code) : '—');
    $number = $credit_note->number ?? __('credit-note.register.draft_number', ['id' => $credit_note->id]);
    $credit_note_date = $credit_note->issue_date ?? $credit_note->adjustment_event_date;
    $format_money = static fn ($minor): string => number_format(((int) $minor) / 100, 2, '.', ',');
    $format_quantity = static function ($quantity): string {
        if ($quantity === null || $quantity === '') {
            return '—';
        }

        return rtrim(rtrim(number_format((float) $quantity, 2, '.', ','), '0'), '.');
    };
@endphp

<article class="credit-note-paper credit-note-document credit-note-receipt-document" aria-labelledby="credit-note-print-title">
    @if ($status === 'draft')
        <div class="credit-note-document-watermark" aria-hidden="true">{{ __('credit-note.document.draft_watermark') }}</div>
    @elseif ($status === 'cancelled')
        <div class="credit-note-document-watermark" aria-hidden="true">{{ __('credit-note.document.cancelled_watermark') }}</div>
    @endif

    <header class="credit-note-document-header credit-note-receipt-header">
        <table class="credit-note-receipt-brand-table" role="presentation">
            <tr>
                <td class="credit-note-receipt-meta-logo-cell">
                    <img class="credit-note-meta-logo" src="{{ asset('images/logo_meta.png') }}" alt="Meta Knowledge logo">
                </td>
                <td class="credit-note-receipt-issuer-cell">
                    <p class="credit-note-issuer-name">{{ $issuer_name }}</p>
                    <p class="credit-note-issuer-contact">
                        {{ $issuer_address }}<br>
                        เลขประจำตัวผู้เสียภาษี : {{ $issuer_tax_id }}
                        @if ($use_receipt_issuer_branding)
                            <br>โทร. {{ $receipt_issuer_phone }}
                        @endif
                    </p>
                </td>
                <td class="credit-note-receipt-eimaths-logo-cell">
                    <img class="credit-note-eimaths-logo" src="{{ asset('images/logo.png') }}" alt="eiMaths logo">
                </td>
            </tr>
        </table>

        <table class="credit-note-receipt-title-table" role="presentation">
            <tr>
                <td></td>
                <td>
                    <h1 id="credit-note-print-title">ใบลดหนี้ / CREDIT NOTE</h1>
                    <span class="credit-note-document-status credit-note-document-status-{{ $status }}">{{ $status_label }}</span>
                </td>
            </tr>
        </table>
    </header>

    <table class="credit-note-receipt-information-table" role="presentation">
        <tr>
            <td class="credit-note-buyer-cell">
                <span class="credit-note-muted-label">นามผู้ซื้อ, ที่อยู่ / Buyer, Address</span>
                <strong class="credit-note-buyer-name">{{ $credit_note->buyer_name ?: '—' }}</strong>
                @if (filled($buyer_address))
                    <span class="credit-note-buyer-address">{!! nl2br(e((string) $buyer_address)) !!}</span>
                @endif
                @if ($buyer_is_vat_registered && filled($credit_note->buyer_tax_id))
                    <span class="credit-note-buyer-tax">Tax ID: {{ $credit_note->buyer_tax_id }}</span>
                @endif
                @if ($buyer_is_vat_registered)
                    <span class="credit-note-buyer-tax">Branch: {{ $credit_note->buyer_branch_code ?: '00000' }}</span>
                @endif
            </td>
            <td class="credit-note-reference-cell">
                <table class="credit-note-reference-fields" role="presentation">
                    <tr>
                        <th scope="row">เลขที่ใบลดหนี้ / Credit Note No.</th>
                        <td>{{ $number }}</td>
                    </tr>
                    <tr>
                        <th scope="row">วันที่ใบลดหนี้ / Credit Note Date</th>
                        <td>{{ optional($credit_note_date)->format('Y-m-d') ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th scope="row">เลขที่ใบเสร็จเดิม / Original Receipt No.</th>
                        <td>{{ $credit_note->original_receipt_number ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th scope="row">วันที่ใบเสร็จเดิม / Original Receipt Date</th>
                        <td>{{ optional($credit_note->original_receipt_date)->format('Y-m-d') ?? '—' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <section class="credit-note-document-section credit-note-line-items" aria-labelledby="credit-note-items-title">
        <h2 id="credit-note-items-title">รายการลดหนี้ / Credit Note Items</h2>
        <table class="credit-note-items-table credit-note-orange-table">
            <thead>
                <tr>
                    <th scope="col" class="credit-note-col-number">ลำดับที่<br>No.</th>
                    <th scope="col" class="credit-note-col-description">รายการ<br>Description</th>
                    <th scope="col" class="credit-note-col-quantity">จำนวนเดิม<br>Original Qty.</th>
                    <th scope="col" class="credit-note-col-quantity">จำนวนที่ลด<br>Credit Qty.</th>
                    <th scope="col" class="credit-note-col-money">ราคาต่อหน่วย<br>Unit Price</th>
                    <th scope="col" class="credit-note-col-money">ส่วนลดเดิม<br>Original Discount</th>
                    <th scope="col" class="credit-note-col-money">จำนวนเงินลด<br>Credit Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($credit_note->Items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->description ?: '—' }}</strong>
                            @if (filled($item->unit))
                                <small>{{ $item->unit }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $format_quantity($item->original_quantity) }}</td>
                        <td class="text-center">{{ $format_quantity($item->credit_quantity) }}</td>
                        <td class="text-right">{{ $format_money($item->original_unit_price_minor) }}</td>
                        <td class="text-right">{{ $format_money($item->original_discount_minor) }}</td>
                        <td class="text-right"><strong>{{ $format_money($item->credit_amount_minor) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="credit-note-items-empty">No credited lines recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="credit-note-document-section credit-note-receipt-summary" aria-label="Original / Corrected / Difference">
        <table class="credit-note-adjustment-table" role="presentation">
            <tr>
                <td class="credit-note-reason-cell">
                    <h2>ข้อมูลอ้างอิงและเหตุผล / Reference &amp; Reason</h2>
                    <dl class="credit-note-reason-list">
                        <dt>ใบเสร็จต้นฉบับ / Original Receipt</dt>
                        <dd>
                            {{ $credit_note->original_receipt_number ?? '—' }}
                            @if ($credit_note->original_receipt_date !== null)
                                · {{ $credit_note->original_receipt_date->format('Y-m-d') }}
                            @endif
                        </dd>
                        <dt>เหตุผล / Reason</dt>
                        <dd><strong>{{ $reason_label }}</strong></dd>
                        @if (!$reason_is_custom && filled($credit_note->reason_details))
                            <dd>{!! nl2br(e((string) $credit_note->reason_details)) !!}</dd>
                        @endif
                        @if (filled($credit_note->late_issue_reason))
                            <dt>เหตุผลที่ออกล่าช้า / Late Issue Reason</dt>
                            <dd>{!! nl2br(e((string) $credit_note->late_issue_reason)) !!}</dd>
                        @endif
                    </dl>
                </td>
                <td class="credit-note-summary-cell">
                    <table class="credit-note-summary-table" role="presentation">
                        <tr>
                            <th scope="row">ยอดเดิม / Original Total</th>
                            <td>{{ $format_money($credit_note->original_grand_total_minor) }}</td>
                        </tr>
                        <tr>
                            <th scope="row">ยอดหลังลดหนี้ / Corrected Total</th>
                            <td>{{ $format_money($credit_note->corrected_grand_total_minor) }}</td>
                        </tr>
                        <tr>
                            <th scope="row">มูลค่าที่คำนวนภาษี / Pre-VAT Amount</th>
                            <td>{{ $format_money($credit_note->difference_taxable_base_minor) }}</td>
                        </tr>
                        <tr>
                            <th scope="row">ภาษีมูลค่าเพิ่ม / VAT</th>
                            <td>{{ $format_money($credit_note->difference_vat_minor) }}</td>
                        </tr>
                        <tr>
                            <th scope="row">มูลค่าที่ยกเว้นภาษี / Non-VAT Amount</th>
                            <td>{{ $format_money($credit_note->difference_exempt_minor) }}</td>
                        </tr>
                        <tr>
                            <th scope="row">ยอดนอกขอบเขตภาษีที่ลด / Out-of-scope Credit</th>
                            <td>{{ $format_money($credit_note->difference_out_of_scope_minor) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </section>

    <table class="credit-note-total-credit-table" role="presentation">
        <tr>
            <th scope="row">ยอดลดหนี้รวม / Total Credit Amount</th>
            <td class="credit-note-total-credit-copy">ยอดเงินที่ลดจากใบเสร็จต้นฉบับ</td>
            <td class="credit-note-total-credit-value">{{ $format_money($credit_note->difference_grand_total_minor) }}</td>
            <td class="credit-note-total-credit-unit">บาท<br>Baht</td>
        </tr>
    </table>

    <footer class="credit-note-document-footer credit-note-receipt-footer credit-note-reason-footer">
        <table class="credit-note-signature-table" role="presentation">
            <tr>
                <td>
                    <span class="credit-note-signature-line">................................</span>
                    <strong>Prepared by: {{ $credit_note->CreatedBy?->name ?? '—' }}</strong>
                    <span>ผู้จัดทำเอกสาร</span>
                </td>
                <td>
                    <span class="credit-note-signature-line">................................</span>
                    <strong>Authorised signature</strong>
                    <span>ลายมือชื่อผู้มีอำนาจ</span>
                </td>
            </tr>
        </table>
        <img class="credit-note-receipt-banner" src="{{ asset('images/banner.png') }}" alt="">
        <p class="credit-note-footer-note">This Credit Note refers to the original receipt shown above. Retain both documents together.</p>
    </footer>
</article>
