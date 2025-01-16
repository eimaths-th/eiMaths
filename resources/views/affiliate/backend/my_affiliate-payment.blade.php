<div class="row mt-20">
    <div class="col-lg-12">
        <div class="white-box">
            <div class="add-visitor">
                {{ Form::open([
                    'class' => 'form-horizontal',
                    'files' => true,
                    'route' => 'add_or_update_paypal_account',
                    'method' => 'POST',
                ]) }}
                <div class="row">
                    <h3 class="mb-30">
                        @if (isset($paypal_account))
                            Update Promptpay Account For Withdraw Commissions From System
                        @else
                            Add Promptpay Account For Withdraw Commissions From System
                        @endif
                    </h3>
                    <div class="col-lg-12 mb-30">
                        <div class="input-effect">
                            <label class="mb-2">Promptpay Account</label>
                            <input required class="primary-input-field form-control{{ $errors->has('paypal_account') ? ' is-invalid' : '' }}"
                                type="text" id="paypal_account" name="paypal_account" autocomplete="off"
                                value="{{ isset($paypal_account) ? $paypal_account : '' }}">
                            <span class="focus-border"></span>
                            @if ($errors->has('paypal_account'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('paypal_account') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    {{-- bank --}}
                    <div class="col-lg-12 mb-30">
                        <div class="input-effect">
                            <label class="mb-2">Account Number</label>
                            <input required class="primary-input-field form-control{{ $errors->has('account_number') ? ' is-invalid' : '' }}"
                                type="text" id="account_number" name="account_number" autocomplete="off"
                                value="{{ isset($account_number) ? $account_number : '' }}">
                            <span class="focus-border"></span>
                            @if ($errors->has('account_number'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('account_number') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-12 mb-30">
                        <div class="input-effect">
                            <label class="mb-2">Bank Name</label>
                            <input required class="primary-input-field form-control{{ $errors->has('bank_name') ? ' is-invalid' : '' }}"
                                type="text" id="bank_name" name="bank_name" autocomplete="off"
                                value="{{ isset($bank_name) ? $bank_name : '' }}">
                            <span class="focus-border"></span>
                            @if ($errors->has('bank_name'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('bank_name') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-12 mb-30">
                        <div class="input-effect">
                            <label class="mb-2">Account Name</label>
                            <input required class="primary-input-field form-control{{ $errors->has('account_name') ? ' is-invalid' : '' }}"
                                type="text" id="account_name" name="account_name" autocomplete="off"
                                value="{{ isset($account_name) ? $account_name : '' }}">
                            <span class="focus-border"></span>
                            @if ($errors->has('account_name'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('account_name') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-12 text-center mtr-10">
                        <button type="submit" class="primary-btn fix-gr-bg submit">
                            <i class="ti-check"></i>
                            @if (isset($paypal_account))
                                {{-- {{__('common.Update')}} --}}
                                Update
                            @else
                                {{-- {{__('common.Add')}} --}}
                                Add
                            @endif
                        </button>
                    </div>
                </div>
                {{ Form::close() }}

            </div>
        </div>
    </div>
</div>
