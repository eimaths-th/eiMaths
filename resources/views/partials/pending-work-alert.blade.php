<li
    class="nav-item dropdown pending-work-notification"
    data-pending-work-alert
    data-storage-key="pending-work-alert:{{ $authenticated_user->id }}"
>
    <button
        class="nav-link pending-work-notification__toggle"
        type="button"
        data-pending-work-alert-toggle
        aria-controls="pending-work-alert-panel-{{ $authenticated_user->id }}"
        aria-expanded="false"
        aria-haspopup="true"
        aria-label="ดูงานที่ต้องจัดการ"
    >
        <i class="fas fa-bell" aria-hidden="true"></i>
        <span
            class="pending-work-notification__badge"
            data-pending-work-alert-badge
            aria-hidden="true"
            hidden
        ></span>
    </button>

    <div
        class="pending-work-notification__panel"
        id="pending-work-alert-panel-{{ $authenticated_user->id }}"
        data-pending-work-alert-panel
        role="region"
        aria-live="polite"
        aria-labelledby="pending-work-alert-title-{{ $authenticated_user->id }}"
        hidden
    >
        <div class="pending-work-notification__header">
            <div>
                <h2
                    class="pending-work-notification__title"
                    id="pending-work-alert-title-{{ $authenticated_user->id }}"
                >
                    งานที่ต้องจัดการ
                </h2>
                <span class="pending-work-notification__total" data-pending-work-alert-total>
                    ไม่มีงานค้าง
                </span>
            </div>
            <button
                class="pending-work-notification__dismiss"
                type="button"
                aria-label="ปิดรายการงานที่ต้องจัดการ"
                data-pending-work-alert-dismiss
            >
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <ul class="pending-work-notification__items">
            <li class="pending-work-notification__item" data-pending-work-item="affiliate" hidden>
                <span class="pending-work-notification__item-icon" aria-hidden="true">
                    <i class="fas fa-user-check"></i>
                </span>
                <div class="pending-work-notification__item-content">
                    <div class="pending-work-notification__item-heading">
                        <strong data-pending-work-item-title>Affiliate account</strong>
                        <span class="pending-work-notification__count" data-pending-work-item-count></span>
                    </div>
                    <p data-pending-work-item-message></p>
                    <a class="btn btn-sm btn-primary pending-work-notification__item-action" data-pending-work-item-action href="#" hidden>
                        ดูรายการ Affiliate ที่รออนุมัติ
                    </a>
                </div>
            </li>
            <li class="pending-work-notification__item" data-pending-work-item="course_application" hidden>
                <span class="pending-work-notification__item-icon" aria-hidden="true">
                    <i class="fas fa-clipboard-list"></i>
                </span>
                <div class="pending-work-notification__item-content">
                    <div class="pending-work-notification__item-heading">
                        <strong data-pending-work-item-title>Affiliate Course Applications</strong>
                        <span class="pending-work-notification__count" data-pending-work-item-count></span>
                    </div>
                    <p data-pending-work-item-message></p>
                    <a class="btn btn-sm btn-primary pending-work-notification__item-action" data-pending-work-item-action href="#" hidden>
                        ดูใบสมัครคอร์ส
                    </a>
                </div>
            </li>
            <li class="pending-work-notification__item" data-pending-work-item="withdrawal" hidden>
                <span class="pending-work-notification__item-icon" aria-hidden="true">
                    <i class="fas fa-money-bill-wave"></i>
                </span>
                <div class="pending-work-notification__item-content">
                    <div class="pending-work-notification__item-heading">
                        <strong data-pending-work-item-title>Affiliate withdrawal management</strong>
                        <span class="pending-work-notification__count" data-pending-work-item-count></span>
                    </div>
                    <p data-pending-work-item-message></p>
                    <a class="btn btn-sm btn-primary pending-work-notification__item-action" data-pending-work-item-action href="#" hidden>
                        จัดการคำขอถอนเงิน
                    </a>
                </div>
            </li>
            <li class="pending-work-notification__item" data-pending-work-item="book" hidden>
                <span class="pending-work-notification__item-icon" aria-hidden="true">
                    <i class="fas fa-book"></i>
                </span>
                <div class="pending-work-notification__item-content">
                    <div class="pending-work-notification__item-heading">
                        <strong data-pending-work-item-title>รายการหนังสือ</strong>
                        <span class="pending-work-notification__count" data-pending-work-item-count></span>
                    </div>
                    <p data-pending-work-item-message></p>
                    <a class="btn btn-sm btn-primary pending-work-notification__item-action" data-pending-work-item-action href="#" hidden>
                        ดูรายการหนังสือ
                    </a>
                </div>
            </li>
        </ul>

        <p class="pending-work-notification__empty" data-pending-work-alert-empty>
            ยังไม่มีงานที่ต้องจัดการ
        </p>
    </div>
</li>
