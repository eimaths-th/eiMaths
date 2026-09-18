@php
    $shipment = $shipment ?? null;
    $destinations = $destinations ?? collect();
    $is_draft_update = $shipment !== null && $shipment->status === \App\Models\BookShipment::STATUS_DRAFT;
    $initial_selected_books = $shipment
        ? $shipment->Items
            ->map(fn ($item): array => [
                'source_book_id' => $item->source_order_detail_list_id === null
                    ? null
                    : (int) $item->source_order_detail_list_id,
                'student_code' => $item->source_student_code,
                'student_name' => $item->source_student_name,
                'book_name' => $item->book_name,
                'level' => $item->level,
                'term' => $item->term,
                'order_number' => $item->source_order_number,
            ])
            ->filter(fn (array $book): bool => $book['source_book_id'] !== null)
            ->values()
            ->all()
        : [];
@endphp

<section class="book-shipment-card mb-4" id="book-shipment-editor" aria-labelledby="book-shipment-editor-title"
    data-mode="{{ $is_draft_update ? 'update' : 'create' }}"
    data-draft-shipment-id="{{ $shipment?->id ?? '' }}"
    data-candidates-url="{{ route('book-shipments.candidates') }}"
    data-store-url="{{ route('book-shipments.store') }}"
    data-update-url="{{ $shipment ? route('book-shipments.update', ['book_shipment' => $shipment->id]) : '' }}"
    data-show-url="{{ $shipment ? route('book-shipments.show', ['book_shipment' => $shipment->id]) : '' }}"
    data-selection-module-url="{{ asset('js/book-shipment-draft-selection.js') }}">
    <div class="book-shipment-card-header d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <span class="book-shipment-eyebrow text-dark">{{ trans($is_draft_update ? 'book-shipments.hq.draft_editor' : 'book-shipments.hq.new_dispatch', []) }}</span>
            <h2 id="book-shipment-editor-title" class="mt-1">{{ trans($is_draft_update ? 'book-shipments.hq.editor_update_title' : 'book-shipments.hq.editor_title', []) }}</h2>
        </div>
        @if ($is_draft_update)
            <span class="book-shipment-status book-shipment-status--draft">{{ $shipment->shipment_number }}</span>
        @endif
    </div>
    <div class="p-3">
        <p class="text-muted small mb-3">{{ trans('book-shipments.hq.editor_description', []) }}</p>
        <div class="row">
            <div class="col-lg-4 book-shipment-field">
                <label for="book-shipment-destination">{{ trans('book-shipments.hq.destination_centre', []) }}</label>
                <select id="book-shipment-destination" class="custom-select" required {{ $is_draft_update ? 'disabled' : '' }}>
                    @if (!$is_draft_update)
                        <option value="">{{ trans('book-shipments.hq.choose_centre', []) }}</option>
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
                <label for="book-shipment-candidate-search">{{ trans('book-shipments.hq.search_candidates', []) }}</label>
                <input id="book-shipment-candidate-search" class="form-control" type="search" maxlength="100"
                    placeholder="{{ trans('book-shipments.hq.search_placeholder', []) }}" autocomplete="off">
            </div>
            <div class="col-lg-3 d-flex align-items-end mt-3 mt-lg-0">
                <button id="book-shipment-load-candidates" class="btn book-shipment-button btn-block" type="button" disabled>
                    {{ trans('book-shipments.hq.load_eligible_books', []) }}
                </button>
            </div>
        </div>

        <div class="book-shipment-live text-muted small mt-3" id="book-shipment-editor-status" aria-live="polite">
            {{ trans('book-shipments.hq.choose_centre_begin', []) }}
        </div>
        <div class="border rounded mt-2" id="book-shipment-candidate-list" aria-live="polite">
            <div class="p-4 text-center text-muted small">{{ trans('book-shipments.hq.no_candidates_loaded', []) }}</div>
        </div>

        <section class="book-shipment-selected mt-3" aria-labelledby="book-shipment-selected-title">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                <h3 id="book-shipment-selected-title" class="mb-0">{{ trans('book-shipments.hq.selected_books', []) }}</h3>
                <span class="small text-muted" aria-live="polite">
                    {{ trans('book-shipments.hq.selected_books_count', ['count' => count($initial_selected_books)]) }}
                </span>
            </div>
            <div id="book-shipment-selected-list" aria-live="polite">
                @forelse ($initial_selected_books as $selected_book)
                    <div class="book-shipment-selected-row"
                        data-selected-book-id="{{ $selected_book['source_book_id'] }}"
                        data-student-code="{{ $selected_book['student_code'] }}"
                        data-student-name="{{ $selected_book['student_name'] }}"
                        data-book-name="{{ $selected_book['book_name'] }}"
                        data-level="{{ $selected_book['level'] }}"
                        data-term="{{ $selected_book['term'] }}"
                        data-order-number="{{ $selected_book['order_number'] }}">
                        <div>
                            <strong>{{ $selected_book['student_code'] ?: trans('book-shipments.hq.student_fallback', []) }} — {{ $selected_book['student_name'] ?: trans('book-shipments.hq.unnamed_student', []) }}</strong>
                            <span class="d-block small text-muted">
                                {{ $selected_book['book_name'] ?: trans('book-shipments.hq.unnamed_book', []) }} · {{ $selected_book['level'] ?: trans('book-shipments.hq.no_level', []) }} · {{ trans('book-shipments.hq.term', []) }} {{ $selected_book['term'] ?: '—' }} · {{ $selected_book['order_number'] ?: trans('book-shipments.hq.no_order_number', []) }}
                            </span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-selected-book data-book-id="{{ $selected_book['source_book_id'] }}">
                            {{ trans('book-shipments.hq.remove', []) }}
                        </button>
                    </div>
                @empty
                    <p class="book-shipment-selected-empty text-muted small mb-0">{{ trans('book-shipments.hq.selected_books_empty', []) }}</p>
                @endforelse
            </div>
            <p class="book-shipment-selected-empty text-muted small mb-0 d-none" id="book-shipment-selected-guidance">
                {{ trans('book-shipments.hq.guidance', []) }}
            </p>
        </section>

        <div class="d-flex flex-wrap align-items-center justify-content-between border-top mt-3 pt-3">
            <div class="small text-muted" aria-live="polite">{{ trans('book-shipments.hq.review_before_saving', []) }}</div>
            <button id="book-shipment-save-draft" class="btn book-shipment-button mt-2 mt-sm-0" type="button" disabled>
                {{ trans($is_draft_update ? 'book-shipments.hq.save_draft_changes' : 'book-shipments.hq.save_draft', []) }}
            </button>
        </div>
    </div>
</section>
