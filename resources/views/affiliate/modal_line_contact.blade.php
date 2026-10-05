@php
    $affiliate_branch_contacts = [
        'RP' => [
            'aliases' => 'ratchaphruek|ratchapruek|ราชพฤกษ์',
            'name' => 'The Crystal SB Ratchapruek',
            'label' => __('course-website.success.branches.rp_label'),
            'address' => 'ชั้น 3 โซน Education เลขที่ 555/9 ตำบลบางขนุน อำเภอบางกรวย จังหวัดนนทบุรี 11130',
            'line_label' => '@eimaths-th',
            'line_url' => 'https://lin.ee/K244eaZ',
            'phone_label' => '061 620 8666',
            'phone_url' => 'tel:0616208666',
            'facebook_label' => 'eiMaths - TH',
            'facebook_url' => 'https://www.facebook.com/search/top?q=eiMaths%20-%20TH',
        ],
        'BK' => [
            'aliases' => 'bangkae|bang kae|บางแค',
            'name' => 'eiMaths Seacon Bangkae',
            'label' => __('course-website.success.branches.bk_label'),
            'address' => 'ชั้น 4 โซน HarborLand ศูนย์การค้าซีคอนบางแค 607 ถนนเพชรเกษม แขวงบางหว้า เขตภาษีเจริญ กรุงเทพมหานคร 10160',
            'line_label' => '@eiMathsBangkae',
            'line_url' => 'https://lin.ee/SFpwGwU',
            'phone_label' => '093 258 5897',
            'phone_url' => 'tel:0932585897',
            'facebook_label' => 'eiMaths - Bangkae',
            'facebook_url' => 'https://www.facebook.com/search/top?q=eiMaths%20-%20Bangkae',
        ],
    ];
@endphp

