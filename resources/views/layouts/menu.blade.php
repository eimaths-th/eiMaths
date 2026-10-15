@php
    $menu_user = auth()->user();
    $menu_route = request()->route()?->getName();
    $menu_can = static fn (string $permission): bool => $menu_user?->can($permission) ?? false;
    $menu_is_central = $menu_user !== null && \App\Support\Branch\BranchAccess::isCentral($menu_user);
    $menu_is_owner = $menu_user?->isSystemAdmin() ?? false;

    $affiliate_portal_access_service = app(\App\Domain\Affiliate\Services\AffiliatePortalAccess::class);
    $affiliate_employee_eligibility_service = app(\App\Domain\Affiliate\Services\AffiliateEmployeeEligibility::class);
    $affiliate_portal_can_access = $menu_user !== null
        && $affiliate_portal_access_service->CanAccess($menu_user);
    $show_employee_affiliate_status = $menu_user !== null
        && (bool) config('affiliate.features.employee_enrollment', false)
        && $affiliate_employee_eligibility_service->IsEligible($menu_user)
        && !$affiliate_portal_can_access;

    $show_ebook_menu = ($menu_is_central && $menu_can('ebook-manage'))
        || $menu_can('ebook-activity-list');
    $show_course_menu = $show_ebook_menu || $menu_can('course-manage')
        || $menu_can('course-list')
        || (!$menu_is_central && $menu_can('teaching-list'));
    $show_finance_menu = $menu_can('receipt-list')
        || ((bool) config('financial-documents.credit_note.enabled') && $menu_can('credit-note-list'))
        || $menu_can('financial-settings-manage');
    $show_stock_menu = $menu_can('product-list') || $book_shipment_alert_role !== null;
    $show_content_menu = $menu_can('blog-list')
        || $menu_can('faq-list')
        || ($menu_can('course-manage') && $menu_can('central-settings-manage'));
    $show_affiliate_admin_menu = (
        (bool) config('affiliate.features.administration', false)
        && ($menu_can('affiliate-list') || $menu_can('affiliate-content-manage'))
    ) || $menu_can('affiliate-config-edit')
        || $menu_can('affiliate-withdrawal-list')
        || $menu_can('affiliate-course-application-list')
        || $menu_can('course-manage');
    $show_quiz_menu = $menu_can('quiz-manage');
    $show_user_menu = $menu_can('user-list') || $menu_can('role-list') || $menu_can('activity-log-list') || $menu_is_owner;
    $show_centre_menu = $menu_can('centre-manage')
        || $menu_can('department-list')
        || $menu_can('branch-transfer');
    $show_system_menu = ($menu_can('all-centre') && $menu_can('central-settings-manage'))
        || $menu_can('integration-settings-manage');

    $crm_menu_active = request()->routeIs('crm.leads.*', 'tracking-links.*');
    $student_menu_active = in_array($menu_route, [
        'contacts', 'guest.report', 'demo.list', 'discontinued', 'contacts.graduated',
        'histories.index', 'parent.index', 'ei_form.backEndIndex',
    ], true);
    $course_menu_active = request()->routeIs('categories.*', 'course.*', 'TeachingPeriod.*', 'ebooks.*');
    $finance_menu_active = request()->routeIs('receipts*', 'credit-notes.*', 'qrcode_payment.*', 'termfee*');
    $stock_menu_active = request()->routeIs('products*', 'book-shipments.*', 'incoming-book-shipments.*');
    $quiz_menu_active = request()->routeIs(
        'home3', 'questions*', 'quizzes*', 'questions-quizzes.*', 'tests', 'assign'
    );
    $content_menu_active = request()->routeIs('blogs.*', 'faqs.*', 'promotion-img.*', 'footer-setting.*');
    $affiliate_admin_active = request()->routeIs(
        'affiliate.admin.*', 'affiliate.finance.configuration.*', 'affiliate.withdrawals.admin.*',
        'affiliate-content-manage.*', 'courses.pending.*'
    );
    $user_menu_active = request()->routeIs('users.*', 'roles.*', 'permission.*', 'generate.*', 'activity-logs.*');
    $centre_menu_active = request()->routeIs('departments*', 'positions*', 'branch-transfers.*');
    $system_menu_active = request()->routeIs('parameter*', 'line-api.*');
