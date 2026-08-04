@extends('layouts.app')

@section('style')
    <style>
        .book-shipment-page {
            --shipment-ink: #152238;
            --shipment-paper: #f5f7fb;
            --shipment-line: #dbe2ec;
            --shipment-amber: #f2b84b;
            --shipment-mint: #2f9b78;
            --shipment-rust: #d66b57;
            background: var(--shipment-paper);
            min-height: calc(100vh - 57px);
            color: var(--shipment-ink);
        }

        .book-shipment-hero {
            background: var(--shipment-ink);
            color: #fff;
            padding: 2.2rem 0 2rem;
            position: relative;
            overflow: hidden;
        }

        .book-shipment-hero::after {
            content: '';
            position: absolute;
            width: 18rem;
            height: 18rem;
            border: 1px solid rgba(242, 184, 75, .32);
            border-radius: 50%;
            right: -5rem;
            top: -8rem;
        }

        .book-shipment-eyebrow {
            color: var(--shipment-amber);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .book-shipment-hero h1 {
            font-size: clamp(1.8rem, 3vw, 2.8rem);
            font-weight: 700;
            letter-spacing: -.035em;
            margin: .35rem 0 .55rem;
        }

        .book-shipment-hero p {
            color: rgba(255, 255, 255, .72);
            margin: 0;
            max-width: 42rem;
        }

        .book-shipment-hero-stat {
            border-left: 1px solid rgba(255, 255, 255, .2);
            padding-left: 1.25rem;
        }

        .book-shipment-hero-stat strong {
            display: block;
            font-size: 1.65rem;
            line-height: 1;
        }

        .book-shipment-hero-stat span {
            color: rgba(255, 255, 255, .62);
            font-size: .78rem;
        }

        .book-shipment-content {
            padding: 1.5rem 0 3rem;
        }

        .book-shipment-card {
            background: #fff;
            border: 1px solid var(--shipment-line);
            border-radius: .45rem;
            box-shadow: 0 8px 24px rgba(21, 34, 56, .05);
        }

        .book-shipment-card-header {
            border-bottom: 1px solid var(--shipment-line);
            padding: 1rem 1.25rem;
        }

        .book-shipment-card-header h2,
        .book-shipment-card-header h3 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
        }

        .book-shipment-filter {
            background: #eef2f7;
            border: 1px solid var(--shipment-line);
            border-radius: .45rem;
            padding: 1rem;
        }

        .book-shipment-filter label,
        .book-shipment-field label {
            color: #536174;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .03em;
            margin-bottom: .35rem;
            text-transform: uppercase;
        }

        .book-shipment-filter .form-control:focus,
        .book-shipment-field .form-control:focus,
        .book-shipment-field .custom-select:focus {
            border-color: var(--shipment-amber);
            box-shadow: 0 0 0 .2rem rgba(242, 184, 75, .2);
        }

        .book-shipment-button {
            background: var(--shipment-amber);
            border-color: var(--shipment-amber);
            color: #152238;
            font-weight: 700;
        }

        .book-shipment-button:hover,
        .book-shipment-button:focus {
            background: #f5c866;
            border-color: #f5c866;
            color: #152238;
        }

        .book-shipment-button:focus,
        .book-shipment-page a:focus,
        .book-shipment-page button:focus,
        .book-shipment-page input:focus,
        .book-shipment-page select:focus {
            outline: 3px solid rgba(242, 184, 75, .55);
            outline-offset: 2px;
        }

        .book-shipment-table thead th {
            background: #f8fafc;
            border-bottom: 2px solid var(--shipment-line);
            color: #536174;
            font-size: .72rem;
            letter-spacing: .05em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .book-shipment-table td {
            vertical-align: middle;
        }

        .book-shipment-number {
            color: var(--shipment-ink);
            font-weight: 700;
        }

        .book-shipment-number:hover {
            color: #8b6419;
        }

        .book-shipment-status {
            border-radius: 999px;
            display: inline-block;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .03em;
            padding: .28rem .55rem;
            white-space: nowrap;
        }

        .book-shipment-status--draft { background: #eef1f6; color: #536174; }
        .book-shipment-status--waiting { background: #fff4d7; color: #8b6419; }
        .book-shipment-status--pending { background: #e5f0ff; color: #225b9c; }
        .book-shipment-status--complete { background: #e1f4ed; color: #216f58; }
        .book-shipment-status--issue { background: #fde8e2; color: #9a3e2d; }
        .book-shipment-status--cancelled { background: #f2e7e9; color: #7b4b55; }

        .book-shipment-empty {
            color: #68778a;
            padding: 3rem 1rem !important;
            text-align: center;
        }

        .book-shipment-live {
            min-height: 1.5rem;
        }

        @media (max-width: 767.98px) {
            .book-shipment-hero-stat {
                border-left: 0;
                border-top: 1px solid rgba(255, 255, 255, .2);
                margin-top: 1.25rem;
                padding-left: 0;
                padding-top: 1rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .book-shipment-page *,
            .book-shipment-page *::before,
            .book-shipment-page *::after {
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
    @endphp

    <div class="book-shipment-page">
        <section class="book-shipment-hero" aria-labelledby="book-shipment-page-title">
            <div class="container-fluid">
                <div class="row align-items-end">
                    <div class="col-lg-8">
                        <span class="book-shipment-eyebrow">HQ dispatch desk</span>
                        <h1 id="book-shipment-page-title">Book Shipments</h1>
                        <p>Prepare New Student books, send them to the right Centre, and keep every acknowledgement visible.</p>
                    </div>
                    <div class="col-lg-4 mt-4 mt-lg-0">
                        <div class="book-shipment-hero-stat">
                            <strong>{{ $shipments->total() }}</strong>
                            <span>shipments in the current result</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <main class="book-shipment-content">
            <div class="container-fluid">
                <div class="book-shipment-live mb-3" id="book-shipment-page-status" aria-live="polite"></div>

                @can('book-shipment-create')
                    @include('book-shipments.hq.create', ['shipment' => null, 'destinations' => $destinations])
                @endcan

                <section class="book-shipment-card mb-4" aria-labelledby="book-shipment-filter-title">
                    <div class="book-shipment-card-header d-flex align-items-center justify-content-between">
                        <h2 id="book-shipment-filter-title">Shipment ledger</h2>
                        <span class="text-muted small">Server-filtered results</span>
                    </div>
                    <div class="p-3">
                        <form class="book-shipment-filter mb-3" method="GET" action="{{ route('book-shipments.index') }}" role="search">
                            <div class="row">
                                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                                    <label for="shipment-number-filter">Shipment number</label>
                                    <input id="shipment-number-filter" class="form-control" type="search" name="shipment_number"
                                        value="{{ request('shipment_number') }}" maxlength="50" placeholder="BS-202608-000001">
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                                    <label for="destination-centre-filter">Destination Centre</label>
                                    <select id="destination-centre-filter" class="custom-select" name="destination_centre_id">
                                        <option value="">All Centres</option>
                                        @foreach ($destinations as $destination)
                                            <option value="{{ $destination->id }}" @selected((string) request('destination_centre_id') === (string) $destination->id)>
                                                {{ $destination->code }} — {{ $destination->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-6 mb-3 mb-lg-0">
                                    <label for="stored-status-filter">Stored status</label>
                                    <select id="stored-status-filter" class="custom-select" name="status">
                                        <option value="">All stored statuses</option>
                                        @foreach ([
                                            \App\Models\BookShipment::STATUS_DRAFT => 'Draft',
                                            \App\Models\BookShipment::STATUS_DISPATCHED => 'Dispatched',
                                            \App\Models\BookShipment::STATUS_CENTRE_RECEIVED => 'Centre received',
                                            \App\Models\BookShipment::STATUS_CANCELLED => 'Cancelled',
                                        ] as $status_value => $status_label)
                                            <option value="{{ $status_value }}" @selected(request('status') === $status_value)>{{ $status_label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-6 mb-3 mb-lg-0">
                                    <label for="derived-status-filter">Progress status</label>
                                    <select id="derived-status-filter" class="custom-select" name="derived_status">
                                        <option value="">All statuses</option>
                                        @foreach ($derived_status_labels as $status_value => $status_label)
                                            <option value="{{ $status_value }}" @selected(request('derived_status') === $status_value)>{{ $status_label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                                    <label for="created-from-filter">Created from</label>
                                    <input id="created-from-filter" class="form-control" type="date" name="created_from" value="{{ request('created_from') }}">
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                                    <label for="created-to-filter">Created to</label>
                                    <input id="created-to-filter" class="form-control" type="date" name="created_to" value="{{ request('created_to') }}">
                                </div>
                                <div class="col-lg-6 col-md-12 d-flex align-items-end">
                                    <div class="w-100 d-flex">
                                        <button class="btn book-shipment-button mr-2" type="submit">Filter</button>
                                        <a class="btn btn-outline-secondary" href="{{ route('book-shipments.index') }}">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table book-shipment-table table-hover mb-0" data-book-shipment-ledger>
                                <caption class="sr-only">HQ book shipment ledger</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Shipment</th>
                                        <th scope="col">Destination</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Items</th>
                                        <th scope="col">Student delivery</th>
                                        <th scope="col">Created</th>
                                        <th scope="col"><span class="sr-only">Actions</span></th>
                                    </tr>
                                </thead>
                                <tbody data-book-shipment-ledger-body>
                                    @forelse ($shipments as $shipment)
                                        @php
                                            $progress = $shipment->progress ?? [];
                                            $derived_status = $shipment->derived_status ?? $shipment->status;
                                            $status_class = match ($derived_status) {
                                                \App\Models\BookShipment::DERIVED_COMPLETED => 'complete',
                                                \App\Models\BookShipment::DERIVED_COMPLETED_WITH_ISSUES => 'issue',
                                                \App\Models\BookShipment::DERIVED_CANCELLED => 'cancelled',
                                                \App\Models\BookShipment::DERIVED_PENDING_STUDENT_DELIVERY => 'pending',
                                                \App\Models\BookShipment::DERIVED_WAITING_FOR_CENTRE => 'waiting',
                                                default => 'draft',
                                            };
                                        @endphp
                                        <tr>
                                            <td>
                                                <a class="book-shipment-number" href="{{ route('book-shipments.show', ['book_shipment' => $shipment->id]) }}">
                                                    {{ $shipment->shipment_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <strong>{{ $shipment->destination_centre_code ?: '—' }}</strong>
                                                <div class="small text-muted">{{ $shipment->destination_centre_name ?: 'Centre unavailable' }}</div>
                                            </td>
                                            <td>
                                                <span class="book-shipment-status book-shipment-status--{{ $status_class }}">
                                                    {{ $derived_status_labels[$derived_status] ?? $derived_status }}
                                                </span>
                                                <div class="small text-muted mt-1">Stored: {{ $shipment->status }}</div>
                                            </td>
                                            <td>
                                                <strong>{{ $progress['total_count'] ?? 0 }}</strong>
                                                <div class="small text-muted">{{ $progress['centre_received_count'] ?? 0 }} Centre received</div>
                                            </td>
                                            <td>
                                                <strong>{{ $progress['student_received_count'] ?? 0 }} / {{ $progress['centre_received_count'] ?? 0 }}</strong>
                                                <div class="small text-muted">{{ $progress['missing_count'] ?? 0 }} missing · {{ $progress['damaged_count'] ?? 0 }} damaged</div>
                                            </td>
                                            <td class="small text-muted">{{ $shipment->created_at?->format('d M Y H:i') ?: '—' }}</td>
                                            <td class="text-right">
                                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('book-shipments.show', ['book_shipment' => $shipment->id]) }}">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="book-shipment-empty" colspan="7">
                                                <strong>No shipments match these filters.</strong>
                                                <div class="small mt-1">Choose a Centre above or create the first shipment from the panel.</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="pt-3" data-book-shipment-ledger-pagination>
                            @if ($shipments->hasPages())
                                {{ $shipments->withQueryString()->links() }}
                            @endif
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
@endsection

@section('script')
    @include('book-shipments.hq.script')
@endsection
