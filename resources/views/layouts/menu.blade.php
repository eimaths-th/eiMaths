<!-- need to remove -->
<li class="nav-item">
    <a href="{{ route('home') }}" class="nav-link {{ Request::is('home') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Dashboard</p>
    </a>
</li>
{{--  --}}
@can('student-list')
    <li class="nav-item {{ in_array(Request::route()->getName(), ['contacts', 'discontinued']) ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ in_array(Request::route()->getName(), ['contacts', 'discontinued']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-graduation-cap"></i>
            <p>Students Management</p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('contacts') }}" class="nav-link {{ Request::is('contacts') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Students List</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('demo.list') }}" class="nav-link {{ Request::is('demo.list') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Demo Students List</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('discontinued') }}" class="nav-link {{ Request::is('discontinued') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Discontinued Student</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('contacts.graduated') }}"
                    class="nav-link {{ Request::is('graduated') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Graduated Student</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('histories.index') }}" class="nav-link {{ Request::is('histories') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Study Histories</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('parent.index') }}" class="nav-link {{ Request::is('parent') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Parent Manage</p>
                </a>
            </li>
        </ul>
    </li>
@endcan
@can('order-list')
    <!--<li class="nav-item {{ in_array(Request::route()->getName(), ['orders']) ? 'menu-open' : '' }}">
                                                                <a href="#" class="nav-link {{ in_array(Request::route()->getName(), ['orders']) ? 'active' : '' }}">
                                                                    <i class="nav-icon fas fa-cart-shopping"></i>
                                                                    <p>Orders Management</p>
                                                                    <i class="fas fa-angle-left right"></i>
                                                                </a>
                                                                <ul class="nav nav-treeview">

                                                                    <li class="nav-item">
                                                                        {{-- <a href="{{ route('orders', ['status' => '1']) }}" --}}
                                                                        <a href="{{ route('orders') }}" {{-- class="nav-link {{ Request::is('orders') && Request::input('status') === '1' ? 'active' : '' }}"> --}}
                                                                            class="nav-link {{ Request::is('orders') ? 'active' : '' }}">
                                                                            <i class="nav-icon fa-solid fa-comment-dots"></i>
                                                                            <p>New Student Orders</p>
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </li>-->
@endcan
@can('invoice-list')
    <!--<li class="nav-item {{ in_array(Request::route()->getName(), ['invoices', 'invoices_pending']) ? 'menu-open' : '' }}">
                                                                <a href="#"
                                                                    class="nav-link {{ in_array(Request::route()->getName(), ['invoices', 'invoices_pending']) ? 'active' : '' }}">
                                                                    <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                                                    <p>Invoice Management</p>
                                                                    <i class="fas fa-angle-left right"></i>
                                                                </a>
                                                                <ul class="nav nav-treeview">
                                                                    <li class="nav-item">
                                                                        <a href="{{ route('invoices_pending') }}"
                                                                            class="nav-link {{ Request::is('invoices_pending') ? 'active' : '' }}">
                                                                            <i class="nav-icon fa-solid fa-comment-dots"></i>
                                                                            <p>Generate Invoice</p>
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item">
                                                                        <a href="{{ route('invoices') }}" class="nav-link {{ Request::is('invoices') ? 'active' : '' }}">
                                                                            <i class="nav-icon fa-solid fa-comment-dots"></i>
                                                                            <p>View Invoice</p>
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </li>-->
@endcan
@can('receipt-list')
    <li
        class="nav-item {{ in_array(Request::route()->getName(), [
            'receipts',
            'receipts_pending',
            'courses.pending.index',
            'commission-list.index',
            'config-withdraw.index',
        ])
            ? 'menu-open'
            : '' }}">
        <a href="#"
            class="nav-link {{ in_array(Request::route()->getName(), [
                'receipts',
                'receipts_pending',
                'courses.pending.index',
                'commission-list.index',
                'config-withdraw.index',
            ])
                ? 'active'
                : '' }}">
            <i class="nav-icon fas fa-sack-dollar"></i>
            <p>Receipt Management</p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('courses.pending.index') }}"
                    class="nav-link {{ Request::is('courses.pending.index') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Courses Pennding</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('config-withdraw.index') }}"
                    class="nav-link {{ Request::is('config-withdraw.index') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Config Withdraw</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('commission-list.index') }}"
                    class="nav-link {{ Request::is('commission-list.index') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Commission List</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('receipts_pending') }}"
                    class="nav-link {{ Request::is('receipts_pending') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Pending Receipt</p>
                </a>
            </li>
            {{-- <li class="nav-item">
                <a href="{{ route('receipts_pending_term') }}"
                    class="nav-link {{ Request::is('receipts_pending_term') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Term Payment Update</p>
                </a>
            </li> --}}
            <li class="nav-item">
                <a href="{{ route('receipts', ['type' => 0]) }}"
                    class="nav-link {{ Route::currentRouteName() == 'receipts' && request('type') == 0 ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Payment Receipt</p>
                </a>
            </li>
            {{--  <li class="nav-item">
                <a href="{{ route('receipts', ['type' => 1]) }}"
                    class="nav-link {{ Route::currentRouteName() == 'receipts' && request('type') == 1 ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Term Student Receipt</p>
                </a>
            </li> --}}
        </ul>
    </li>
