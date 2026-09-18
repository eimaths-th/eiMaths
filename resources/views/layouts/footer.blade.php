<footer class="main-footer app-footer">
    <div class="app-footer__inner">
        <span class="app-footer__copyright">
            {{ __('navigation.footer.copyright', ['year' => date('Y')]) }}
        </span>
        <span id="real-time-clock" class="app-footer__clock"></span>
    </div>
    <span id="location" hidden></span>
    <span id="weather" hidden></span>
    <a class="scroll-to-top rounded" href="#page-top"
        aria-label="{{ __('navigation.footer.back_to_top') }}"
        title="{{ __('navigation.footer.back_to_top') }}">
        <i class="fas fa-angle-up" aria-hidden="true"></i>
    </a>
</footer>
