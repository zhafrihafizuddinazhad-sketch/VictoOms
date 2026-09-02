<aside class="main-sidebar sidebar-dark-primary elevation-4">

    {{-- ================================================= --}}
    {{-- BRAND --}}
    {{-- ================================================= --}}

    @php

        $user = auth()->user();

    @endphp


    {{-- OWNER BRAND --}}

    @if($user->hasRole('owner'))

        <a
            href="{{ route('owner.dashboard') }}"
            class="brand-link"
        >

            <span class="brand-text font-weight-light">

                Victo OMS

            </span>

        </a>

    @endif


    {{-- ADMIN BRAND --}}

    @if($user->hasRole('admin'))

        <a
            href="{{ route('admin.dashboard') }}"
            class="brand-link"
        >

            <span class="brand-text font-weight-light">

                Victo OMS

            </span>

        </a>

    @endif


    {{-- DESIGNER BRAND --}}

    @if($user->hasRole('designer'))

        <a
            href="{{ route('designer.dashboard') }}"
            class="brand-link"
        >

            <span class="brand-text font-weight-light">

                Victo OMS

            </span>

        </a>

    @endif


    {{-- CAMERAMAN BRAND --}}

    @if($user->hasRole('cameraman'))

        <a
            href="{{ route('cameraman.dashboard') }}"
            class="brand-link"
        >

            <span class="brand-text font-weight-light">

                Victo OMS

            </span>

        </a>

    @endif


    <div class="sidebar">

        <nav class="mt-3">

            <ul
                class="nav nav-pills nav-sidebar flex-column"
                data-lte-toggle="treeview"
            >


                {{-- ================================================= --}}
                {{-- OWNER SIDEBAR --}}
                {{-- ================================================= --}}

                @if($user->hasRole('owner'))


                    {{-- Dashboard --}}

                    <li class="nav-item">

                        <a
                            href="{{ route('owner.dashboard') }}"
                            class="nav-link
                            {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}"
                        >

                            <i class="nav-icon fas fa-home"></i>

                            <p>

                                Dashboard

                            </p>

                        </a>

                    </li>


                    {{-- Customers --}}

                    <li class="nav-item">

                        <a
                            href="{{ route('customers.index') }}"
                            class="nav-link
                            {{ request()->routeIs('customers.*') ? 'active' : '' }}"
                        >

                            <i class="nav-icon fas fa-users"></i>

                            <p>

                                Customers

                            </p>

                        </a>

                    </li>


                    {{-- Orders --}}

                    <li class="nav-item">

                        <a
                            href="{{ route('orders.index') }}"
                            class="nav-link
                            {{ request()->routeIs('orders.*') ? 'active' : '' }}"
                        >

                            <i class="nav-icon fas fa-shopping-cart"></i>

                            <p>

                                Orders

                            </p>

                        </a>

                    </li>



                    
                    {{-- Designer Monitoring --}}

                    <li class="nav-item">

                        <a
                            href="{{ route('designer.monitoring') }}"
                            class="nav-link
                            {{ request()->routeIs('designer.monitoring') ? 'active' : '' }}"
                        >

                            <i class="nav-icon fas fa-user-pen"></i>

                            <p>

                                Designer Monitoring

                            </p>

                        </a>

                    </li>


                    {{-- Cameraman Monitoring --}}

                    <li class="nav-item">

                        <a
                            href="{{ route('owner.cameramen.index') }}"
                            class="nav-link
                            {{ request()->routeIs('owner.cameramen.*') ? 'active' : '' }}"
                        >

                            <i class="nav-icon fas fa-camera"></i>

                            <p>

                                Cameraman Monitoring

                            </p>

                        </a>

                    </li>

                    <li class="nav-item">
    <a href="{{ route('owner.reports') }}"
       class="nav-link {{ request()->routeIs('owner.reports') ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-bar"></i>
        <p>Reports</p>
    </a>
