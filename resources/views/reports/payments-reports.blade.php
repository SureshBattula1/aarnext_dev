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
            <h5 class="card-title">Payments Reports</h5>
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
                            <input type="text" class="form-control" id="search_items" placeholder="Search by name...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control fields" id="sub_grp_search" name="sub_grp_search">
                            <option value="">Select Sub Group Type</option>
                            <option value="GENERAL">General</option>
                            <option value="HOSPITAL">Hospital</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control fields" id="pay_method" name="pay_method">
                            <option value="">Payment Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="tpa">Tpa</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-1"></div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                {{-- <div class="col-md-2" style="margin-top: 23px;">
                    <button id="exportExcel" class="btn btn-success">Export to Excel</button>
                </div> --}}
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        {{-- <table id="items_table" class="table dataTable no-footer">
                            <thead>
                                <tr>
                                   
                                    <tr></tr>
                              
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="5"></th>
                                    <th style="text-align:right">Total:</th>
                                    <th id="total_amount"></th>
                                    <th id="cash_footer"></th>
                                    <th id="bank_footer"></th>
                                    <th id="tpa_footer"></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table> --}}
                        <table id="items_table" class="table dataTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer Code</th>
                                    <th>Customer Name</th>
                                    <th>Date</th>
                                    <th>Payment Method</th>
                                    <th>Payment Type</th>
                                    <th>Total Amount</th>
                                    <th>Cash</th>
                                    <th>Bank</th>
                                    <th>TPA</th>
                                    <th>Sub Group</th>
                                    <th>User Name</th>
                                    {{-- dynamic invoice columns will be injected here --}}
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="5"></th>
                                    <th style="text-align:right">Total:</th>
                                    <th id="total_amount"></th>
                                    <th id="cash_footer"></th>
                                    <th id="bank_footer"></th>
                                    <th id="tpa_footer"></th>
                                    <th colspan="2"></th>
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
        let itemsTable;
    
        function buildInvoiceColumns(count) {
            let invoiceColumns = [];
            for (let i = 1; i <= count; i++) {
                invoiceColumns.push(
                    { 
                        "data": null, 
                        "title": `Invoice No ${i}`,
                        "render": function (data, type, row) {
                            return row.invoices && row.invoices[i-1] ? row.invoices[i-1].invoice_no : '-';
                        },
                        "createdCell": function (td) { $(td).addClass('text-left'); }
                    },
                    { 
                        "data": null, 
                        "title": `Invoice Amount ${i}`,
                        "render": function (data, type, row) {
                            return row.invoices && row.invoices[i-1] ? row.invoices[i-1].inv_amount : '-';
                        },
                        "createdCell": function (td) { $(td).addClass('text-right'); }
                    },
                    { 
                        "data": null, 
                        "title": `Invoice Date ${i}`,
                        "render": function (data, type, row) {
                            if (row.invoices && row.invoices[i-1]) {
                                const date = new Date(row.invoices[i-1].invoice_date);
                                return date.toISOString().split('T')[0];
                            }
                            return '-';
                        },
                        "createdCell": function (td) { $(td).addClass('text-left'); }
                    }
                );
            }
            return invoiceColumns;
        }
    
        function initDataTable(invoiceCount) {
            if ($.fn.DataTable.isDataTable('#items_table')) {
                $('#items_table').DataTable().destroy();
                $('#items_table thead').empty();
            }
    
            let baseColumns = [
                { "data": 'id', "title": "ID", "createdCell": (td) => $(td).addClass('text-left') },
                { "data": 'customer_code', "title": "Customer Code", "createdCell": (td) => $(td).addClass('text-left') },
                { "data": 'card_name', "title": "Customer Name", "createdCell": (td) => $(td).addClass('text-left') },
                { 
                    "data": 'created_at', "title": "Date",
                    "render": function (data) {
                        if (data) {
                            const date = new Date(data);
                            return date.toISOString().split('T')[0];
                        }
                        return '-'; 
                    },
                    "createdCell": (td) => $(td).addClass('text-left')
                },
                { "data": 'payment_method', "title": "Payment Method", "createdCell": (td) => $(td).addClass('text-left') },
                { 
                    "data": 'payment_type', "title": "Payment Type",
                    "render": (data) => data ? data : '-',
                    "createdCell": (td) => $(td).addClass('text-left')
                },
                { "data": 'total_amount', "title": "Total Amount", "createdCell": (td) => $(td).addClass('text-right') },
                { 
    data: 'cash', 
    title: "Cash",
    render: data => (parseFloat(data || 0)).toFixed(2),
    createdCell: td => $(td).addClass('text-right')
},
{ 
    data: 'online', 
    title: "Bank",
    render: (data, type, row) => row.bank_tpa === 'bank' ? (parseFloat(data || 0)).toFixed(2) : '0.00',
    createdCell: td => $(td).addClass('text-right')
},
{ 
    data: 'online', 
    title: "TPA",
    render: (data, type, row) => row.bank_tpa === 'tpa' ? (parseFloat(data || 0)).toFixed(2) : '0.00',
    createdCell: td => $(td).addClass('text-right')
},

                { "data": 'sub_grp', "title": "Sub Group", "createdCell": (td) => $(td).addClass('text-left') },
                { "data": 'user_name', "title": "User Name", "createdCell": (td) => $(td).addClass('text-left') }
            ];
    
            itemsTable = $('#items_table').DataTable({
                dom: '<"html5buttons"B>tp',
                processing: true,
                serverSide: true,
                pageLength: 10,  // default
                ajax: {
                    url: "{{ url('payments-reports-json') }}",
                    type: "POST",
                    data: function (d) {
                        // extend DataTables params with your filters
                        return $.extend({}, d, {
                            search: $('#search_items').val(),
                            start_date: $('#start_date').val(),
                            end_date: $('#end_date').val(),
                            sub_grp_search: $('#sub_grp_search').val(),
                            pay_method: $('#pay_method').val(),
                            length: $('#page_length').val(),
                            _token: "{{ csrf_token() }}" // CSRF token for Laravel
                        });
                    },
                    "dataSrc": function (json) {
                        if (json.avgInvoiceCount && json.avgInvoiceCount !== invoiceCount) {
                            initDataTable(json.avgInvoiceCount);
                            return [];
                        }
                        return json.data;
                    }
                },
                "columns": baseColumns.concat(buildInvoiceColumns(invoiceCount)),
    
                "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                    var page = this.api().page.info().page;
                    var length = this.api().page.info().length;
                    var index = (page * length + (iDisplayIndex + 1));
                    $('td:eq(0)', nRow).html(index);
                },
    
               
                
                "drawCallback": function(settings) {
                    var api = this.api();
                    var data = api.rows({ page: 'current' }).data();

                    let totalAmount = 0, cash = 0, bank = 0, tpa = 0;

                    data.each(function(row) {
                        totalAmount += parseFloat(row.total_amount || 0);
                        cash += parseFloat(row.cash || 0);
                        if (row.bank_tpa === 'bank') {
                            bank += parseFloat(row.online || 0);
                        } else if (row.bank_tpa === 'tpa') {
                            tpa += parseFloat(row.online || 0);
                        }
                    });

                    $('#total_amount').text(totalAmount.toFixed(2));
                    $('#cash_footer').text(cash.toFixed(2));
                    $('#bank_footer').text(bank.toFixed(2));
                    $('#tpa_footer').text(tpa.toFixed(2));
            },
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Export Excel',
                    footer: true, // ⚡ this works for PDF/Print only
                  
                }
            ]

            });
    
            // Filters
            $('#search_items').on('keyup', function() { itemsTable.draw(); });
            $('#page_length').change(function() { itemsTable.page.len($(this).val()).draw(); });
            $('#start_date, #end_date').on('change', function() { itemsTable.draw(); });
            $('#sub_grp_search, #pay_method').on('change', function() { itemsTable.draw(); });
    
        }
    
        initDataTable(0);
    });
</script>

@endpush
