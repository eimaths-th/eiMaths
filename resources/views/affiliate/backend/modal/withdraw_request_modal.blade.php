<div class="modal fade admin-query" id="withdraw_request_modal">
    <div class="modal-dialog modal_800px modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Create Withdraw Request</h4>
                <button type="button" class="close " data-bs-dismiss="modal">
                    <i class="ti-close "></i>
                </button>
            </div>

            <div class="modal-body">
                @if (!isset($paypal_account))
                    <div class="row">
                        <div class="col">
                            <div class="alert alert-info">
                                <strong>Info!</strong>
                                Withdraw Commissions By Prompt or Bank Account
                            </div>
                        </div>
                    </div>
                @endif
                <form id="create_withdraw_request" method="POST">
                    @csrf
                    <div class="row">
                        <input type="hidden" value="{{ Auth::user()->id }}" name="user_id">
                        <div class="col-lg-6">
                            <div class="primary_input mb-25">
                                <label class="primary_input_label" for="withdraw_amount">Withdraw
                                    Amount <span class="required_mark_theme">*</span> <span id="withdraw_amount_msg"
                                        class="text-danger"></span></label>
                                <input step="0.01" autocomplete="off" name="withdraw_amount" id="withdraw_amount"
                                    value="" class="primary_input_field" placeholder="Withdraw Amount"
                                    type="number">
                                <span class="text-danger" id="error_withdraw_amount"></span>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="primary_input mb-15">
                                <label class="primary_input_label" for="payment_type">Payment Type
                                    <span class="required_mark_theme">*</span></label>
                                <select class="primary_select mb-15" id="payment_type" name="payment_type">
                                    <option disabled selected value="">Select One</option>
                                    <option value="1">PromptPay</option>
                                    <option value="2">Bank Account</option>
                                </select>
                                <span class="text-danger" id="error_payment_type"></span>
                            </div>
                        </div>

                        <div class="col-lg-12 text-center">
                            <div class="d-flex justify-content-center">
                                <button id="withdraw_submit_btn" class="primary-btn semi_large2 fix-gr-bg" type="submit">
                                    <i class="ti-check"></i>
                                    Submit
                                </button>
                                <button class="primary-btn semi_large2 fix-gr-bg ms-3" id="save_button_parent" data-bs-dismiss="modal" type="button">
                                    <i class="ti-check"></i>
                                    Cancel
                                </button>
                            </div>
                        </div>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
