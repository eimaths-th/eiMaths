<div class="modal cs_modal fade admin-query" id="edit_withdraw_request_modal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    @if($transaction->payment_type == 3)
                        {{ __('affiliate.Update Transfer Amount') }}
                    @else
                        {{ __('affiliate.Update Withdraw Request') }}
                    @endif

                </h4>
                <button type="button" class="close " data-bs-dismiss="modal">
                    <i class="ti-close "></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="update_withdraw_request">
                    @method('PUT')
                    <input type="hidden" value="{{$transaction->id}}" id="rowId" name="id">
                    @if($transaction->payment_type == 3)
                        <div class="row">

                            <input type="hidden" value="1" name="balance_transfer_request">
                            <input type="hidden" value="{{$user->id}}" name="user_id">
                            <input type="hidden" value="3" name="payment_type">

                            <div class="col-lg-12">
                                <div class="mb-25">
                                    <label class="primary_input_label"
                                           for="transfer_amount">{{__('affiliate.Transfer Amount') }} <span
                                            class="required_mark_theme">*</span> <span id="transfer_amount_msg"
                                                                                       class="text-danger"></span></label>
                                    <input step="0.01" autocomplete="off" name="transfer_amount" id="transfer_amount"
                                           value="{{$transaction->withdraw_amount}}" class="primary_input_field"
                                           placeholder="{{__('affiliate.Transfer Amount') }}" type="number">
                                    <span class="text-danger" id="error_transfer_amount"></span>
                                </div>
                            </div>
                            <div class="col-lg-12 text-center mt-20">
                                <div class="d-flex justify-content-center">
                                    <button id="transfer_submit_btn" class="link_value theme_btn small_btn4 me-1"
                                            type="submit"><i class="ti-check"></i>{{__('common.Submit') }}</button>
                                    <button class="link_value theme_btn small_btn4 me-1" id="save_button_parent"
                                            data-bs-dismiss="modal" type="button"><i
                                            class="ti-check"></i>{{__('common.Cancel') }}</button>
                                </div>
                            </div>
                        </div>
                    @else
                        @if(!isset($paypal_account))
                            <div class="row">
                                <div class="col">
                                    <div class="alert alert-info">
                                        <strong>Info!</strong> {{__('affiliate.Withdraw Commissions By Paypal Add Account')}}
                                        .
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="row">
                            <input type="hidden" value="{{$user->id}}" name="user_id">
                            <div class="col-lg-12">
                                <div class="mb-25">
                                    <label class="primary_input_label"
                                           for="withdraw_amount">{{__('affiliate.Withdraw Amount') }} <span
                                            class="required_mark_theme">*</span> <span id="withdraw_amount_msg"
                                                                                       class="text-danger"></span></label>
                                    <input step="0.01" autocomplete="off" name="withdraw_amount" id="withdraw_amount"
                                           value="{{$transaction->withdraw_amount}}" class="primary_input_field"
                                           type="number">
                                    <span class="text-danger" id="error_withdraw_amount"></span>
                                </div>
                            </div>

                            <div class="col-lg-12 mt-15 mb-15">
                                <label class="primary_label2" for="payment_type">{{__('affiliate.Payment Type')}} <span
                                        class="required_mark_theme">*</span></label>
                                <select class="theme_select select_transparent wide mb_20" id="payment_type"
                                        name="payment_type">
                                    <option disabled selected value="">{{__('affiliate.Select One')}}</option>
                                    <option
                                        {{$transaction->payment_type == 1 ?'selected' :''}} value="1">{{__('affiliate.Offline')}}</option>
                                    @if(isset($paypal_account))
                                        <option
                                            {{$transaction->payment_type == 2 ?'selected' :''}} value="2">{{__('affiliate.Paypal')}}</option>
                                    @endif
                                </select>
                                <span class="text-danger" id="error_payment_type"></span>
                            </div>

                            <div class="col-lg-12 text-center mt-20">
                                <div class="d-flex justify-content-center">
                                    <button id="withdraw_submit_btn" class="link_value theme_btn small_btn4 me-1"
                                            type="submit"><i class="ti-check"></i>{{__('common.Submit') }}</button>
                                    <button class="link_value theme_btn small_btn4 me-1" id="save_button_parent"
                                            data-bs-dismiss="modal" type="button"><i
                                            class="ti-check"></i>{{__('common.Cancel') }}</button>
                                </div>
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

