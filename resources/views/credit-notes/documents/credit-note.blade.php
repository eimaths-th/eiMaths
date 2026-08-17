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
    $issuer_branch_code = $credit_note->issuer_branch_code ?: config('financial-documents.credit_note.issuer.branch_code', '00000');
    $issuer_name = filled($issuer_name) ? $issuer_name : $receipt_issuer_name;
    $issuer_address = filled($issuer_address) ? $issuer_address : $receipt_issuer_address;
    $issuer_tax_id = filled($issuer_tax_id) ? $issuer_tax_id : $receipt_issuer_tax_id;
    $buyer_branch_code = $credit_note->buyer_branch_code ?: '00000';
    $status = $credit_note->status?->value ?? 'draft';
    $status_label = __('credit-note.statuses.' . $status);
    $reason_code = $credit_note->reason_code?->value;
    $reason_label = $reason_code ? __('credit-note.reasons.' . $reason_code) : '—';
    $number = $credit_note->number ?? 'Draft #' . $credit_note->id;
@endphp

<article class="credit-note-paper credit-note-document" aria-labelledby="credit-note-print-title">
    @if ($status === 'draft')
        <div class="credit-note-document-watermark" aria-hidden="true">DRAFT</div>
    @elseif ($status === 'cancelled')
        <div class="credit-note-document-watermark" aria-hidden="true">CANCELLED</div>
    @endif

    <header class="credit-note-document-header credit-note-receipt-header">
        <table class="credit-note-receipt-brand-table" role="presentation">
            <tr>
                <td class="credit-note-receipt-meta-logo-cell">
                    <img class="credit-note-meta-logo" src="{{ asset('images/logo_meta.png') }}" alt="Meta Knowledge logo">
                </td>
                <td class="credit-note-receipt-issuer-cell">
                    <p class="credit-note-issuer-name">{{ $issuer_name }}</p>
                    <p class="credit-note-issuer-contact">{{ $issuer_address }}<br>เลขประจำตัวผู้เสียภาษี : {{ $issuer_tax_id }}@if ($use_receipt_issuer_branding)<br>โทร. {{ $receipt_issuer_phone }}@endif</p>
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
                    <h1 id="credit-note-print-title">ใบลดหนี้ / Credit Note</h1>
                    <p>Tax Credit Note document</p>
                    <span class="credit-note-document-status credit-note-document-status-{{ $status }}">{{ $status_label }}</span>
                </td>
            </tr>
        </table>
    </header>

    <section class="credit-note-document-meta credit-note-receipt-meta" aria-label="Document reference">
        <div class="credit-note-document-meta-item">
            <span class="credit-note-document-meta-label">Document no.</span>
            <span class="credit-note-document-meta-value">{{ $number }}</span>
        </div>
        <div class="credit-note-document-meta-item">
            <span class="credit-note-document-meta-label">Original receipt</span>
            <span class="credit-note-document-meta-value">{{ $credit_note->original_receipt_number ?? '—' }}</span>
        </div>
        <div class="credit-note-document-meta-item">
            <span class="credit-note-document-meta-label">Receipt date</span>
            <span class="credit-note-document-meta-value">{{ optional($credit_note->original_receipt_date)->format('Y-m-d') ?? '—' }}</span>
        </div>
        <div class="credit-note-document-meta-item">
            <span class="credit-note-document-meta-label">Adjustment date</span>
            <span class="credit-note-document-meta-value">{{ optional($credit_note->adjustment_event_date)->format('Y-m-d') ?? '—' }}</span>
        </div>
    </section>

    <table class="credit-note-party-table" role="presentation">
        <tr>
            <td>
                <span class="credit-note-party-label">Issuer / ผู้ออกเอกสาร</span>
                <p class="credit-note-party-name">{{ $issuer_name ?: '—' }}</p>
                <p>{!! nl2br(e((string) $issuer_address)) !!}</p>
                <p>Tax ID: {{ $issuer_tax_id ?: '—' }}</p>
                <p>Branch: {{ $issuer_branch_code }}</p>
            </td>
            <td>
                <span class="credit-note-party-label">Buyer / ผู้ซื้อ</span>
                <p class="credit-note-party-name">{{ $credit_note->buyer_name ?: '—' }}</p>
                <p>{!! nl2br(e((string) $credit_note->buyer_address)) !!}</p>
                <p>Tax ID: {{ $credit_note->buyer_tax_id ?: '—' }}</p>
                <p>Branch: {{ $buyer_branch_code }}</p>
            </td>
        </tr>
    </table>

    <section class="credit-note-document-section" aria-labelledby="credit-note-items-title">
        <h2 id="credit-note-items-title">Items / รายการ</h2>
        <table class="credit-note-items-table credit-note-orange-table">
            <thead>
                <tr>
                    <th scope="col" style="width: 5%;">No.</th>
                    <th scope="col">Description</th>
                    <th scope="col" class="text-right" style="width: 18%;">Original</th>
                    <th scope="col" class="text-right" style="width: 18%;">Credit amount</th>
                    <th scope="col" style="width: 18%;">VAT</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($credit_note->Items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->description ?: '—' }}</td>
                        <td class="text-right">{{ number_format(((int) $item->original_line_total_minor) / 100, 2, '.', ',') }}</td>
                        <td class="text-right">{{ number_format(((int) $item->credit_amount_minor) / 100, 2, '.', ',') }}</td>
                        <td>{{ $item->tax_treatment?->value ?? '—' }} @if ($item->vat_rate_basis_points !== null)<small>({{ number_format($item->vat_rate_basis_points / 100, 2, '.', '') }}%)</small>@endif</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="credit-note-items-empty">No credited lines recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="credit-note-document-section credit-note-receipt-summary" aria-labelledby="credit-note-summary-title">
        <h2 id="credit-note-summary-title">Original / Corrected / Difference</h2>
        <table class="credit-note-summary-table" role="presentation">
            <tr>
                <td>
                    <span class="credit-note-summary-label">Original total</span>
                    <span class="credit-note-summary-value">{{ number_format(((int) $credit_note->original_grand_total_minor) / 100, 2, '.', ',') }} <span class="credit-note-summary-unit">THB</span></span>
                </td>
                <td class="credit-note-summary-primary">
                    <span class="credit-note-summary-label">Corrected total</span>
                    <span class="credit-note-summary-value">{{ number_format(((int) $credit_note->corrected_grand_total_minor) / 100, 2, '.', ',') }} <span class="credit-note-summary-unit">THB</span></span>
                </td>
                <td class="credit-note-summary-highlight">
                    <span class="credit-note-summary-label">Difference</span>
                    <span class="credit-note-summary-value">{{ number_format(((int) $credit_note->difference_grand_total_minor) / 100, 2, '.', ',') }} <span class="credit-note-summary-unit">THB</span></span>
                </td>
                <td>
                    <span class="credit-note-summary-label">Taxable base difference</span>
                    <span class="credit-note-summary-value">{{ number_format(((int) $credit_note->difference_taxable_base_minor) / 100, 2, '.', ',') }} <span class="credit-note-summary-unit">THB</span></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="credit-note-summary-label">VAT difference</span>
                    <span class="credit-note-summary-value">{{ number_format(((int) $credit_note->difference_vat_minor) / 100, 2, '.', ',') }} <span class="credit-note-summary-unit">THB</span></span>
                </td>
                <td>
                    <span class="credit-note-summary-label">Exempt difference</span>
                    <span class="credit-note-summary-value">{{ number_format(((int) $credit_note->difference_exempt_minor) / 100, 2, '.', ',') }} <span class="credit-note-summary-unit">THB</span></span>
                </td>
                <td>
                    <span class="credit-note-summary-label">Out-of-scope difference</span>
                    <span class="credit-note-summary-value">{{ number_format(((int) $credit_note->difference_out_of_scope_minor) / 100, 2, '.', ',') }} <span class="credit-note-summary-unit">THB</span></span>
                </td>
                <td></td>
            </tr>
        </table>
    </section>

    <section class="credit-note-document-section" aria-labelledby="credit-note-reason-title">
        <h2 id="credit-note-reason-title">Reason / เหตุผล</h2>
        <div class="credit-note-reason-box">
            <p><strong>{{ $reason_label }}</strong></p>
            @if (filled($credit_note->reason_details))
                <p>{!! nl2br(e((string) $credit_note->reason_details)) !!}</p>
            @endif
            @if (filled($credit_note->late_issue_reason))
                <p><strong>Late issue reason:</strong> {!! nl2br(e((string) $credit_note->late_issue_reason)) !!}</p>
            @endif
        </div>
    </section>

    <footer class="credit-note-document-footer credit-note-receipt-footer">
        <table class="credit-note-signature-table" role="presentation">
            <tr>
                <td>Prepared by: {{ $credit_note->created_by ?? '—' }}</td>
                <td>Authorised signature / ลายมือชื่อผู้มีอำนาจ</td>
            </tr>
        </table>
        <img class="credit-note-receipt-banner" src="{{ asset('images/banner.png') }}" alt="">
        <p class="credit-note-footer-note">This document is generated from the eiMaths financial record. Please retain it with the original receipt.</p>
    </footer>
</article>
