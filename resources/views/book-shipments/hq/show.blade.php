@extends('layouts.app')

@section('style')
    <style>
        .book-shipment-detail {
            background: #f5f7fb;
            color: #152238;
            min-height: calc(100vh - 57px);
        }

        .book-shipment-detail-hero {
            background: #152238;
            color: #fff;
            padding: 1.6rem 0;
        }

        .book-shipment-detail-hero h1 {
            font-size: clamp(1.55rem, 3vw, 2.25rem);
            font-weight: 700;
            letter-spacing: -.03em;
            margin: .35rem 0 .25rem;
        }

        .book-shipment-detail-hero p {
            color: rgba(255, 255, 255, .7);
            margin: 0;
        }

        .book-shipment-detail-content {
            padding: 1.5rem 0 3rem;
        }

        .book-shipment-detail-card {
            background: #fff;
            border: 1px solid #dbe2ec;
            border-radius: .45rem;
            box-shadow: 0 8px 24px rgba(21, 34, 56, .05);
        }

        .book-shipment-detail-card-header {
            border-bottom: 1px solid #dbe2ec;
            padding: 1rem 1.25rem;
        }

        .book-shipment-detail-card-header h2 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
        }

        .book-shipment-danger-card {
            border-color: #f1b8bd;
        }

        .book-shipment-danger-card .book-shipment-detail-card-header {
            background: #fff5f5;
            color: #842029;
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
            outline: 3px solid rgba(242, 184, 75, .55);
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
            \App\Models\BookShipment::DERIVED_DRAFT => 'Draft',
            \App\Models\BookShipment::DERIVED_WAITING_FOR_CENTRE => 'Waiting for Centre',
            \App\Models\BookShipment::DERIVED_PENDING_STUDENT_DELIVERY => 'Pending student delivery',
            \App\Models\BookShipment::DERIVED_COMPLETED => 'Completed',
            \App\Models\BookShipment::DERIVED_COMPLETED_WITH_ISSUES => 'Completed with issues',
            \App\Models\BookShipment::DERIVED_CANCELLED => 'Cancelled',
        ];
        $derived_status = $shipment->derived_status ?? $shipment->status;
    @endphp

    <div class="book-shipment-detail" data-book-shipment-detail data-shipment-id="{{ $shipment->id }}">
        <section class="book-shipment-detail-hero" aria-labelledby="book-shipment-detail-title">
            <div class="container-fluid">
                <a class="text-light small" href="{{ route('book-shipments.index') }}">&larr; Back to shipment ledger</a>
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
                        <h2 id="book-shipment-snapshot-title">Frozen shipment details</h2>
                    </div>
                    <div class="p-3">
                        <dl class="row book-shipment-snapshot mb-0">
                            <div class="col-md-3"><dt>Destination Centre</dt><dd>{{ $shipment->destination_centre_name ?: '—' }}</dd></div>
                            <div class="col-md-3"><dt>Created by</dt><dd>{{ $shipment->created_by_name ?: '—' }}</dd></div>
                            <div class="col-md-3"><dt>Dispatched by</dt><dd>{{ $shipment->dispatched_by_name ?: '—' }}</dd></div>
                            <div class="col-md-3"><dt>Centre received by</dt><dd>{{ $shipment->centre_received_by_name ?: '—' }}</dd></div>
                            <div class="col-md-3"><dt>Created at</dt><dd>{{ $shipment->created_at?->format('d M Y H:i') ?: '—' }}</dd></div>
                            <div class="col-md-3"><dt>Dispatched at</dt><dd>{{ $shipment->dispatched_at?->format('d M Y H:i') ?: '—' }}</dd></div>
                            <div class="col-md-3"><dt>Centre received at</dt><dd>{{ $shipment->centre_received_at?->format('d M Y H:i') ?: '—' }}</dd></div>
                            <div class="col-md-3"><dt>Stored status</dt><dd>{{ $shipment->status }}</dd></div>
                        </dl>
                        @if ($shipment->cancellation_reason)
                            <div class="alert alert-warning mb-0" role="note">
                                <strong>Cancellation reason:</strong> {{ $shipment->cancellation_reason }}
                            </div>
                        @endif
                    </div>
                </section>

                @include('book-shipments.hq.partials.progress', ['shipment' => $shipment])

                <section class="book-shipment-detail-card mb-4" aria-labelledby="book-shipment-items-title">
                    <div class="book-shipment-detail-card-header d-flex align-items-center justify-content-between">
                        <h2 id="book-shipment-items-title">Shipment items</h2>
                        <span class="small text-muted">{{ $shipment->Items->count() }} frozen item(s)</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table book-shipment-item-table table-hover mb-0">
                            <caption class="sr-only">Frozen books included in this shipment</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Student</th>
                                    <th scope="col">Book</th>
                                    <th scope="col">Level / term</th>
                                    <th scope="col">Order</th>
                                    <th scope="col">Centre status</th>
                                    <th scope="col">Issue note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($shipment->Items as $item)
                                    <tr>
                                        <td><strong>{{ $item->source_student_code }}</strong><div class="small text-muted">{{ $item->source_student_name }}</div></td>
                                        <td>{{ $item->book_name }}</td>
                                        <td>{{ $item->level ?: '—' }} / {{ $item->term ?: '—' }}</td>
                                        <td>{{ $item->source_order_number ?: '—' }}</td>
                                        <td>{{ $item->centre_status }}</td>
                                        <td>{{ $item->centre_note ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="text-muted text-center p-4" colspan="6">No shipment items.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                @if ($shipment->status === \App\Models\BookShipment::STATUS_DRAFT && auth()->user()->can('book-shipment-dispatch'))
                    <section class="book-shipment-detail-card mb-4" aria-labelledby="book-shipment-dispatch-title">
                        <div class="book-shipment-detail-card-header"><h2 id="book-shipment-dispatch-title">Ready to dispatch?</h2></div>
                        <div class="p-3">
                            <p class="small text-muted">Dispatch locks the selected books and sends one acknowledgement notification to the destination Centre.</p>
                            <form method="POST" action="{{ route('book-shipments.dispatch', ['book_shipment' => $shipment->id]) }}" data-transition-form data-transition="dispatch" data-action-url="{{ route('book-shipments.dispatch', ['book_shipment' => $shipment->id]) }}">
                                @csrf
                                <button class="btn btn-primary" type="submit">Dispatch shipment</button>
                            </form>
                        </div>
                    </section>
                @endif

                @if ($shipment->status === \App\Models\BookShipment::STATUS_DRAFT && auth()->user()->can('book-shipment-cancel'))
                    <section class="book-shipment-detail-card book-shipment-danger-card mb-4" aria-labelledby="book-shipment-discard-title">
                        <div class="book-shipment-detail-card-header"><h2 id="book-shipment-discard-title">Discard this draft</h2></div>
                        <div class="p-3">
                            <p class="small text-muted">Discarding keeps the shipment history and releases its selected books for another attempt. This action cannot be undone.</p>
                            <form method="POST" action="{{ route('book-shipments.discard', ['book_shipment' => $shipment->id]) }}" data-transition-form data-transition="discard" data-action-url="{{ route('book-shipments.discard', ['book_shipment' => $shipment->id]) }}" data-success-url="{{ route('book-shipments.index') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="book-shipment-discard-reason">Draft discard reason <span class="text-danger">(required)</span></label>
                                    <textarea class="form-control" id="book-shipment-discard-reason" name="reason" rows="3" minlength="3" maxlength="1000" aria-describedby="book-shipment-discard-error"></textarea>
                                    <div class="invalid-feedback d-block" id="book-shipment-discard-error" aria-live="polite"></div>
                                </div>
                                <button class="btn btn-danger" type="submit">Discard this draft</button>
                            </form>
                        </div>
                    </section>
                @endif

                @if ($shipment->status === \App\Models\BookShipment::STATUS_DISPATCHED && auth()->user()->can('book-shipment-cancel'))
                    <section class="book-shipment-detail-card mb-4" aria-labelledby="book-shipment-cancel-title">
                        <div class="book-shipment-detail-card-header"><h2 id="book-shipment-cancel-title">Cancel before Centre receipt</h2></div>
                        <div class="p-3">
                            <form method="POST" action="{{ route('book-shipments.cancel', ['book_shipment' => $shipment->id]) }}" data-transition-form data-transition="cancel" data-action-url="{{ route('book-shipments.cancel', ['book_shipment' => $shipment->id]) }}">
                                @csrf
                                <div class="form-group">
                                    <label for="book-shipment-cancellation-reason">Cancellation reason <span class="text-danger">(required)</span></label>
                                    <textarea class="form-control" id="book-shipment-cancellation-reason" name="reason" rows="3" minlength="3" maxlength="1000" aria-describedby="book-shipment-cancellation-error"></textarea>
                                    <div class="invalid-feedback d-block" id="book-shipment-cancellation-error" aria-live="polite"></div>
                                </div>
                                <button class="btn btn-outline-danger" type="submit">Cancel shipment</button>
                            </form>
                        </div>
                    </section>
                @endif

                @if ($shipment->status === \App\Models\BookShipment::STATUS_CENTRE_RECEIVED && auth()->user()->can('student-list'))
                    <div class="mb-4">
                        <a class="btn btn-primary" href="{{ url('/contacts?shipment_id=' . $shipment->id) }}">Open shipment Student List</a>
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