@endcan
@can('product-list')
    <li class="nav-item {{ in_array(Request::route()->getName(), ['products']) ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ in_array(Request::route()->getName(), ['products']) ? 'active' : '' }}">
            <i class="nav-icon fa-solid fa-cubes"></i>
            <p>Stock Management</p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('products') }}" class="nav-link {{ Request::is('products') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p> Education Acceseries</p>
                </a>
            </li>
        </ul>
    </li>
@endcan
@can('centre-manage')
    <li
        class="nav-item {{ in_array(Request::route()->getName(), ['departments', 'positions', 'termfee', 'parameter']) ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ in_array(Request::route()->getName(), ['departments', 'positions', 'termfee', 'parameter']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-gear"></i>
            <p>Centre Management</p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('departments') }}" class="nav-link {{ Request::is('departments') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Centre</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('positions') }}" class="nav-link {{ Request::is('positions') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Department</p>
                </a>
            </li>
            @can('all-centre')
                <li class="nav-item">
                    <a href="{{ route('termfee') }}" class="nav-link {{ Request::is('termfee') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-comment-dots"></i>
                        <p>Term Fee & Book Price </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('parameter') }}" class="nav-link {{ Request::is('parameter') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-comment-dots"></i>
                        <p>Parameter Setting </p>
                    </a>
                </li>
            @endcan
            {{-- <li class="nav-item" >
            <a href="{{ route('persons') }}" class="nav-link {{ Request::is('persons') ? 'active' : '' }}">
                <i class="fas fa-user-tie nav-icon"></i>
                <p>บุคคล</p>
            </a>
        </li> --}}
        </ul>
    </li>
@endcan


@can('course-manage')
    <li
        class="nav-item {{ in_array(Request::route()->getName(), ['categories.index', 'course.list', 'course.level.list']) ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ in_array(Request::route()->getName(), ['categories.index', 'course.list', 'course.level.list']) ? 'active' : '' }}">
            {{-- <i class="nav-icon fas fa-user-gear"></i> --}}
            <i class="nav-icon fas fa-book"></i> <!-- good -->
            {{-- <i class="nav-icon fas fa-chalkboard-teacher"></i> --}}
            {{-- <i class="nav-icon fas fa-graduation-cap"></i> --}}
            {{-- <i class="nav-icon fas fa-chalkboard-teacher"></i> --}}
            <p>Course Manage</p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            {{-- @can('user-list') --}}
            <li class="nav-item">
                <a href="{{ route('categories.index') }}"
                    class="nav-link {{ Request::is('categories.index') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Category List</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('course.list') }}" class="nav-link {{ Request::is('course.list') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>All Courses</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('course.level.list') }}"
                    class="nav-link {{ Request::is('course.level.list') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Course level</p>
                </a>
            </li>
            {{-- @endcan
            @can('role-list') --}}

            {{-- @endcan --}}
        </ul>
    </li>
@endcan
@can('user-list')
    <li
        class="nav-item {{ in_array(Request::route()->getName(), ['users.index', 'roles.index', 'generate.system', 'generate.student', 'permission.index']) ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ in_array(Request::route()->getName(), ['users.index', 'roles.index', 'generate.system', 'generate.student', 'permission.index']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-gear"></i>
            <p>User Management</p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            @can('user-list')
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ Request::is('users.index') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-comment-dots"></i>
                        <p>Users</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('users.affiliate') }}"
                        class="nav-link {{ Request::is('users.index') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-comment-dots"></i>
                        <p>Users Affiliate</p>
                    </a>
                </li>
            @endcan
            @can('role-list')
                <li class="nav-item">
                    <a href="{{ route('roles.index') }}" class="nav-link {{ Request::is('roles.index') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-comment-dots"></i>
                        <p>Roles</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('permission.index') }}"
                        class="nav-link {{ Request::is('permission.index') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-comment-dots"></i>
                        <p>Premissions</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('line-api.index') }}"
                        class="nav-link {{ Request::is('line-api.index') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-comment-dots"></i>
                        <p>line-api</p>
                    </a>
                </li>
            @endcan
            <li
                class="nav-item {{ in_array(Request::route()->getName(), ['users.index', 'roles.index', 'generate.system', 'generate.student', 'permission.index']) ? 'menu-open' : '' }}">
                <a href="#"
                    class="nav-link {{ in_array(Request::route()->getName(), ['users.index', 'roles.index', 'generate.system', 'generate.student', 'permission.index']) ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-gear"></i>
                    <p>Generate Passworld</p>
                    <i class="fas fa-angle-left right"></i>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('generate.system') }}"
                            class="nav-link {{ Request::is('generate.system') ? 'active' : '' }}">
                            <i class="nav-icon fa-solid fa-comment-dots"></i>
                            <p>System</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('generate.student') }}"
                            class="nav-link {{ Request::is('generate.student') ? 'active' : '' }}">
                            <i class="nav-icon fa-solid fa-comment-dots"></i>
                            <p>Student</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('student.login') }}"
                            class="nav-link {{ Request::is('student.login') ? 'active' : '' }}">
                            <i class="nav-icon fa-solid fa-comment-dots"></i>
                            <p>Student Login</p>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </li>
    <li
        class="nav-item {{ in_array(Request::route()->getName(), ['affiliate.index', 'affiliate-configurations.index', 'affiliate.courses']) ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ in_array(Request::route()->getName(), ['affiliate.index', 'affiliate-configurations.index', 'affiliate.courses']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-gear"></i>
            <p>Affiliate Management</p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">

            <li class="nav-item">
                <a href="{{ route('affiliate.index') }}" target="_blank"
                    class="nav-link {{ Request::is('affiliate.index') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Affiliate Fontend</p>
                </a>
            </li>
            @can('role-list')
                <li class="nav-item">
                    <a href="{{ route('affiliate-configurations.index') }}"
                        class="nav-link {{ Request::is('affiliate-configurations.index') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-comment-dots"></i>
                        <p>Commission Config</p>
                    </a>
                </li>
            @endcan
            <li class="nav-item">
                <a href="{{ route('affiliate.courses') }}" target="_blank"
                    class="nav-link {{ Request::is('affiliate.courses') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Course Fontend</p>
                </a>
            </li>
        </ul>
    </li>
    <li class="nav-item {{ in_array(Request::route()->getName(), ['feedback.index']) ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ in_array(Request::route()->getName(), ['feedback.index']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-gear"></i>
            <p>Feedbacks Management</p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">

            <li class="nav-item">
                <a href="{{ route('feedback.index') }}"
                    class="nav-link {{ Request::is('affiliate.index') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Feedbacks </p>
                </a>
            </li>

        </ul>
    </li>
    {{-- <li
        class="nav-item {{ in_array(Request::route()->getName(), ['users.index', 'roles.index', 'generate', 'permission.index']) ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ in_array(Request::route()->getName(), ['users.index', 'roles.index', 'generate', 'permission.index']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-gear"></i>
            <p>User Management</p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            @can('user-list')
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ Request::is('users.index') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-comment-dots"></i>
                        <p>Users</p>
                    </a>
                </li>
            @endcan
            @can('role-list')
                <li class="nav-item">
                    <a href="{{ route('roles.index') }}" class="nav-link {{ Request::is('roles.index') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-comment-dots"></i>
                        <p>Roles</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('permission.index') }}"
                        class="nav-link {{ Request::is('permission.index') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-comment-dots"></i>
                        <p>Permissions</p>
                    </a>
                </li>
            @endcan
            <li
                class="nav-item {{ in_array(Request::route()->getName(), ['generate.system', 'generate.student']) ? 'menu-open' : '' }}">
                <a href="#"
                    class="nav-link {{ in_array(Request::route()->getName(), ['generate.system', 'generate.student']) ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-gear"></i>
                    <p>Generate Password</p>
                    <i class="fas fa-angle-left right"></i>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('generate.system') }}"
                            class="nav-link {{ Request::is('generate.system') ? 'active' : '' }}">

                            <i class="nav-icon fa-solid fa-comment-dots"></i>
                            <p>System</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('generate.student') }}"
                            class="nav-link {{ Request::is('/generate-password/student') ? 'active' : '' }}">
                            <i class="nav-icon fa-solid fa-comment-dots"></i>
                            <p>Student</p>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </li> --}}

