@once
    @php($tutorial_config = app(\App\Domain\Affiliate\Services\AffiliateTutorialService::class)->ForUser(auth()->user()))
    <script type="application/json" id="affiliate-tutorial-config">@json($tutorial_config)</script>
    <dialog class="affiliate-tour" id="affiliate-tutorial" aria-labelledby="affiliate-tutorial-title" aria-describedby="affiliate-tutorial-body">
        <svg class="affiliate-tour__shade" width="100%" height="100%" aria-hidden="true">
            <defs><mask id="affiliate-tutorial-mask"><rect width="100%" height="100%" fill="white"/><rect data-tour-cutout rx="10" fill="black" width="0" height="0"/></mask></defs>
            <rect width="100%" height="100%" fill="#111e30" fill-opacity="0.76" mask="url(#affiliate-tutorial-mask)"/>
        </svg>
        <div class="affiliate-tour__ring" data-tour-ring aria-hidden="true" hidden></div>
        <section class="affiliate-tour__panel" data-tour-panel>
            <div class="affiliate-tour__eyebrow"><span>eiMaths</span><span data-tour-progress></span></div>
            <div class="affiliate-tour__track" role="progressbar" aria-label="{{ __('affiliate_tutorial.labels.name') }}" aria-valuemin="1" data-tour-meter><span data-tour-bar></span></div>
            <img class="affiliate-tour__hero" data-tour-hero alt="" hidden>
            <h2 id="affiliate-tutorial-title" tabindex="-1"></h2>
            <p id="affiliate-tutorial-body"></p>
            <p class="affiliate-tour__hint" data-tour-hint>{{ __('affiliate_tutorial.labels.preview') }}</p>
            <details class="affiliate-tour__example" data-tour-example hidden>
                <summary>{{ __('affiliate_tutorial.labels.illustration') }}</summary>
                <img data-tour-example-image alt="{{ __('affiliate_tutorial.labels.imageAlt') }}" loading="lazy">
            </details>
            <p class="affiliate-tour__error" data-tour-error role="alert" hidden></p>
            <div class="affiliate-tour__actions">
                <button type="button" class="affiliate-tour__skip" data-tour-skip>{{ __('affiliate_tutorial.labels.skip') }}</button>
                <button type="button" class="affiliate-tour__back" data-tour-back>{{ __('affiliate_tutorial.labels.back') }}</button>
                <button type="button" class="affiliate-tour__next" data-tour-next>{{ __('affiliate_tutorial.labels.next') }}</button>
            </div>
        </section>
    </dialog>
    <p class="affiliate-tour__feedback" data-tour-feedback role="status" hidden></p>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/affiliate-tutorial.css') }}">
    @endpush
    @push('scripts')
        <script type="module" src="{{ asset('js/affiliate-tutorial.js') }}"></script>
    @endpush
@endonce
