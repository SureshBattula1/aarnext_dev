@extends('layouts.app')
@section('content')
<style>
    #view-more, #view-less , #cview-more, #cview-less, #scview-more, #scview-less {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
            float: right;
    }
    .dataTables_wrapper .dataTables_paginate {
        display: none;
    }
    .table td {
        padding: .5rem;
    }
</style>
<div class="content-wrapper dashboard-page">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="row">
          <div class="col-12 col-xl-5 mb-4 mb-xl-0">
            <h4 class="fw-bold">Hi, Welcomeback!</h4>
            <h4 class="fw-normal mb-0">Fortuneart Dashboard,</h4>
          </div>
          {{-- <div class="col-12 col-xl-7">
            <div class="d-flex align-items-center justify-content-between flex-wrap">
              <div class="border-right pe-4 mb-3 mb-xl-0">
                <p class="text-muted">Balance</p>
                <h4 class="mb-0 fw-bold">$40079.60 M</h4>
              </div>
              <div class="border-right pe-4 mb-3 mb-xl-0">
                <p class="text-muted">Today’s profit</p>
                <h4 class="mb-0 fw-bold">$175.00 M</h4>
              </div>
              <div class="border-right pe-4 mb-3 mb-xl-0">
                <p class="text-muted">Purchases</p>
                <h4 class="mb-0 fw-bold">4006</h4>
              </div>
              <div class="pe-3 mb-3 mb-xl-0">
                <p class="text-muted">Downloads</p>
                <h4 class="mb-0 fw-bold">4006</h4>
              </div>
              <div class="mb-3 mb-xl-0">
                <button class="btn btn-warning rounded-0 text-white">Downloads</button>
              </div>
            </div>
          </div> --}}
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <p class="card-title text-md-center text-xl-left">No. of InActive Clients</p>
            <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                <a onclick="setCustomerStatus('inactive')" class="hyperlink-nostyle" href="{{ url('sap-webid-customers')}}"><h3 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0">{{ $total_inactive_customers }}</h3></a>
                <i class="ti-user icon-md text-danger mb-0 mb-md-3 mb-xl-0"></i>
            </div>
            {{-- <p class="mb-0 mt-2 text-warning">2.00% <span class="text-black ms-1"><small>(30 days)</small></span></p> --}}
          </div>
        </div>
      </div>
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <p class="card-title text-md-center text-xl-left">No. of Active Clients</p>
            <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
              <a onclick="setCustomerStatus('active')" class="hyperlink-nostyle" href="{{ url('sap-webid-customers')}}"><h3 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0">{{ $total_active_customers }}</h3></a>
              <i class="ti-user icon-md text-success mb-0 mb-md-3 mb-xl-0"></i>
            </div>
            <p class="mb-0 mt-2 text-danger">{{ $thirtyDaysAgoCustomers }}<span class="text-black ms-1"><small>(30 days)</small></span></p>
          </div>
        </div>
      </div>
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            {{-- <p class="card-title text-md-center text-xl-left">Today’s Bookings</p>
            <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
              <h3 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0">40016</h3>
              <i class="ti-agenda icon-md text-muted mb-0 mb-md-3 mb-xl-0"></i>
            </div>
            <p class="mb-0 mt-2 text-success">10.00%<span class="text-black ms-1"><small>(30 days)</small></span></p> --}}
          </div>
        </div>
      </div>
      <div class="col-md-3 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            {{-- <p class="card-title text-md-center text-xl-left">Total Items Bookings</p>
            <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
              <h3 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0">61344</h3>
              <i class="ti-layers-alt icon-md text-muted mb-0 mb-md-3 mb-xl-0"></i>
            </div>
            <p class="mb-0 mt-2 text-success">22.00%<span class="text-black ms-1"><small>(30 days)</small></span></p> --}}
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card bg-primary border-0 position-relative">
          <div class="card-body">
            <p class="card-title text-white">Performance Overview</p>
            <div id="performanceOverview" class="carousel slide performance-overview-carousel position-static pt-2" data-bs-ride="carousel">
              <div class="carousel-inner">
                <div class="carousel-item active">
                  <div class="row">
                    <div class="col-md-4 item">
                      <div class="d-flex flex-column flex-xl-row mt-4 mt-md-0">
                        <div class="icon icon-a text-white me-3">
                          <i class="ti-cup icon-lg ms-3"></i>
                        </div>
                        <div class="content text-white">
                          <div class="d-flex flex-wrap align-items-center mb-2 mt-3 mt-xl-1">
                            <h3 class="fw-light me-2 mb-1">Revenue</h3>
                            <h3 class="mb-0">0</h3>
                          </div>
                          <div class="col-8 col-md-7 d-flex border-bottom border-info align-items-center justify-content-between px-0 pb-2 mb-3">
                            <h5 class="mb-0">+0</h5>
                            <div class="d-flex align-items-center">
                              <i class="ti-angle-down me-2"></i>
                              <h5 class="mb-0">0%</h5>
                            </div>
                          </div>
                          <p class="text-white fw-light pe-lg-2 pe-xl-5">The total number of sessions within the date range. It is the period time a user is actively engaged with your website, page or app, etc</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 item">
                      <div class="d-flex flex-column flex-xl-row mt-5 mt-md-0">
                        <div class="icon icon-b text-white me-3">
                          <i class="ti-bar-chart icon-lg ms-3"></i>
                        </div>
                        <div class="content text-white">
                          <div class="d-flex flex-wrap align-items-center mb-2 mt-3 mt-xl-1">
                            <h3 class="fw-light me-2 mb-1">Sales</h3>
                            <h3 class="mb-0">$0</h3>
                          </div>
                          <div class="col-8 col-md-7 d-flex border-bottom border-info align-items-center justify-content-between px-0 pb-2 mb-3">
                            <h5 class="mb-0">-0</h5>
                            <div class="d-flex align-items-center">
                              <i class="ti-angle-down me-2"></i>
                              <h5 class="mb-0">0%</h5>
                            </div>
                          </div>
                          <p class="text-white fw-light pe-lg-2 pe-xl-5">The total number of sessions within the date range. It is the period time a user is actively engaged with your website, page or app, etc</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 item">
                      <div class="d-flex flex-column flex-xl-row mt-5 mt-md-0">
                        <div class="icon icon-c text-white me-3">
                          <i class="ti-shopping-cart-full icon-lg ms-3"></i>
                        </div>
                        <div class="content text-white">
                          <div class="d-flex flex-wrap align-items-center mb-2 mt-3 mt-xl-1">
                            <h3 class="fw-light me-2 mb-1">Purchases</h3>
                            <h3 class="mb-0">0</h3>
                          </div>
                          <div class="col-8 col-md-7 d-flex border-bottom border-info align-items-center justify-content-between px-0 pb-2 mb-3">
                            <h5 class="mb-0">+0</h5>
                            <div class="d-flex align-items-center">
                              <i class="ti-angle-down me-2"></i>
                              <h5 class="mb-0">0%</h5>
                            </div>
                          </div>
                          <p class="text-white fw-light pe-lg-2 pe-xl-5">The total number of sessions within the date range. It is the period time a user is actively engaged with your website, page or app, etc</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="carousel-item">
                  <div class="row">
                    <div class="col-md-4 item">
                      <div class="d-flex flex-column flex-xl-row mt-4 mt-md-0">
                        <div class="icon icon-a text-white me-3">
                          <i class="ti-cup icon-lg ms-3"></i>
                        </div>
                        <div class="content text-white">
                          <div class="d-flex flex-wrap align-items-center mb-2 mt-3 mt-xl-1">
                            <h3 class="fw-light me-2 mb-1">Clients</h3>
                            <h3 class="mb-0">0</h3>
                          </div>
                          <div class="col-8 col-md-7 d-flex border-bottom border-info align-items-center justify-content-between px-0 pb-2 mb-3">
                            <h5 class="mb-0">+0</h5>
                            <div class="d-flex align-items-center">
                              <i class="ti-angle-down me-2"></i>
                              <h5 class="mb-0">0%</h5>
                            </div>
                          </div>
                          <p class="text-white fw-light pe-lg-2 pe-xl-5">The total number of sessions within the date range. It is the period time a user is actively engaged with your website, page or app, etc</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 item">
                      <div class="d-flex flex-column flex-xl-row mt-5 mt-md-0">
                        <div class="icon icon-b text-white me-3">
                          <i class="ti-bar-chart icon-lg ms-3"></i>
                        </div>
                        <div class="content text-white">
                          <div class="d-flex flex-wrap align-items-center mb-2 mt-3 mt-xl-1">
                            <h3 class="fw-light me-2 mb-1">Order</h3>
                            <h3 class="mb-0">0</h3>
                          </div>
                          <div class="col-8 col-md-7 d-flex border-bottom border-info align-items-center justify-content-between px-0 pb-2 mb-3">
                            <h5 class="mb-0">-0</h5>
                            <div class="d-flex align-items-center">
                              <i class="ti-angle-down me-2"></i>
                              <h5 class="mb-0">0%</h5>
                            </div>
                          </div>
                          <p class="text-white fw-light pe-lg-2 pe-xl-5">The total number of sessions within the date range. It is the period time a user is actively engaged with your website, page or app, etc</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 item">
                      <div class="d-flex flex-column flex-xl-row mt-5 mt-md-0">
                        <div class="icon icon-c text-white me-3">
                          <i class="ti-shopping-cart-full icon-lg ms-3"></i>
                        </div>
                        <div class="content text-white">
                          <div class="d-flex flex-wrap align-items-center mb-2 mt-3 mt-xl-1">
                            <h3 class="fw-light me-2 mb-1">Purchases</h3>
                            <h3 class="mb-0">0</h3>
                          </div>
                          <div class="col-8 col-md-7 d-flex border-bottom border-info align-items-center justify-content-between px-0 pb-2 mb-3">
                            <h5 class="mb-0">+0</h5>
                            <div class="d-flex align-items-center">
                              <i class="ti-angle-down me-2"></i>
                              <h5 class="mb-0">0%</h5>
                            </div>
                          </div>
                          <p class="text-white fw-light pe-lg-2 pe-xl-5">The total number of sessions within the date range. It is the period time a user is actively engaged with your website, page or app, etc</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <a class="carousel-control-prev" href="#performanceOverview" role="button" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>

              </a>
              <a class="carousel-control-next" href="#performanceOverview" role="button" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>

              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row" id="dash-datatable-saleemp-row">
      <div class="col-md-7 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <p class="card-title mb-0">Sales Employees <strong>({{ $totalCustomerByAllSalesEmp }})</strong></p>
            <div class="table-responsive">
              <table id="dash-datatable-saleemp" class="table table-striped table-borderless">
                <thead>
                  <tr>
                    <th>Sales Employee</th>
                    <th>Customers </th>
                  </tr>
                </thead>
                <tbody>
                    @foreach ($customersBySalesEmp as $result)
                    <tr>
                        <td>{{ $result['sales_emp_name'] }}</td>
                        <td class="fw-bold"><a href="{{url('sap-webid-customers')}}" onclick="setSalesEmployeeFilter('{{ $result['sales_emp_name'] }}')">{{ $result['total'] }}</a></td>
                      </tr>
                    @endforeach
                </tbody>
              </table>
              <div id="view-more">View More</div>
              <div id="view-less" style="display: none;">View Less</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-5 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
              <p class="card-title mb-0">Category</p>
              <div class="table-responsive">
                <table id="dash-datatable-category" class="table table-striped table-borderless">
                  <thead>
                    <tr>
                      <th>Category</th>
                      <th>Customers</th>
                    </tr>
                  </thead>
                  <tbody>
                      @foreach ($customersByCategory as $result)
                      <tr>
                          <td>{{ $result['new_u_csubcat'] }}</td>
                          <td class="fw-bold"><a href="{{url('sap-webid-customers')}}" onclick="setCategoryFilter('{{ $result['new_u_csubcat'] }}')">{{ $result['total'] }}</a></td>
                        </tr>
                      @endforeach
                  </tbody>
                </table>
                <div id="cview-more">View More</div>
                <div id="cview-less" style="display: none;">View Less</div>
              </div>
            </div>
          </div>
      </div>
    </div>
    <div class="row">
        <div class="col-md-6 stretch-card grid-margin">
          <div class="card">
              <div class="card-body">
                <p class="card-title mb-0">Sub Category</p>
                <div class="table-responsive">
                  <table id="dash-datatable-subcategory" class="table table-striped table-borderless">
                    <thead>
                      <tr>
                        <th>Sub Category</th>
                        <th>Customers</th>
                      </tr>
                    </thead>
                    <tbody>
                        @foreach ($customersBySubCategory as $result)
                        <tr>
                            <td>{{ $result['new_u_csubcat2'] }}</td>
                            <td class="fw-bold"><a href="{{url('sap-webid-customers')}}" onclick="setSubCategoryFilter('{{ $result['new_u_csubcat2'] }}')">{{ $result['total'] }}</a></td>
                          </tr>
                        @endforeach
                    </tbody>
                  </table>
                  <div id="scview-more">View More</div>
                <div id="scview-less" style="display: none;">View Less</div>
                </div>
              </div>
            </div>
        </div>
        <div class="col-md-6 stretch-card">
          <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <p class="card-title">Charts</p>
                  <div class="charts-data">
                    <div class="mt-3">
                      <p class="text-muted mb-0">Orders</p>
                      <div class="d-flex justify-content-between align-items-center">
                        <div class="progress progress-md flex-grow-1 me-4">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <p class="text-muted mb-0">5k</p>
                      </div>
                    </div>
                    <div class="mt-3">
                      <p class="text-muted mb-0">Users</p>
                      <div class="d-flex justify-content-between align-items-center">
                        <div class="progress progress-md flex-grow-1 me-4">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 35%" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <p class="text-muted mb-0">3k</p>
                      </div>
                    </div>
                    <div class="mt-3">
                      <p class="text-muted mb-0">Downloads</p>
                      <div class="d-flex justify-content-between align-items-center">
                        <div class="progress progress-md flex-grow-1 me-4">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 48%" aria-valuenow="48" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <p class="text-muted mb-0">992</p>
                      </div>
                    </div>
                    <div class="mt-3">
                      <p class="text-muted mb-0">Visitors</p>
                      <div class="d-flex justify-content-between align-items-center">
                        <div class="progress progress-md flex-grow-1 me-4">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <p class="text-muted mb-0">687</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-12 stretch-card grid-margin">
              <div class="card data-icon-card-primary">
                <div class="card-body">
                  <p class="card-title text-white">Number of Meetings</p>
                  <div class="row">
                    <div class=
                    "col-8 text-white">
                      <h3>3404</h3>
                      <p class="text-white fw-light mb-0">The total number of sessions within the date range. It is the period time</p>
                    </div>
                    <div class="col-4 background-icon">
                      <i class="ti-calendar"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <p class="card-title">Order and Downloads</p>
              <p class="text-muted fw-light">The total number of sessions within the date range. It is the period time a user is actively engaged with your website, page or app, etc</p>
              <div class="d-flex flex-wrap mb-5">
                <div class="me-5 mt-3">
                  <p class="text-muted">Order value</p>
                  <h3>12.3k</h3>
                </div>
                <div class="me-5 mt-3">
                  <p class="text-muted">Orders</p>
                  <h3>14k</h3>
                </div>
                <div class="me-5 mt-3">
                  <p class="text-muted">Users</p>
                  <h3>71.56%</h3>
                </div>
                <div class="mt-3">
                  <p class="text-muted">Downloads</p>
                  <h3>34040</h3>
                </div>
              </div>
              <canvas id="order-chart"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-6 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <p class="card-title">Sales Report</p>
              <p class="text-muted fw-light">The total number of sessions within the date range. It is the period time a user is actively engaged with your website, page or app, etc</p>
              <div id="sales-legend" class="chartjs-legend mt-4 mb-2"></div>
              <canvas id="sales-chart"></canvas>
            </div>
            <div class="card border-right-0 border-left-0 border-bottom-0">
              <div class="d-flex justify-content-center justify-content-md-end">
                <div class="dropdown flex-md-grow-1 flex-xl-grow-0">
                  <button class="btn btn-lg btn-outline-dark dropdown-toggle rounded-0 border-top-0 border-bottom-0" type="button" id="dropdownMenuDate2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    Today
                  </button>
                  <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuDate2">
                    <a class="dropdown-item" href="#">January - March</a>
                    <a class="dropdown-item" href="#">March - June</a>
                    <a class="dropdown-item" href="#">June - August</a>
                    <a class="dropdown-item" href="#">August - November</a>
                  </div>
                </div>
                <button class="btn btn-lg btn-outline-light text-primary rounded-0 border-0 d-none d-md-block" type="button"> View all </button>
              </div>
            </div>
          </div>
        </div>
      </div>

    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card position-relative">
          <div class="card-body">
            <p class="card-title">Detailed Reports</p>
            <div id="detailedReports" class="carousel slide detailed-report-carousel position-static pt-2" data-bs-ride="carousel">
              <div class="carousel-inner">
                <div class="carousel-item active">
                  <div class="row">
                    <div class="col-md-12 col-xl-3 d-flex flex-column justify-content-center">
                      <div class="ms-xl-4">
                        <h1>$34040</h1>
                        <h3 class="fw-light mb-xl-4">North America</h3>
                        <p class="text-muted mb-2 mb-xl-0">The total number of sessions within the date range. It is the period time a user is actively engaged with your website, page or app, etc</p>
                      </div>
                      </div>
                    <div class="col-md-12 col-xl-9">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="table-responsive mb-3 mb-md-0">
                            <table class="table table-borderless report-table">
                              <tr>
                                <td class="text-muted">Illinois</td>
                                <td class="w-100 px-0">
                                  <div class="progress progress-md mx-4">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                </td>
                                <td><h5 class="fw-bold mb-0">713</h5></td>
                              </tr>
                              <tr>
                                <td class="text-muted">Washington</td>
                                <td class="w-100 px-0">
                                  <div class="progress progress-md mx-4">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                </td>
                                <td><h5 class="fw-bold mb-0">583</h5></td>
                              </tr>
                              <tr>
                                <td class="text-muted">Mississippi</td>
                                <td class="w-100 px-0">
                                  <div class="progress progress-md mx-4">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                </td>
                                <td><h5 class="fw-bold mb-0">924</h5></td>
                              </tr>
                              <tr>
                                <td class="text-muted">California</td>
                                <td class="w-100 px-0">
                                  <div class="progress progress-md mx-4">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                </td>
                                <td><h5 class="fw-bold mb-0">664</h5></td>
                              </tr>
                              <tr>
                                <td class="text-muted">Maryland</td>
                                <td class="w-100 px-0">
                                  <div class="progress progress-md mx-4">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 40%" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                </td>
                                <td><h5 class="fw-bold mb-0">560</h5></td>
                              </tr>
                              <tr>
                                <td class="text-muted">Alaska</td>
                                <td class="w-100 px-0">
                                  <div class="progress progress-md mx-4">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                </td>
                                <td><h5 class="fw-bold mb-0">793</h5></td>
                              </tr>
                            </table>
                          </div>
                        </div>
                        <div class="col-md-6 mt-3">
                          <canvas id="north-america-chart"></canvas>
                          <div id="north-america-legend"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="carousel-item">
                  <div class="row">
                    <div class="col-md-12 col-xl-3 d-flex flex-column justify-content-center">
                      <div class="ms-xl-4">
                        <h1>$61321</h1>
                        <h3 class="fw-light mb-xl-4">South America</h3>
                        <p class="text-muted mb-2 mb-xl-0">It is the period time a user is actively engaged with your website, page or app, etc. The total number of sessions within the date range. </p>
                      </div>
                    </div>
                    <div class="col-md-12 col-xl-9">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="table-responsive mb-3 mb-md-0">
                            <table class="table table-borderless report-table">
                              <tr>
                                <td class="text-muted">Brazil</td>
                                <td class="w-100 px-0">
                                  <div class="progress progress-md mx-4">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                </td>
                                <td><h5 class="fw-bold mb-0">613</h5></td>
                              </tr>
                              <tr>
                                <td class="text-muted">Argentina</td>
                                <td class="w-100 px-0">
                                  <div class="progress progress-md mx-4">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                </td>
                                <td><h5 class="fw-bold mb-0">483</h5></td>
                              </tr>
                              <tr>
                                <td class="text-muted">Peru</td>
                                <td class="w-100 px-0">
                                  <div class="progress progress-md mx-4">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                </td>
                                <td><h5 class="fw-bold mb-0">824</h5></td>
                              </tr>
                              <tr>
                                <td class="text-muted">Chile</td>
                                <td class="w-100 px-0">
                                  <div class="progress progress-md mx-4">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                </td>
                                <td><h5 class="fw-bold mb-0">564</h5></td>
                              </tr>
                              <tr>
                                <td class="text-muted">Colombia</td>
                                <td class="w-100 px-0">
                                  <div class="progress progress-md mx-4">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 40%" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                </td>
                                <td><h5 class="fw-bold mb-0">460</h5></td>
                              </tr>
                              <tr>
                                <td class="text-muted">Uruguay</td>
                                <td class="w-100 px-0">
                                  <div class="progress progress-md mx-4">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                </td>
                                <td><h5 class="fw-bold mb-0">693</h5></td>
                              </tr>
                            </table>
                          </div>
                        </div>
                        <div class="col-md-6 mt-3">
                          <canvas id="south-america-chart"></canvas>
                          <div id="south-america-legend"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <a class="carousel-control-prev" href="#detailedReports" role="button" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              </a>
              <a class="carousel-control-next" href="#detailedReports" role="button" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
  <!-- content-wrapper ends -->


