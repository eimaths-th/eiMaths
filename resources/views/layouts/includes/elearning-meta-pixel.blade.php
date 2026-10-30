@once
@php
    $elearningPixelId = (string) config('services.elearning_meta_pixel.id');
    $elearningPublicPage = request()->routeIs('student.login', 'guest.quiz.register');
    $elearningSuccessEvent = null;
    if (request()->routeIs('guest.quiz.home', 'home3')) {
        // Consume the server-confirmed result once, so refreshes do not count again.
        $pendingEvent = session()->pull('elearning_meta_event');
        $authenticated = request()->routeIs('guest.quiz.home')
            ? auth('guest_quiz')->check() : auth('student')->check();
        if ($authenticated && in_array($pendingEvent, ['CompleteRegistration', 'ELearningLogin'], true)) {
            $elearningSuccessEvent = $pendingEvent;
        }
    }
@endphp
@if (config('services.elearning_meta_pixel.enabled') && preg_match('/^[0-9]+$/D', $elearningPixelId) && request()->getHost() === 'app.eimaths-th.com' && ($elearningPublicPage || $elearningSuccessEvent))
    {{-- Event names only: never pass account, form, password or learning-result fields. --}}
    <script>
        (function () {
            if (window.location.hostname !== 'app.eimaths-th.com') return;
            const pixelId = @json($elearningPixelId);
            const successEvent = @json($elearningSuccessEvent);
            const registrationPage = @json(request()->routeIs('guest.quiz.register'));
            !function(f,b,e,v,n,t,s) {
                if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;
                s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s);
            }(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');

            const sent = new Set();
            function sendOnce(eventName, custom) {
                if (sent.has(eventName)) return;
                try {
                    fbq(custom ? 'trackSingleCustom' : 'trackSingle', pixelId, eventName);
                    sent.add(eventName);
                } catch (error) {
                    // Tracking failure must never prevent registration or login.
                }
            }
            try {
                fbq('set', 'autoConfig', false, pixelId);
                fbq('init', pixelId);
            } catch (error) { return; }
            sendOnce('PageView', false);
            window.eimathsTrackFreeTrialOpen = function () {
                sendOnce('OpenFreeTrialForm', true);
            };
            if (registrationPage) window.eimathsTrackFreeTrialOpen();
            if (successEvent === 'CompleteRegistration') sendOnce(successEvent, false);
            if (successEvent === 'ELearningLogin') sendOnce(successEvent, true);
        })();
    </script>
@endif
@endonce