@endphp

@can('dashboard')
    <li class="nav-item">
        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="nav-icon fas fa-home"></i>
            <p><span class="nav-menu-label" title="{{ __('navigation.menu.dashboard') }}">{{ __('navigation.menu.dashboard') }}</span></p>
        </a>
    </li>
@endcan

@if ($show_employee_affiliate_status)
    <li class="nav-item">
        <a href="{{ route('affiliate.employee-access.show') }}"
            class="nav-link {{ request()->routeIs('affiliate.employee-access.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-id-badge"></i>
            <p><span class="nav-menu-label" title="{{ __('affiliate.employee_access.menu_label') }}">{{ __('affiliate.employee_access.menu_label') }}</span></p>
        </a>
    </li>
@endif

@if ($affiliate_portal_can_access && (bool) config('affiliate.features.wallet_withdrawals', false))
    @php
        $affiliate_finance_active = request()->routeIs(
            'affiliate.dashboard',
            'affiliate.guide.*',
            'affiliate.payout-accounts.*',
            'affiliate.withdrawals.index',
            'affiliate.analytics.*',
            'affiliate.media.*',
        );
    @endphp
    <li class="nav-item {{ $affiliate_finance_active ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $affiliate_finance_active ? 'active' : '' }}">
            <i class="nav-icon fas fa-wallet"></i>
            <p><span class="nav-menu-label" title="{{ __('navigation.menu.affiliate_finance') }}">{{ __('navigation.menu.affiliate_finance') }}</span></p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('affiliate.dashboard') }}"
                    class="nav-link {{ request()->routeIs('affiliate.dashboard') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-chart-line"></i>
                    <p><span class="nav-menu-label" title="{{ __('navigation.menu.affiliate_dashboard') }}">{{ __('navigation.menu.affiliate_dashboard') }}</span></p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('affiliate.guide.index') }}"
                    class="nav-link {{ request()->routeIs('affiliate.guide.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-book-open"></i>
                    <p><span class="nav-menu-label" title="{{ __('navigation.menu.affiliate_guide') }}">{{ __('navigation.menu.affiliate_guide') }}</span></p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('affiliate.payout-accounts.index') }}"
                    class="nav-link {{ request()->routeIs('affiliate.payout-accounts.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-credit-card"></i>
                    <p><span class="nav-menu-label" title="{{ __('navigation.menu.payout_accounts') }}">{{ __('navigation.menu.payout_accounts') }}</span></p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('affiliate.withdrawals.index') }}"
                    class="nav-link {{ request()->routeIs('affiliate.withdrawals.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-money-bill-wave"></i>
                    <p><span class="nav-menu-label" title="{{ __('navigation.menu.withdrawals') }}">{{ __('navigation.menu.withdrawals') }}</span></p>
                </a>
            </li>
            @if ((bool) config('affiliate.features.analytics', false))
                <li class="nav-item">
                    <a href="{{ route('affiliate.analytics.index') }}"
                        class="nav-link {{ request()->routeIs('affiliate.analytics.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-pie"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.analytics') }}">{{ __('navigation.menu.analytics') }}</span></p>
                    </a>
                </li>
            @endif
            @if ((bool) config('affiliate.features.media_library', false))
                <li class="nav-item">
                    <a href="{{ route('affiliate.media.index') }}"
                        class="nav-link {{ request()->routeIs('affiliate.media.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-photo-video"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.media') }}">{{ __('navigation.menu.media') }}</span></p>
                    </a>
                </li>
            @endif
        </ul>
    </li>
@endif

@can('crm-leads-menu-view')
    <li class="nav-item {{ $crm_menu_active ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $crm_menu_active ? 'active' : '' }}">
            <i class="nav-icon fas fa-address-book"></i>
            <p><span class="nav-menu-label" title="{{ __('navigation.menu.crm_leads') }}">{{ __('navigation.menu.crm_leads') }}</span></p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('crm.leads.index') }}"
                    class="nav-link {{ request()->routeIs('crm.leads.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-friends"></i>
                    <p><span class="nav-menu-label" title="{{ __('navigation.menu.leads_list') }}">{{ __('navigation.menu.leads_list') }}</span></p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('tracking-links.index') }}"
                    class="nav-link {{ request()->routeIs('tracking-links.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-link"></i>
                    <p><span class="nav-menu-label" title="{{ __('navigation.menu.tracking_link_generator') }}">{{ __('navigation.menu.tracking_link_generator') }}</span></p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('tracking-links.report') }}"
                    class="nav-link {{ request()->routeIs('tracking-links.report') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-chart-bar"></i>
                    <p><span class="nav-menu-label" title="{{ __('navigation.menu.tracking_link_report') }}">{{ __('navigation.menu.tracking_link_report') }}</span></p>
                </a>
            </li>
        </ul>
    </li>