@endsection

@push('scripts')
<script src="{{asset('vendors/chart.js/Chart.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/dashboard.js')}}"></script>

<script type="text/javascript" src="{{asset('js/off-canvas.js')}}"></script>
<script type="text/javascript" src="{{asset('js/hoverable-collapse.js')}}"></script>
<script type="text/javascript" src="{{asset('js/template.js')}}"></script>
<script type="text/javascript" src="{{asset('js/settings.js')}}"></script>
<script type="text/javascript" src="{{asset('js/todolist.js')}}"></script>
<script>

        $(document).ready(function() {

            var buttons = [];

            // Check if the user role is 1
            if (userRole === 1) {
                buttons = [
                    {
                        extend: 'copy',
                        text: 'Copy',
                        className: 'btn btn-secondary'
                    },
                    {
                        extend: 'csv',
                        text: 'CSV',
                        className: 'btn btn-secondary'
                    },
                    {
                        extend: 'excel',
                        text: 'Excel',
                        className: 'btn btn-secondary'
                    }
                ];
            }

            var salesemptable = $('#dash-datatable-saleemp').DataTable({
                paging: true,
                pageLength: 10,
                lengthChange: false,
                order: [[1, 'desc']],
                dom: 'Bfrtip',
                buttons: buttons,
            });

            $('#view-more').on('click', function() {
                salesemptable.page.len(-1).draw();
                $(this).hide();
                $('#view-less').show();
             });

            $('#view-less').on('click', function() {
                salesemptable.page.len(10).draw(false);
                $(this).hide();
                $('#view-more').show();
                $('html, body').animate({ scrollTop: $("#dash-datatable-saleemp").offset().top - 120 }, 0);
            });


            var categorytable = $('#dash-datatable-category').DataTable({
                paging: true,
                pageLength: 10,
                lengthChange: false,
                searching: true,
                order: [[1, 'desc']],
                dom: 'Bfrtip',
                buttons: buttons,
            });

            $('#cview-more').on('click', function() {
                categorytable.page.len(-1).draw();
                $(this).hide();
                $('#cview-less').show();
             });

            $('#cview-less').on('click', function() {
                categorytable.page.len(10).draw(false);
                $(this).hide();
                $('#cview-more').show();
                $('html, body').animate({ scrollTop: $("#dash-datatable-category").offset().top - 120 }, 0);
            });

            var subcategorytable = $('#dash-datatable-subcategory').DataTable({
                paging: true,
                pageLength: 10,
                lengthChange: false,
                searching: true,
                order: [[1, 'desc']],
                dom: 'Bfrtip',
                buttons: buttons,
            });

            $('#scview-more').on('click', function() {
                subcategorytable.page.len(-1).draw();
                $(this).hide();
                $('#scview-less').show();
             });

            $('#scview-less').on('click', function() {
                subcategorytable.page.len(10).draw(false);
                $(this).hide();
                $('#scview-more').show();
                $('html, body').animate({ scrollTop: $("#dash-datatable-subcategory").offset().top - 120 }, 0);
            });

        });

</script>
<script>

    function setSalesEmployeeFilter(salesempname) {
         sessionStorage.setItem('dashboardSaleEmpName', salesempname);
    }
    function setCategoryFilter(category) {
         sessionStorage.setItem('dashboardCategory', category);
    }
    function setSubCategoryFilter(subcategory) {
         sessionStorage.setItem('dashboardSubCategory', subcategory);
    }
    function setCustomerStatus(status) {
         sessionStorage.setItem('dashboardStatus', status);
    }

 </script>
@endpush


