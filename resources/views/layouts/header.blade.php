<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link sidebar-toggle-btn" data-widget="pushmenu" href="#" role="button"><i
                    class="fas fa-bars"></i></a>
        </li>
        {{-- <li><a href="#" class="nav-link">
                <h5></h5>
            </a></li> --}}
        <li class="nav-item d-none d-sm-inline-block">

            <a href="#" class="nav-link">[ <i class="fas fa-lg fa-building nav-icon"></i> <b
                    class="text-primary">Centre : @if (Auth::check() && Auth::user()->department && Auth::user()->department->name)
                        {{ Auth::user()->department->name }}
                    @endif
                </b> ]</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="#" class="nav-link">[ <i class="fas fa-users fa-lg nav-icon"></i> <b class="text-primary">Department :
                    @if (Auth::check() && Auth::user()->position && Auth::user()->position->name)
                        {{ Auth::user()->position->name }}
                    @endif
                </b> ]</a>
        </li>
      {{--   <li class="nav-item d-none d-sm-inline-block">
            <a href="#" class="nav-link">
                <span id="real-time-clock"></span>

            </a>
        </li> --}}
        {{-- <li class="nav-item d-none d-sm-inline-block">
            <a href="#" class="nav-link">
                <span class="digital-clock"> <i class="fas fa-clock"></i>  เวลา: &nbsp;
                    <div id="hours" class="digit">00</div>
                    <span>:</span>
                    <div id="minutes" class="digit">00</div>
                    <span>:</span>
                    <div id="seconds" class="digit">00</div>
                </span>
            </a>
        </li> --}}
       {{--  <li class="nav-item d-none d-sm-inline-block">
            <a href="#" class="nav-link">
                <span id="weather" style="display: block;  margin-top:-5px; margin-left:-15px"></span>
            </a>
        </li> --}}

    </ul>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item d-none d-sm-inline-block">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-xl fa-expand-arrows-alt"></i> <b class="text-primary">Zoom in/out screen</b>
            </a>
        </li>
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/User_icon_2.svg/2048px-User_icon_2.svg.png"
                    height="150" class="user-image img-circle elevation-1" alt="User Image">
                <span class="d-none d-md-inline text-primary">{{ Auth::user()->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <!-- User image -->
                <li class="user-header bg-primary">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/User_icon_2.svg/2048px-User_icon_2.svg.png"
                        height="150" class="img-circle elevation-1" alt="User Image">
                    <p>
                        {{ Auth::user()->name }}
                        <small>Member since {{ Auth::user()->created_at->format('M. Y') }}</small>
                    </p>
                </li>
                <!-- Menu Footer-->
                <li class="user-footer">
                    <a href="#" class="btn btn-default btn-flat">Profile</a>
                    <a href="#" class="btn btn-default btn-flat float-right"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Sign out
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</nav>
