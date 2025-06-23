<header>
    <div id="sticky-header" class="header_area ">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="header__wrapper">
                        <!-- header__left__start  -->
                        <div class="header__left d-flex align-items-center gap-20 ">
                            <div class="">
                                <a class="logo_img" href="https://www.eimaths-th.com/">
                                    <img class="p-2" src="{{ asset('images/logo.png') }}" width="150"
                                        alt="eiMaths ecommerce">
                                </a>
                            </div>
                            <div class="me-3 translator-switch">


                            </div>

                            {{-- <div class="category_search d-flex category_box_iner">
                                <form action="https://ecommerce.eimaths-th.com/search">
                                <form action="#">
                                    <div class="input-group theme_search_field">
                                        <div class="input-group-prepend">
                                            <button class="btn" type="button" id="button-addon1"><i
                                                    class="ti-search"></i>
                                            </button>
                                        </div>

                                        <input type="text" class="form-control" name="query"
                                            placeholder="ค้นหาหลักสูตร ทักษะ และวิดีโอ" onfocus="this.placeholder = ''"
                                            onblur="this.placeholder = 'ค้นหาหลักสูตร ทักษะ และวิดีโอ'">

                                    </div>
                                </form>
                            </div> --}}
                        </div>
                        <!-- header__left__start  -->

                        <!-- main_menu_start  -->
                        <div class="main_menu text-end d-none d-lg-block category_box_iner">
                            <nav>
                                {{-- <div class="menu_dropdown">
                                    <ul>
                                        <li class="mega_menu_dropdown active_menu_item">
                                            <a href="https://ecommerce.eimaths-th.com/courses?category=2">
                                            <a href="#">
                                                Upper Primary
                                            </a>
                                        </li>
                                        <li class="mega_menu_dropdown active_menu_item">
                                            <a href="https://ecommerce.eimaths-th.com/courses?category=1">
                                            <a href="#">
                                                Lower Primary
                                            </a>
                                        </li>
                                    </ul>
                                </div> --}}


                                {{-- <ul id="mobile-menu">


                                    <li class="  ">
                                        <a href="#"> Home </a>
                                    </li>

                                    <li class="  ">
                                        <a href="#"> Courses </a>
                                    </li>

                                    <li class="  ">
                                        <a href="#">

                                            Classes</a>
                                    </li>

                                    <li class="  ">
                                        <a href="#">

                                            Affiliate</a>
                                    </li>

                                    <li class="  ">
                                        <a href="#">
                                            Contact Us</a>
                                    </li>

                                    <li><a href="#"></a></li>


                                </ul> --}}


                            </nav>
                        </div>
                        <!-- main_menu_start  -->


                        <div class="me-3 translator-switch">


                        </div>
                        <!-- header__right_start  -->
                        @auth()
                            <div class="header__right login_user">
                                <div class="profile_info collaps_part">
                                    <div class="profile_img collaps_icon d-flex align-items-center">
                                        <div class="studentProfileThumb"
                                            style="background-image: url('{{ getProfileImage(Auth::user()->image, Auth::user()->name) }}')">
                                        </div>

                                        <span class="">{{ Auth::user()->name }}
                                            {{-- <br style="display: block"> --}}
                                            <small class="d-block">
                                                @if (showEcommerce())
                                                    @if (Auth::user()->role_id == 3)
                                                        @if (Auth::user()->balance == 0)
                                                            {{ Settings('currency_symbol') ?? '৳' }} 0
                                                        @else
                                                            {{ getPriceFormat(Auth::user()->balance) }}
                                                        @endif
                                                    @endif
                                                @endif
                                            </small>
                                        </span>
                                    </div>
                                    <div class="profile_info_iner collaps_part_content">
                                        @if (Auth::user()->hasRole('Affiliate-user'))
                                            {{-- <a href="{{ route('studentDashboard') }}">{{ __('dashboard.Dashboard') }}</a> --}}
                                            <a href="{{ route('my_affiliate.index') }}">Dashboard</a>
                                            {{-- <a href="{{ auth()->user()->username ? route('profileUniqueUrl', auth()->user()->username) : '' }}">{{ __('frontendmanage.My Profile') }}</a> --}}
                                            {{-- <a href="#">My Profile</a> --}}
                                            {{-- <a href="{{ route('users.settings') }}">{{ __('frontend.Account Settings') }}</a> --}}
                                            {{-- <a href="#">Account Settings</a> --}}

                                            @if (isModuleActive('Affiliate') && auth()->user()->affiliate_request != 1)
                                                <a
                                                    href="{{ routeIsExist('affiliate.users.request') ? route('affiliate.users.request') : '' }}">{{ __('frontend.Join Affiliate Program') }}</a>
                                            @endif
                                        @else
                                            {{-- <a href="{{ route('dashboard') }}">{{ __('dashboard.Dashboard') }}</a> --}}
                                            <a href="{{ route('my_affiliate.index') }}">Dashboard</a>
                                            {{-- <a href="#">My Profile</a>
                                            <a href="#">Account Settings</a> --}}

                                            {{-- <a href="{{ auth()->user()->name ? route('profileUniqueUrl', auth()->user()->name) : '' }}">{{ __('frontendmanage.My Profile') }}</a> --}}

                                            {{-- <a href="{{ route('users.settings') }}">{{ __('frontend.Account Settings') }}</a> --}}
                                        @endif
                                        {{-- @if (isModuleActive('UserType'))
                                            @foreach (auth()->user()->userRoles as $role)
                                                @php
                                                    if ($role->id == auth()->user()->role_id) {
                                                        continue;
                                                    }
                                                @endphp
                                                <a href="{{ route('usertype.changePanel', $role->id) }}">
                                                    {{ __('common.Switch to') }} {{ $role->name }}
                                                </a>
                                            @endforeach
                                        @endif --}}
                                        
                                        <a href="{{ route('affiliate.logout') }}"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log
                                            Out</a>

                                        <form id="logout-form" method="POST" action="{{ route('affiliate.logout') }}"
                                            style="display: none;">
                                            @csrf
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endauth
                        @guest()
                            <div class="header__right">
                                <div class="contact_wrap d-flex align-items-center">
                                    <div class="contact_btn">
                                        <a href="{{ route('affiliate.login') }}"
                                            class="theme_btn small_btn2">เข้าสู่ระบบ</a>
                                    </div>
                                </div>
                            </div>
                        @endguest
                        <!-- header__right_end  -->
                    </div>
                </div>
                <div class="col-12">
                    <div class="mobile_menu d-block d-lg-none"></div>
                </div>
            </div>
        </div>
    </div>
</header>
