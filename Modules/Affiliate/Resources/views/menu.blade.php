@php
    $affiliate= false;

    if(request()->is('affiliate/*'))
    {
        $affiliate = true;
    }
@endphp
<li class="{{ $affiliate ?'mm-active' : '' }}">
    <a href="javascript:;" class="has-arrow" aria-expanded="{{ $affiliate ? 'true' : 'false' }}">
        <div class="nav_icon_small">
            <span class="fas fa-industry"></span>
        </div>
        <div class="nav_title">
            <span>{{__('affiliate.Affiliate')}}</span>
            @if(env('APP_SYNC'))
                <span class="demo_addons">Addon</span>
            @endif
        </div>
    </a>
    <ul>

            <li>
                <a href="{{route('affiliate.my_affiliate.index')}}"
                   class="{{request()->routeIs('affiliate.my_affiliate.index') ? 'active' : ''}}"> {{__('affiliate.My Affiliate')}}</a>
            </li>

        @if(permissionCheck('affiliate.pending_withdraw'))
            <li>
                <a href="{{route('affiliate.pending_withdraw')}}"
                   class="{{request()->routeIs('affiliate.pending_withdraw') ? 'active' : ''}}"> {{__('affiliate.Pending Withdrawn')}}</a>
            </li>
        @endif
        @if(permissionCheck('affiliate.complete_withdraw'))
            <li>
                <a href="{{route('affiliate.complete_withdraw')}}"
                   class="{{request()->routeIs('affiliate.complete_withdraw') ? 'active' : ''}}"> {{__('affiliate.Complete Withdrawn')}}</a>
            </li>
        @endif
        @if(permissionCheck('affiliate.configurations.update'))
            <li>
                <a href="{{route('affiliate.configurations.index')}}"
                   class="{{request()->routeIs('affiliate.configurations.index') ? 'active' : ''}}"> {{__('affiliate.Configurations')}}</a>
            </li>
        @endif

        @if(permissionCheck('affiliate.users.index'))
            <li>
                <a href="{{route('affiliate.users.index')}}"
                   class="{{request()->routeIs('affiliate.users.index') ? 'active' : ''}}"> {{__('affiliate.Users')}}</a>
            </li>
        @endif

        @if(permissionCheck('affiliate.frontend'))
            <li>
                <a href="{{route('affiliate.frontend.edit')}}"
                   class="{{request()->routeIs('affiliate.frontend') ? 'active' : ''}}"> {{__('affiliate.Page Design')}}</a>
            </li>
        @endif


    </ul>
</li>

