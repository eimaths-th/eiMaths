<footer class="quiz-footer">
    <div class="quiz-footer-container">
        <div class="quiz-footer-grid">
            <section class="quiz-footer-brand" aria-labelledby="quiz-footer-brand-title">
                <img class="quiz-footer-logo" src="{{ asset('images/logo.png') }}" alt="eiMaths">
                <h2 id="quiz-footer-brand-title">{{ __('quiz-management.layout.footer_learning_center') }}</h2>
                <p>{{ __('quiz-management.layout.footer_mission') }}</p>
                <p>{{ __('quiz-management.layout.footer_mission_detail') }}</p>
            </section>

            <section aria-labelledby="quiz-footer-ratchapruek-title">
                <h2 id="quiz-footer-ratchapruek-title">สาขา ราชพฤกษ์</h2>
                <p class="quiz-footer-branch-name">Ratchapruek Branch</p>
                <ul class="quiz-footer-contacts">
                    <li><i class="fas fa-phone-alt" aria-hidden="true"></i><a href="tel:+66616208666">+666-1620-8666</a></li>
                    <li><i class="fab fa-line" aria-hidden="true"></i><a href="https://lin.ee/TqNbyqr" target="_blank" rel="noopener noreferrer">@eimaths-th</a></li>
                    <li><i class="fab fa-facebook" aria-hidden="true"></i><a href="https://www.facebook.com/eimaths.th" target="_blank" rel="noopener noreferrer">eiMaths TH</a></li>
                </ul>
            </section>

            <section aria-labelledby="quiz-footer-bangkae-title">
                <h2 id="quiz-footer-bangkae-title">สาขา บางแค</h2>
                <p class="quiz-footer-branch-name">Bangkae Branch</p>
                <ul class="quiz-footer-contacts">
                    <li><i class="fas fa-phone-alt" aria-hidden="true"></i><a href="tel:+66932585897">+6693-258-5897</a></li>
                    <li><i class="fab fa-line" aria-hidden="true"></i><a href="https://lin.ee/SFpwGwU" target="_blank" rel="noopener noreferrer">@eiMathsBangkae</a></li>
                    <li><i class="fab fa-facebook" aria-hidden="true"></i><a href="https://www.facebook.com/eimaths.th" target="_blank" rel="noopener noreferrer">eiMaths - Bangkae</a></li>
                </ul>
            </section>
        </div>

        <div class="quiz-footer-bottom">
            <p>&copy; {{ date('Y') }} eiMaths Learning Center</p>
            <p>{{ __('quiz-management.layout.footer_rights') }}</p>
        </div>
    </div>
</footer>
