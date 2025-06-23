<nav id="sidebar" class="sidebar  ">

    <div class="update_sidebar">
        {{-- <a class="large_logo" href="{{ url('/') }}"> --}}
        <a class="large_logo mt-2 mb-2" href="{{ route('my_affiliate.index') }}">
            <img class="text-center" style="display: block; margin: auto;"
                src="{{ asset('uploads/main/files/08-08-2024/logo-eimath.png') }}" alt="Eimath Logo" width="50%">
        </a>

        {{-- <a class="mini_logo" href="{{ route('my_affiliate.index') }}">
            <img src="{{ asset('uploads/main/files/08-08-2024/logo-eimath.png') }}" alt="">
        </a> --}}
        <a id="close_sidebar" class="d-lg-none">
            <i class="ti-close"></i>
        </a>
    </div>
    @php
        if (Auth::user()->name) {
            $name = Auth::user()->name;
        } else {
            $name = Auth::user()->email;
        }
    @endphp
    <div class="sidebar-user text-center">
        <div class="sidebar-profile mx-auto">

            <img src="https://ui-avatars.com/api/?background=random&amp;name={{ $name }}" alt="">

        </div>
        <h4>

            {{ $name }}

        </h4>

        <div class="sidebar-badge">

        </div>
    </div>
    <ul id="sidebar_menu">



        <li class="mm-active">
            <a href=" # " class="  has-arrow " aria-expanded="false">
                <div class="nav_icon_small">
                    <span class="fas fa-th"></span>
                </div>
                <div class="nav_title">
                    <span>Affiliate</span>
                </div>
            </a>
            <ul>
                <li class="mm-active">
                    <a href="{{ route('my_affiliate.index') }}">
                        My Affiliate
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</nav>
