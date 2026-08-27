<div
    data-pending-work-alert
    data-storage-key="pending-work-alert:{{ $authenticated_user->id }}"
>
    <aside
        class="pending-work-alert"
        data-pending-work-alert-toast
        role="status"
        aria-live="polite"
        aria-atomic="true"
        hidden
    >
        <div class="pending-work-alert__content">
            <h2 class="pending-work-alert__title">งานที่ต้องจัดการ</h2>
            <ul class="pending-work-alert__items">
                <li class="pending-work-alert__item" data-pending-work-item="affiliate" hidden>
                    <div class="pending-work-alert__item-content">
                        <div class="pending-work-alert__item-heading">
                            <strong data-pending-work-item-title>Affiliate account</strong>
                            <span class="pending-work-alert__count" data-pending-work-item-count></span>
                        </div>
                        <p data-pending-work-item-message></p>
                        <a class="btn btn-sm btn-primary" data-pending-work-item-action href="#" hidden>
                            ดูรายการ Affiliate ที่รออนุมัติ
                        </a>
                    </div>
                </li>
                <li class="pending-work-alert__item" data-pending-work-item="book" hidden>
                    <div class="pending-work-alert__item-content">
                        <div class="pending-work-alert__item-heading">
                            <strong data-pending-work-item-title>รายการหนังสือ</strong>
                            <span class="pending-work-alert__count" data-pending-work-item-count></span>
                        </div>
                        <p data-pending-work-item-message></p>
                        <a class="btn btn-sm btn-primary" data-pending-work-item-action href="#" hidden>
                            ดูรายการหนังสือ
                        </a>
                    </div>
                </li>
            </ul>
        </div>
        <button
            class="pending-work-alert__dismiss"
            type="button"
            aria-label="ปิดการแจ้งเตือนงานที่ต้องจัดการ"
            data-pending-work-alert-dismiss
        >
            <span aria-hidden="true">&times;</span>
        </button>
    </aside>
</div>