@endcan
@can('quiz-manage')
    <li
        class="nav-item {{ in_array(Request::route()->getName(), ['questions', 'quizzes', 'questions-quizzes.create']) ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ in_array(Request::route()->getName(), ['questions', 'quizzes', 'questions-quizzes.create']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-gear"></i>
            <p>Quiz Management</p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            {{-- @can('user-list') --}}
            <li class="nav-item">
                <a href="{{ route('home3') }}" target="_black"
                    class="nav-link {{ Request::is('home3') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Home</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('questions') }}" class="nav-link {{ Request::is('questions') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Questions</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('quizzes') }}" class="nav-link {{ Request::is('quizzes') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Quizzes</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('questions-quizzes.create') }}"
                    class="nav-link {{ Request::is('questions-quizzes.create') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Questions&Quizzes</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('tests') }}" class="nav-link {{ Request::is('permission.index') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Tests</p>
                </a>
            </li>


            {{-- @endcan
            @can('role-list') --}}

            {{-- @endcan --}}
        </ul>
    </li>
@endcan
@php
    // dd(Auth::user()->id);
@endphp
@if (Auth::user()->id == 6)
    <li
        class="nav-item {{ in_array(Request::route()->getName(), ['ts.questions.index', 'ts.questions.create', 'ts.questions.view']) ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ in_array(Request::route()->getName(), ['ts.questions.index', 'ts.questions.create', 'ts.questions.view']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-gear"></i>
            <p>TestSystem Questions</p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            {{-- @can('user-list') --}}
            <li class="nav-item">
                <a href="{{ route('ts.questions.index') }}" target="_black"
                    class="nav-link {{ Request::is('ts.questions.index') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>ts.questions.index</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('ts.questions.create') }}"
                    class="nav-link {{ Request::is('ts.questions.create') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>ts.questions.create</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('ts.questions.view') }}"
                    class="nav-link {{ Request::is('ts.questions.view') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>ts.questions.view</p>
                </a>
            </li>
        </ul>
    </li>
@else
@endif
