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
                                <input type="text" class="form-control" id="search_items"
                                    placeholder="Search by  name...">

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
                        <select class="form-control fields" id="status_search" name="status_search" class="form-control" style="margin-top: 24px;">
                            <option value="all">Select Status</option>
                            <option value="0">Open</option>
                            <option value="1">Closed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                        <select class="form-control fields" id="doc_num_search" name="doc_num_search" style="margin-top: 24px;">
                            <option value="">Select Sap Sync Doc</option>
                            <option value="1">Synced</option>
                            <option value="2">Pending</option>
                        </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" class="form-control" id="start_date"
                                name="start_date
                        date" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" class="form-control" id="end_date"
                                name="end_date
                        date" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-2" style="margin-top: 23px;">
                    <div>
                    <select name="search_ware_house[]" id="search_ware_house" class="form-control" multiple="multiple">
                        <option value="AAR-BIE">AAR-BIE</option>
                        <option value="AAR-CAB">AAR-CAB</option>
                        <option value="AAR-DUN">AAR-DUN</option>
                        <option value="AAR-GAB">AAR-GAB</option>
                        <option value="AAR-HUA">AAR-HUA</option>
                        <option value="AAR-LOB">AAR-LOB</option>
                        <option value="AAR-LUB">AAR-LUB</option>
                        <option value="AAR-MAL">AAR-MAL</option>
                        <option value="AAR-MEN">AAR-MEN</option>
                        <option value="AAR-MUL">AAR-MUL</option>
                        <option value="AAR-PAL">AAR-PAL</option>
                        <option value="AAR-SAU">AAR-SAU</option>
                        <option value="AAR-SUM">AAR-SUM</option>
                        <option value="AAR-UIG">AAR-UIG</option>
                        <option value="AAR-VIA">AAR-VIA</option>
                        <option value="AAR-OND">AAR-OND</option>
                        <option value="ALT-BEN">ALT-BEN</option>
                        <option value="ALT-CZN">ALT-CZN</option>
                        <option value="ALT-MOX">ALT-MOX</option>
                        <option value="ALT-ZAN">ALT-ZAN</option>
                        <option value="EQU-PAL">EQU-PAL</option>
                    </select>
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
                                        <th>Cash</th>
                                        <th>Cash/Credit</th>
                                        <th>Credit</th>
                                        <th>Hospital</th>
                                        <th>Sales Employee Name</th>
                                        <th>Memo</th>
                                        <th>Sap Status</th>
                                        <th>Sap Updated Date</th>
                                        <th>Doc Number</th>
                                        <th>Sap Error Msg</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th colspan="6"></th>
                                        <th colspan="1" id="total_amount" style="text-align:"></th>
                                        <th colspan="1" id="total_paid_amount"></th>
                                        <th colspan="1" id="total_balance_amount"></th>
                                        <th colspan="2"></th>
                                        <th colspan="1" id="total_cash" style="text-align:right;"></th>
                                        <th colspan="1" id="total_cash_credit" style="text-align:right;"></th>
                                        <th colspan="1" id="total_credit" style="text-align:right;"></th>
                                        <th colspan="1" id="total_hospital" style="text-align:right;"></th>
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
    $(document).ready(function(){
        $('#search_ware_house').select2({
            placeholder: "Select Store",
            allowClear: true,
            width: '100%'
        });
    });
