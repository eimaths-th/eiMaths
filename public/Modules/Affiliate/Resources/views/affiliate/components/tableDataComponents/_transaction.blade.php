<div role="tabpanel" class="tab-pane fade" id="transaction_tab">
    <div class="main-title">
        <h3 class="mb-20">{{__('affiliate.Withdraw History')}}</h3>
    </div>
    <div class="QA_section QA_section_heading_custom check_box_table">
        <div class="QA_table ">
            <div class="">
                <table id="lms_table" class="table Crm_table_active3">
                    <thead>
                    <tr>
                        <th>{{__('affiliate.SL')}}</th>
                        <th>{{__('affiliate.Request Date')}}</th>
                        <th>{{__('affiliate.Amount')}}</th>
                        <th>{{__('affiliate.Payment Type')}}</th>
                        <th>{{__('affiliate.Status')}}</th>
                        <th>{{__('affiliate.Confirm Date')}}</th>
                        <th>{{__('common.Action')}}</th>

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
                                    <span class="badge_5">Offline</span>
                                @elseif($item->payment_type == 2)
                                    <span class="badge_5">Paypal</span>
                                @else
                                    <span class="badge_5"> Add User Wallet</span>
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
                                <div class="dropdown CRM_dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2"
                                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ __('common.Select') }}
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                                        @if(permissionCheck('affiliate.my_affiliate.index') && $item->status == 0)
                                            <a href="#" class="dropdown-item edit_row"
                                               data-id="{{$item->id}}">{{ __('common.Edit') }}</a>
                                        @endif
                                        @if(permissionCheck('affiliate.my_affiliate.index') && $item->status == 0)
                                            <a href="#" class="dropdown-item delete_row" data-id="{{ $item->id }}"
                                               type="button">{{ __('common.Delete') }}</a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
