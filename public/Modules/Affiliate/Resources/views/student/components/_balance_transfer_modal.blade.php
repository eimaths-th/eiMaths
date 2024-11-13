<div class="modal cs_modal fade admin-query" id="balance_transfer_modal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('affiliate.Balance Transfer To User Wallet') }}</h4>
                <button type="button" class="close " data-bs-dismiss="modal">
                    <i class="ti-close "></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="create_balance_transfer">
                    <div class="row">
                        <input type="hidden" value="{{$user->id}}" name="user_id">
                        <input type="hidden" value="3" name="payment_type">
                        <div class="col-lg-12">
                            <div class="mb-25">
                                <label class="primary_input_label"
                                       for="transfer_amount">{{__('affiliate.Transfer Amount') }} <span
                                        class="required_mark_theme">*</span> <span id="transfer_amount_msg"
                                                                                   class="text-danger"></span></label>
                                <input step="0.01" autocomplete="off" name="transfer_amount" id="transfer_amount"
                                       value="{{old('transfer_amount')}}" class="primary_input_field"
                                       placeholder="{{__('affiliate.Transfer Amount') }}" type="number">
                                <span class="text-danger" id="error_transfer_amount"></span>
                            </div>
                        </div>
                        <div class="col-lg-12 text-center mt-20">
                            <div class="d-flex justify-content-center">
                                <button id="transfer_submit_btn" class="link_value theme_btn small_btn4  me-2"
                                        type="submit"><i class="ti-check"></i>{{__('common.Submit') }}</button>
                                <button class="link_value theme_btn small_btn4" id="save_button_parent"
                                        data-bs-dismiss="modal" type="button"><i
                                        class="ti-close"></i>{{__('common.Cancel') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

