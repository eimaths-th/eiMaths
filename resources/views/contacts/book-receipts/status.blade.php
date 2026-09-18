@php
    $status_text = $total_count === 0
        ? __('students.book_receipts.no_books')
        : ($received_count === $total_count
            ? __('students.book_receipts.received_all', ['received' => $received_count, 'total' => $total_count])
            : __('students.book_receipts.received_some', ['received' => $received_count, 'total' => $total_count]));
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
            aria-label="{{ __('students.book_receipts.manage_for', ['code' => $student_code, 'name' => $student_name]) }}"
        >
            {{ __('students.book_receipts.manage') }}
        </button>
    @endcan
</div>
