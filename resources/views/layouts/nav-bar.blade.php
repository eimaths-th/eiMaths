<nav class="bg-white border-b border-gray-100">
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
                    <a href="{{ route('home3') }}">
                        <img src="{{ asset('images/logo.png') }}" style="height: 65px; width: 130px;"
                            alt="Application Logo" />
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                @if (Auth::guard('student')->check() || Auth::check())
                    @php
                        // Helper function to generate classes based on the active route
                        function navLinkClasses($routeName)
                        {
                            $active = request()->routeIs($routeName);
                            return $active
                                ? 'inline-flex items-center px-1 pt-1 border-b-2 border-orange-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
                                : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out';
                        }
                    @endphp

                    {{--
                        <a href="{{ route('home3') }}" class="{{ navLinkClasses('home3') }}"> Home </a>
                        <a href="{{ route('tests') }}" class="{{ navLinkClasses('tests') }}"> Tests </a>
                    --}}
                    <a href="{{ route('home3') }}" class="{{ navLinkClasses('home3') }}"> Home </a>
                    <a href="{{ route('leaderboard') }}" class="{{ navLinkClasses('leaderboard') }}"> Leaderboard </a>
                    <a href="{{ route('myresults') }}"
                        class="{{ navLinkClasses('myresults') }}">{{ __('My Results') }}</a>

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
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700"
                                        onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</button>
                                </form>
                            @elseif(isset($student))
                                {{-- {{ $student->nickname }} --}}
                                <form method="POST" action="{{ route('student.logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700"
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
                    <a href="{{ route('student.login') }}" class="text-gray-700 hover:text-gray-800">Log In</a>
                    {{-- <a href="{{ route('register') }}" class="ml-4 text-gray-700 hover:text-gray-800">Register</a> --}}
                @endauth
        </div>

        <!-- Hamburger -->
        <div class="-mr-2 flex items-center sm:hidden">
            <button @click="toggleDropdown"
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
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
