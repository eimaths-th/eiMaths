<div class="modal fade" id="BookReceiptModal" tabindex="-1" role="dialog"
    aria-labelledby="BookReceiptModalLabel" aria-hidden="true" data-can-edit="{{ auth()->user()?->can('student-edit') ? '1' : '0' }}">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="BookReceiptModalLabel">{{ __('students.book_receipts.title') }}</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="{{ __('common.actions.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="BookReceiptLoading" class="alert alert-info d-none" role="status" aria-live="polite">
                    {{ __('common.feedback.loading') }}
                </div>
                <div id="BookReceiptError" class="alert alert-danger d-none" role="alert" aria-live="assertive"></div>
                <div id="BookReceiptSummary" class="mb-3" aria-live="polite"></div>
                <div id="BookReceiptOrders"></div>
                <section id="BookReceiptHistory" class="d-none" aria-labelledby="BookReceiptHistoryLabel">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 id="BookReceiptHistoryLabel" class="mb-0">{{ __('students.history.title') }}</h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="BookReceiptHistoryBack">
                            {{ __('students.book_receipts.back_to_books') }}
                        </button>
                    </div>
                    <div id="BookReceiptHistoryList" aria-live="polite"></div>
                </section>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">{{ __('common.actions.close') }}</button>
            </div>
        </div>
    </div>
</div>
