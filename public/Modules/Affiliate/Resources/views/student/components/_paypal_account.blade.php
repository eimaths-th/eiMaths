<div class="modal cs_modal fade admin-query" id="affiliate_add_paypal_modal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    @if(isset($paypal_account))
                        {{__('affiliate.Update Paypal Account For Withdraw Commissions From System')}}
                    @else
                        {{__('affiliate.Add Paypal Account For Withdraw Commissions From System')}}
                    @endif
                </h4>
                <button type="button" class="close " data-bs-dismiss="modal">
                    <i class="ti-close "></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-20">
                    <div class="col-lg-12">
                        <div class="white-box">
                            <div class="add-visitor">
                                {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'affiliate.add_or_update_paypal_account',
                                    'method' => 'POST']) }}
                                <div class="account_profile_form">
                                    <div class="account_title">
                                        {{-- <h3 class="font_22 f_w_700 ">
                                             @if(isset($paypal_account))
                                                 {{__('affiliate.Update Paypal Account For Withdraw Commissions From System')}}
                                             @else
                                                 {{__('affiliate.Add Paypal Account For Withdraw Commissions From System')}}
                                             @endif
                                         </h3> --}}
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label class="primary_label2"
                                                   for="paypal_account"> {{__('affiliate.Paypal Account')}}</label>
                                            <input required name="paypal_account" id="paypal_account"
                                                   value="{{isset($paypal_account) ? $paypal_account :''}}"
                                                   class="primary_input mb_20 {{ $errors->has('paypal_account') ? ' is-invalid' : '' }}"
                                                   type="text">
                                            @if ($errors->has('paypal_account'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('paypal_account') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        <div class="col-12">


                                            <button class="theme_btn w-100 text-center">
                                                @if(isset($paypal_account))
                                                    {{__('common.Update')}}
                                                @else
                                                    {{__('common.Add')}}
                                                @endif
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
