@extends('layouts.app')

@section('style')
    <link rel="stylesheet" href="{{ asset('css/credit-notes.css') }}">
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1>{{ __('credit-note.navigation.title') }}</h1>
            <a class="btn btn-outline-primary" href="{{ route('credit-notes.index') }}">{{ __('credit-note.actions.refresh') }}</a>
        </div>
    </section>
    <section class="content" aria-labelledby="credit-note-register-title">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header"><h2 id="credit-note-register-title" class="card-title">{{ __('credit-note.navigation.register') }}</h2></div>
                <div class="card-body">
                    <p id="credit-note-feedback" class="sr-only" aria-live="polite" data-error-message="{{ __('credit-note.accessibility.error') }}"></p>
                    <div class="table-responsive">
                        <table class="table table-striped credit-note-table">
                            <caption class="sr-only">{{ __('credit-note.register.caption') }}</caption>
                            <thead><tr>
                                <th scope="col">{{ __('credit-note.register.number') }}</th><th scope="col">{{ __('credit-note.register.status') }}</th><th scope="col">{{ __('credit-note.register.original_receipt') }}</th>
                                <th scope="col">{{ __('credit-note.register.adjustment_date') }}</th><th scope="col">{{ __('credit-note.register.difference') }}</th><th scope="col">{{ __('credit-note.register.actions') }}</th>
                            </tr></thead>
                            <tbody>
                            @forelse ($credit_notes as $credit_note)
                                <tr>
                                    <td>{{ $credit_note->number ?? __('credit-note.register.draft_number', ['id' => $credit_note->id]) }}</td>
                                    <td>@include('credit-notes.partials.status-badge', ['credit_note' => $credit_note])</td>
                                    <td>{{ $credit_note->original_receipt_number ?? '—' }}</td>
                                    <td>{{ optional($credit_note->adjustment_event_date)->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ number_format(((int) $credit_note->difference_grand_total_minor) / 100, 2, '.', ',') }}</td>
                                    <td>@include('credit-notes.partials.list-actions', ['credit_note' => $credit_note])</td>
                                </tr>
                            @empty
                                <tr><td colspan="6">{{ __('credit-note.register.empty') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $credit_notes->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script src="{{ asset('js/credit-note-index.js') }}" defer></script>
@endsection
