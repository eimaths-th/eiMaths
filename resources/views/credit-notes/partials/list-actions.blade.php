@can('credit-note-show')
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('credit-notes.show', $credit_note) }}">{{ __('credit-note.actions.view') }}</a>
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('credit-notes.print', $credit_note) }}">{{ __('credit-note.actions.print') }}</a>
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('credit-notes.pdf', $credit_note) }}">{{ __('credit-note.actions.pdf') }}</a>
@endcan
@if (($credit_note->status?->value ?? null) === 'draft' && auth()->user()?->can('credit-note-edit'))
    <a class="btn btn-sm btn-outline-primary" href="{{ route('credit-notes.show', $credit_note) }}#editor">{{ __('credit-note.actions.save') }}</a>
@endif
