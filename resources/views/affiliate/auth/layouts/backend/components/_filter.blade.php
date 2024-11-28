@php
    $min_withdraw = affiliateConfig('min_withdraw');
    $user_balance = $user->affiliateWallet ? $user->affiliateWallet->amount : 0;
    $withdraw_flag = true;
    $withdraw_tooltip = "";
    if($user_balance < $min_withdraw){
        $withdraw_tooltip = "Your balance less then minimum payout amount.";
        $withdraw_flag = false;
    }
@endphp
<div class="row mb-30 align-items-end row-gap-3">
    <div class="col-xxl-3 date-range-block">
        <div class="mb-15 date_range">
            <div class="primary_datepicker_input filter">
                <label class="primary_input_label" for="">{{__('affiliate.Select Date Range')}}</label>
                <div class="g-0  input-right-icon">
                    <div class="col-12">
                        <div class="">
                            <input readonly class="primary_input_field filter_date_input_field" type="text"
                                   name="date_range_filter" value="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 mt-30 affiliate_filter_area">
        <div class="d-flex">
            <button id="reset-date-filter" type="button"
                    class="link_value theme_btn small_btn4 me-3">{{__('affiliate.Filter')}}</button>
            <a type="button" href="#
            {{-- {{route('student.my_affiliate.index')}} --}}
            "
               class="link_value theme_btn small_btn4">{{__('affiliate.Reset')}}</a>
        </div>
    </div>
    <div class="col mt-30">
        <div class="d-flex flex-wrap gap-3 justify-content-start justify-content-xxl-end">

            {{-- @if(permissionCheck('student.my_affiliate.index'))
                <a href="#" id="balance_transfer_btn" type="button"
                   class="link_value theme_btn small_btn4 me-1">{{__('affiliate.Balance Transfer To Wallet')}}</a>
            @endif
            @if(permissionCheck('student.my_affiliate.index'))
                <a title="{{$withdraw_tooltip}}" id="withdraw_request_btn" type="button" href="#"
                   class="link_value theme_btn small_btn4 {{!$withdraw_flag ? 'cursor-not-allowed' :''}}">{{__('affiliate.Withdraw Request')}}</a>
            @endif --}}
        </div>
    </div>
</div>
