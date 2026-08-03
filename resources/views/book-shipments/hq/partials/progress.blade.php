@php
    $progress = $shipment->progress ?? [];
    $total_count = (int) ($progress['total_count'] ?? 0);
    $centre_received_count = (int) ($progress['centre_received_count'] ?? 0);
    $student_received_count = (int) ($progress['student_received_count'] ?? 0);
    $issue_count = (int) ($progress['missing_count'] ?? 0) + (int) ($progress['damaged_count'] ?? 0);
@endphp

<section class="book-shipment-card mb-4" aria-labelledby="book-shipment-progress-title">
    <div class="book-shipment-card-header d-flex align-items-center justify-content-between">
        <h2 id="book-shipment-progress-title">Progress at a glance</h2>
        <span class="small text-muted">{{ $student_received_count }} of {{ $centre_received_count }} Centre-received books delivered</span>
    </div>
    <div class="p-3">
        <div class="row">
            <div class="col-sm-3 mb-3 mb-sm-0">
                <div class="small text-muted">Shipment items</div>
                <strong class="h4">{{ $total_count }}</strong>
            </div>
            <div class="col-sm-3 mb-3 mb-sm-0">
                <div class="small text-muted">Centre received</div>
                <strong class="h4 text-success">{{ $centre_received_count }}</strong>
            </div>
            <div class="col-sm-3 mb-3 mb-sm-0">
                <div class="small text-muted">Student received</div>
                <strong class="h4 text-primary">{{ $student_received_count }}</strong>
            </div>
            <div class="col-sm-3">
                <div class="small text-muted">Issues</div>
                <strong class="h4 {{ $issue_count > 0 ? 'text-danger' : 'text-muted' }}">{{ $issue_count }}</strong>
            </div>
        </div>
        <div class="progress" style="height: .55rem;" aria-label="Student delivery progress">
            <div class="progress-bar bg-success" role="progressbar"
                style="width: {{ $centre_received_count > 0 ? min(100, ($student_received_count / $centre_received_count) * 100) : 0 }}%"
                aria-valuenow="{{ $student_received_count }}" aria-valuemin="0" aria-valuemax="{{ max(1, $centre_received_count) }}"></div>
        </div>
        <div class="small text-muted mt-2">Missing and damaged items stay visible as immutable shipment history.</div>
    </div>
</section>
