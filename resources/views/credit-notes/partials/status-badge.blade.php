@php($status = $credit_note->status?->value ?? 'unknown')
<span class="credit-note-status credit-note-status-{{ $status }}" role="status">
    {{ __('credit-note.statuses.' . $status) }}
</span>
