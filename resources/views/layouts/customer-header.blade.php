<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>AARNEXT - Customer Portal</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/select2/select2.min.css') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" />

    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
            background-color: #f9fafc;
            color: #333;
            line-height: 1.6;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100dvh;
        }
        .form-group {
            margin-bottom: 2px !important;
        }
        /* HEADER */
        .customer-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 99999 !important;
        }

        .customer-header .navbar-brand img {
            height: 38px;
            width: auto !important;
        }

        /* TOP NAV */
        .customer-nav {
            background-color: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            overflow: visible !important;
            position: sticky;
            top: 54px;
            z-index: 99998 !important;
        }

        .customer-nav .nav-link {
            color: #4b5563;
            padding: .4rem 1.2rem;
            border-radius: .375rem;
            transition: all .2s;
            white-space: nowrap;
        }

        .customer-nav .nav-link:hover {
            background: #e5e7eb;
            color: #111827;
        }

        .customer-nav .nav-link.active {
            background: #2563eb;
            color: #fff !important;
        }

        /* DROPDOWN FIX */
        .customer-nav .dropdown-menu,
        .customer-header .dropdown-menu {
            position: absolute !important;
            z-index: 999999999 !important;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: .375rem;
            box-shadow: 0 6px 15px rgba(0, 0, 0, .12);
            margin-top: .35rem;
            min-width: 190px;
            overflow: visible !important;
        }

        /* Show dropdown on hover or click */
        .customer-nav .dropdown:hover .dropdown-menu {
            display: block;
        }

        .customer-nav .dropdown-item {
            padding: .55rem 1rem;
            font-size: .875rem;
            color: #4b5563;
        }

        .customer-nav .dropdown-item:hover {
            background: #eef2ff;
            color: #111827;
        }

        /* CONTENT */
        .customer-content {
            flex: 1;
            padding: 12px 0;
            overflow: visible !important; 
            z-index: 1 !important;
        }

        .customer-content .container-fluid {
            overflow: visible !important;
        }

        html, body {
            height: 100%;
        }

        .container-scroller {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        footer {
            background: #fff;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            padding: 1rem 0;
            margin-top: auto;
        }

        @media(max-width:768px) {
            .customer-header .navbar-brand span {
                display: none;
            }
        }

        /* === NEW FIX for right alignment === */
        #profileDropdown + .dropdown-menu {
            right: 0 !important;
            left: auto !important;
            transform: translateX(0) !important;
            margin-right: 10px !important;
        }
    </style>
</head>

<body>
    <div class="container-scroller" style="overflow:visible !important;">
        <!-- ===== Header ===== -->
        <nav class="navbar customer-header">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div class="navbar-brand d-flex align-items-center">
                    <img src="{{ asset('images/arrnext-logo.png') }}" alt="AARNEXT Logo">
                    <span class="ms-2">Customer Portal</span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="me-3 text-secondary fw-medium">
                        {{-- Welcome, {{ Auth::guard('customer')->user()->card_name }} --}}
                         Store  - {{ Auth::guard('customer')->user()->store_location }}
                    </span>

                    <ul class="navbar-nav navbar-nav-right">
                        <li class="nav-item nav-profile dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"
                                id="profileDropdown">
                                <img src="{{ asset('images/people.png') }}" alt="profile"
                                    style="width:30px !important; height:30px !important;" />
                            </a>
                            <div class="dropdown-menu dropdown-menu-right navbar-dropdown"
                                aria-labelledby="profileDropdown">
                                <a class="dropdown-item">
                                    <i class="ti-user text-primary"></i>
                                    <?php if (Auth::check()) { echo Auth::user()->name; } ?>
                                </a>
                                <a class="dropdown-item" href="{{ route('changePasswordGet') }}">
                                    <i class="ti-settings text-primary"></i>
                                    Change Password
                                </a>
                                <a class="dropdown-item" href="{{ route('customer.logout') }}"
                                    onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                                    <i class="ti-power-off text-primary"></i>
                                    Logout
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">{{ csrf_field() }}</form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- ===== Navigation ===== -->
        <nav class="customer-nav">
            <div class="container-fluid">
                <ul class="nav nav-pills py-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}"
                            href="{{ route('customer.dashboard') }}">
                            <i class="ti-dashboard"></i> Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customer.orders') ? 'active' : '' }}"
                            href="{{ route('customer.orders') }}">
                            <i class="ti-shopping-cart"></i> Orders
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('customer.order.reports', 'customer.invoice.reports') ? 'active' : '' }}"
                            href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="ti-receipt"></i> Reports
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
                            <li><a class="dropdown-item {{ request()->routeIs('customer.order.reports') ? 'active' : '' }}"
                                    href="{{ route('customer.order.reports') }}">
                                    <i class="ti-shopping-cart"></i> Order Reports
                                </a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('customer.invoice.reports') ? 'active' : '' }}"
                                    href="{{ route('customer.invoice.reports') }}">
                                    <i class="ti-receipt"></i> Invoice Reports
                                </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- ===== Main Content ===== -->
        <div class="customer-content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>

        <!-- ===== Footer ===== -->
        <footer class="text-center py-3">
            <div class="container">
                <span>© {{ date('Y') }} AARNEXT. All rights reserved.</span>
            </div>
        </footer>
    </div>

    <!-- ===== Scripts ===== -->
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>
    <script src="{{ asset('vendors/select2/select2.min.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/template.js') }}"></script>
    @stack('scripts')
</body>

</html>
