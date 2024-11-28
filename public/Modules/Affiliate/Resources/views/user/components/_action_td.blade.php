@if(permissionCheck('affiliate.users.approved'))
    <a class="primary-btn radius_30px text-white fix-gr-bg user_confirm w-fit" href="#" data-id="{{$row->id}}">
        @if($row->accept_affiliate_request == 0)
            {{__('affiliate.Active')}}
        @else
            {{__('affiliate.Inactive')}}
        @endif

    </a>
@endif