@endcan

@can('student-list')
    <li class="nav-item {{ $student_menu_active ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $student_menu_active ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-graduate"></i>
            <p><span class="nav-menu-label" title="{{ __('navigation.menu.students_management') }}">{{ __('navigation.menu.students_management') }}</span></p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('contacts') }}" class="nav-link {{ $menu_route === 'contacts' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-users"></i>
                    <p><span class="nav-menu-label" title="{{ __('navigation.menu.students_list') }}">{{ __('navigation.menu.students_list') }}</span></p>
                </a>
            </li>
            @if (\App\Support\Branch\BranchAccess::isCentral(Auth::user()))
                <li class="nav-item">
                    <a href="{{ route('guest.report') }}" class="nav-link {{ $menu_route === 'guest.report' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.guest_reports') }}">{{ __('navigation.menu.guest_reports') }}</span></p>
                    </a>
                </li>
            @endif
            @if ((bool) config('demo-students.enabled', false))
                <li class="nav-item">
                    <a href="{{ route('demo.list') }}" class="nav-link {{ $menu_route === 'demo.list' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-clock"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.demo_students') }}">{{ __('navigation.menu.demo_students') }}</span></p>
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a href="{{ route('discontinued') }}" class="nav-link {{ $menu_route === 'discontinued' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-slash"></i>
                    <p><span class="nav-menu-label" title="{{ __('navigation.menu.discontinued_students') }}">{{ __('navigation.menu.discontinued_students') }}</span></p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('contacts.graduated') }}"
                    class="nav-link {{ $menu_route === 'contacts.graduated' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-graduation-cap"></i>
                    <p><span class="nav-menu-label" title="{{ __('navigation.menu.graduated_students') }}">{{ __('navigation.menu.graduated_students') }}</span></p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('histories.index') }}"
                    class="nav-link {{ request()->routeIs('histories.*', 'history.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-history"></i>
                    <p><span class="nav-menu-label" title="{{ __('navigation.menu.study_histories') }}">{{ __('navigation.menu.study_histories') }}</span></p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('parent.index') }}"
                    class="nav-link {{ request()->routeIs('parent.*', 'parents.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-friends"></i>
                    <p><span class="nav-menu-label" title="{{ __('navigation.menu.parents') }}">{{ __('navigation.menu.parents') }}</span></p>
                </a>
            </li>
            @if ($menu_is_central)
            <li class="nav-item">
                <a href="{{ route('ei_form.backEndIndex') }}"
                    class="nav-link {{ $menu_route === 'ei_form.backEndIndex' ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file-signature"></i>
                    <p><span class="nav-menu-label" title="{{ __('navigation.menu.eiform') }}">{{ __('navigation.menu.eiform') }}</span></p>
                </a>
            </li>
            @endif
        </ul>
    </li>
@endcan

