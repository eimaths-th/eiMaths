@extends('layouts.app')

@section('style')
    <style>
        .book-shipment-page {
            --shipment-ink: #343a40;
            --shipment-paper: #f4f6f9;
            --shipment-line: #dee2e6;
            --shipment-primary: #007bff;
            background: var(--shipment-paper);
            min-height: calc(100vh - 57px);
            color: var(--shipment-ink);
        }

        .book-shipment-hero {
            background: #fff;
            border-bottom: 1px solid var(--shipment-line);
            color: var(--shipment-ink);
            padding: 1rem 0;
        }

        .book-shipment-eyebrow {
            color: #6c757d;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .book-shipment-hero h1 {
            color: var(--shipment-ink);
            font-size: clamp(1.65rem, 3vw, 2.25rem);
            font-weight: 700;
            letter-spacing: normal;
            margin: .25rem 0 .35rem;
        }

        .book-shipment-hero p {
            color: #6c757d;
            margin: 0;
            max-width: 42rem;
        }

        .book-shipment-hero-stat {
            border-left: 1px solid var(--shipment-line);
            padding-left: 1.25rem;
        }

        .book-shipment-hero-stat strong {
            color: var(--shipment-ink);
            display: block;
            font-size: 1.65rem;
            line-height: 1;
        }

        .book-shipment-hero-stat span {
            color: #6c757d;
            font-size: .78rem;
        }

        .book-shipment-content {
            padding: 1rem 0 2rem;
        }

        .book-shipment-card {
            background: #fff;
            border: 1px solid var(--shipment-line);
            border-radius: .25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
        }

        .book-shipment-card-header {
            background: var(--shipment-primary);
            border-bottom: 0;
            color: #fff;
            padding: .75rem 1.25rem;
        }

        .book-shipment-card-header h2,
        .book-shipment-card-header h3 {
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
        }

        .book-shipment-filter {
            background: #f8f9fa;
            border: 1px solid var(--shipment-line);
            border-radius: .25rem;
            padding: 1rem;
        }

        .book-shipment-filter label,
        .book-shipment-field label {
            color: #495057;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .03em;
            margin-bottom: .35rem;
            text-transform: uppercase;
        }

        .book-shipment-filter .form-control:focus,
        .book-shipment-field .form-control:focus,
        .book-shipment-field .custom-select:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .2);
        }

        .book-shipment-button {
            background: var(--shipment-primary);
            border-color: var(--shipment-primary);
            color: #fff;
            font-weight: 700;
        }

        .book-shipment-button:hover,
        .book-shipment-button:focus {
            background: #0069d9;
            border-color: #0062cc;
            color: #fff;
        }

        .book-shipment-button:focus,
        .book-shipment-page a:focus,
        .book-shipment-page button:focus,
        .book-shipment-page input:focus,
        .book-shipment-page select:focus {
            outline: 3px solid rgba(0, 123, 255, .25);
            outline-offset: 2px;
        }

        .book-shipment-table thead th {
            background: #f8fafc;
            border-bottom: 2px solid var(--shipment-line);
            color: #495057;
            font-size: .72rem;
            letter-spacing: .05em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .book-shipment-table td {
            vertical-align: middle;
        }

        .book-shipment-number {
            color: var(--shipment-primary);
            font-weight: 700;
        }

        .book-shipment-number:hover {
            color: #0056b3;
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
                border-top: 1px solid var(--shipment-line);
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
            \App\Models\BookShipment::DERIVED_DRAFT => trans('book-shipments.status.DRAFT', []),
            \App\Models\BookShipment::DERIVED_WAITING_FOR_CENTRE => trans('book-shipments.status.WAITING_FOR_CENTRE', []),
            \App\Models\BookShipment::DERIVED_PENDING_STUDENT_DELIVERY => trans('book-shipments.status.PENDING_STUDENT_DELIVERY', []),
            \App\Models\BookShipment::DERIVED_COMPLETED => trans('book-shipments.status.COMPLETED', []),
            \App\Models\BookShipment::DERIVED_COMPLETED_WITH_ISSUES => trans('book-shipments.status.COMPLETED_WITH_ISSUES', []),
            \App\Models\BookShipment::DERIVED_CANCELLED => trans('book-shipments.status.CANCELLED', []),
        ];
    @endphp

    <div class="book-shipment-page">
        <section class="book-shipment-hero" aria-labelledby="book-shipment-page-title">
            <div class="container-fluid">
                <div class="row align-items-end">
                    <div class="col-lg-8">
                        <span class="book-shipment-eyebrow">{{ trans('book-shipments.hq.eyebrow', []) }}</span>
                        <h1 id="book-shipment-page-title">{{ trans('book-shipments.hq.title', []) }}</h1>
                        <p>{{ trans('book-shipments.hq.description', []) }}</p>
                    </div>
                    <div class="col-lg-4 mt-4 mt-lg-0">
                        <div class="book-shipment-hero-stat">
                            <strong>{{ $shipments->total() }}</strong>
                            <span>{{ trans('book-shipments.hq.result_count', ['count' => $shipments->total()]) }}</span>
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
                        <h2 id="book-shipment-filter-title">{{ trans('book-shipments.hq.ledger_title', []) }}</h2>
                        <span class="text-muted small">{{ trans('book-shipments.hq.filtered_results', []) }}</span>
                    </div>
                    <div class="p-3">
                        <form class="book-shipment-filter mb-3" method="GET" action="{{ route('book-shipments.index') }}" role="search">
                            <div class="row">
                                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                                    <label for="shipment-number-filter">{{ trans('book-shipments.hq.shipment_number', []) }}</label>
                                    <input id="shipment-number-filter" class="form-control" type="search" name="shipment_number"
                                        value="{{ request('shipment_number') }}" maxlength="50" placeholder="BS-202608-000001">
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                                    <label for="destination-centre-filter">{{ trans('book-shipments.hq.destination_centre', []) }}</label>
                                    <select id="destination-centre-filter" class="custom-select" name="destination_centre_id">
                                        <option value="">{{ trans('book-shipments.hq.all_centres', []) }}</option>
                                        @foreach ($destinations as $destination)
                                            <option value="{{ $destination->id }}" @selected((string) request('destination_centre_id') === (string) $destination->id)>
                                                {{ $destination->code }} — {{ $destination->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-6 mb-3 mb-lg-0">
                                    <label for="stored-status-filter">{{ trans('book-shipments.hq.stored_status', []) }}</label>
                                    <select id="stored-status-filter" class="custom-select" name="status">
                                        <option value="">{{ trans('book-shipments.hq.all_stored_statuses', []) }}</option>
                                        @foreach ([
                                            \App\Models\BookShipment::STATUS_DRAFT => trans('book-shipments.stored_status.DRAFT', []),
                                            \App\Models\BookShipment::STATUS_DISPATCHED => trans('book-shipments.stored_status.DISPATCHED', []),
                                            \App\Models\BookShipment::STATUS_CENTRE_RECEIVED => trans('book-shipments.stored_status.CENTRE_RECEIVED', []),
                                            \App\Models\BookShipment::STATUS_CANCELLED => trans('book-shipments.stored_status.CANCELLED', []),
                                        ] as $status_value => $status_label)
                                            <option value="{{ $status_value }}" @selected(request('status') === $status_value)>{{ $status_label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-6 mb-3 mb-lg-0">
                                    <label for="derived-status-filter">{{ trans('book-shipments.hq.progress_status', []) }}</label>
                                    <select id="derived-status-filter" class="custom-select" name="derived_status">
                                        <option value="">{{ trans('book-shipments.hq.all_statuses', []) }}</option>
                                        @foreach ($derived_status_labels as $status_value => $status_label)
                                            <option value="{{ $status_value }}" @selected(request('derived_status') === $status_value)>{{ $status_label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                                    <label for="created-from-filter">{{ trans('book-shipments.hq.created_from', []) }}</label>
                                    <input id="created-from-filter" class="form-control" type="date" name="created_from" value="{{ request('created_from') }}">
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                                    <label for="created-to-filter">{{ trans('book-shipments.hq.created_to', []) }}</label>
                                    <input id="created-to-filter" class="form-control" type="date" name="created_to" value="{{ request('created_to') }}">
                                </div>
                                <div class="col-lg-6 col-md-12 d-flex align-items-end">
                                    <div class="w-100 d-flex">
                                        <button class="btn book-shipment-button mr-2" type="submit">{{ trans('book-shipments.hq.filter', []) }}</button>
                                        <a class="btn btn-outline-secondary" href="{{ route('book-shipments.index') }}">{{ trans('book-shipments.hq.reset', []) }}</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table book-shipment-table table-hover mb-0" data-book-shipment-ledger>
                                <caption class="sr-only">{{ trans('book-shipments.hq.ledger_title', []) }}</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">{{ trans('book-shipments.hq.shipment', []) }}</th>
                                        <th scope="col">{{ trans('book-shipments.hq.destination', []) }}</th>
                                        <th scope="col">{{ trans('book-shipments.hq.status', []) }}</th>
                                        <th scope="col">{{ trans('book-shipments.hq.items', []) }}</th>
                                        <th scope="col">{{ trans('book-shipments.hq.student_delivery', []) }}</th>
                                        <th scope="col">{{ trans('book-shipments.hq.created', []) }}</th>
                                        <th scope="col"><span class="sr-only">{{ trans('book-shipments.hq.actions', []) }}</span></th>
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
                                                <div class="small text-muted">{{ $shipment->destination_centre_name ?: trans('book-shipments.hq.centre_unavailable', []) }}</div>
                                            </td>
                                            <td>
                                                <span class="book-shipment-status book-shipment-status--{{ $status_class }}">
                                                    {{ $derived_status_labels[$derived_status] ?? $derived_status }}
                                                </span>
                                                <div class="small text-muted mt-1">{{ trans('book-shipments.hq.stored_prefix', ['status' => trans('book-shipments.stored_status.' . $shipment->status, [])]) }}</div>
                                            </td>
                                            <td>
                                                <strong>{{ $progress['total_count'] ?? 0 }}</strong>
                                                <div class="small text-muted">{{ trans('book-shipments.hq.centre_received_count', ['count' => $progress['centre_received_count'] ?? 0]) }}</div>
                                            </td>
                                            <td>
                                                <strong>{{ $progress['student_received_count'] ?? 0 }} / {{ $progress['centre_received_count'] ?? 0 }}</strong>
                                                <div class="small text-muted">{{ trans('book-shipments.hq.issue_counts', ['missing' => $progress['missing_count'] ?? 0, 'damaged' => $progress['damaged_count'] ?? 0]) }}</div>
                                            </td>
                                            <td class="small text-muted">{{ $shipment->created_at?->format('d/m/Y H:i') ?: '—' }}</td>
                                            <td class="text-right">
                                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('book-shipments.show', ['book_shipment' => $shipment->id]) }}">
                                                    {{ trans('book-shipments.hq.view', []) }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="book-shipment-empty" colspan="7">
                                                <strong>{{ trans('book-shipments.hq.empty_title', []) }}</strong>
                                                <div class="small mt-1">{{ trans('book-shipments.hq.empty_description', []) }}</div>
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
