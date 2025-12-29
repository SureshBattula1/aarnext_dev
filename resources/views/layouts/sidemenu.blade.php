<link href="{{ asset('css/all.min.css')}}" rel="stylesheet">

<link href="{{ asset('css/bootstrap-icons.min.css') }}" rel="stylesheet">



@php
  $userRole = Auth::user()->role;
@endphp

<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <!-- Dashboard -->
    <li class="nav-item {{ Request::is('/') || Request::is('home') ? 'active' : '' }}">
      <a class="nav-link" href="{{ url('/') }}">
        <i class="ti-home menu-icon"></i>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>

    <!-- Customer -->
    <li class="nav-item">
      <a class="nav-link" href="{{ url('new-customer') }}">
        <i class="ti-write menu-icon"></i>
        <span class="menu-title">Customer</span>
      </a>
    </li>

    <!-- Show to all except role 5 -->
    @if ($userRole != 5)
      <li class="nav-item">
        <a class="nav-link" href="{{ url('items-list') }}">
          <i class="ti-layout menu-icon"></i>
          <span class="menu-title">Items</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="{{ url('quotations') }}">
          <i class="ti-layers-alt menu-icon"></i>
          <span class="menu-title">Quotation</span>
        </a>
      </li>
    @endif

    <!-- Sales Orders -->
    <li class="nav-item">
      <a class="nav-link" href="{{ url('sales') }}">
        <i class="ti-view-list menu-icon"></i>
        <span class="menu-title">Sales Orders</span>
      </a>
    </li>

    <!-- Show to all except role 5 -->
    @if ($userRole != 5)
      <li class="nav-item">
        <a class="nav-link" href="{{ url('Invoice-generation') }}">
          <i class="fas fa-cart-plus menu-icon"></i>
          <span class="menu-title">Invoice Generation</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="{{ url('credits') }}">
          <i class="ti-loop menu-icon"></i>
          <span class="menu-title">Credit Note</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="{{ url('coming-soon') }}">
          <i class="ti-notepad menu-icon"></i>
          <span class="menu-title">Expenses</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="{{ url('direct-payment-list') }}">
          <i class="ti-money menu-icon"></i>
          <span class="menu-title">Direct Payments</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="{{ url('inv-payments-list') }}">
          <i class="ti-money menu-icon"></i>
          <span class="menu-title">Invoice Payments</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="{{ url('rft-list') }}">
          <i class="ti-briefcase menu-icon"></i>
          <span class="menu-title">RFT</span>
        </a>
      </li>
    @endif
    <li class="nav-item">
      <a class="nav-link" href="{{ url('rft-planning') }}">
        <i class="ti-briefcase menu-icon"></i>
        <span class="menu-title">RFT Planning</span>
      </a>
    </li>
    
    <!-- Only for Admin (role 1) -->
    @if ($userRole == 1)
      <li class="nav-item">
        <a class="nav-link" href="{{ url('sales-employees-list') }}">
          <i class="ti-briefcase menu-icon"></i>
          <span class="menu-title">Sales Employees</span>
        </a>
      </li>
    @endif

    <!-- Reports -->
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#reportMenu" aria-expanded="false" aria-controls="reportMenu">
        <i class="ti-palette menu-icon"></i>
        <span class="menu-title">Reports</span>
        <i class="menu-arrow"></i>
      </a>

      <div class="collapse" id="reportMenu">
        <ul class="nav flex-column sub-menu">
            @if ($userRole != 5)
              <li class="nav-item"><a class="nav-link" href="{{ url('reports-quotation-list') }}">Quotation Reports</a></li>
            @endif
            <li class="nav-item"><a class="nav-link" href="{{ url('reports-sales-order-list') }}">Order Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('reports-order-items-list') }}">Order by Items Reports</a></li>

          @if ($userRole != 5)
          <li class="nav-item"><a class="nav-link" href="{{ url('reports-list') }}">Sales Reports</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ url('sales-items-reports') }}">Sales by Items Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('payments-reports-list') }}">Payments Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('expenses-reports-list') }}">Expenses Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('rft-reports-list') }}">RFT Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('rft-with-batches-reports') }}">RFT with Batches Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('inv-items-by-batches-list') }}">Invoice Batches Reports</a></li>
          @endif
        </ul>
      </div>
    </li>

    <!-- Admin Reports -->
    @if ($userRole == 1)
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#adminReportMenu" aria-expanded="false" aria-controls="adminReportMenu">
          <i class="ti-briefcase menu-icon"></i>
          <span class="menu-title">Admin Reports</span>
          <i class="menu-arrow"></i>
        </a>

        <div class="collapse" id="adminReportMenu">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"><a class="nav-link" href="{{ url('admin-reports-sales-order-list') }}">Order Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('admin-sales-reports-list') }}">Sales Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('admin-reports-payments-list') }}">Payments Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('admin-reports-items-list') }}">Items Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('admin-reports-rft-doc-list') }}">RFT Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('admin-reports-rft-list') }}">RFT Items Reports</a></li>
          </ul>
        </div>
      </li>
    @endif
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#approvalMenu" aria-expanded="false" aria-controls="approvalMenu">
          <i class="ti-briefcase menu-icon"></i>
          <span class="menu-title">Approvals</span>
          <i class="menu-arrow"></i>
        </a>
    
        <div class="collapse" id="approvalMenu">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"><a class="nav-link" href="{{ url('credit-note-approve') }}">Recredit Note</a></li>
          </ul>
        </div>
      </li>
    {{-- <li class="nav-item">
      <a class="nav-link" href="{{ url('credit-note-approve') }}">
        <i class="ti-briefcase menu-icon"></i>
        <span class="menu-title">Recredit Notes</span>
      </a>
    </li> --}}
    <!-- Logo -->
    <li style="margin:0 auto; position:fixed; padding:0px 0px 8px 40px; bottom:0;">
      <img src="{{ asset('images/360_logo1.png') }}" alt="logo" width="90" style="background:#ffffff; padding:4px 10px; border-radius:8px;" />
    </li>
  </ul>
  
</nav>