@if ($show_course_menu)
    <li class="nav-item {{ $course_menu_active ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $course_menu_active ? 'active' : '' }}">
            <i class="nav-icon fas fa-chalkboard-teacher"></i>
            <p><span class="nav-menu-label" title="{{ __('navigation.menu.course_management') }}">{{ __('navigation.menu.course_management') }}</span></p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            @can('course-list')
                <li class="nav-item">
                    <a href="{{ route('course.catalog') }}" class="nav-link {{ $menu_route === 'course.catalog' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-book-open"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.course_catalog') }}">{{ __('navigation.menu.course_catalog') }}</span></p>
                    </a>
                </li>
            @endcan
            @can('course-manage')
                <li class="nav-item">
                    <a href="{{ route('categories.index') }}"
                        class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tags"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.course_categories') }}">{{ __('navigation.menu.course_categories') }}</span></p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('course.list') }}" class="nav-link {{ $menu_route === 'course.list' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-book"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.courses') }}">{{ __('navigation.menu.courses') }}</span></p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('course.level.list') }}"
                        class="nav-link {{ request()->routeIs('course.level.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-layer-group"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.course_levels') }}">{{ __('navigation.menu.course_levels') }}</span></p>
                    </a>
                </li>
            @endcan
            @if ($menu_can('course-manage') || $menu_can('teaching-list'))
                <li class="nav-item">
                    <a href="{{ route('TeachingPeriod.index') }}"
                        class="nav-link {{ request()->routeIs('TeachingPeriod.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.teaching_periods') }}">{{ __('navigation.menu.teaching_periods') }}</span></p>
                    </a>
                </li>
            @endif
            @if ($show_ebook_menu)
                <li class="nav-item">
                    <a href="{{ route('ebooks.index') }}" class="nav-link {{ request()->routeIs('ebooks.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-book-open"></i>
                        <p><span class="nav-menu-label" title="{{ __('ebooks.menu') }}">{{ __('ebooks.menu') }}</span></p>
                    </a>
                </li>
            @endif
            @can('course-manage')
                <li class="nav-item">
                    <a href="{{ route('affiliate.courses') }}" target="_blank" class="nav-link">
                        <i class="nav-icon fas fa-external-link-alt"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.course_website') }}">{{ __('navigation.menu.course_website') }}</span></p>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if ($show_finance_menu)
    <li class="nav-item {{ $finance_menu_active ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $finance_menu_active ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-invoice-dollar"></i>
            <p><span class="nav-menu-label" title="{{ __('navigation.menu.finance_documents') }}">{{ __('navigation.menu.finance_documents') }}</span></p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            @can('receipt-list')
                <li class="nav-item">
                    <a href="{{ route('receipts_pending') }}"
                        class="nav-link {{ $menu_route === 'receipts_pending' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-clock"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.pending_receipts') }}">{{ __('navigation.menu.pending_receipts') }}</span></p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('receipts', ['type' => 0]) }}"
                        class="nav-link {{ $menu_route === 'receipts' && (string) request('type') === '0' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-receipt"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.payment_receipts') }}">{{ __('navigation.menu.payment_receipts') }}</span></p>
                    </a>
                </li>
            @endcan
            @if ((bool) config('financial-documents.credit_note.enabled') && $menu_can('credit-note-list'))
                <li class="nav-item">
                    <a href="{{ route('credit-notes.index') }}"
                        class="nav-link {{ request()->routeIs('credit-notes.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-invoice"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.credit_notes') }}">{{ __('navigation.menu.credit_notes') }}</span></p>
                    </a>
                </li>
            @endif
            @can('financial-settings-manage')
                <li class="nav-item">
                    <a href="{{ route('qrcode_payment.index') }}"
                        class="nav-link {{ request()->routeIs('qrcode_payment.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-qrcode"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.qr_payment') }}">{{ __('navigation.menu.qr_payment') }}</span></p>
                    </a>
                </li>
                @can('all-centre')
                    <li class="nav-item">
                        <a href="{{ route('termfee') }}" class="nav-link {{ request()->routeIs('termfee*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tags"></i>
                            <p><span class="nav-menu-label" title="{{ __('navigation.menu.term_fees_book_prices') }}">{{ __('navigation.menu.term_fees_book_prices') }}</span></p>
                        </a>
                    </li>
                @endcan
            @endcan
        </ul>
    </li>
@endif

