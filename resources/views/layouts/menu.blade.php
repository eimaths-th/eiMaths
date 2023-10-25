<!-- need to remove -->
<li class="nav-item">
    <a href="{{ route('home') }}" class="nav-link {{ Request::is('home') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Dashboard</p>
    </a>
</li>
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
                <a href="{{ route('discontinued') }}" class="nav-link {{ Request::is('discontinued') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Discontinued Student</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('contacts.graduated') }}" class="nav-link {{ Request::is('contacts') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Graduated Student</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('histories.index') }}" class="nav-link {{ Request::is('contacts') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>Histories</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('parent.index') }}" class="nav-link {{ Request::is('contacts') ? 'active' : '' }}">
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
    <li class="nav-item {{ in_array(Request::route()->getName(), ['receipts', 'receipts_pending']) ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ in_array(Request::route()->getName(), ['receipts', 'receipts_pending']) ? 'active' : '' }}">
            <i class="nav-icon fas fa-sack-dollar"></i>
            <p>Receipt Management</p>
            <i class="fas fa-angle-left right"></i>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('receipts_pending') }}"
                    class="nav-link {{ Request::is('receipts_pending') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-comment-dots"></i>
                    <p>New Student Pending</p>
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
                    <p>New Student Receipt</p>
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
            {{-- <li class="nav-item" >
            <a href="{{ route('persons') }}" class="nav-link {{ Request::is('persons') ? 'active' : '' }}">
                <i class="fas fa-user-tie nav-icon"></i>
                <p>บุคคล</p>
            </a>
        </li> --}}
        </ul>
    </li>
@endcan
@can('user-list')
    <li class="nav-item {{ in_array(Request::route()->getName(), ['users.index', 'roles.index']) ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ in_array(Request::route()->getName(), ['users.index', 'roles.index']) ? 'active' : '' }}">
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
            @endcan
        </ul>
    </li>
@endcan