</script>
    <script>
        var salesType = "{{ $sales_type ?? '' }}";
        console.log('salesType', salesType)
        $(document).ready(function() {
            var itemsTable = $('#items_table').DataTable({
                "dom": '<"html5buttons"B>tp', 
                "serverSide": true,
                "ajax": {
                    "url": "{{ url('admin-sales-reports-json') }}",
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
                        d.doc_num_search = $('#doc_num_search').val();
                        d.search_ware_house = $('#search_ware_house').val() ? $('#search_ware_house').val().join(',') : ''; 

                    },
                },
                "columns": [{
                        "data": 'id',
                        "title": "ID",
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-right');
                        }
                    },
                    {
                        "data": 'doc_num',
                        "title": "Doc Number",
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-left');
                        }

                    },
                    {
                        "data": 'customer_id',
                        "title": "Customer ID",
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-left');
                        }
                    },
                    {
                        "data": 'card_name',
                        "title": "Customer Name",
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-left');
                        }
                    },
                    {
                        "data": salesType !== '' ? '' : 'sales_type',
                        "title": "Sales Type",
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-left');
                        }
                    },
                    {
                        "data": 'order_date',
                        "title": "Order Date",
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-left');
                        }
                    },
                    {
                        "data": 'total',
                        "title": "Total",
                        "render": function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        },
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-right');
                        }

                    },
                    {
                        "data": 'paid_amount',
                        "title": "Paid Amount",
                        "render": function(data, type, row) {
                            return isNaN(parseFloat(data)) ? "0.00" : parseFloat(data).toFixed(2);
                        },
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-right');
                        }
                    },
                    {
                        "data": null,
                        "title": "Balance Amount",
                        "render": function(data, type, row) {
                            let total = parseFloat(row.total) || 0;
                            let paidAmount = parseFloat(row.paid_amount) || 0;
                            let balanceAmount = total - paidAmount;
                            return balanceAmount.toFixed(2);
                        },
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-right');
                        }
                    },
                    {
                        "data": 'status',
                        "title": "Status",
                        "render": function(data, type, row) {
                            return data === 1 ? "Completed" : "Pending";
                        },
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-left');
                        }
                    },
                    {
                        "data": 'user_name',
                        "title": "User Name",
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-left');
                        }
                    },
                    {
                        "data": null,
                        "title": "Cash",
                        "render": function(data, type, row) {
                            return row.sales_type === "cash" ? parseFloat(row.total).toFixed(2) :
                                "0.00";
                        },
                        "createdCell": function(td) {
                            $(td).addClass('text-right');
                        }
                    },
                    {
                        "data": null,
                        "title": "Cash/Credit",
                        "render": function(data, type, row) {
                            return row.sales_type === "cash/credit" ? parseFloat(row.total).toFixed(
                                2) : "0.00";
                        },
                        "createdCell": function(td) {
                            $(td).addClass('text-right');
                        }
                    },
                    {
                        "data": null,
                        "title": "Credit",
                        "render": function(data, type, row) {
                            return row.sales_type === "credit" ? parseFloat(row.total).toFixed(2) :
                                "0.00";
                        },
                        "createdCell": function(td) {
                            $(td).addClass('text-right');
                        }
                    },
                    {
                        "data": null,
                        "title": "Hospital",
                        "render": function(data, type, row) {
                            return row.sales_type === "hospital" ? parseFloat(row.total).toFixed(
                                2) : "0.00";
                        },
                        "createdCell": function(td) {
                            $(td).addClass('text-right');
                        }
                    },
                    {
                        "data": 'sales_employee_name',
                        "title": "Sales Employee Name",
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-left');
                        }
                    },
                    {
                        "data": 'memo',
                        "title": "Memo",
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-left');
                        }
                    },
                    {
                        "data": "sap_status",
                        "name": "sap_status"
                    },
                    {
                        "data": "sap_updated_at",
                        "name": "sap_updated_at",
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                return data ? data.split(' ')[0] : '';
                            }
                            return data;
                        },
                    },
                    {
                        "data": "sap_created_id",
                        "name": "sap_created_id"
                    },
                    {
                        "data": "sap_error_msg",
                        "name": "sap_error_msg",
                        "render": function(data, type, row) {
                            if (data) {

                                const maxLength = 50;
                                const regex = new RegExp(`.{1,${maxLength}}`, 'g');
                                const lines = data.match(regex) || [];
                                return lines.map(line => `<div>${line}</div>`).join(
                                '');
                            }
                            return '';
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
                    var response = data.json;
                    if (response) {
                        // Calculate totals for each category
                        var totalCash = 0,
                            totalCashCredit = 0,
                            totalCredit = 0,
                            totalHospital = 0;
                        var totalAmount = 0,
                            totalPaidAmount = 0,
                            totalBalanceAmount = 0;

                        // Loop through the rows and calculate sums
                        itemsTable.rows().data().each(function(rowData) {
                            totalAmount += parseFloat(rowData.total) || 0;
                            totalPaidAmount += parseFloat(rowData.paid_amount) || 0;
                            totalBalanceAmount += (parseFloat(rowData.total) - parseFloat(rowData.paid_amount)) || 0;
                            if (rowData.sales_type === "cash") totalCash += parseFloat(rowData.total) || 0;
                            if (rowData.sales_type === "cash/credit") totalCashCredit += parseFloat(rowData.total) || 0;
                            if (rowData.sales_type === "credit") totalCredit += parseFloat(rowData.total) || 0;
                            if (rowData.sales_type === "hospital") totalHospital += parseFloat(rowData.total) || 0;
                        });

                        // Update totals in the footer with formatted numbers
                        $('#total_amount').text(formatNumber(totalAmount));
                        $('#total_paid_amount').text(formatNumber(totalPaidAmount));
                        $('#total_balance_amount').text(formatNumber(totalBalanceAmount));
                        $('#total_cash').text(formatNumber(totalCash));
                        $('#total_cash_credit').text(formatNumber(totalCashCredit));
                        $('#total_credit').text(formatNumber(totalCredit));
                        $('#total_hospital').text(formatNumber(totalHospital));
                    } else {
                        $('#total_amount').text('0,00');
                        $('#total_paid_amount').text('0,00');
                        $('#total_balance_amount').text('0,00');
                        $('#total_cash').text('0,00');
                        $('#total_cash_credit').text('0,00');
                        $('#total_credit').text('0,00');
                        $('#total_hospital').text('0,00');
                    }
                }
            });
            function formatNumber(value) {
                return value.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

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
            $('#search_ware_house').on('select2:select select2:unselect', function() {
                itemsTable.draw();
            });
            $('#doc_num_search').on('change', function() {
                itemsTable.draw();
            });
        });
        
    </script>
@endpush
