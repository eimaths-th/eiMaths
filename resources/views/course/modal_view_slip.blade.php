<div class="modal fade" id="ModalViewslip" tabindex="-1" aria-labelledby="purchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="modal-header">
                <h5 class="modal-title" id="purchaseModalLabel">{{ __('affiliate-management.course_applications.actions.view_slip') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ __('affiliate-management.course_applications.form.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="modal-body">
                <div class="text-center">
                    <img id="slip_preview" src="" alt="{{ __('affiliate-management.course_applications.actions.view_slip') }}" style="width: 200px; margin-top: 10px;">
                </div>
            </div>
            <div class="modal-footer payment_btn d-flex justify-content-between" id="modal-footer">
                <button type="button" class="btn btn-danger small_btn3" data-bs-dismiss="modal">{{ __('affiliate-management.course_applications.form.close') }}</button>
            </div>
        </div>
    </div>
</div>
