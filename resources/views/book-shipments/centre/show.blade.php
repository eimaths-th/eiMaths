@extends('layouts.app')

@section('style')
    <style>
        .centre-receipt-page {
            --centre-ink: #343a40;
            --centre-paper: #f4f6f9;
            --centre-line: #dee2e6;
            --centre-primary: #007bff;
            background: var(--centre-paper);
            color: var(--centre-ink);
            min-height: calc(100vh - 57px);
        }

        .centre-receipt-hero {
            background: #fff;
            border-bottom: 1px solid var(--centre-line);
            color: var(--centre-ink);
            padding: 1rem 0;
        }

        .centre-receipt-hero h1 {
            color: var(--centre-ink);
            font-size: clamp(1.45rem, 3vw, 2rem);
            font-weight: 700;
            letter-spacing: normal;
            margin: .35rem 0 .3rem;
        }

        .centre-receipt-hero p {
            color: #6c757d;
            margin: 0;
        }

        .centre-receipt-back {
            color: var(--centre-primary);
            font-size: .85rem;
            font-weight: 700;
        }

        .centre-receipt-back:hover,
        .centre-receipt-back:focus {
            color: #0056b3;
        }

        .centre-receipt-content {
            padding: 1rem 0 2rem;
        }

        .centre-receipt-card {
            background: #fff;
            border: 1px solid var(--centre-line);
            border-radius: .25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
        }

        .centre-receipt-card-header {
            background: var(--centre-primary);
            border-bottom: 0;
            color: #fff;
            padding: .75rem 1.25rem;
        }

        .centre-receipt-card-header h2 {
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
        }

        .centre-receipt-table thead th {
            background: #f8fafc;
            border-bottom: 2px solid var(--centre-line);
            color: #495057;
            font-size: .72rem;
            letter-spacing: .05em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .centre-receipt-table td {
            vertical-align: middle;
        }

        .centre-receipt-page .form-control:focus,
        .centre-receipt-page .custom-select:focus,
        .centre-receipt-page a:focus,
        .centre-receipt-page button:focus,
        .centre-receipt-page select:focus,
        .centre-receipt-page textarea:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .2);
            outline: 3px solid rgba(0, 123, 255, .25);
            outline-offset: 2px;
        }

        .centre-receipt-submit {
            background: #28a745;
            border-color: #28a745;
            color: #fff;
            font-weight: 700;
        }

        .centre-receipt-submit:hover,
        .centre-receipt-submit:focus {
            background: #218838;
            border-color: #1e7e34;
            color: #fff;
        }

        .centre-receipt-status {
            border-radius: 999px;
            display: inline-block;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .03em;
            padding: .28rem .55rem;
        }

        .centre-receipt-status--pending { background: #fff4d7; color: #8b6419; }
        .centre-receipt-status--received { background: #e1f4ed; color: #216f58; }
        .centre-receipt-status--missing,
        .centre-receipt-status--damaged { background: #fde8e2; color: #9a3e2d; }

        .centre-receipt-live {
            min-height: 1.5rem;
        }

        @media (prefers-reduced-motion: reduce) {
            .centre-receipt-page *,
            .centre-receipt-page *::before,
            .centre-receipt-page *::after {
                scroll-behavior: auto !important;
                transition: none !important;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $is_dispatched = $shipment->status === \App\Models\BookShipment::STATUS_DISPATCHED;
        $status_labels = [
            \App\Models\BookShipmentItem::CENTRE_STATUS_PENDING => trans('book-shipments.centre_status.PENDING', []),
            \App\Models\BookShipmentItem::CENTRE_STATUS_RECEIVED => trans('book-shipments.centre_status.RECEIVED', []),
            \App\Models\BookShipmentItem::CENTRE_STATUS_MISSING => trans('book-shipments.centre_status.MISSING', []),
            \App\Models\BookShipmentItem::CENTRE_STATUS_DAMAGED => trans('book-shipments.centre_status.DAMAGED', []),
        ];
    @endphp

    <div class="centre-receipt-page">
        <section class="centre-receipt-hero" aria-labelledby="centre-receipt-page-title">
            <div class="container-fluid">
                <a class="centre-receipt-back" href="{{ route('incoming-book-shipments.index') }}">← {{ trans('book-shipments.centre.back_to_incoming', []) }}</a>
                <h1 id="centre-receipt-page-title">{{ $shipment->shipment_number }}</h1>
                <p>{{ $shipment->destination_centre_name ?: trans('book-shipments.centre.destination_fallback', []) }} · {{ trans('book-shipments.centre.item_count', ['count' => $shipment->Items->count()]) }}</p>
            </div>
        </section>

        <main class="centre-receipt-content">
            <div class="container-fluid">
                <div class="centre-receipt-live mb-3" id="centre-receipt-status" aria-live="polite"></div>

                <section class="centre-receipt-card mb-4" aria-labelledby="centre-receipt-action-title">
                    <div class="centre-receipt-card-header d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h2 id="centre-receipt-action-title">{{ $is_dispatched ? trans('book-shipments.centre.receive_shipment', []) : trans('book-shipments.centre.shipment_received', []) }}</h2>
                            <div class="small text-muted mt-1">
                                {{ $is_dispatched ? trans('book-shipments.centre.record_results', []) : trans('book-shipments.centre.receipt_locked', []) }}
                            </div>
                        </div>
                        @if ($shipment->centre_received_at)
                            <span class="small text-muted">{{ trans('book-shipments.centre.received_at', []) }} {{ $shipment->centre_received_at->format('d/m/Y H:i') }}</span>
                        @endif
                    </div>

                    <form id="centre-receipt-form" class="p-3" data-receive-form="true"
                        data-action-url="{{ route('incoming-book-shipments.receive', ['book_shipment' => $shipment->id]) }}">
                        <div class="table-responsive">
                            <table class="table centre-receipt-table">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ trans('book-shipments.centre.student', []) }}</th>
                                        <th scope="col">{{ trans('book-shipments.centre.book', []) }}</th>
                                        <th scope="col">{{ trans('book-shipments.centre.order', []) }}</th>
                                        <th scope="col">{{ trans('book-shipments.centre.result', []) }}</th>
                                        <th scope="col">{{ trans('book-shipments.centre.note', []) }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($shipment->Items as $item)
                                        @php
                                            $item_status = $item->centre_status ?: \App\Models\BookShipmentItem::CENTRE_STATUS_PENDING;
                                            $note_id = 'centre-receipt-note-' . $item->id;
                                        @endphp
                                        <tr data-receipt-item="{{ $item->id }}">
                                            <td>
                                                <strong>{{ $item->source_student_code ?: trans('book-shipments.centre.student_fallback', []) }}</strong>
                                                <div class="small text-muted">{{ $item->source_student_name ?: trans('book-shipments.centre.unnamed_student', []) }}</div>
                                            </td>
                                            <td>
                                                <strong>{{ $item->book_name }}</strong>
                                                <div class="small text-muted">{{ $item->level ?: trans('book-shipments.centre.no_level', []) }} · {{ trans('book-shipments.centre.term', []) }} {{ $item->term ?: '—' }}</div>
                                            </td>
                                            <td>{{ $item->source_order_number ?: '—' }}</td>
                                            <td>
                                                @if ($is_dispatched)
                                                    <label class="sr-only" for="centre-receipt-result-{{ $item->id }}">{{ trans('book-shipments.centre.result_for', ['book' => $item->book_name]) }}</label>
                                                    <select id="centre-receipt-result-{{ $item->id }}" class="custom-select" data-receipt-status="{{ $item->id }}">
                                                        <option value="">{{ trans('book-shipments.centre.choose_result', []) }}</option>
                                                        @foreach ([
                                                            \App\Models\BookShipmentItem::CENTRE_STATUS_RECEIVED,
                                                            \App\Models\BookShipmentItem::CENTRE_STATUS_MISSING,
                                                            \App\Models\BookShipmentItem::CENTRE_STATUS_DAMAGED,
                                                        ] as $status_value)
                                                            <option value="{{ $status_value }}">{{ $status_labels[$status_value] }}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <span class="centre-receipt-status centre-receipt-status--{{ strtolower($item_status) }}">
                                                        {{ $status_labels[$item_status] ?? $item_status }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($is_dispatched)
                                                    <label class="sr-only" for="{{ $note_id }}">{{ trans('book-shipments.centre.note_for', ['book' => $item->book_name]) }}</label>
                                                    <textarea id="{{ $note_id }}" class="form-control" rows="2" maxlength="1000"
                                                        data-receipt-note="{{ $item->id }}" placeholder="{{ trans('book-shipments.centre.note_required_placeholder', []) }}"></textarea>
                                                @else
                                                    {{ $item->centre_note ?: trans('book-shipments.centre.no_note', []) }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($is_dispatched)
                            <div class="d-flex flex-wrap align-items-center justify-content-between mt-3">
                                <p class="small text-muted mb-2 mb-md-0">{{ trans('book-shipments.centre.missing_or_damaged_note', []) }}</p>
                                <button class="btn centre-receipt-submit" type="submit">{{ trans('book-shipments.centre.confirm_receipt', []) }}</button>
                            </div>
                        @else
                            <div class="alert alert-success mb-3">{{ trans('book-shipments.centre.receipt_recorded', []) }}</div>
                            @if (auth()->user()->can('student-list'))
                                <a class="btn btn-primary" href="{{ url('/contacts?shipment_id=' . $shipment->id) }}">{{ trans('book-shipments.centre.open_student_list', []) }}</a>
                            @endif
                        @endif
                    </form>
                </section>

                @include('book-shipments.hq.partials.progress', ['shipment' => $shipment])
                @include('book-shipments.hq.partials.history', ['shipment' => $shipment])
            </div>
        </main>
    </div>
@endsection

@if ($is_dispatched)
    @section('script')
        @include('book-shipments.centre.script')
    @endsection
@endif