@if ($show_stock_menu)
    <li class="nav-item {{ $stock_menu_active ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $stock_menu_active ? 'active' : '' }}">
            <i class="nav-icon fas fa-boxes"></i>
            <p>
                <span class="nav-menu-label" title="{{ __('navigation.menu.stock_logistics') }}">{{ __('navigation.menu.stock_logistics') }}</span>
                @if ($book_shipment_alert_role === 'centre')
                    <span class="right badge badge-danger sidebar-menu-badge sidebar-menu-badge--parent"
                        data-book-shipment-badge="centre" hidden
                        aria-label="{{ __('navigation.status.no_incoming_shipments') }}"></span>
                @elseif ($book_shipment_alert_role === 'hq')
                    <span class="right badge badge-danger sidebar-menu-badge sidebar-menu-badge--parent"
                        data-book-shipment-badge="hq" hidden
                        aria-label="{{ __('navigation.status.no_active_shipments') }}"></span>
                @endif
            </p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            @can('product-list')
                <li class="nav-item">
                    <a href="{{ route('products') }}" class="nav-link {{ request()->routeIs('products*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-box-open"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.products') }}">{{ __('navigation.menu.products') }}</span></p>
                    </a>
                </li>
            @endcan
            @if ($book_shipment_alert_role === 'hq')
                <li class="nav-item">
                    <a href="{{ route('book-shipments.index') }}"
                        class="nav-link {{ request()->routeIs('book-shipments.*') ? 'active' : '' }}"
                        title="{{ __('navigation.menu.outgoing_book_shipments') }}">
                        <i class="nav-icon fas fa-truck"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.outgoing_book_shipments') }}">{{ __('navigation.menu.outgoing_book_shipments') }}</span></p>
                    </a>
                </li>
            @endif
            @if ($book_shipment_alert_role === 'centre')
                <li class="nav-item">
                    <a href="{{ route('incoming-book-shipments.index') }}"
                        class="nav-link {{ request()->routeIs('incoming-book-shipments.*') ? 'active' : '' }}"
                        title="{{ __('navigation.menu.incoming_book_shipments') }}">
                        <i class="nav-icon fas fa-inbox"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.incoming_book_shipments') }}">{{ __('navigation.menu.incoming_book_shipments') }}</span></p>
                    </a>
                </li>
            @endif
        </ul>
    </li>
@endif

@if ($show_quiz_menu)
    <li class="nav-item {{ $quiz_menu_active ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $quiz_menu_active ? 'active' : '' }}">
            <i class="nav-icon fas fa-question-circle"></i>
            <p><span class="nav-menu-label" title="{{ __('navigation.menu.quiz_management') }}">{{ __('navigation.menu.quiz_management') }}</span></p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            @can('quiz-manage')
                <li class="nav-item">
                    <a href="{{ route('home3') }}" target="_blank" class="nav-link {{ $menu_route === 'home3' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-home"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.quiz_home') }}">{{ __('navigation.menu.quiz_home') }}</span></p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('questions') }}" class="nav-link {{ request()->routeIs('questions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-list-alt"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.question_bank') }}">{{ __('navigation.menu.question_bank') }}</span></p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('quizzes') }}" class="nav-link {{ request()->routeIs('quizzes*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-clipboard-check"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.quizzes') }}">{{ __('navigation.menu.quizzes') }}</span></p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('questions-quizzes.create') }}"
                        class="nav-link {{ request()->routeIs('questions-quizzes.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tools"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.quiz_builder') }}">{{ __('navigation.menu.quiz_builder') }}</span></p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('tests') }}" class="nav-link {{ $menu_route === 'tests' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-vial"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.test_preview') }}">{{ __('navigation.menu.test_preview') }}</span></p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('assign') }}" class="nav-link {{ $menu_route === 'assign' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-check"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.assign_quiz') }}">{{ __('navigation.menu.assign_quiz') }}</span></p>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if ($show_content_menu)
    <li class="nav-item {{ $content_menu_active ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $content_menu_active ? 'active' : '' }}">
            <i class="nav-icon fas fa-newspaper"></i>
            <p><span class="nav-menu-label" title="{{ __('navigation.menu.content_management') }}">{{ __('navigation.menu.content_management') }}</span></p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            @can('blog-list')
                <li class="nav-item">
                    <a href="{{ route('blogs.index') }}" class="nav-link {{ request()->routeIs('blogs.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-blog"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.blog') }}">{{ __('navigation.menu.blog') }}</span></p>
                    </a>
                </li>
            @endcan
            @can('faq-list')
                <li class="nav-item">
                    <a href="{{ route('faqs.index') }}" class="nav-link {{ request()->routeIs('faqs.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-question"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.faq') }}">{{ __('navigation.menu.faq') }}</span></p>
                    </a>
                </li>
            @endcan
            @if ($menu_can('course-manage') && $menu_can('central-settings-manage'))
                <li class="nav-item">
                    <a href="{{ route('promotion-img.index') }}"
                        class="nav-link {{ request()->routeIs('promotion-img.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-images"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.promotion_banners') }}">{{ __('navigation.menu.promotion_banners') }}</span></p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('footer-setting.index') }}"
                        class="nav-link {{ request()->routeIs('footer-setting.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-window-maximize"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.footer_settings') }}">{{ __('navigation.menu.footer_settings') }}</span></p>
                    </a>
                </li>
            @endif
        </ul>
    </li>
