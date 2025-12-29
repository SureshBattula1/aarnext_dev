@extends('layouts.customer-header')
<style>
    .text-right {
        text-align: right;
    }
    .text-left {
        text-align: left;
    }
    @media (max-width: 768px) {
        .cust_data_form .col-md-1, .cust_data_form .col-md-3, .cust_data_form .col-md-2 {
            margin-bottom: 10px;
        }
        .cust_data_form .col-md-1 {
            width: 100%;
        }
        .cust_data_form .col-md-3 {
            width: 100%;
        }
        .cust_data_form .col-md-2 {
            width: 100%;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table th, .table td {
            white-space: nowrap;
        }
    }
    .viewInvoice{
       height: 35px;
       width: 150px;
    }
</style>
@section('content')
<div class="content-wrapper container-fluid px-2 px-md-3">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">My Invoice Reports</h5>
            <div class="row">
                <div class="row cust_data_form">
                    <div class="col-12 col-md-1">
                        <div class="form-group">
                            <select class="form-control" style="width: 100%;" id="page_length">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="1000">1000</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 margin pull-right no-m-top">
                        <div class="input-group">
                            <input type="text" class="form-control" id="search_items" placeholder="Search by name...">
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <select class="form-control" name="sales_type" id="sales_type">
                            <option value=""> Select Sales Type</option>
                            <option value="Cash">Cash</option>
                            <option value="Cash/Credit">Cash/Credit</option>
                            <option value="Hospital">Hospital</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <select class="form-control fields" id="status_search" name="status_search">
                            <option value="">Select Status</option>
                            <option value="0">Open</option>
                            <option value="1">Closed</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-2"></div>
                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="" required>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table id="invoices_table" class="table dataTable no-footer">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Doc Number</th>
                                    <th>Sales Type</th>
                                    {{-- <th>Sales Employee Name</th> --}}
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Invoice</th>
                                </tr>
                            </thead>
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
        var invoicesTable = $('#invoices_table').DataTable({
            "dom": '<"html5buttons"B>tp',
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ url('customer-invoice-reports-json') }}",
                "type": "GET",
                "data": function(d) {
                    d.search = $('#search_items').val();
                    d.sales_type = $('#sales_type').val();
                    d.length = $('#page_length').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.status_search = $('#status_search').val();
                },
            },
            "columns": [
                { "data": 'id', "title": "ID",
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-right');
                    }
                },
                { "data": 'doc_num', "title": "Document Num",
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                },
                { "data": 'sales_type', "title": "Sales Type",
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                },
                { "data": 'order_date', "title": "Date",
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                },
                { "data": 'total', "title": "Total",
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-right');
                    }
                },
                { "data": 'status', "title": "Status" ,
                        "render": function(data, type, row) {
                            if (data === 1) {
                                return '<span class="text-success">Approved</span>';
                            } else if(data === 2){
                                return '<span class="text-success">Rejected</span>';
                            } else {
                                return '<span class="text-warning">Pending</span>';
                            }
                        },
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                },
                {
                    "data": 'id',
                    "title": "Actions",
                    "render": function(data, type, row) {
                        return `<button class="btn btn-primary viewInvoice" onclick="viewInvoice('${data}')">View Invoice</button>`;
                    },
                    "createdCell": function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-center');
                    }
                },
            ],
            "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                var page = this.api().page.info().page;
                var length = this.api().page.info().length;
                var index = (page * length + (iDisplayIndex + 1));
                $('td:eq(0)', nRow).html(index);
            },
            "drawCallback": function(data) {
                var response = data.json;
                if (response && response.total_amount) {
                    $('#total_amount').text(response.total_amount.toFixed(2));
                } else {
                    $('#total_amount').text('0.00');
                }
            }
        });

        $('#search_items').on('keyup', function() {
            invoicesTable.draw();
        });
        $('#page_length').change(function() {
            invoicesTable.page.len($(this).val()).draw();
        });
        $('#sales_type').on('change', function() {
            invoicesTable.draw();
        });
        $('#start_date, #end_date').on('change', function() {
            invoicesTable.draw();
        });
        $('#status_search').on('change', function() {
            invoicesTable.draw();
        });
    });

    function viewInvoice(id) {
        var url = '{{ url('/') }}/pdf/invoices/invoice_' + id + '.pdf';
        window.open(url, '_blank');
    }
</script>
@endpush
