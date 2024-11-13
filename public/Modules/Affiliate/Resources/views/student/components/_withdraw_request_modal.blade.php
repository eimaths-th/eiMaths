<div class="modal cs_modal fade admin-query" id="withdraw_request_modal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('affiliate.Create Withdraw Request') }}</h4>
                <button type="button" class="close " data-bs-dismiss="modal">
                    <i class="ti-close "></i>
                </button>
            </div>
            <div class="modal-body">
                @if(!isset($paypal_account))
                    <div class="row">
                        <div class="col">
                            <div class="alert alert-info">
                                <strong>Info!</strong> {{__('affiliate.Withdraw Commissions By Paypal Add Account')}}.
                            </div>
                        </div>
                    </div>
                @endif
                <form id="create_withdraw_request">
                    <div class="row">
                        <input type="hidden" value="{{$user->id}}" name="user_id">

                        <div class="col-lg-12 mb-15">
                            <label class="primary_label2" for="withdraw_amount">{{__('affiliate.Withdraw Amount') }}
                                <span class="required_mark_theme">*</span> <span id="withdraw_amount_msg"
                                                                                 class="text-danger"></span></label>
                            <input step="0.01" autocomplete="off" name="withdraw_amount" id="withdraw_amount"
                                   value="{{old('withdraw_amount')}}" type="number"
                                   placeholder="{{__('affiliate.Withdraw Amount') }}">
                            <span class="text-danger" id="error_withdraw_amount"></span>
                        </div>
                        <div class="col-lg-12 mb-15">
                            <label class="primary_label2" for="payment_type">{{__('affiliate.Payment Type')}} <span
                                    class="required_mark_theme">*</span></label>
                            <select class="theme_select select_transparent wide mb_20" id="payment_type"
                                    name="payment_type">
                                <option disabled selected value="">{{__('affiliate.Select One')}}</option>
                                <option value="1">{{__('affiliate.Offline')}}</option>
                                @if(isset($paypal_account))
                                    <option value="2">{{__('affiliate.Paypal')}}</option>
                                @endif
                            </select>
                            <span class="text-danger" id="error_payment_type"></span>
                        </div>

                        <div class="col-lg-12 text-center">
                            <div class="d-flex justify-content-center">
                                <button id="withdraw_submit_btn" class="link_value theme_btn small_btn4 me-1"
                                        type="submit"><i class="ti-check"></i>{{__('common.Submit') }}</button>
                                <button class="link_value theme_btn small_btn4 me-1" id="save_button_parent"
                                        data-bs-dismiss="modal" type="button"><i
                                        class="ti-check"></i>{{__('common.Cancel') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