<style>
    .course-success-modal .modal-dialog {
        max-width: 560px;
        margin: 1.5rem auto;
        padding: 0 1rem;
    }

    .course-success-modal .modal-content {
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.2);
    }

    .course-success-modal .modal-header {
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem 1.5rem 0;
        border: 0;
    }

    .course-success-modal__heading {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 0.875rem;
    }

    .course-success-modal__status-icon {
        display: inline-flex;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #ecfdf3;
        color: #15803d;
    }

    .course-success-modal__status-icon svg {
        width: 23px;
        height: 23px;
        stroke-width: 2.4;
    }

    .course-success-modal__eyebrow {
        margin: 0 0 0.2rem;
        color: #c2410c;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .course-success-modal .modal-title {
        margin: 0;
        color: #172033;
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .course-success-modal__subtitle {
        margin: 0.2rem 0 0;
        color: #667085;
        font-size: 0.86rem;
    }

    .course-success-modal__close {
        display: inline-flex;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        align-items: center;
        justify-content: center;
        margin-left: auto;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #ffffff;
        color: #667085;
        transition: background-color 0.18s ease, color 0.18s ease;
    }

    .course-success-modal__close:hover,
    .course-success-modal__close:focus-visible {
        background: #f8fafc;
        color: #172033;
    }

    .course-success-modal__close svg {
        width: 19px;
        height: 19px;
    }

    .course-success-modal .modal-body {
        padding: 1.25rem 1.5rem 1.5rem;
    }

    .course-success-modal__notice {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
        padding: 0.9rem 1rem;
        border: 1px solid #fed7aa;
        border-radius: 14px;
        background: #fffaf5;
        color: #7c2d12;
    }

    .course-success-modal__notice svg {
        width: 20px;
        height: 20px;
        flex: 0 0 20px;
        margin-top: 0.1rem;
        color: #ea580c;
    }

    .course-success-modal__notice strong,
    .course-success-modal__notice span {
        display: block;
    }

    .course-success-modal__notice strong {
        margin-bottom: 0.15rem;
        color: #172033;
        font-size: 0.93rem;
    }

    .course-success-modal__notice span {
        color: #667085;
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .course-success-modal__selection {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.65rem;
    }

    .course-success-modal__selection span {
        color: #667085;
        font-size: 0.82rem;
        font-weight: 500;
    }

    .course-success-modal__selection strong {
        color: #172033;
        font-size: 0.9rem;
        text-align: right;
    }

    .course-success-branch {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        padding: 1.1rem;
    }

    .course-success-branch__header {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding-bottom: 0.95rem;
        border-bottom: 1px solid #eef2f6;
    }

    .course-success-branch__pin {
        display: inline-flex;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: #fff7ed;
        color: #ea580c;
    }

    .course-success-branch__pin svg {
        width: 20px;
        height: 20px;
    }

    .course-success-branch__title {
        margin: 0;
        color: #172033;
        font-size: 1.03rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .course-success-branch__label {
        display: block;
        margin-top: 0.1rem;
        color: #667085;
        font-size: 0.8rem;
    }

    .course-success-branch__address {
        display: flex;
        gap: 0.65rem;
        margin: 0;
        padding: 0.95rem 0;
        color: #475467;
        font-size: 0.84rem;
        line-height: 1.65;
    }

    .course-success-branch__address svg {
        width: 18px;
        height: 18px;
        flex: 0 0 18px;
        margin-top: 0.16rem;
        color: #98a2b3;
    }

    .course-success-branch__contacts {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.55rem;
        margin: 0 0 1rem;
    }

    .course-success-branch__contact {
        min-width: 0;
        padding: 0.7rem;
        border-radius: 11px;
        background: #f8fafc;
    }

    .course-success-branch__contact span,
    .course-success-branch__contact strong {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .course-success-branch__contact span {
        margin-bottom: 0.15rem;
        color: #98a2b3;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .course-success-branch__contact strong {
        color: #344054;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .course-success-branch__actions {
        display: grid;
        grid-template-columns: 1.2fr 1fr 1fr;
        gap: 0.55rem;
    }

    .course-success-branch__action {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        padding: 0.6rem 0.7rem;
        border: 1px solid #d0d5dd;
        border-radius: 10px;
        background: #ffffff;
        color: #344054;
        font-size: 0.79rem;
        font-weight: 600;
        text-decoration: none;
        transition: border-color 0.18s ease, background-color 0.18s ease, color 0.18s ease;
    }

    .course-success-branch__action:hover,
    .course-success-branch__action:focus-visible {
        border-color: #f97316;
        background: #fff7ed;
        color: #c2410c;
        text-decoration: none;
    }

    .course-success-branch__action--primary {
        border-color: #ea580c;
        background: #ea580c;
        color: #ffffff;
    }

    .course-success-branch__action--primary:hover,
    .course-success-branch__action--primary:focus-visible {
        border-color: #c2410c;
        background: #c2410c;
        color: #ffffff;
    }

    .course-success-branch__action svg {
        width: 17px;
        height: 17px;
        flex: 0 0 17px;
    }

    .course-success-modal__empty {
        padding: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #f8fafc;
        color: #475467;
        font-size: 0.86rem;
        line-height: 1.6;
    }

    .course-success-modal .modal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid #eef2f6;
        background: #f8fafc;
    }

    .course-success-modal__follow-up {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
        max-width: 315px;
        margin: 0;
        color: #667085;
        font-size: 0.76rem;
        line-height: 1.5;
        text-align: left;
    }

    .course-success-modal__follow-up svg {
        width: 16px;
        height: 16px;
        flex: 0 0 16px;
        margin-top: 0.12rem;
    }

    .course-success-modal__done {
        display: inline-flex;
        min-height: 40px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.55rem 1rem;
        border: 1px solid #172033;
        border-radius: 10px;
        background: #172033;
        color: #ffffff;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .course-success-modal__done:hover,
    .course-success-modal__done:focus-visible {
        background: #293548;
        color: #ffffff;
    }

    .course-success-modal__done svg {
        width: 16px;
        height: 16px;
    }

    @media (max-width: 575.98px) {
        .course-success-modal .modal-dialog {
            margin: 0.75rem auto;
            padding: 0 0.5rem;
        }

        .course-success-modal .modal-header {
            padding: 1.2rem 1.1rem 0;
        }

        .course-success-modal .modal-body {
            padding: 1rem 1.1rem 1.15rem;
        }

        .course-success-modal .modal-title {
            font-size: 1.15rem;
        }

        .course-success-modal__status-icon {
            width: 40px;
            height: 40px;
            flex-basis: 40px;
        }

        .course-success-modal__selection {
            display: block;
        }

        .course-success-modal__selection strong {
            display: block;
            margin-top: 0.15rem;
            text-align: left;
        }

        .course-success-branch__contacts,
        .course-success-branch__actions {
            grid-template-columns: 1fr;
        }

        .course-success-modal .modal-footer {
            align-items: stretch;
            flex-direction: column;
            padding: 0.9rem 1.1rem 1.1rem;
        }

        .course-success-modal__follow-up {
            max-width: none;
        }

        .course-success-modal__done {
            width: 100%;
        }
    }
</style>

<div class="modal fade course-success-modal" id="modalLineContact" tabindex="-1" aria-labelledby="courseSuccessModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="course-success-modal__heading">
                    <span class="course-success-modal__status-icon" aria-hidden="true">
                        <i data-lucide="check"></i>
                    </span>
                    <div>
                        <p class="course-success-modal__eyebrow">{{ __('course-website.success.eyebrow') }}</p>
                        <h3 class="modal-title" id="courseSuccessModalTitle">{{ __('course-website.success.title') }}</h3>
                        <p class="course-success-modal__subtitle">{{ __('course-website.success.subtitle') }}</p>
                    </div>
                </div>
                <button type="button" class="course-success-modal__close" data-bs-dismiss="modal" aria-label="{{ __('course-website.success.close') }}">
                    <i data-lucide="x" aria-hidden="true"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="course-success-modal__notice">
                    <i data-lucide="calendar-check" aria-hidden="true"></i>
                    <div>
                        <strong>{{ __('course-website.success.next_step_title') }}</strong>
                        <span>{{ __('course-website.success.next_step_message') }}</span>
                    </div>
                </div>

                <div class="course-success-modal__selection">
                    <span>{{ __('course-website.success.selected_branch') }}</span>
                    <strong data-course-success-branch-name>{{ __('course-website.success.checking_branch') }}</strong>
                </div>

                @foreach ($affiliate_branch_contacts as $branch_code => $contact)
                    <article
                        class="course-success-branch"
                        data-course-success-branch
                        data-branch-code="{{ $branch_code }}"
                        data-branch-aliases="{{ $contact['aliases'] }}"
                        data-branch-display-name="{{ $contact['name'] }}"
                        hidden
                    >
                        <div class="course-success-branch__header">
                            <span class="course-success-branch__pin" aria-hidden="true">
                                <i data-lucide="building-2"></i>
                            </span>
                            <div>
                                <h4 class="course-success-branch__title">{{ $contact['name'] }}</h4>
                                <span class="course-success-branch__label">{{ $contact['label'] }}</span>
                            </div>
                        </div>

                        <p class="course-success-branch__address">
                            <i data-lucide="map-pin" aria-hidden="true"></i>
                            <span>{{ $contact['address'] }}</span>
                        </p>

                        <div class="course-success-branch__contacts" aria-label="{{ __('course-website.success.contact_details') }}">
                            <div class="course-success-branch__contact">
                                <span>LINE</span>
                                <strong>{{ $contact['line_label'] }}</strong>
                            </div>
                            <div class="course-success-branch__contact">
                                <span>{{ __('course-website.success.phone') }}</span>
                                <strong>{{ $contact['phone_label'] }}</strong>
                            </div>
                            <div class="course-success-branch__contact">
                                <span>Facebook</span>
                                <strong>{{ $contact['facebook_label'] }}</strong>
                            </div>
                        </div>

                        <div class="course-success-branch__actions">
                            <a class="course-success-branch__action course-success-branch__action--primary" href="{{ $contact['line_url'] }}" target="_blank" rel="noopener">
                                <i data-lucide="message-circle" aria-hidden="true"></i>
                                <span>{{ __('course-website.success.chat_line') }}</span>
                            </a>
                            <a class="course-success-branch__action" href="{{ $contact['phone_url'] }}">
                                <i data-lucide="phone" aria-hidden="true"></i>
                                <span>{{ __('course-website.success.call_branch') }}</span>
                            </a>
                            <a class="course-success-branch__action" href="{{ $contact['facebook_url'] }}" target="_blank" rel="noopener">
                                <i data-lucide="external-link" aria-hidden="true"></i>
                                <span>Facebook</span>
                            </a>
                        </div>
                    </article>
                @endforeach

                <div class="course-success-modal__empty" data-course-success-branch-empty hidden>
                    {{ __('course-website.success.empty_message') }}
                </div>
            </div>

            <div class="modal-footer">
                <p class="course-success-modal__follow-up">
                    <i data-lucide="clock" aria-hidden="true"></i>
                    <span>{{ __('course-website.success.follow_up') }}</span>
                </p>
                <button type="button" class="course-success-modal__done" data-bs-dismiss="modal">
                    <i data-lucide="x" aria-hidden="true"></i>
                    <span>{{ __('course-website.success.close') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

@once
    <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js" defer></script>
@endonce

<script>
    (() => {
        const modal = document.getElementById('modalLineContact');
        if (!modal) return;

        const branchCards = Array.from(modal.querySelectorAll('[data-course-success-branch]'));
        const branchName = modal.querySelector('[data-course-success-branch-name]');
        const emptyState = modal.querySelector('[data-course-success-branch-empty]');
        const normalize = (value) => String(value || '')
            .toLocaleLowerCase('th')
            .replace(/[^a-z0-9ก-๙]/g, '');

        const renderLucideIcons = () => {
            if (window.lucide) {
                window.lucide.createIcons({
                    attrs: {
                        'aria-hidden': 'true',
                        'stroke-width': 1.9,
                    },
                });
            }
        };

        window.setCourseSuccessBranch = (branch = {}) => {
            const selectedCode = normalize(branch.code);
            const selectedName = normalize(branch.name);

            const selectedCard = branchCards.find((card) => {
                if (selectedCode && normalize(card.dataset.branchCode) === selectedCode) return true;

                const aliases = String(card.dataset.branchAliases || '')
                    .split('|')
                    .map(normalize)
                    .filter(Boolean);

                return selectedName && aliases.some((alias) => selectedName.includes(alias));
            });

            branchCards.forEach((card) => {
                card.hidden = card !== selectedCard;
            });

            emptyState.hidden = Boolean(selectedCard);
            branchName.textContent = branch.name || selectedCard?.dataset.branchDisplayName || @json(__('course-website.success.selected_branch'));
            renderLucideIcons();
        };

        modal.addEventListener('shown.bs.modal', renderLucideIcons);

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', renderLucideIcons, { once: true });
        } else {
            renderLucideIcons();
        }
    })();
</script>
