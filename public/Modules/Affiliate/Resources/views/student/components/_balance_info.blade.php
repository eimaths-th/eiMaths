<div class="mb-30 affiliate_balence_grid">


    <div class="card">
        <h4 class="pb-0 mb-0">
            {{$user->affiliateWallet ? showPrice($user->affiliateWallet->amount) : 0}}
        </h4>
        <p class="">{{__('affiliate.Current Balance')}}</p>
    </div>

    <div class="card">
        <h4 class="pb-0 mb-0">
            @if($start_date && $end_date)
                {{showPrice($user->affiliateCommissions->where('status',1)->whereBetween('date',[$start_date,$end_date])->sum('amount'))}}
            @else
                {{showPrice($user->affiliateCommissions->where('status',1)->sum('amount'))}}
            @endif
        </h4>
        <p class="">{{__('affiliate.Total Earning')}}</p>
    </div>

    <div class="card">
        <h4 class="pb-0 mb-0">
            @if($start_date && $end_date)
                {{showPrice($user->affiliateCommissions->where('status',0)->whereBetween('date',[$start_date,$end_date])->sum('amount'))}}
            @else
                {{showPrice($user->affiliateCommissions->where('status',0)->sum('amount'))}}
            @endif
        </h4>
        <p class="">{{__('affiliate.Pending Earning')}}</p>
    </div>

    <div class="card">
        <h4 class="pb-0 mb-0">
            @if($start_date && $end_date)
                {{showPrice($user->affiliateTransaction->where('status',1)->where('payment_type','!=',3)->whereBetween('confirm_date',[$start_date,$end_date])->sum('withdraw_amount'))}}
            @else
                {{showPrice($user->affiliateTransaction->where('status',1)->where('payment_type','!=',3)->sum('withdraw_amount'))}}
            @endif
        </h4>
        <p class="">{{__('affiliate.Withdrawn')}}</p>
    </div>

    <div class="card">
        <h4 class="pb-0 mb-0">
            @if($start_date && $end_date)
                {{showPrice($user->affiliateTransaction->where('status',0)->where('payment_type','!=',3)->whereBetween('request_date',[$start_date,$end_date])->sum('withdraw_amount'))}}
            @else
                {{showPrice($user->affiliateTransaction->where('status',0)->where('payment_type','!=',3)->sum('withdraw_amount'))}}
            @endif
        </h4>
        <p class="">{{__('affiliate.Pending Withdrawn')}}</p>
    </div>

    <div class="card">
        <h4 class="pb-0 mb-0">
            @if($start_date && $end_date)
                {{showPrice($user->affiliateTransaction->where('status',1)->where('payment_type',3)->whereBetween('confirm_date',[$start_date,$end_date])->sum('withdraw_amount'))}}
            @else
                {{showPrice($user->affiliateTransaction->where('status',1)->where('payment_type',3)->sum('withdraw_amount'))}}
            @endif
        </h4>
        <p class="">{{__('affiliate.Transfer To User Wallet')}}</p>
    </div>

    <div class="card">
        <h4 class="pb-0 mb-0">
            @if($start_date && $end_date)
                {{showPrice($user->affiliateTransaction->where('status',0)->where('payment_type',3)->whereBetween('confirm_date',[$start_date,$end_date])->sum('withdraw_amount'))}}
            @else
                {{showPrice($user->affiliateTransaction->where('status',0)->where('payment_type',3)->sum('withdraw_amount'))}}
            @endif
        </h4>
        <p class="">{{__('affiliate.Pending Transfer')}}</p>
    </div>


</div>

