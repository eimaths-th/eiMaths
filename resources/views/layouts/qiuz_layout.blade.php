<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.qiuz_css')
</head>

<body class="font-sans antialiased" style="display: flex; flex-direction: column; min-height: 100vh; margin: 0;">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.nav-bar')
        <hr>
        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ $header }}
                    </h2>
                </div>
            </header>
            <hr>
        @endif

        <!-- Page Content -->
        <main>
            @yield('content')

        </main>
        <br>
        <br>
        <br>
        <br>
        <br>

        {{--
        @livewire('footer') --}}
        @include('home.footer')

    </div>



    @include('layouts.qiuz_script')
    @livewireScripts
</body>

</html>
