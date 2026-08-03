@php
    $status_text = $total_count === 0
        ? 'No books'
        : ($received_count === $total_count
            ? "Received all {$received_count}/{$total_count}"
            : "Received {$received_count}/{$total_count}");
@endphp

<div class="book-receipt-status" data-student-id="{{ $student_id }}">
    <span class="badge {{ $total_count > 0 && $received_count === $total_count ? 'badge-success' : 'badge-secondary' }}">
        {{ $status_text }}
    </span>
    @can('student-list')
        <button
            type="button"
            class="btn btn-sm btn-outline-primary btn-book-receipts"
            data-student-id="{{ $student_id }}"
            data-student-name="{{ $student_name }}"
            aria-label="Manage book receipts for {{ $student_code }} {{ $student_name }}"
        >
            Manage
        </button>
    @endcan
</div>
