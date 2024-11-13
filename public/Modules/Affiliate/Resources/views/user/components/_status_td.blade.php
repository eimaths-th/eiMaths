@if($row->accept_affiliate_request == 0)
    <span class="badge_3">{{__('affiliate.Pending')}}</span>
@else
    <span class="badge_1">{{__('affiliate.Active')}}</span>
@endif
