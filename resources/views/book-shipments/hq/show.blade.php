@extends('layouts.app')

@section('style')
    <style>
        .book-shipment-detail {
            background: #f4f6f9;
            color: #343a40;
            min-height: calc(100vh - 57px);
        }

        .book-shipment-detail-hero {
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            color: #343a40;
            padding: 1rem 0;
        }

        .book-shipment-detail-hero h1 {
            color: #343a40;
            font-size: clamp(1.45rem, 3vw, 2rem);
            font-weight: 700;
            letter-spacing: normal;
            margin: .25rem 0 .2rem;
        }

        .book-shipment-detail-hero p {
            color: #6c757d;
            margin: 0;
        }

        .book-shipment-detail-back {
            color: #007bff;
            font-size: .85rem;
            font-weight: 600;
        }

        .book-shipment-detail-back:hover,
        .book-shipment-detail-back:focus {
            color: #0056b3;
        }

        .book-shipment-detail-content {
            padding: 1rem 0 2rem;
        }

        .book-shipment-detail-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: .25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
        }

        .book-shipment-detail-card-header {
            background: #007bff;
            border-bottom: 0;
            color: #fff;
            padding: .75rem 1.25rem;
        }

        .book-shipment-detail-card-header h2 {
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
        }

        .book-shipment-danger-card {
            border-color: #dc3545;
        }

        .book-shipment-danger-card .book-shipment-detail-card-header {
            background: #dc3545;
            color: #fff;
        }

        .book-shipment-snapshot dt {
            color: #68778a;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .book-shipment-snapshot dd {
            margin-bottom: 1rem;
        }

        .book-shipment-item-table thead th {
            background: #f8fafc;
            color: #536174;
            font-size: .72rem;
            letter-spacing: .05em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .book-shipment-detail a:focus,
        .book-shipment-detail button:focus,
        .book-shipment-detail input:focus {
            outline: 3px solid rgba(0, 123, 255, .25);
            outline-offset: 2px;
        }

        @media (prefers-reduced-motion: reduce) {
            .book-shipment-detail *,
            .book-shipment-detail *::before,
            .book-shipment-detail *::after {
                scroll-behavior: auto !important;
                transition: none !important;
            }
        }

        @include('book-shipments.hq.editor-style')
    </style>
@endsection

@section('content')
    @php
        $derived_status_labels = [
            \App\Models\BookShipment::DERIVED_DRAFT => trans('book-shipments.status.DRAFT', [], 'th'),
            \App\Models\BookShipment::DERIVED_WAITING_FOR_CENTRE => trans('book-shipments.status.WAITING_FOR_CENTRE', [], 'th'),
            \App\Models\BookShipment::DERIVED_PENDING_STUDENT_DELIVERY => trans('book-shipments.status.PENDING_STUDENT_DELIVERY', [], 'th'),
            \App\Models\BookShipment::DERIVED_COMPLETED => trans('book-shipments.status.COMPLETED', [], 'th'),
            \App\Models\BookShipment::DERIVED_COMPLETED_WITH_ISSUES => trans('book-shipments.status.COMPLETED_WITH_ISSUES', [], 'th'),
            \App\Models\BookShipment::DERIVED_CANCELLED => trans('book-shipments.status.CANCELLED', [], 'th'),
        ];
        $centre_status_labels = [
            \App\Models\BookShipmentItem::CENTRE_STATUS_PENDING => trans('book-shipments.centre_status.PENDING', [], 'th'),
            \App\Models\BookShipmentItem::CENTRE_STATUS_RECEIVED => trans('book-shipments.centre_status.RECEIVED', [], 'th'),
            \App\Models\BookShipmentItem::CENTRE_STATUS_MISSING => trans('book-shipments.centre_status.MISSING', [], 'th'),
            \App\Models\BookShipmentItem::CENTRE_STATUS_DAMAGED => trans('book-shipments.centre_status.DAMAGED', [], 'th'),
        ];
        $derived_status = $shipment->derived_status ?? $shipment->status;
    @endphp

    <div class="book-shipment-detail" data-book-shipment-detail data-shipment-id="{{ $shipment->id }}">
        <section class="book-shipment-detail-hero" aria-labelledby="book-shipment-detail-title">
            <div class="container-fluid">
                <a class="book-shipment-detail-back" href="{{ route('book-shipments.index') }}">&larr; {{ trans('book-shipments.hq.back_to_ledger', [], 'th') }}</a>
                <div class="d-flex flex-wrap align-items-center justify-content-between mt-2">
                    <div>
                        <h1 id="book-shipment-detail-title">{{ $shipment->shipment_number }}</h1>
                        <p>{{ $shipment->destination_centre_code }} — {{ $shipment->destination_centre_name }}</p>
                    </div>
                    <span class="book-shipment-status book-shipment-status--{{ match ($derived_status) {
                        \App\Models\BookShipment::DERIVED_COMPLETED => 'complete',
                        \App\Models\BookShipment::DERIVED_COMPLETED_WITH_ISSUES => 'issue',
                        \App\Models\BookShipment::DERIVED_CANCELLED => 'cancelled',
                        \App\Models\BookShipment::DERIVED_PENDING_STUDENT_DELIVERY => 'pending',
                        \App\Models\BookShipment::DERIVED_WAITING_FOR_CENTRE => 'waiting',
                        default => 'draft',
                    } }}">
                        {{ $derived_status_labels[$derived_status] ?? $derived_status }}
                    </span>
                </div>
            </div>
        </section>

        <main class="book-shipment-detail-content">
            <div class="container-fluid">
                <div class="book-shipment-live mb-3" id="book-shipment-detail-status" aria-live="polite"></div>

                @if ($shipment->status === \App\Models\BookShipment::STATUS_DRAFT)
                    @can('book-shipment-create')
                        @include('book-shipments.hq.create', ['shipment' => $shipment, 'destinations' => collect()])
                    @endcan
                @endif

                <section class="book-shipment-detail-card mb-4" aria-labelledby="book-shipment-snapshot-title">
                    <div class="book-shipment-detail-card-header">
                        <h2 id="book-shipment-snapshot-title">{{ trans('book-shipments.hq.frozen_details', [], 'th') }}</h2>
                    </div>
                    <div class="p-3">
                        <dl class="row book-shipment-snapshot mb-0">
                            <div class="col-md-3"><dt>{{ trans('book-shipments.hq.destination_centre', [], 'th') }}</dt><dd>{{ $shipment->destination_centre_name ?: '—' }}</dd></div>
                            <div class="col-md-3"><dt>{{ trans('book-shipments.hq.created_by', [], 'th') }}</dt><dd>{{ $shipment->created_by_name ?: '—' }}</dd></div>
                            <div class="col-md-3"><dt>{{ trans('book-shipments.hq.dispatched_by', [], 'th') }}</dt><dd>{{ $shipment->dispatched_by_name ?: '—' }}</dd></div>
                            <div class="col-md-3"><dt>{{ trans('book-shipments.hq.centre_received_by', [], 'th') }}</dt><dd>{{ $shipment->centre_received_by_name ?: '—' }}</dd></div>
                            <div class="col-md-3"><dt>{{ trans('book-shipments.hq.created_at', [], 'th') }}</dt><dd>{{ $shipment->created_at?->format('d/m/Y H:i') ?: '—' }}</dd></div>
                            <div class="col-md-3"><dt>{{ trans('book-shipments.hq.dispatched_at', [], 'th') }}</dt><dd>{{ $shipment->dispatched_at?->format('d/m/Y H:i') ?: '—' }}</dd></div>
                            <div class="col-md-3"><dt>{{ trans('book-shipments.hq.centre_received_at', [], 'th') }}</dt><dd>{{ $shipment->centre_received_at?->format('d/m/Y H:i') ?: '—' }}</dd></div>
                            <div class="col-md-3"><dt>{{ trans('book-shipments.hq.stored_status', [], 'th') }}</dt><dd>{{ trans('book-shipments.stored_status.' . $shipment->status, [], 'th') }}</dd></div>
                        </dl>
                        @if ($shipment->cancellation_reason)
                            <div class="alert alert-warning mb-0" role="note">
                                <strong>{{ trans('book-shipments.hq.cancellation_reason', [], 'th') }}:</strong> {{ $shipment->cancellation_reason }}
                            </div>
                        @endif
                    </div>
                </section>

                @include('book-shipments.hq.partials.progress', ['shipment' => $shipment])

                <section class="book-shipment-detail-card mb-4" aria-labelledby="book-shipment-items-title">
                    <div class="book-shipment-detail-card-header d-flex align-items-center justify-content-between">
                        <h2 id="book-shipment-items-title">{{ trans('book-shipments.hq.shipment_items', [], 'th') }}</h2>
                        <span class="small text-muted">{{ trans('book-shipments.hq.frozen_items_count', ['count' => $shipment->Items->count()], 'th') }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table book-shipment-item-table table-hover mb-0">
                            <caption class="sr-only">{{ trans('book-shipments.hq.frozen_books_caption', [], 'th') }}</caption>
                            <thead>
                                <tr>
                                    <th scope="col">{{ trans('book-shipments.hq.student', [], 'th') }}</th>
                                    <th scope="col">{{ trans('book-shipments.hq.book', [], 'th') }}</th>
                                    <th scope="col">{{ trans('book-shipments.hq.level_term', [], 'th') }}</th>
                                    <th scope="col">{{ trans('book-shipments.hq.order', [], 'th') }}</th>
                                    <th scope="col">{{ trans('book-shipments.hq.centre_status', [], 'th') }}</th>
                                    <th scope="col">{{ trans('book-shipments.hq.issue_note', [], 'th') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($shipment->Items as $item)
                                    <tr>
                                        <td><strong>{{ $item->source_student_code ?: trans('book-shipments.hq.student_fallback', [], 'th') }}</strong><div class="small text-muted">{{ $item->source_student_name ?: trans('book-shipments.hq.unnamed_student', [], 'th') }}</div></td>
                                        <td>{{ $item->book_name }}</td>
                                        <td>{{ $item->level ?: '—' }} / {{ $item->term ?: '—' }}</td>
                                        <td>{{ $item->source_order_number ?: '—' }}</td>
                                        <td>{{ $centre_status_labels[$item->centre_status] ?? $item->centre_status }}</td>
                                        <td>{{ $item->centre_note ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="text-muted text-center p-4" colspan="6">{{ trans('book-shipments.hq.no_shipment_items', [], 'th') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                @if ($shipment->status === \App\Models\BookShipment::STATUS_DRAFT && auth()->user()->can('book-shipment-dispatch'))
                    <section class="book-shipment-detail-card mb-4" aria-labelledby="book-shipment-dispatch-title">
                        <div class="book-shipment-detail-card-header"><h2 id="book-shipment-dispatch-title">{{ trans('book-shipments.hq.ready_to_dispatch', [], 'th') }}</h2></div>
                        <div class="p-3">
                            <p class="small text-muted">{{ trans('book-shipments.hq.dispatch_description', [], 'th') }}</p>
                            <form method="POST" action="{{ route('book-shipments.dispatch', ['book_shipment' => $shipment->id]) }}" data-transition-form data-transition="dispatch" data-action-url="{{ route('book-shipments.dispatch', ['book_shipment' => $shipment->id]) }}">
                                @csrf
                                <button class="btn btn-primary" type="submit">{{ trans('book-shipments.hq.dispatch', [], 'th') }}</button>
                            </form>
                        </div>
                    </section>
                @endif

                @if ($shipment->status === \App\Models\BookShipment::STATUS_DRAFT && auth()->user()->can('book-shipment-cancel'))
                    <section class="book-shipment-detail-card book-shipment-danger-card mb-4" aria-labelledby="book-shipment-discard-title">
                        <div class="book-shipment-detail-card-header"><h2 id="book-shipment-discard-title">{{ trans('book-shipments.hq.discard_draft', [], 'th') }}</h2></div>
                        <div class="p-3">
                            <p class="small text-muted">{{ trans('book-shipments.hq.discard_description', [], 'th') }}</p>
                            <form method="POST" action="{{ route('book-shipments.discard', ['book_shipment' => $shipment->id]) }}" data-transition-form data-transition="discard" data-action-url="{{ route('book-shipments.discard', ['book_shipment' => $shipment->id]) }}" data-success-url="{{ route('book-shipments.index') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="book-shipment-discard-reason">{{ trans('book-shipments.hq.draft_discard_reason', [], 'th') }} <span class="text-danger">{{ trans('book-shipments.hq.required', [], 'th') }}</span></label>
                                    <textarea class="form-control" id="book-shipment-discard-reason" name="reason" rows="3" minlength="3" maxlength="1000" aria-describedby="book-shipment-discard-error"></textarea>
                                    <div class="invalid-feedback d-block" id="book-shipment-discard-error" aria-live="polite"></div>
                                </div>
                                <button class="btn btn-danger" type="submit">{{ trans('book-shipments.hq.discard_draft', [], 'th') }}</button>
                            </form>
                        </div>
                    </section>
                @endif

                @if ($shipment->status === \App\Models\BookShipment::STATUS_DISPATCHED && auth()->user()->can('book-shipment-cancel'))
                    <section class="book-shipment-detail-card mb-4" aria-labelledby="book-shipment-cancel-title">
                        <div class="book-shipment-detail-card-header"><h2 id="book-shipment-cancel-title">{{ trans('book-shipments.hq.cancel_before_receipt', [], 'th') }}</h2></div>
                        <div class="p-3">
                            <form method="POST" action="{{ route('book-shipments.cancel', ['book_shipment' => $shipment->id]) }}" data-transition-form data-transition="cancel" data-action-url="{{ route('book-shipments.cancel', ['book_shipment' => $shipment->id]) }}">
                                @csrf
                                <div class="form-group">
                                    <label for="book-shipment-cancellation-reason">{{ trans('book-shipments.hq.cancellation_reason', [], 'th') }} <span class="text-danger">{{ trans('book-shipments.hq.required', [], 'th') }}</span></label>
                                    <textarea class="form-control" id="book-shipment-cancellation-reason" name="reason" rows="3" minlength="3" maxlength="1000" aria-describedby="book-shipment-cancellation-error"></textarea>
                                    <div class="invalid-feedback d-block" id="book-shipment-cancellation-error" aria-live="polite"></div>
                                </div>
                                <button class="btn btn-outline-danger" type="submit">{{ trans('book-shipments.hq.cancel', [], 'th') }}</button>
                            </form>
                        </div>
                    </section>
                @endif

                @if ($shipment->status === \App\Models\BookShipment::STATUS_CENTRE_RECEIVED && auth()->user()->can('student-list'))
                    <div class="mb-4">
                        <a class="btn btn-primary" href="{{ url('/contacts?shipment_id=' . $shipment->id) }}">{{ trans('book-shipments.hq.open_student_list', [], 'th') }}</a>
                    </div>
                @endif

                @include('book-shipments.hq.partials.history', ['shipment' => $shipment])
            </div>
        </main>
    </div>
@endsection

@section('script')
    @include('book-shipments.hq.script')
@endsection
