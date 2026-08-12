@if (auth()->user()?->can('credit-note-create'))
    <form method="POST" action="{{ route('credit-notes.store', $receipt) }}" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-warning">{{ __('credit-note.actions.create') }}</button>
    </form>
@else
    <span class="text-muted">{{ __('credit-note.actions.unavailable') }}</span>
@endif