@endif

@if ($show_affiliate_admin_menu)
    <li class="nav-item {{ $affiliate_admin_active ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $affiliate_admin_active ? 'active' : '' }}">
            <i class="nav-icon fas fa-handshake"></i>
            <p>
                <span class="nav-menu-label" title="{{ __('navigation.menu.affiliate_management') }}">{{ __('navigation.menu.affiliate_management') }}</span>
                @if (
                    ((bool) config('affiliate.features.administration', false) && $menu_can('affiliate-list'))
                    || $menu_can('affiliate-course-application-list')
                    || $menu_can('affiliate-withdrawal-list')
                )
                    <span class="right badge badge-danger sidebar-menu-badge sidebar-menu-badge--parent"
                        data-affiliate-management-pending-badge hidden
                        aria-label="{{ __('navigation.status.no_affiliate_pending') }}"></span>
                @endif
            </p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            @can('affiliate-course-application-list')
                <li class="nav-item">
                    <a href="{{ route('courses.pending.index') }}"
                        class="nav-link {{ request()->routeIs('courses.pending.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>
                            <span class="nav-menu-label" title="{{ __('navigation.menu.affiliate_course_applications') }}">{{ __('navigation.menu.affiliate_course_applications') }}</span>
                            <span class="right badge badge-danger sidebar-menu-badge"
                                data-affiliate-course-application-badge hidden
                                aria-label="{{ __('navigation.status.no_course_applications') }}"></span>
                        </p>
                    </a>
                </li>
            @endcan
            @if ((bool) config('affiliate.features.administration', false) && $menu_can('affiliate-list'))
                <li class="nav-item">
                    <a href="{{ route('affiliate.admin.index') }}"
                        class="nav-link {{ request()->routeIs('affiliate.admin.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-check"></i>
                        <p>
                            <span class="nav-menu-label" title="{{ __('navigation.menu.affiliate_accounts') }}">{{ __('navigation.menu.affiliate_accounts') }}</span>
                            <span class="right badge badge-danger sidebar-menu-badge"
                                data-affiliate-pending-approval-badge hidden
                                aria-label="{{ __('navigation.status.no_affiliate_approval') }}"></span>
                        </p>
                    </a>
                </li>
            @endif
            @can('affiliate-config-edit')
                <li class="nav-item">
                    <a href="{{ route('affiliate.finance.configuration.edit') }}"
                        class="nav-link {{ request()->routeIs('affiliate.finance.configuration.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-sliders-h"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.affiliate_configuration') }}">{{ __('navigation.menu.affiliate_configuration') }}</span></p>
                    </a>
                </li>
            @endcan
            @can('affiliate-withdrawal-list')
                <li class="nav-item">
                    <a href="{{ route('affiliate.withdrawals.admin.index') }}"
                        class="nav-link {{ request()->routeIs('affiliate.withdrawals.admin.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-money-bill-wave"></i>
                        <p>
                            <span class="nav-menu-label" title="{{ __('navigation.menu.affiliate_withdrawals') }}">{{ __('navigation.menu.affiliate_withdrawals') }}</span>
                            <span class="right badge badge-danger sidebar-menu-badge"
                                data-affiliate-withdrawal-badge hidden
                                aria-label="{{ __('navigation.status.no_withdrawal_requests') }}"></span>
                        </p>
                    </a>
                </li>
            @endcan
            @if ((bool) config('affiliate.features.administration', false) && $menu_can('affiliate-content-manage'))
                <li class="nav-item">
                    <a href="{{ route('affiliate-content-manage.index') }}"
                        class="nav-link {{ request()->routeIs('affiliate-content-manage.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-photo-video"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.affiliate_content') }}">{{ __('navigation.menu.affiliate_content') }}</span></p>
                    </a>
                </li>
            @endif
            @can('course-manage')
                <li class="nav-item">
                    <a href="{{ route('affiliate.index') }}" target="_blank" class="nav-link">
                        <i class="nav-icon fas fa-external-link-alt"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.affiliate_website') }}">{{ __('navigation.menu.affiliate_website') }}</span></p>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if ($show_user_menu)
    <li class="nav-item {{ $user_menu_active ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $user_menu_active ? 'active' : '' }}">
            <i class="nav-icon fas fa-users-cog"></i>
            <p><span class="nav-menu-label" title="{{ __('navigation.menu.user_access') }}">{{ __('navigation.menu.user_access') }}</span></p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            @can('user-list')
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.users') }}">{{ __('navigation.menu.users') }}</span></p>
                    </a>
                </li>
            @endcan
            @can('activity-log-list')
                <li class="nav-item">
                    <a href="{{ route('activity-logs.index') }}" class="nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-history"></i>
                        <p><span class="nav-menu-label" title="{{ __('activity-log.title') }}">{{ __('activity-log.title') }}</span></p>
                    </a>
                </li>
            @endcan
            @can('role-list')
                <li class="nav-item">
                    <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-tag"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.roles') }}">{{ __('navigation.menu.roles') }}</span></p>
                    </a>
                </li>
            @endcan
            @if ($menu_is_owner)
                <li class="nav-item">
                    <a href="{{ route('permission.index') }}"
                        class="nav-link {{ request()->routeIs('permission.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-key"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.permissions') }}">{{ __('navigation.menu.permissions') }}</span></p>
                    </a>
                </li>
            @endif
            @can('user-list')
                <li class="nav-item">
                    <a href="{{ route('generate.student') }}"
                        class="nav-link {{ $menu_route === 'generate.student' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-lock"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.generate_student_password') }}">{{ __('navigation.menu.generate_student_password') }}</span></p>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@can('user-list')
    <li class="nav-item">
        <a href="{{ route('feedback.index') }}"
            class="nav-link {{ request()->routeIs('feedback.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-comments"></i>
            <p><span class="nav-menu-label" title="{{ __('navigation.menu.feedbacks') }}">{{ __('navigation.menu.feedbacks') }}</span></p>
        </a>
    </li>
