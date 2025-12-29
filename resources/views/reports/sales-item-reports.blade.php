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
            <h5 class="card-title">Sales Items Reports</h5>
            <div class="row cust_data_form">
                <!-- Page Length Selector -->
                <div class="col-md-1">
                    <div class="form-group">
                        <select class="form-control" id="page_length">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="10000000">All</option>
                        </select>
                    </div>
                </div>

                <!-- Search Box -->
                <div class="col-md-3 margin pull-right no-m-top">
                    <div class="input-group">
                        <input type="text" class="form-control" id="search_items" placeholder="Search by name...">
                    </div>
                </div>

                <!-- Sales Type Filter -->
                <div class="col-md-3">
                    <select class="form-control" name="sales_type" id="sales_type">
                        <option value="">Select Sales Type</option>
                        <option value="Cash">Cash</option>
                        <option value="Cash/Credit">Cash/Credit</option>
                        <option value="Hospital">Hospital</option>
                        <option value="Credit">Credit</option>
                    </select>
                </div>

                <!-- Sub Group Filter -->
                <div class="col-md-3">
                    <select class="form-control fields" id="sub_grp_search" name="sub_grp_search">
                        <option value="">Select Sub Group Type</option>
                        <option value="GENERAL">General</option>
                        <option value="HOSPITAL">Hospital</option>
                    </select>
                </div>

                <!-- Report Type Filter -->
                <div class="col-md-2">
                    <select class="form-control fields" id="sales_report" name="sales_report">
                        <option value="invoices">Invoices</option>
                        <option value="credits">Credit Notes</option>
                    </select>
                </div>
            </div>

            <div class="row ">
                <!-- Status Filter -->
                <div class="col-md-2">
                    <select class="form-control fields" id="status_search" name="status_search">
                        <option value="all">Select Status</option>
                        <option value="0">Open</option>
                        <option value="1">Closed</option>
                    </select>
                </div>

                <!-- Start Date -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <!-- End Date -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <!-- Export to Excel Button -->
                <div class="col-md-2" style="margin-top: 23px;">
                    <button id="exportExcel" class="btn btn-success">Export to Excel</button>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="items_table" class="table table-bordered">
                            <thead>
                            <tr>
                                    <th>ID</th>
                                    <th>Item No</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Discount (%)</th>
                                    <th>Tax (%)</th>
                                    <th>Total</th>
                                    <th>Doc Number</th>
                                    <th>Order Date</th>
                                    <th>Sales Type</th>
                                    <th>Customer ID</th>
                                    <th>Customer Name</th>
                                    <th>Sub Group</th>
                                    <th>Status</th>
                                    <th>User Name</th>
                                    <th>Sales Employee</th>
                                </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th colspan="3" style="text-align:right;">Totals:</th>
                                <th id="totalQuantity"></th>
                                <th id="totalPrice"></th>
                                <th colspan="2"></th>
                                <th id="totalItemTotal"></th>
                                <th colspan="8"></th>
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
        "dom": 'tp',
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('sales-items-reports-json') }}",
            type: "GET",
            data: function(d) {
                d.search_items = $('#search_items').val();
                d.sales_type = $('#sales_type').val();
                d.sub_grp_search = $('#sub_grp_search').val();
                d.sales_report = $('#sales_report').val();
                d.status_search = $('#status_search').val();
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.length = $('#page_length').val();
            }
        },
        columns: [
            { data: 'item_id' ,
                "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-right');
                    }
            },
            { data: 'item_no' ,
                "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
            },
            { data: 'item_desc' ,
                "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
            },
            { data: 'quantity' ,
                "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-right');
                    }
            },
            { data: 'price' ,
                "createdCell": function (td, cellData, rowData, row, col) {
        
                    const formattedValue = parseFloat(cellData).toFixed(2); 
                    $(td).addClass('text-right').text(formattedValue);
                }
            },
            { data: 'disc_percent' ,
                "createdCell": function (td, cellData, rowData, row, col) {
        
                const formattedValue = parseFloat(cellData).toFixed(2); 
                $(td).addClass('text-right').text(formattedValue);
            }
            },
            { data: 'gst_rate' ,
                "createdCell": function (td, cellData, rowData, row, col) {
        
                    const formattedValue = parseFloat(cellData).toFixed(2); 
                    $(td).addClass('text-right').text(formattedValue);
                }
             },
            { data: 'item_total' ,
                "createdCell": function (td, cellData, rowData, row, col) {
        
                const formattedValue = parseFloat(cellData).toFixed(2); 
                $(td).addClass('text-right').text(formattedValue);
            }
            },
            { data: 'doc_number' ,
                "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
            },
            { data: 'order_date',
                "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
             },
            { data: 'sales_type',
                "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
             },
            { data: 'customer_id',
                "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
             },
            { data: 'customer_name',
                "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
             },
            { data: 'sub_grp',
                "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
             },
            { 
                data: 'status', 
                render: function(data) {
                    return data == 0 ? 'Pending' : 'Completed';
                } ,
                "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
            },
            { data: 'user_name' ,
                "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
            },
            { data: 'sales_employee',
                "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
             }
        ],
        footerCallback: function(row, data, start, end, display) {
            // Get totals from the AJAX response
            var api = this.api();
            var totals = api.ajax.json().totals;

            $('#totalQuantity').html(totals.total_quantity);
            $('#totalPrice').html(totals.total_price);
            $('#totalItemTotal').html(totals.total_item_total);
        },
        rowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            var page = this.api().page.info().page;
            var length = this.api().page.info().length;
            var index = (page * length + (iDisplayIndex + 1));
            $('td:eq(0)', nRow).html(index);
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

    $('#sales_report').on('change', function() {
        itemsTable.draw();
    });

    // Export to Excel
    $('#exportExcel').on('click', function() {
        var params = $.param({
            search: $('#search_items').val(),
            sales_type: $('#sales_type').val(),
            sub_grp_search: $('#sub_grp_search').val(),
            sales_report: $('#sales_report').val(),
            status_search: $('#status_search').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val()
        });
        window.location.href = "{{ url('export-sales-items-reports') }}?" + params;
    });
});

</script>
@endpush
