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
                                <option value="100000000">All</option>
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
                <div class="col-md-2">
                    <select class="form-control fields" id="doc_num_search" name="doc_num_search" style="margin-top: 24px;">
                        <option value="">Select Sap Sync Doc</option>
                        <option value="1">Synced</option>
                        <option value="2">Pending</option>
                    </select>
                </div>
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
                                    <th>Sap Status</th>
                                    <th>Sap Updated Date</th>
                                    <th>Doc Number</th>
                                    <th>Sap Error Msg</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="6">Total:</th>
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
    $(document).ready(function(){
        $('#search_ware_house').select2({
            placeholder: "Select Store",
            allowClear: true,
            width: '100%'
        });
    });
</script>
<script>
    $(document).ready(function() {
        var itemsTable = $('#items_table').DataTable({
            "dom": '<"html5buttons"B>tp', 
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ url('admin-reports-payments-json') }}", 
                "type": "GET",
                "data": function(d) {
                    d.search = $('#search_items').val();
                    d.length = $('#page_length').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.sub_grp_search = $('#sub_grp_search').val();
                    d.pay_method = $('#pay_method').val();
                    //d.search_ware_house = $('#search_ware_house').val();
                    d.doc_num_search = $('#doc_num_search').val();
                    d.search_ware_house = $('#search_ware_house').val() ? $('#search_ware_house').val().join(',') : ''; 
                }
            },
            "columns": [
                { "data": 'id', "title": "ID", 
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                 },
                { "data": 'customer_code', "title": "Customer Code" ,
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                 },
                { "data": 'card_name', "title": "Customer Name" ,
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                 },
                {  "data": 'created_at',
                    "title": "Date",
                    "render": function (data, type, row) {
                        if (data) {
                            const date = new Date(data);
                            // Format the date as 'YYYY-MM-DD'
                            const formattedDate = date.toISOString().split('T')[0];
                            return formattedDate;
                        }
                        return '-'; 
                    },
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                },
                { "data": 'payment_method', "title": "Payment Method" ,
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                },
                { "data": 'payment_type', "title": "Payment Type" ,
                    "render": function (data, type, row) {
                        return data ? data : '-'; 
                    },
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                },
                { "data": 'total_amount', "title": "Total Amount" ,
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-right');
                    },
                   

                 },
                
                { "data": 'cash', "title": "Cash" ,
                    "render": function (data, type, row) {
                        return data ? data : '0'; 
                    },
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-right');
                    }

                },
                //{ "data": 'online', "title": "Bank" },
                //{ "data": 'online', "title": " TPA" },
                { "data": 'online', "title": "Bank", "render": function (data, type, row) {
                        return row.bank_tpa === 'bank' ? `<span>${data}</span>` : 0;
                    },
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-right');
                    }
                 },
                { "data": 'online', "title": "TPA", "render": function (data, type, row) {
                    return row.bank_tpa === 'tpa' ? `<span>${data}</span>` : 0;
                    },
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-right');
                    }
                },
                { "data": 'sub_grp', "title": "Sub Group" ,
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                },
                { "data": 'user_name', "title": "User Name", 
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                 },
                 {
                    "data": "is_sap_updated",
                    "name": "is_sap_updated",
                    "render": function(data, type, row) {
                        return data === 1 ? "Completed" : "Pending";
                    },
                    "createdCell": function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                        $(td).css({
                            "font-weight": "bold",
                            "font-size": "14px"
                        })
                        if (cellData === 1) {
                            $(td).css("color", "green" );
                        } else {
                            $(td).css("color", "#FFC107"); 
                        }
                    }
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
            "drawCallback": function(settings) {
                var api = this.api();
                var data = api.rows({ page: 'current' }).data();
                let totalAmount = 0;
                let excessAmount = 0;
                let cash = 0;
                let bank = 0;
                let tpa = 0;

                data.each(function(row) {
                    totalAmount += parseFloat(row.total_amount || 0);
                    excessAmount += parseFloat(row.excess_amount || 0);
                    cash += parseFloat(row.cash || 0);
                    if (row.bank_tpa === 'bank') {
                        bank += parseFloat(row.online || 0);
                    } else if (row.bank_tpa === 'tpa') {
                        tpa += parseFloat(row.online || 0);
                    }
                });

                // Update the footer
                $('#total_amount').text(totalAmount.toFixed(2));
                $('#excess_amount_footer').text(excessAmount.toFixed(2));
                $('#cash_footer').text(cash.toFixed(2));
                $('#bank_footer').text(bank.toFixed(2));
                $('#tpa_footer').text(tpa.toFixed(2));
            }

        });

        $('#search_items').on('keyup', function() {
            itemsTable.draw();
        });

        $('#page_length').change(function() {
            itemsTable.page.len($(this).val()).draw();
        });

        $('#start_date, #end_date').on('change', function() {
            itemsTable.draw();
        });

        $('#sub_grp_search').on('change', function() {
            itemsTable.draw();
        });
        $('#pay_method').on('change', function() {
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
