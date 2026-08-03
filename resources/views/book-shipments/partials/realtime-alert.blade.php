@php
    $is_centre_alert = $book_shipment_alert_role === 'centre';
    $summary_url = $is_centre_alert
        ? route('incoming-book-shipments.pending-summary')
        : route('book-shipments.receipt-alert-summary');
    $index_url = $is_centre_alert
        ? route('incoming-book-shipments.index')
        : route('book-shipments.index');
    $detail_base_url = $is_centre_alert
        ? url('/incoming-book-shipments')
        : url('/book-shipments');
@endphp

<div
    data-book-shipment-alert
    data-book-shipment-alert-role="{{ $book_shipment_alert_role }}"
    data-summary-url="{{ $summary_url }}"
    data-index-url="{{ $index_url }}"
    data-detail-base-url="{{ $detail_base_url }}"
    data-storage-key="book-shipment-alert:{{ $book_shipment_alert_role }}:{{ $authenticated_user->id }}"
>
    <aside
        class="book-shipment-alert"
        data-book-shipment-alert-toast
        role="status"
        aria-live="polite"
        aria-atomic="true"
        hidden
    >
        <div class="book-shipment-alert__content">
            <h2 class="book-shipment-alert__title" data-book-shipment-alert-title></h2>
            <p class="book-shipment-alert__message" data-book-shipment-alert-message></p>
            <a class="btn btn-sm btn-primary" data-book-shipment-alert-centre-action href="#" hidden>
                View shipment
            </a>
            <form method="POST" action="#" data-book-shipment-alert-read-form hidden>
                @csrf
                <button class="btn btn-sm btn-primary" type="submit" data-book-shipment-alert-hq-action disabled>
                    Open shipment
                </button>
            </form>
        </div>
        <button
            class="book-shipment-alert__dismiss"
            type="button"
            aria-label="Dismiss notification"
            data-book-shipment-alert-dismiss
        >
            <span aria-hidden="true">&times;</span>
        </button>
    </aside>
</div>
