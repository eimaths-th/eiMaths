@extends('layouts.credit-note-print')

@section('content')
    <main class="credit-note-print-content" aria-labelledby="credit-note-print-title">
        @include('credit-notes.documents.credit-note', ['credit_note' => $credit_note])
        <div class="credit-note-print-actions" aria-label="Print actions">
            <button class="credit-note-print-action credit-note-print-action-primary" type="button" onclick="window.print()">
                <span aria-hidden="true">&#128438;</span> Print
            </button>
            <a class="credit-note-print-action credit-note-print-action-danger" href="{{ route('credit-notes.index') }}">
                <span aria-hidden="true">&#10005;</span> Close
            </a>
        </div>
    </main>
@endsection
