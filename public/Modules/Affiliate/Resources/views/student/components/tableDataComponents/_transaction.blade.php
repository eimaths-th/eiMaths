<div role="tabpanel" class="tab-pane fade" id="transaction_tab">
    {{--    <div class="main-title">--}}
    {{--        <h3 class="mb-20">{{__('affiliate.Withdraw History')}}</h3>--}}
    {{--    </div>--}}

    <div class="col-xl-12">
        <div class="table-responsive">
            <table class="table custom_table3 mb-0">
                <thead>
                <tr>
                    <th>{{__('affiliate.SL')}}</th>
                    <th>{{__('affiliate.Request Date')}}</th>
                    <th>{{__('affiliate.Amount')}}</th>
                    <th>{{__('affiliate.Payment Type')}}</th>
                    <th>{{__('affiliate.Status')}}</th>
                    <th>{{__('affiliate.Confirm Date')}}</th>
                    <th class="text-center">{{__('common.Action')}}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($user_transaction_data as  $item)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{showDate($item->request_date)}}</td>
                        <td>{{showPrice($item->withdraw_amount)}}</td>
                        <td>
                            @if($item->payment_type == 1)
                                <span class="badge_5">{{__('affiliate.Offline')}}</span>
                            @elseif($item->payment_type == 2)
                                <span class="badge_5">{{__('affiliate.Paypal')}}</span>
                            @else
                                <span class="badge_5"> {{__('affiliate.Add User Wallet')}}</span>
                            @endif
                        </td>
                        <td>
                            @if($item->status == 0)
                                <span class="badge_3">{{__('affiliate.Pending')}}</span>
                            @elseif($item->status == 1)
                                <span class="badge_1">{{__('affiliate.Done')}}</span>
                            @else
                                <span class="badge_4">{{__('affiliate.Cancel')}}</span>
                            @endif
                        </td>
                        <td>
                            {{$item->confirm_date?showDate($item->confirm_date):"NA"}}
                        </td>

                        <td>
                            @if(permissionCheck('affiliate.my_affiliate.index') && $item->status == 0)
                                <a href="#" class="link_value theme_btn small_btn4 me-1"
                                   data-id="{{$item->id}}">{{ __('common.Edit') }}</a>
                            @endif
                            @if(permissionCheck('affiliate.my_affiliate.index') && $item->status == 0)
                                <a href="#" class="link_value theme_btn small_btn4 me-1" data-id="{{ $item->id }}"
                                   type="button">{{ __('common.Delete') }}</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{ $user_transaction_data->links() }}
        </div>
    </div>
</div>
