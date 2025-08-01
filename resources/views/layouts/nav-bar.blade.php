<style>
    /* Nav-bar Modern Styling */
    .modern-nav {
        background: linear-gradient(135deg, #ffffff 0%, #ffffff 100%);
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        border: none;
        transition: all 0.3s ease;
    }

    .nav-logo {
        transition: transform 0.3s ease;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    }

    .nav-logo:hover {
        transform: scale(1.05);
    }

    .nav-link {
        position: relative;
        color: #6d6d6d !important;
        font-weight: 500;
        transition: all 0.3s ease;
        padding: 8px 16px;
        border-radius: 6px;
        margin: 0 4px;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-1px);
        color: #6d6d6d !important;
    }

    .nav-link.active {
        background: rgba(255, 182, 25, 0.2);
        color: #6d6d6d !important;
        border-bottom: 2px solid #ffb443;
    }

    .dropdown-button {
        background: rgba(117, 117, 117, 0.1);
        color: #6d6d6d !important;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        backdrop-filter: blur(5px);
    }

    .dropdown-button:hover {
        background: rgba(105, 105, 105, 0.2);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .dropdown-menu {
        background: rgba(173, 173, 173, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown-menu a,
    .dropdown-menu button {
        transition: all 0.2s ease;
        border-radius: 4px;
        margin: 2px 4px;
    }

    .dropdown-menu a:hover,
    .dropdown-menu button:hover {
        background: #667eea;
        color: white !important;
        transform: translateX(4px);
    }

    .hamburger-btn {
        color: white !important;
        transition: all 0.3s ease;
    }

    .hamburger-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: rotate(90deg);
    }

    .login-link {
        background: rgba(255, 255, 255, 0.1);
        color: white !important;
        padding: 8px 16px;
        border-radius: 6px;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .login-link:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Mobile responsive improvements */
    @media (max-width: 640px) {
        .modern-nav {
            padding: 0.5rem 1rem;
        }

        .nav-logo img {
            height: 50px !important;
            width: 100px !important;
        }
    }
</style>

<nav class="modern-nav border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                {{-- <div class="shrink-0 flex items-center">
                    <a href="{{ route('home3') }}">
                        <img src="{{ asset('images/logo.png') }}" class="block h-9 w-auto fill-current text-gray-800"
                            alt="Application Logo" />
                    </a>
                </div> --}}
                <div class="shrink-0 flex items-center">
                    @php
                        if (isset($student) && $student->type === 'demo') {
                            $route = route('home3');
                        } else {
                            $route = route('home3');
                        }
                    @endphp
                    <a href="{{ $route }}" class="nav-logo">
                        <img src="{{ asset('images/logo.png') }}" style="height: 65px; width: 130px;"
                            alt="Application Logo" />
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                @if (Auth::guard('student')->check() || Auth::check())
                    <a href="{{ $route }}" class="nav-link {{ request()->routeIs('home3') ? 'active' : '' }}"> Home </a>
                    <a href="{{ route('leaderboard') }}" class="nav-link {{ request()->routeIs('leaderboard') ? 'active' : '' }}"> Leaderboard </a>
                    <a href="{{ route('myresults') }}"
                        class="nav-link {{ request()->routeIs('myresults') ? 'active' : '' }}">{{ __('My Results') }}</a>

                    <!-- User Dropdown -->
                    <div class="relative">
                        <button
                            class="dropdown-button inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>
                                @if (isset($user))
                                    {{ $user->name }}
                                @elseif(isset($student))
                                    {{ $student->nickname }}
                                @else
                                    Guest
                                @endif
                            </div>
                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                        <div
                            class="dropdown-menu hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg py-1">
                            {{-- <a href="{{ route('profile.edit') }}"
                                class="block px-4 py-2 text-sm text-gray-700">{{ __('Profile') }}</a> --}}
                            @if (isset($user))
                                {{-- {{ $user->name }} --}}
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700"
                                        onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</button>
                                </form>
                            @elseif(isset($student))
                                {{-- {{ $student->nickname }} --}}
                                <form method="POST" action="{{ route('student.logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700"
                                        onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</button>
                                </form>
                            @else
                                Guest
                            @endif
                            {{-- <form method="POST" action="{{ route('student.logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700"
                                    onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</button>
                            </form> --}}
                        </div>
                    </div>
                @else
                    <a href="{{ route('student.login') }}" class="login-link">Log In</a>
                    {{-- <a href="{{ route('register') }}" class="ml-4 text-gray-700 hover:text-gray-800">Register</a> --}}
                @endauth
        </div>

        <!-- Hamburger -->
        <div class="-mr-2 flex items-center sm:hidden">
            <button @click="toggleDropdown"
                class="hamburger-btn inline-flex items-center justify-center p-2 rounded-md focus:outline-none transition duration-150 ease-in-out">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                    <path class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Responsive Navigation Menu -->
<div class="sm:hidden hidden">
    @auth
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                {{-- <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700">{{ __('Profile') }}</a> --}}
                <a href="{{ route('myresults') }}"
                    class="block px-4 py-2 text-sm text-gray-700 {{ request()->routeIs('myresults') ? 'active' : '' }}">{{ __('My Results') }}</a>
                {{--
                        <a href="{{ route('admins') }}" class="block px-4 py-2 text-sm text-gray-700">Admins</a>
                        <a href="{{ route('questions') }}" class="block px-4 py-2 text-sm text-gray-700">Questions</a>
                        <a href="{{ route('quizzes') }}" class="block px-4 py-2 text-sm text-gray-700">Quizzes</a>
                        <a href="{{ route('tests') }}" class="block px-4 py-2 text-sm text-gray-700">Tests</a>
                    --}}
                <form method="POST" action="{{ route('student.logout') }}">
                    @csrf
                    <button type="submit"
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700">Logout</button>
                </form>
                {{-- <form method="POST" action="{{ route('student.logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700"
                            onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</button>
                    </form> --}}
            </div>
        </div>
    @else
        <a href="{{ route('login') }}" class="block px-4 py-2 text-sm text-gray-700">Log In</a>
        <a href="{{ route('register') }}" class="block px-4 py-2 text-sm text-gray-700">Register</a>
    @endauth
</div>
</nav>
