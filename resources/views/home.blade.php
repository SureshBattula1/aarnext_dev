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
    .content-wrapper.dashboard-page {
      min-height: 296px;
    }

    .scroll-container {
        overflow-x: auto;
        white-space: nowrap;
        padding: 1rem;
    }

    .scroll-container .row {
        display: flex;
        flex-wrap: nowrap;
    }
</style>
<div class="content-wrapper dashboard-page">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="row">
          <div class="col-12 col-xl-5 mb-4 mb-xl-0">
            <h4 class="fw-bold">Hi, Welcome back!</h4>
            <!--<h4 class="fw-normal mb-0">Aarnext Dashboard,</h4>-->
            <h4 class="fw-normal mb-0">Aarnext <span style="color: orange"><?php if (Auth::check()) { echo Auth::user()->ware_house; }  ?></span>  Dashboard,</h4>
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
    <div class="scroll-container">
      <div class="row flex-nowrap">
        <div class="col-md-3 grid-margin stretch-card">
          <div class="card">
          <div class="card-body">
              <p class="card-title text-md-center text-xl-left">Sales</p>
              <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                <h4 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0"><span id="sales_reports_day_amount"></span> <span class="text-black ms-1"><small class="text-success">- DAY</small></span></h4>
                <i class="ti-agenda icon-md text-muted mb-0 mb-md-3 mb-xl-0"></i>
              </div>
              <!--<p class="mb-0 mt-2 text-success">10.00%<span class="text-black ms-1"><small>(30 days)</small></span></p> -->
              <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                <h4 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0"><span id="sales_reports_month_amount"></span> <span class="text-warning ms-1"><small>- MONTH</small></span></h4>
              </div>
              <!--<p class="mb-0 mt-2 text-warning">10.00%<span class="text-black ms-1"><small>(30 days)</small></span></p>-->
            </div>
          </div>
        </div>

        <div class="col-md-3 grid-margin stretch-card">
          <div class="card">
          <div class="card-body">
              <p class="card-title text-md-center text-xl-left">Collection</p>
              <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                <h4 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0"><span id="payment_reports_day_amount"></span> <span class="text-black ms-1"><small class="text-success">- DAY</small></span></h4>
                <i class="ti-agenda icon-md text-muted mb-0 mb-md-3 mb-xl-0"></i>
              </div>
              <!--<p class="mb-0 mt-2 text-success">10.00%<span class="text-black ms-1"><small>(30 days)</small></span></p> -->
              <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                <h4 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0"><span id="payment_reports_month_amount"></span> <span class="text-warning ms-1"><small>- MONTH</small></span></h4>
              </div>
              <!--<p class="mb-0 mt-2 text-warning">10.00%<span class="text-black ms-1"><small>(30 days)</small></span></p>-->
            </div>
          </div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <p class="card-title text-md-center text-xl-left">Cash</p>
              <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                <h4 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0"><span id="cash_day_amount"></span> <span class="text-black ms-1"><small class="text-success">- DAY</small></span></h4>
                <i class="ti-agenda icon-md text-muted mb-0 mb-md-3 mb-xl-0"></i>
              </div>
              <!--<p class="mb-0 mt-2 text-success">10.00%<span class="text-black ms-1"><small>(30 days)</small></span></p> -->
              <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                <h4 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0"><span id="cash_month_amount"></span> <span class="text-warning ms-1"><small>- MONTH</small></span></h4>
              </div>
              <!--<p class="mb-0 mt-2 text-warning">10.00%<span class="text-black ms-1"><small>(30 days)</small></span></p>-->
            </div>
          </div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <p class="card-title text-md-center text-xl-left">TPA</p>
              <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                <h4 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0"><span id="tpa_day_amount"></span> <span class="text-black ms-1"><small class="text-success">- DAY</small></span></h4>
                <i class="ti-agenda icon-md text-muted mb-0 mb-md-3 mb-xl-0"></i>
              </div>
              <!--<p class="mb-0 mt-2 text-success">10.00%<span class="text-black ms-1"><small>(30 days)</small></span></p> -->
              <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                <h4 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0"><span id="tpa_month_amount"></span> <span class="text-warning ms-1"><small>- MONTH</small></span></h4>
              </div>
              <!--<p class="mb-0 mt-2 text-warning">10.00%<span class="text-black ms-1"><small>(30 days)</small></span></p>-->
            </div>
          </div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <p class="card-title text-md-center text-xl-left">Sales Order</p>
              <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                <h4 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0"><span id="order_day_amount"></span> <span class="text-black ms-1"><small class="text-success">- DAY</small></span></h4>
                <i class="ti-agenda icon-md text-muted mb-0 mb-md-3 mb-xl-0"></i>
              </div>
              <!--<p class="mb-0 mt-2 text-success">10.00%<span class="text-black ms-1"><small>(30 days)</small></span></p> -->
              <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                <h4 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0"><span id="order_month_amount"></span> <span class="text-warning ms-1"><small>- MONTH</small></span></h4>
              </div>
              <!--<p class="mb-0 mt-2 text-warning">10.00%<span class="text-black ms-1"><small>(30 days)</small></span></p>-->
            </div>
          </div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <p class="card-title text-md-center text-xl-left">Invoices</p>
              <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                <h4 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0"><span id="invoice_day_amount"></span> <span class="text-black ms-1"><small class="text-success">- DAY</small></span></h4>
                <i class="ti-agenda icon-md text-muted mb-0 mb-md-3 mb-xl-0"></i>
              </div>
              <!--<p class="mb-0 mt-2 text-success">10.00%<span class="text-black ms-1"><small>(30 days)</small></span></p> -->
              <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                <h4 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0"><span id="invoice_month_amount"></span> <span class="text-warning ms-1"><small>- MONTH</small></span></h4>
              </div>
              <!--<p class="mb-0 mt-2 text-warning">10.00%<span class="text-black ms-1"><small>(30 days)</small></span></p>-->
            </div>
          </div>
        </div>
        
        <div class="col-md-3 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <p class="card-title text-md-center text-xl-left">Credit Note</p>
              <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                <h4 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0"><span id="credit_day_amount"></span> <span class="text-black ms-1"><small class="text-success">- DAY</small></span></h4>
                <i class="ti-agenda icon-md text-muted mb-0 mb-md-3 mb-xl-0"></i>
              </div>
              <!--<p class="mb-0 mt-2 text-success">10.00%<span class="text-black ms-1"><small>(30 days)</small></span></p> -->
              <div class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                <h4 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0"><span id="credit_month_amount"></span> <span class="text-warning ms-1"><small>- MONTH</small></span></h4>
              </div>
              <!--<p class="mb-0 mt-2 text-warning">10.00%<span class="text-black ms-1"><small>(30 days)</small></span></p>-->
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

        $(document).ready(function () {
          $.ajax({
              url: 'dashboard-data', 
              method: 'GET',
              success: function (response) {
                $('#customers-count').text(response.customers_count);
                  $('#items-count').text(response.items_count);

                  $('#cash_day_amount').text(response.cash_amount);
                  $('#cash_month_amount').text(response.cash_amount_month);

                  $('#tpa_day_amount').text(response.tpa_amount);
                  $('#tpa_month_amount').text(response.tpa_amount_month);

                  $('#order_day_amount').text(response.order_day_amount);
                  $('#order_month_amount').text(response.order_month_amount);

                  $('#invoice_day_amount').text(response.invoice_day_amount);
                  $('#invoice_month_amount').text(response.invoice_month_amount);

                  $('#payment_reports_day_amount').text(response.payment_reports_day_amount);
                  $('#payment_reports_month_amount').text(response.payment_reports_month_amount);

                  $('#credit_day_amount').text(response.credit_day_amount);
                  $('#credit_month_amount').text(response.credit_month_amount);

                  $('#sales_reports_day_amount').text(response.invoice_day_amount - response.credit_day_amount);
                  $('#sales_reports_month_amount').text(response.invoice_month_amount - response.credit_month_amount);
              },
              error: function (error) {
                  console.error('Error fetching dashboard data:', error);
              },
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


