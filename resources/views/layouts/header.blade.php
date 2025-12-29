<nav class="navbar col-lg-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        @if(isset($ware_house_image))
        <a class="navbar-brand brand-logo" href="#">
            <img src="{{ asset($ware_house_image->store_logo) }}" class="me-2" alt="logo"/>
        </a>
    @else
        <a class="navbar-brand brand-logo" href="#">
            <img src="{{ asset($ware_house_image->store_logo) }}" class="me-2" alt="default logo"/>
        </a>
    @endif
          <a class="navbar-brand brand-logo-mini" href="#"><img src="{{ asset('images/arrnext-logo.png') }}" alt="logo"/></a>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
      <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
        <span class="ti-layout-grid2"></span>
      </button>
      <ul class="navbar-nav me-lg-2">
        <li class="nav-item nav-search d-none d-lg-block">

        </li>



      </ul>
      <div style="margin:0 auto;"><img src="{{ asset('images/SAP_BOne2.png') }}" class="me-2" alt="logo" width="120" /></div>
      <div style="font-weight:bold;">
            <?php if (Auth::check()) { echo Auth::user()->ware_house; }  ?>
      </div>

      @if(Auth::user()->role == 1 || Auth::user()->role == 5)
          <form action="{{ route('updateWarehouse') }}" method="POST">
            @csrf
            <div class="dropdown mx-3">
                <select id="warehouse-dropdown" class="form-select" name="warehouse" onchange="this.form.submit()">
                    <option value="" disabled>Select Warehouse</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->whs_code }}"
                            @if (Auth::check() && Auth::user()->ware_house == $warehouse->whs_code) selected @endif>
                            {{ $warehouse->whs_code }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
      @endif

      <ul class="navbar-nav navbar-nav-right">
        <li class="nav-item nav-profile dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" id="profileDropdown">
            <img src="{{ asset('images/people.png') }}" alt="profile" style="width:30px !important; height:30px !important;"/>
          </a>
          <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
            <a class="dropdown-item">
              <i class="ti-user text-primary"></i>
              <?php if (Auth::check()) { echo Auth::user()->name; }  ?>
            </a>
            @php
              $userRole = Auth::user()->name;
            @endphp
            @if($userRole == 'ACH-OFF4' || $userRole == 'ADMIN')
            <a class="dropdown-item" href="{{ route('changePasswordGet') }}">
              <i class="ti-settings text-primary"></i>
              Change Password
            </a>
            @endif
            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();">
              <i class="ti-power-off text-primary"></i>
              Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">{{ csrf_field() }}</form>

          </div>
        </li>
      </ul>
      <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
        <span class="ti-layout-grid2"></span>
      </button>
    </div>

  </nav>

  <script>
    function handleDropdownChange() {
        const selectedValue = document.getElementById('warehouse-dropdown').value;
        alert('Selected Warehouse: ' + selectedValue);
    }
</script>

 <script type="text/javascript" src="{{asset('js/jquery-3.4.1.min.js')}}"></script>
 <script src="{{asset('vendors/js/vendor.bundle.base.js')}}"></script>
<script src="{{asset('js/jquery.validate.min.js')}}"></script>
 <script src="{{asset('js/plugins/jquery.dataTables.min.js')}}"></script>
 <script src="{{asset('js/plugins/dataTables.tableTools.js')}}"></script>
 <script src="{{asset('js/plugins/dataTables.editor.min.js')}}"></script>
 <script src="{{asset('js/plugins/dataTables.buttons.min.js')}}"></script>
 <script src="{{asset('js/plugins/dataTables.colReorder.min.js')}}"></script>
 <script src="{{asset('js/plugins/jszip.min.js')}}"></script>
 <script src="{{asset('js/plugins/buttons.html5.min.js')}}"></script>
<script src="{{asset('js/plugins/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('js/plugins/dataTables.bootstrap.min.js')}}"></script>
 <script src="{{asset('js/dt/fnPagingInfo.js')}}?r=<?php echo time();?>"></script>