@endcan

@if ($show_centre_menu)
    <li class="nav-item {{ $centre_menu_active ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $centre_menu_active ? 'active' : '' }}">
            <i class="nav-icon fas fa-building"></i>
            <p><span class="nav-menu-label" title="{{ __('navigation.menu.centre_management') }}">{{ __('navigation.menu.centre_management') }}</span></p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            @can('centre-manage')
                <li class="nav-item">
                    <a href="{{ route('departments') }}"
                        class="nav-link {{ request()->routeIs('departments*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-school"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.centres') }}">{{ __('navigation.menu.centres') }}</span></p>
                    </a>
                </li>
            @endcan
            @can('department-list')
                <li class="nav-item">
                    <a href="{{ route('positions') }}"
                        class="nav-link {{ request()->routeIs('positions*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-sitemap"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.departments') }}">{{ __('navigation.menu.departments') }}</span></p>
                    </a>
                </li>
            @endcan
            @can('branch-transfer')
                <li class="nav-item">
                    <a href="{{ route('branch-transfers.index') }}"
                        class="nav-link {{ request()->routeIs('branch-transfers.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-exchange-alt"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.transfer_branch') }}">{{ __('navigation.menu.transfer_branch') }}</span></p>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif

@if ($show_system_menu)
    <li class="nav-item {{ $system_menu_active ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $system_menu_active ? 'active' : '' }}">
            <i class="nav-icon fas fa-cogs"></i>
            <p><span class="nav-menu-label" title="{{ __('navigation.menu.system_settings') }}">{{ __('navigation.menu.system_settings') }}</span></p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            @if ($menu_can('all-centre') && $menu_can('central-settings-manage'))
                <li class="nav-item">
                    <a href="{{ route('parameter') }}" class="nav-link {{ request()->routeIs('parameter*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-sliders-h"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.system_parameters') }}">{{ __('navigation.menu.system_parameters') }}</span></p>
                    </a>
                </li>
            @endif
            @can('integration-settings-manage')
                <li class="nav-item">
                    <a href="{{ route('line-api.index') }}"
                        class="nav-link {{ request()->routeIs('line-api.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-plug"></i>
                        <p><span class="nav-menu-label" title="{{ __('navigation.menu.line_api') }}">{{ __('navigation.menu.line_api') }}</span></p>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endif