</li>


                @endif


                {{-- ================================================= --}}
                {{-- ADMIN SIDEBAR --}}
                {{-- ================================================= --}}

                @if($user->hasRole('admin'))


                    {{-- ========================= --}}
                    {{-- DASHBOARD --}}
                    {{-- ========================= --}}

                    <li class="nav-item">

                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="nav-link
                            {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        >

                            <i class="nav-icon fas fa-tachometer-alt"></i>

                            <p>

                                Dashboard

                            </p>

                        </a>

                    </li>


                    {{-- ========================= --}}
                    {{-- ORDERS --}}
                    {{-- ========================= --}}

                    <li class="nav-header">

                        ORDERS

                    </li>


                    {{-- Order Management --}}

                    <li class="nav-item">

                        <a
                            href="{{ route('admin.orders.index') }}"
                            class="nav-link
                            {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                        >

                            <i class="nav-icon fas fa-shopping-cart"></i>

                            <p>

                                Order Management

                            </p>

                        </a>

                    </li>


                    {{-- Create Order --}}

                    <li class="nav-item">

                        <a
                            href="{{ route('admin.orders.create') }}"
                            class="nav-link
                            {{ request()->routeIs('admin.orders.create') ? 'active' : '' }}"
                        >

                            <i class="nav-icon fas fa-plus-circle"></i>

                            <p>

                                Create Order

                            </p>

                        </a>

                    </li>


                    {{-- ========================= --}}
                    {{-- CUSTOMERS --}}
                    {{-- ========================= --}}

                    <li class="nav-header">

                        CUSTOMERS

                    </li>


                    <li class="nav-item">

                        <a
                            href="{{ route('admin.customers.index') }}"
                            class="nav-link
                            {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"
                        >

                            <i class="nav-icon fas fa-users"></i>

                            <p>

                                Customer Management

                            </p>

                        </a>

                    </li>


                    {{-- ========================= --}}
                    {{-- MONITORING --}}
                    {{-- ========================= --}}

                    <li class="nav-header">

                        MONITORING

                    </li>


                    {{-- Designer Monitoring --}}

                    <li class="nav-item">

                        <a
                            href="{{ route('designer.monitoring') }}"
                            class="nav-link
                            {{ request()->routeIs('designer.monitoring') ? 'active' : '' }}"
                        >

                            <i class="nav-icon fas fa-user-pen"></i>

                            <p>

                                Designer Monitoring

                            </p>

                        </a>

                    </li>


                    {{-- Cameraman Monitoring --}}

                    <li class="nav-item">

                        <a
                            href="{{ route('admin.cameramen.index') }}"
                            class="nav-link
                            {{ request()->routeIs('admin.cameramen.*') ? 'active' : '' }}"
                        >

                            <i class="nav-icon fas fa-camera"></i>

                            <p>

                                Cameraman Monitoring

                            </p>

                        </a>

                    </li>


                @endif


                {{-- ================================================= --}}
                {{-- DESIGNER SIDEBAR --}}
                {{-- ================================================= --}}

                @if($user->hasRole('designer'))


                    {{-- Dashboard --}}

                    <li class="nav-item">

                        <a
                            href="{{ route('designer.dashboard') }}"
                            class="nav-link
                            {{ request()->routeIs('designer.dashboard') ? 'active' : '' }}"
                        >

                            <i class="nav-icon fas fa-home"></i>

                            <p>

                                Dashboard

                            </p>

                        </a>

                    </li>


                    {{-- My Tasks --}}

                    <li class="nav-item">

                        <a
                            href="{{ route('designer.task') }}"
                            class="nav-link
                            {{ request()->routeIs('designer.task*') ? 'active' : '' }}"
                        >

                            <i class="nav-icon fas fa-tasks"></i>

                            <p>

                                My Tasks

                            </p>

                        </a>

                    </li>


                @endif


                {{-- ================================================= --}}
                {{-- CAMERAMAN SIDEBAR --}}
                {{-- ================================================= --}}

                @if($user->hasRole('cameraman'))


                    {{-- Dashboard --}}

                    <li class="nav-item">

                        <a
                            href="{{ route('cameraman.dashboard') }}"
                            class="nav-link
                            {{ request()->routeIs('cameraman.dashboard') ? 'active' : '' }}"
                        >

                            <i class="nav-icon fas fa-home"></i>

                            <p>

                                Dashboard

                            </p>

                        </a>

                    </li>


                    {{-- Photo Tasks --}}

                    <li class="nav-item">

                        <a
                            href="{{ route('cameraman.tasks') }}"
                            class="nav-link
                            {{ request()->routeIs('cameraman.tasks') ? 'active' : '' }}"
                        >

                            <i class="nav-icon fas fa-camera"></i>

                            <p>

                                Photo Tasks

                            </p>

                        </a>

                    </li>


                @endif


            </ul>

        </nav>

    </div>

</aside>