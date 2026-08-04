@extends('layouts.app')

@section('style')
    <style>
        .centre-receipt-page {
            --centre-ink: #152238;
            --centre-paper: #f5f7fb;
            --centre-line: #dbe2ec;
            --centre-mint: #2f9b78;
            --centre-amber: #f2b84b;
            background: var(--centre-paper);
            color: var(--centre-ink);
            min-height: calc(100vh - 57px);
        }

        .centre-receipt-hero {
            background: var(--centre-ink);
            color: #fff;
            padding: 1.5rem 0 1.7rem;
        }

        .centre-receipt-hero h1 {
            font-size: clamp(1.7rem, 3vw, 2.45rem);
            font-weight: 700;
            letter-spacing: -.035em;
            margin: .55rem 0 .45rem;
        }

        .centre-receipt-hero p {
            color: rgba(255, 255, 255, .72);
            margin: 0;
        }

        .centre-receipt-back {
            color: #6fe0b5;
            font-size: .85rem;
            font-weight: 700;
        }

        .centre-receipt-back:hover,
        .centre-receipt-back:focus {
            color: #fff;
        }

        .centre-receipt-content {
            padding: 1.5rem 0 3rem;
        }

        .centre-receipt-card {
            background: #fff;
            border: 1px solid var(--centre-line);
            border-radius: .45rem;
            box-shadow: 0 8px 24px rgba(21, 34, 56, .05);
        }

        .centre-receipt-card-header {
            border-bottom: 1px solid var(--centre-line);
            padding: 1rem 1.25rem;
        }

        .centre-receipt-card-header h2 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
        }

        .centre-receipt-table thead th {
            background: #f8fafc;
            border-bottom: 2px solid var(--centre-line);
            color: #536174;
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
            border-color: var(--centre-mint);
            box-shadow: 0 0 0 .2rem rgba(47, 155, 120, .2);
            outline: 3px solid rgba(47, 155, 120, .22);
            outline-offset: 2px;
        }

        .centre-receipt-submit {
            background: var(--centre-mint);
            border-color: var(--centre-mint);
            color: #fff;
            font-weight: 700;
        }

        .centre-receipt-submit:hover,
        .centre-receipt-submit:focus {
            background: #267d62;
            border-color: #267d62;
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
            \App\Models\BookShipmentItem::CENTRE_STATUS_PENDING => 'Pending',
            \App\Models\BookShipmentItem::CENTRE_STATUS_RECEIVED => 'Received',
            \App\Models\BookShipmentItem::CENTRE_STATUS_MISSING => 'Missing',
            \App\Models\BookShipmentItem::CENTRE_STATUS_DAMAGED => 'Damaged',
        ];
    @endphp

    <div class="centre-receipt-page">
        <section class="centre-receipt-hero" aria-labelledby="centre-receipt-page-title">
            <div class="container-fluid">
                <a class="centre-receipt-back" href="{{ route('incoming-book-shipments.index') }}">← Incoming shipments</a>
                <h1 id="centre-receipt-page-title">{{ $shipment->shipment_number }}</h1>
                <p>{{ $shipment->destination_centre_name ?: 'Destination Centre' }} · {{ $shipment->Items->count() }} shipment item(s)</p>
            </div>
        </section>

        <main class="centre-receipt-content">
            <div class="container-fluid">
                <div class="centre-receipt-live mb-3" id="centre-receipt-status" aria-live="polite"></div>

                <section class="centre-receipt-card mb-4" aria-labelledby="centre-receipt-action-title">
                    <div class="centre-receipt-card-header d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h2 id="centre-receipt-action-title">{{ $is_dispatched ? 'Receive shipment' : 'Shipment received' }}</h2>
                            <div class="small text-muted mt-1">
                                {{ $is_dispatched ? 'Record one result for every book before confirming.' : 'This receipt is locked and can no longer be changed.' }}
                            </div>
                        </div>
                        @if ($shipment->centre_received_at)
                            <span class="small text-muted">Received {{ $shipment->centre_received_at->format('d M Y H:i') }}</span>
                        @endif
                    </div>

                    <form id="centre-receipt-form" class="p-3" data-receive-form="true"
                        data-action-url="{{ route('incoming-book-shipments.receive', ['book_shipment' => $shipment->id]) }}">
                        <div class="table-responsive">
                            <table class="table centre-receipt-table">
                                <thead>
                                    <tr>
                                        <th scope="col">Student</th>
                                        <th scope="col">Book</th>
                                        <th scope="col">Order</th>
                                        <th scope="col">Result</th>
                                        <th scope="col">Note</th>
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
                                                <strong>{{ $item->source_student_code ?: 'Student' }}</strong>
                                                <div class="small text-muted">{{ $item->source_student_name ?: 'Unnamed student' }}</div>
                                            </td>
                                            <td>
                                                <strong>{{ $item->book_name }}</strong>
                                                <div class="small text-muted">{{ $item->level ?: 'No level' }} · Term {{ $item->term ?: '—' }}</div>
                                            </td>
                                            <td>{{ $item->source_order_number ?: '—' }}</td>
                                            <td>
                                                @if ($is_dispatched)
                                                    <label class="sr-only" for="centre-receipt-result-{{ $item->id }}">Result for {{ $item->book_name }}</label>
                                                    <select id="centre-receipt-result-{{ $item->id }}" class="custom-select" data-receipt-status="{{ $item->id }}">
                                                        <option value="">Choose result</option>
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
                                                    <label class="sr-only" for="{{ $note_id }}">Note for {{ $item->book_name }}</label>
                                                    <textarea id="{{ $note_id }}" class="form-control" rows="2" maxlength="1000"
                                                        data-receipt-note="{{ $item->id }}" placeholder="Required for missing or damaged"></textarea>
                                                @else
                                                    {{ $item->centre_note ?: '—' }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($is_dispatched)
                            <div class="d-flex flex-wrap align-items-center justify-content-between mt-3">
                                <p class="small text-muted mb-2 mb-md-0">Missing or damaged books require a note.</p>
                                <button class="btn centre-receipt-submit" type="submit">Confirm receipt</button>
                            </div>
                        @else
                            <div class="alert alert-success mb-3">The Centre has recorded this shipment. Open Student List to track student delivery.</div>
                            @if (auth()->user()->can('student-list'))
                                <a class="btn btn-primary" href="{{ url('/contacts?shipment_id=' . $shipment->id) }}">Open shipment Student List</a>
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
