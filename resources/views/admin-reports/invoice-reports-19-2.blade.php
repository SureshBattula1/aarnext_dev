@extends('layouts.app')
<style>
    .text-right {
        text-align: right;
    }
    .text-left {
        text-align: left;
    }
</style>
@section('content')
<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Sales Reports</h5>
            <div class="row">
            <div class="row cust_data_form">
                        <div class="col-md-1">
                            <div class="form-group">
                                <select class="form-control" style="width: 100%;" id="page_length">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="1000000">All</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 margin pull-right no-m-top">
                            <div class="input-group">
                                <input type="text" class="form-control" id="search_items" placeholder="Search by  name...">
                                
                            </div>
                        </div>
                        <div class="col-md-3">
                                <!--<label class="" for="sales_type">Sales Type</label>-->
                                <select class="form-control" name="sales_type" id="sales_type">
                                            <option value=""> Select Sales Type</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Cash/Credit">Cash/Credit</option>
                                            <option value="Hospital">Hospital</option>
                                            <option value="credit">Credit</option>
                                </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control fields" id="sub_grp_search" name="sub_grp_search">
                                    <option value="">Select Sub Group Type</option>
                                    <option value="GENERAL">General</option>
                                    <option value="HOSPITAL">Hospital</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control fields" id="sales_report" name="sales_report">
                                    <option value="invoices">Invoices</option>
                                    <option value="credits">Credit Notes</option>
                                </select>
                            </div>
                    </div>
                
            </div>
            <div class="row">
                <!--<div class="col-md-1"></div>-->
                <div class="col-md-2">
                    <select class="form-control fields" id="status_search" name="status_search">
                        <option value="all" >Select Status</option>
                        <option value="0">Open</option>
                        <option value="1">Closed</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date
                        date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date
                        date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="col-md-2" style="margin-top: 23px;">
                    <div>
                        <button id="exportExcel" class="btn btn-success">Export to Excel</button>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    

                    <div class="table-responsive">
                        <table id="items_table" class="table dataTable no-footer">
                            <thead>
                            <tr>
                                    <th>ID</th>
                                    <th>Doc Number</th>
                                    <th>Customer ID</th>
                                    <th>Customer Name</th>
                                    <th>Sales Type</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Paid Amount</th>
                                    <th>Balance Amount</th>
                                    <th>Status</th>
                                    <th>User Name</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="6"></th>
                                    <th colspan="1" id="total_amount" style="text-align:"></th>
                                    <th colspan="1" id="total_paid_amount" ></th>
                                    <th colspan="1" id="total_balance_amount" ></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var itemsTable = $('#items_table').DataTable({
            "dom": '',
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ url('invoice-reports-json') }}",
                "type": "GET",
                "data": function(d) {
                    d.search = $('#search_items').val();
                    d.sales_type = $('#sales_type').val();
                    d.length = $('#page_length').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.sub_grp_search = $('#sub_grp_search').val();
                    d.status_search = $('#status_search').val();
                    d.sales_report = $('#sales_report').val();
                },
               
            },
            "columns": [
                { "data": 'id', "title": "ID" ,
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-right');
                    }
                },
                { "data": 'doc_num', "title": "Doc Number" ,
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }

                },
                { "data": 'customer_id', "title": "Customer ID" ,
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                 },
                { "data": 'card_name', "title": "Customer Name" ,
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                 },
                { "data": 'sales_type', "title": "Sales Type" ,
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                },
                { "data": 'order_date', "title": "Order Date" ,
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                },
                { "data": 'total',
                     "title": "Total" ,
                     "render": function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        },
                        "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-right');
                    }

                },
                { "data": 'paid_amount',
                    "title": "Paid Amount" ,
                    "render": function(data, type, row) {
                            return isNaN(parseFloat(data)) ? "0.00" : parseFloat(data).toFixed(2);
                    },
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-right');
                    }
                },
                {
                    "data": null, 
                    "title": "Balance Amount",
                    "render": function(data, type, row) {
                        if (isNaN(parseFloat(row.total)) || isNaN(parseFloat(row.paid_amount))) {
                            return "0.00"; 
                        }
                        return isNaN(parseFloat(data)) ? "0.00" : (row.total - row.paid_amount).toFixed(2); 
                    },
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-right');
                    }
                },
                {   "data": 'status',
                    "title": "Status" ,
                    "render": function(data, type, row) {
                        return data === 1 ? "Completed" : "Pending";
                    },
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                },
                { "data": 'user_name', "title": "User Name" ,
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                 }
            ],
            
            "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                var page = this.api().page.info().page;
                var length = this.api().page.info().length;
                var index = (page * length + (iDisplayIndex + 1));
                $('td:eq(0)', nRow).html(index);
            },

            "drawCallback": function(data) {
                    // Access the total amount from the server response
                    var response = data.json;
                    if (response) {

                        $('#total_amount').text(response.total_amount.toFixed(2));
                        $('#total_paid_amount').text(response.total_paid_amount.toFixed(2));
                        $('#total_balance_amount').text(response.total_balance_amount.toFixed(2));

                    } else {
                        $('#total_amount').text('0.00');
                        $('#total_paid_amount').text('0.00');
                        $('#total_balance_amount').text('0.00');
                    }
                }
            });

        $('#search_items').on('keyup', function() {
            itemsTable.draw();
        });

        $('#page_length').change(function() {
            itemsTable.page.len($(this).val()).draw();
        });
        $('#sales_type').on('change', function() {
            itemsTable.draw();
        });
        $('#start_date, #end_date').on('change', function() {
            itemsTable.draw();
        });
        $('#sub_grp_search').on('change', function() {
            itemsTable.draw();
        });
        $('#status_search').on('change', function() {
            itemsTable.draw();
        });
        $('#sales_report').on('change' , function(){
            itemsTable.draw();
        })
    });
    $('#exportExcel').on('click', function() {
        var salesType = $('#sales_type').val();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        var searchItems = $('#search_items').val();
        var pageLength = $('#page_length').val();
        var subGrpSearch = $('#sub_grp_search').val();
        var statusSearch = $('#status_search').val();
        var salesReport = $('#sales_report').val(); 

        window.location.href = "{{ url('export-invoice-report') }}?sales_type=" + salesType + 
            "&sales_report=" + salesReport + 
            "&start_date=" + startDate + 
            "&end_date=" + endDate + 
            "&search_user=" + searchItems + 
            "&sub_grp_search=" + subGrpSearch + 
            "&status_search=" + statusSearch + 
            "&page_length=" + pageLength;
    });
</script>  
@endpush