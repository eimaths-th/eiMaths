@php
    $shipment = $shipment ?? null;
    $destinations = $destinations ?? collect();
    $is_draft_update = $shipment !== null && $shipment->status === \App\Models\BookShipment::STATUS_DRAFT;
    $initial_selection = $shipment
        ? $shipment->Items->pluck('source_order_detail_list_id')->filter()->map(fn ($book_id) => (int) $book_id)->values()->all()
        : [];
@endphp

<section class="book-shipment-card mb-4" id="book-shipment-editor" aria-labelledby="book-shipment-editor-title"
    data-mode="{{ $is_draft_update ? 'update' : 'create' }}"
    data-candidates-url="{{ route('book-shipments.candidates') }}"
    data-store-url="{{ route('book-shipments.store') }}"
    data-update-url="{{ $shipment ? route('book-shipments.update', ['book_shipment' => $shipment->id]) : '' }}"
    data-show-url="{{ $shipment ? route('book-shipments.show', ['book_shipment' => $shipment->id]) : '' }}"
    data-initial-selection="@json($initial_selection)">
    <div class="book-shipment-card-header d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <span class="book-shipment-eyebrow text-dark">{{ $is_draft_update ? 'Draft editor' : 'New dispatch' }}</span>
            <h2 id="book-shipment-editor-title" class="mt-1">{{ $is_draft_update ? 'Adjust this draft' : 'Prepare a book shipment' }}</h2>
        </div>
        @if ($is_draft_update)
            <span class="book-shipment-status book-shipment-status--draft">{{ $shipment->shipment_number }}</span>
        @endif
    </div>
    <div class="p-3">
        <p class="text-muted small mb-3">Select a destination first. Candidates come only from eligible New Student books for that Centre.</p>
        <div class="row">
            <div class="col-lg-4 book-shipment-field">
                <label for="book-shipment-destination">Destination Centre</label>
                <select id="book-shipment-destination" class="custom-select" required {{ $is_draft_update ? 'disabled' : '' }}>
                    @if (!$is_draft_update)
                        <option value="">Choose a Centre</option>
                        @foreach ($destinations as $destination)
                            <option value="{{ $destination->id }}" @selected($shipment && (int) $shipment->destination_centre_id === (int) $destination->id)>
                                {{ $destination->code }} — {{ $destination->name }}
                            </option>
                        @endforeach
                    @else
                        <option value="{{ $shipment->destination_centre_id }}" selected>
                            {{ $shipment->destination_centre_code }} — {{ $shipment->destination_centre_name }}
                        </option>
                    @endif
                </select>
            </div>
            <div class="col-lg-5 book-shipment-field mt-3 mt-lg-0">
                <label for="book-shipment-candidate-search">Search candidates</label>
                <input id="book-shipment-candidate-search" class="form-control" type="search" maxlength="100"
                    placeholder="Student code, name, order, or book" autocomplete="off">
            </div>
            <div class="col-lg-3 d-flex align-items-end mt-3 mt-lg-0">
                <button id="book-shipment-load-candidates" class="btn book-shipment-button btn-block" type="button" disabled>
                    Load eligible books
                </button>
            </div>
        </div>

        <div class="book-shipment-live text-muted small mt-3" id="book-shipment-editor-status" aria-live="polite">
            Choose a Centre to begin.
        </div>
        <div class="border rounded mt-2" id="book-shipment-candidate-list" aria-live="polite">
            <div class="p-4 text-center text-muted small">No candidates loaded.</div>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between border-top mt-3 pt-3">
            <div class="small text-muted" aria-live="polite">
                Selected books: <strong id="book-shipment-selected-count">{{ count($initial_selection) }}</strong>
            </div>
            <button id="book-shipment-save-draft" class="btn book-shipment-button mt-2 mt-sm-0" type="button" disabled>
                {{ $is_draft_update ? 'Save draft changes' : 'Save shipment draft' }}
            </button>
        </div>
    </div>
</section>
