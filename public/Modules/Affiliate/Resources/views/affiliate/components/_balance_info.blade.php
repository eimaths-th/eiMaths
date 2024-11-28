<div class="row row-gap-4 mt-20 mb-30">
    <div class="col">
        <a href="#" class="d-block">
            <div class="white-box single-summery">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3>{{__('affiliate.Current Balance')}}</h3>
                        <p class="mb-0">{{__('affiliate.Affiliate Account Current Balance')}}</p>
                    </div>
                    <h1 class="gradient-color2">{{getPriceFormat(($user->affiliateWallet ? showPrice($user->affiliateWallet->amount) : 0),false)}}</h1>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="#" class="d-block">
            <div class="white-box single-summery">
                <div class="d-flex justify-content-between">
                    <div class="row">
                        <div class="col-12">
                            <div class="row d-flex justify-content-between">
                                <div>
                                    <h3>{{__('affiliate.Total Earning')}}</h3>
                                </div>
                                <h1 class="gradient-color2">
                                    @if($start_date && $end_date)
                                        {{getPriceFormat($user->affiliateCommissions->where('status',1)->whereBetween('date',[$start_date,$end_date])->sum('amount'),false)}}
                                    @else
                                        {{getPriceFormat($user->affiliateCommissions->where('status',1)->sum('amount'),false)}}
                                    @endif
                                </h1>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row d-flex justify-content-between">
                                <div>
                                    <h3>{{__('affiliate.Pending')}}</h3>
                                </div>
                                <h1 class="gradient-color2">
                                    @if($start_date && $end_date)
                                        {{getPriceFormat($user->affiliateCommissions->where('status',0)->whereBetween('date',[$start_date,$end_date])->sum('amount'),false)}}
                                    @else
                                        {{getPriceFormat($user->affiliateCommissions->where('status',0)->sum('amount'),false)}}
                                    @endif
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="#" class="d-block">
            <div class="white-box single-summery">
                <div class="d-flex justify-content-between">
                    <div class="row">
                        <div class="col-12">
                            <div class="row d-flex justify-content-between">
                                <div>
                                    <h3>{{__('affiliate.Withdrawn')}}</h3>
                                </div>
                                <h1 class="gradient-color2">
                                    @if($start_date && $end_date)
                                        {{getPriceFormat($user->affiliateTransaction->where('status',1)->where('payment_type','!=',3)->whereBetween('confirm_date',[$start_date,$end_date])->sum('withdraw_amount'),false)}}
                                    @else
                                        {{getPriceFormat($user->affiliateTransaction->where('status',1)->where('payment_type','!=',3)->sum('withdraw_amount'),false)}}
                                    @endif
                                </h1>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row d-flex justify-content-between">
                                <div>
                                    <h3>{{__('affiliate.Pending')}}</h3>
                                </div>
                                <h1 class="gradient-color2">
                                    @if($start_date && $end_date)
                                        {{getPriceFormat($user->affiliateTransaction->where('status',0)->where('payment_type','!=',3)->whereBetween('request_date',[$start_date,$end_date])->sum('withdraw_amount'),false)}}
                                    @else
                                        {{getPriceFormat($user->affiliateTransaction->where('status',0)->where('payment_type','!=',3)->sum('withdraw_amount'),false)}}
                                    @endif
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="#" class="d-block">
            <div class="white-box single-summery">
                <div class="d-flex justify-content-between">
                    <div class="row">
                        <div class="col-12">
                            <div class="row d-flex justify-content-between">
                                <div>
                                    <h3>{{__('affiliate.Transfer To User Wallet')}}</h3>
                                </div>
                                <h1 class="gradient-color2">
                                    @if($start_date && $end_date)
                                        {{getPriceFormat($user->affiliateTransaction->where('status',1)->where('payment_type',3)->whereBetween('confirm_date',[$start_date,$end_date])->sum('withdraw_amount'),false)}}
                                    @else
                                        {{getPriceFormat($user->affiliateTransaction->where('status',1)->where('payment_type',3)->sum('withdraw_amount'),false)}}
                                    @endif

                                </h1>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row d-flex justify-content-between">
                                <div>
                                    <h3>{{__('affiliate.Pending')}}</h3>
                                </div>
                                <h1 class="gradient-color2">
                                    @if($start_date && $end_date)
                                        {{getPriceFormat($user->affiliateTransaction->where('status',0)->where('payment_type',3)->whereBetween('confirm_date',[$start_date,$end_date])->sum('withdraw_amount'),false)}}
                                    @else
                                        {{getPriceFormat($user->affiliateTransaction->where('status',0)->where('payment_type',3)->sum('withdraw_amount'),false)}}
                                    @endif

                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
