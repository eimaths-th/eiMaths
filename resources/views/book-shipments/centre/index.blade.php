@extends('layouts.app')

@section('style')
    <style>
        .centre-shipment-page {
            --centre-ink: #343a40;
            --centre-paper: #f4f6f9;
            --centre-line: #dee2e6;
            --centre-primary: #007bff;
            background: var(--centre-paper);
            color: var(--centre-ink);
            min-height: calc(100vh - 57px);
        }

        .centre-shipment-hero {
            background: #fff;
            border-bottom: 1px solid var(--centre-line);
            color: var(--centre-ink);
            padding: 1rem 0;
        }

        .centre-shipment-eyebrow {
            color: #6c757d;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .centre-shipment-hero h1 {
            color: var(--centre-ink);
            font-size: clamp(1.65rem, 3vw, 2.25rem);
            font-weight: 700;
            letter-spacing: normal;
            margin: .25rem 0 .35rem;
        }

        .centre-shipment-hero p {
            color: #6c757d;
            margin: 0;
            max-width: 42rem;
        }

        .centre-shipment-content {
            padding: 1rem 0 2rem;
        }

        .centre-shipment-card {
            background: #fff;
            border: 1px solid var(--centre-line);
            border-radius: .25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
        }

        .centre-shipment-card-header {
            background: var(--centre-primary);
            border-bottom: 0;
            color: #fff;
            padding: .75rem 1.25rem;
        }

        .centre-shipment-card-header h2 {
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
        }

        .centre-shipment-filter {
            background: #f8f9fa;
            border: 1px solid var(--centre-line);
            border-radius: .25rem;
            padding: 1rem;
        }

        .centre-shipment-filter label {
            color: #495057;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .03em;
            margin-bottom: .35rem;
            text-transform: uppercase;
        }

        .centre-shipment-filter .form-control:focus,
        .centre-shipment-filter .custom-select:focus,
        .centre-shipment-page a:focus,
        .centre-shipment-page button:focus,
        .centre-shipment-page input:focus,
        .centre-shipment-page select:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .2);
            outline: 3px solid rgba(0, 123, 255, .25);
            outline-offset: 2px;
        }

        .centre-shipment-button {
            background: var(--centre-primary);
            border-color: var(--centre-primary);
            color: #fff;
            font-weight: 700;
        }

        .centre-shipment-button:hover,
        .centre-shipment-button:focus {
            background: #0069d9;
            border-color: #0062cc;
            color: #fff;
        }

        .centre-shipment-table thead th {
            background: #f8fafc;
            border-bottom: 2px solid var(--centre-line);
            color: #495057;
            font-size: .72rem;
            letter-spacing: .05em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .centre-shipment-table td {
            vertical-align: middle;
        }

        .centre-shipment-number {
            color: var(--centre-primary);
            font-weight: 700;
        }

        .centre-shipment-status {
            border-radius: 999px;
            display: inline-block;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .03em;
            padding: .28rem .55rem;
            white-space: nowrap;
        }

        .centre-shipment-status--waiting { background: #fff4d7; color: #8b6419; }
        .centre-shipment-status--pending { background: #e5f0ff; color: #225b9c; }
        .centre-shipment-status--complete { background: #e1f4ed; color: #216f58; }
        .centre-shipment-status--issue { background: #fde8e2; color: #9a3e2d; }
        .centre-shipment-status--cancelled { background: #f2e7e9; color: #7b4b55; }

        @media (prefers-reduced-motion: reduce) {
            .centre-shipment-page *,
            .centre-shipment-page *::before,
            .centre-shipment-page *::after {
                scroll-behavior: auto !important;
                transition: none !important;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $derived_status_labels = [
            \App\Models\BookShipment::DERIVED_WAITING_FOR_CENTRE => trans('book-shipments.status.WAITING_FOR_CENTRE', [], 'th'),
            \App\Models\BookShipment::DERIVED_PENDING_STUDENT_DELIVERY => trans('book-shipments.status.PENDING_STUDENT_DELIVERY', [], 'th'),
            \App\Models\BookShipment::DERIVED_COMPLETED => trans('book-shipments.status.COMPLETED', [], 'th'),
            \App\Models\BookShipment::DERIVED_COMPLETED_WITH_ISSUES => trans('book-shipments.status.COMPLETED_WITH_ISSUES', [], 'th'),
            \App\Models\BookShipment::DERIVED_CANCELLED => trans('book-shipments.status.CANCELLED', [], 'th'),
        ];
    @endphp

    <div class="centre-shipment-page">
        <section class="centre-shipment-hero" aria-labelledby="centre-shipment-page-title">
            <div class="container-fluid">
                <span class="centre-shipment-eyebrow">{{ trans('book-shipments.centre.eyebrow', [], 'th') }}</span>
                <h1 id="centre-shipment-page-title">{{ trans('book-shipments.centre.title', [], 'th') }}</h1>
                <p>{{ trans('book-shipments.centre.description', [], 'th') }}</p>
            </div>
        </section>

        <main class="centre-shipment-content">
            <div class="container-fluid">
                <section class="centre-shipment-card mb-4" aria-labelledby="centre-shipment-filter-title">
                    <div class="centre-shipment-card-header d-flex align-items-center justify-content-between">
                        <h2 id="centre-shipment-filter-title">{{ trans('book-shipments.centre.ledger_title', [], 'th') }}</h2>
                        <span class="text-muted small">{{ trans('book-shipments.centre.centre_prefix', [], 'th') }} {{ auth()->user()->department?->name ?: '—' }}</span>
                    </div>
                    <div class="p-3">
                        <form class="centre-shipment-filter" method="GET" action="{{ route('incoming-book-shipments.index') }}" role="search">
                            <div class="row align-items-end">
                                <div class="col-lg-4 mb-3 mb-lg-0">
                                    <label for="incoming-shipment-number-filter">{{ trans('book-shipments.centre.shipment_number', [], 'th') }}</label>
                                    <input id="incoming-shipment-number-filter" class="form-control" name="shipment_number"
                                        value="{{ request('shipment_number') }}" placeholder="BS-202608-000001">
                                </div>
                                <div class="col-lg-3 mb-3 mb-lg-0">
                                    <label for="incoming-derived-status-filter">{{ trans('book-shipments.centre.progress_status', [], 'th') }}</label>
                                    <select id="incoming-derived-status-filter" class="custom-select" name="derived_status">
                                        <option value="">{{ trans('book-shipments.centre.all_statuses', [], 'th') }}</option>
                                        @foreach ($derived_status_labels as $status_value => $status_label)
                                            <option value="{{ $status_value }}" @selected(request('derived_status') === $status_value)>{{ $status_label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 mb-3 mb-lg-0">
                                    <label for="incoming-created-from-filter">{{ trans('book-shipments.centre.created_from', [], 'th') }}</label>
                                    <input id="incoming-created-from-filter" class="form-control" type="date" name="created_from"
                                        value="{{ request('created_from') }}">
                                </div>
                                <div class="col-lg-2 d-flex">
                                    <button class="btn centre-shipment-button mr-2" type="submit">{{ trans('book-shipments.centre.filter', [], 'th') }}</button>
                                    <a class="btn btn-outline-secondary" href="{{ route('incoming-book-shipments.index') }}">{{ trans('book-shipments.centre.reset', [], 'th') }}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="centre-shipment-card" aria-labelledby="incoming-shipment-table-title">
                    <div class="centre-shipment-card-header">
                        <h2 id="incoming-shipment-table-title">{{ trans('book-shipments.centre.awaiting_action', [], 'th') }}</h2>
                    </div>
                    <div class="table-responsive">
                        <table class="table centre-shipment-table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">{{ trans('book-shipments.centre.shipment', [], 'th') }}</th>
                                    <th scope="col">{{ trans('book-shipments.centre.items', [], 'th') }}</th>
                                    <th scope="col">{{ trans('book-shipments.centre.progress', [], 'th') }}</th>
                                    <th scope="col">{{ trans('book-shipments.centre.dispatched', [], 'th') }}</th>
                                    <th scope="col"><span class="sr-only">{{ trans('book-shipments.hq.actions', [], 'th') }}</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($shipments as $shipment)
                                    @php
                                        $derived_status = $shipment->getAttribute('derived_status');
                                        $progress = $shipment->getAttribute('progress') ?? [];
                                        $status_class = match ($derived_status) {
                                            \App\Models\BookShipment::DERIVED_COMPLETED => 'complete',
                                            \App\Models\BookShipment::DERIVED_COMPLETED_WITH_ISSUES => 'issue',
                                            \App\Models\BookShipment::DERIVED_CANCELLED => 'cancelled',
                                            \App\Models\BookShipment::DERIVED_PENDING_STUDENT_DELIVERY => 'pending',
                                            default => 'waiting',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <a class="centre-shipment-number" href="{{ route('incoming-book-shipments.show', ['book_shipment' => $shipment->id]) }}">
                                                {{ $shipment->shipment_number }}
                                            </a>
                                            <div class="small text-muted">{{ $shipment->created_at?->format('d/m/Y H:i') ?: '—' }}</div>
                                        </td>
                                        <td>{{ $progress['total_count'] ?? 0 }}</td>
                                        <td>
                                            <span class="centre-shipment-status centre-shipment-status--{{ $status_class }}">
                                                {{ $derived_status_labels[$derived_status] ?? $derived_status }}
                                            </span>
                                            <div class="small text-muted mt-1">
                                                {{ trans('book-shipments.centre.issue_counts', ['received' => $progress['centre_received_count'] ?? 0, 'missing' => $progress['missing_count'] ?? 0, 'damaged' => $progress['damaged_count'] ?? 0], 'th') }}
                                            </div>
                                        </td>
                                        <td>{{ $shipment->dispatched_at?->format('d/m/Y H:i') ?: '—' }}</td>
                                        <td class="text-right">
                                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('incoming-book-shipments.show', ['book_shipment' => $shipment->id]) }}">{{ trans('book-shipments.centre.view', [], 'th') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center text-muted py-5" colspan="5">
                                            {{ trans('book-shipments.centre.no_results', [], 'th') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($shipments->hasPages())
                        <div class="p-3">{{ $shipments->withQueryString()->links() }}</div>
                    @endif
                </section>
            </div>
        </main>
    </div>
@endsection
