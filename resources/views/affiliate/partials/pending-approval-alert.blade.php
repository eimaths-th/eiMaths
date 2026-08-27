<div
    data-affiliate-pending-approval-alert
    data-summary-url="{{ route('affiliate.admin.pending-summary') }}"
    data-index-url="{{ route('affiliate.admin.index') }}"
    data-storage-key="affiliate-pending-approval-alert:{{ $authenticated_user->id }}"
>
    <aside
        class="affiliate-pending-approval-alert"
        data-affiliate-pending-approval-toast
        role="status"
        aria-live="polite"
        aria-atomic="true"
        hidden
    >
        <div class="affiliate-pending-approval-alert__content">
            <h2 class="affiliate-pending-approval-alert__title" data-affiliate-pending-approval-title></h2>
            <p class="affiliate-pending-approval-alert__message" data-affiliate-pending-approval-message></p>
            <a class="btn btn-sm btn-primary" data-affiliate-pending-approval-action href="#" hidden>
                ดูรายการ Affiliate ที่รออนุมัติ
            </a>
        </div>
        <button
            class="affiliate-pending-approval-alert__dismiss"
            type="button"
            aria-label="ปิดการแจ้งเตือน Affiliate"
            data-affiliate-pending-approval-dismiss
        >
            <span aria-hidden="true">&times;</span>
        </button>
    </aside>
</div>
