<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.quiz_css')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        @keyframes pulse {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.8; }
        }
    </style>
</head>

<body style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; display: flex; flex-direction: column; min-height: 100vh; margin: 0; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); position: relative;">

    <!-- Background Pattern -->
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.03; z-index: -1; background-image: radial-gradient(circle at 2px 2px, #f97316 1px, transparent 0); background-size: 40px 40px;"></div>

    <!-- Main Container -->
    <div style="min-height: 100vh; display: flex; flex-direction: column; position: relative;">

        <!-- Navigation -->
        <div style="position: relative; z-index: 10;">
            @include('layouts.nav-bar')
        </div>

        <!-- Decorative Line -->
        <div style="height: 4px; background: linear-gradient(90deg, #f97316 0%, #fb923c 50%, #f97316 100%); box-shadow: 0 2px 10px rgba(249, 115, 22, 0.3);"></div>

        <!-- Page Heading -->
        @if (isset($header))
            <header style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); border-bottom: 1px solid rgba(249, 115, 22, 0.1); position: relative;">
                <div style="max-width: 1280px; margin: 0 auto; padding: 32px 24px; position: relative;">
                    <!-- Header Background Decoration -->
                    <div style="position: absolute; top: 0; right: 0; width: 200px; height: 100%; background: linear-gradient(135deg, rgba(249, 115, 22, 0.05) 0%, transparent 70%); border-radius: 0 0 0 100px;"></div>

                    <div style="position: relative; z-index: 2;">
                        <h2 style="font-weight: 700; font-size: 28px; color: #1e293b; line-height: 1.2; margin: 0; display: flex; align-items: center; gap: 12px;">
                            <div style="width: 6px; height: 40px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); border-radius: 3px; box-shadow: 0 2px 8px rgba(249, 115, 22, 0.3);"></div>
                            {{ $header }}
                        </h2>
                        <div style="margin-top: 8px; height: 2px; width: 60px; background: linear-gradient(90deg, #f97316 0%, #fb923c 100%); border-radius: 1px;"></div>
                    </div>
                </div>
            </header>
        @endif

        <!-- Main Content Area -->
        <main style="flex: 1; position: relative; z-index: 1;">
            <!-- Content Container -->
            <div style="max-width: 1280px; margin: 0 auto; padding: 40px 24px; position: relative;">
                <!-- Content Background -->
                <div style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border-radius: 24px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); min-height: 400px; position: relative; overflow: hidden;">
                    <!-- Content Decoration -->
                    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(249, 115, 22, 0.1) 0%, transparent 70%); border-radius: 50%;"></div>
                    <div style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(251, 146, 60, 0.08) 0%, transparent 70%); border-radius: 50%;"></div>

                    <!-- Content Wrapper -->
                    <div style="position: relative; z-index: 2; padding: 32px;">
                        @yield('content')
                    </div>
                </div>
            </div>
        </main>

        <!-- Spacer for Footer -->
        <div style="height: 80px;"></div>

        {{--
        @livewire('footer') --}}
        @include('home.footer')

    </div>



    @include('layouts.quiz_script')
    @livewireScripts
</body>

</html>
