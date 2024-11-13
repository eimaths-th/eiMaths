<div class="row mt-20">
    <div class="col-lg-12">
        <div class="white-box">
            <div class="add-visitor">
                {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'affiliate.add_or_update_paypal_account',
                       'method' => 'POST']) }}
                <div class="row">
                    <h3 class="mb-30">
                        @if(isset($paypal_account))
                            {{__('affiliate.Update Paypal Account For Withdraw Commissions From System')}}
                        @else
                            {{__('affiliate.Add Paypal Account For Withdraw Commissions From System')}}
                        @endif
                    </h3>
                    <div class="col-lg-12 mb-30">
                        <div class="input-effect">
                            <label class="mb-2"> {{__('affiliate.Paypal Account')}}</label>
                            <input required
                                   class="primary-input-field form-control{{ $errors->has('paypal_account') ? ' is-invalid' : '' }}"
                                   type="text" id="paypal_account" name="paypal_account" autocomplete="off"
                                   value="{{isset($paypal_account) ? $paypal_account :''}}">
                            <span class="focus-border"></span>
                            @if ($errors->has('paypal_account'))
                                <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('paypal_account') }}</strong>
                                                         </span>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-12 text-center mtr-10">
                        <button type="submit" class="primary-btn fix-gr-bg submit">
                            <i class="ti-check"></i>
                            @if(isset($paypal_account))
                                {{__('common.Update')}}
                            @else
                                {{__('common.Add')}}
                            @endif
                        </button>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
