<section aria-labelledby="credit-note-timeline-title" class="credit-note-timeline">
    <div class="credit-note-section-heading">
        <div>
            <span class="credit-note-section-kicker">Audit trail</span>
            <h3 id="credit-note-timeline-title">Document timeline</h3>
        </div>
        <i class="fas fa-history" aria-hidden="true"></i>
    </div>
    <div class="credit-note-timeline-grid">
        <div class="credit-note-timeline-entry">
            <span class="credit-note-timeline-dot credit-note-timeline-dot-created" aria-hidden="true"></span>
            <span class="credit-note-timeline-label">Created by</span>
            <strong>{{ $credit_note->CreatedBy?->name ?? '—' }}</strong>
        </div>
        <div class="credit-note-timeline-entry">
            <span class="credit-note-timeline-dot credit-note-timeline-dot-issued" aria-hidden="true"></span>
            <span class="credit-note-timeline-label">Issued by</span>
            <strong>{{ $credit_note->IssuedBy?->name ?? '—' }}</strong>
        </div>
        <div class="credit-note-timeline-entry">
            <span class="credit-note-timeline-dot credit-note-timeline-dot-cancelled" aria-hidden="true"></span>
            <span class="credit-note-timeline-label">Cancelled by</span>
            <strong>{{ $credit_note->CancelledBy?->name ?? '—' }}</strong>
        </div>
    </div>
</section>
