@extends('layouts.app')
<style>
    .viewInvoice {
        height: 30px;
        width: 150px;
        background-color: #3c8dbc;
    }
    .add-new-btn{
        width: 150px;
        height: 30px;
        margin-top:24px;
    }
    .form-control.search{
        height:30px;
    }
</style>
@section('content')
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
            <h4 class="card-title">SALES ORDER LIST</h4>
            <div class="row">
                    <div class="col-md-12"> <!-- MainContent Col-12 -->
                    <div class="row cust_data_form">
                            <div class="col-md-1">
                                <div class="form-group">
                                    <select class="form-control" style="width: 100%;margin-top:26px" id="branch_count">
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3 margin pull-right no-m-top">
                                <div class="input-group">
                                    <input type="text" class="form-control search no-border-right" id="search_user"
                                        placeholder="Search by customer name..." style="margin-top:26px">
                                </div>
                            </div>

                            <div class="col-md-3 margin pull-right no-m-top">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date"
                                        name="start_date
                                    date"
                                        value="">
                                </div>
                            </div>
                            <div class="col-md-3 margin pull-right no-m-top">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date"
                                        name="end_date
                                    date"
                                        value="">
                                </div>
                            </div>
                            <div class="col-md-2 margin pull-right no-m-top">
                                <button class="nav-link btn btn-primary btn-link add-new-btn animated-border"
                                    onclick="location.href='{{ url('order-generating') }}'">
                                    Create Sales Order
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="loading-spinner" style="display:none;">
                                    <div class="jumping-dots-loader">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="quotations_table" class="table dataTable no-footer">
                                        <thead>
                                        <tr>
                                                <th>#</th>
                                                <th>Document Number</th>
                                                <th>Date</th>
                                                <th>Customer Code</th>
                                                <th>Customer Name</th>
                                                <th>Group Name</th>
                                                <th>Reference Number</th>
                                                <th>Sales Type</th>
                                                <th>User Id</th>
                                                <th>Total without Tax</th>
                                                <th>Tax Value</th>
                                                <th>Total with Tax</th>                                                <th>Sales Employee</th>
                                                <th>Status</th>
                                                {{-- <th>Sales Order Number</th> --}}
                                                <th>Print</th>
                                                <th>User Name</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div> <!-- MainContent Col-12 -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var userRole = 1; // Assuming role 1 is Admin
            var buttons = [];

            if (userRole === 1) {
                buttons = [{
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

            var QuotationsTable = $('#quotations_table').DataTable({
                "dom": '<"html5buttons"B>tp',
                "bServerSide": true,
                "serverSide": true,
                "buttons": buttons,
                "processing": true,
                "bRetrieve": true,
                "paging": true,
                "ordering": true,
                "ajax": {
                    "url": '{{ url('/sales-order-json') }}',
                    "type": "GET",
                    "data": function(d) {
                        d.branch_count = $('#branch_count').val() || '';
                        d.search_user = $('#search_user').val() || '';
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    },
                    "beforeSend": function() {
                        $('.loading-spinner').show();
                    },
                    "complete": function() {
                        $('.loading-spinner').hide();
                    }
                },
                "columns": [
                    {
                        "data": "id",
                        "name": "id",
                        "render": function(data, type, row) {
                            var url = `pdf/sales/sales_copy_${data}.pdf`;
                            return `<a href="${url}" target="_blank">${data}</a>`;
                        }
                    },
                    {
                        "data": "sales_invoice_no",
                        "name": "sales_invoice_no"
                    },
                    {
                        "data": "order_date",
                        "name": "order_date"
                    },

                    {
                        "data": "customer_id",
                        "name": "customer_id",
                        "render": function(data, type, row, meta) {
                            return '<a href="' + '{{ url('order-payments-list') }}/' + row.id +
                                '">' + data + '</a>';
                        }
                    },
                    {
                        "data": "card_name",
                        "name": "card_name"
                    },
                    {
                        "data": "grp_name",
                        "name": "grp_name"
                    },
                    {
                        "data": "sales_ref_no",
                        "name": "sales_ref_no"
                    },
                    {
                        "data": "sales_type",
                        "name": "sales_type"
                    },
                    {
                        "data": "user_id",
                        "name": "user_id"
                    },
                    {
                        "data": "total_price",
                        "render": function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        "data": "total_tax",
                        "render": function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        "data": "total_with_tax_sum",
                        "render": function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },

                    {
                        "data": "name",
                        "name": "name"
                    },

                    {
                        "data": "status_display",
                        "name": "status_display"
                    },
                    // {
                    //     "data": "sales_invoice_no",
                    //     "name": "sales_invoice_no"
                    // },
                    {
                        "data": "id",
                        "render": function(data, type, row) {
                            return `<button class="btn btn-primary viewInvoice" onclick="viewInvoice('${data}')">View Sales Order</button>`;
                        }
                    },
                    {
                        "data": "code",
                        "name": "code"
                    },

                ],
                "order": [
                    [1, "asc"]
                ],
                "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    var page = this.fnPagingInfo().iPage;
                    var length = this.fnPagingInfo().iLength;
                    var index = (page * length + (iDisplayIndex + 1));
                    // $('td:eq(0)', nRow).html(index);
                },
            });

            $('#search_user').on('keyup', function() {
                QuotationsTable.draw();
            });

            $('#branch_count').change(function() {
                QuotationsTable.page.len($(this).val()).draw();
            });
            $('#start_date, #end_date').on('change', function() {
                QuotationsTable.draw();
            });
        });

        function viewInvoice(id) {
            var url = '{{ url('pdf/sales/sales_copy_') }}' + id + '.pdf';
            window.open(url, '_blank');
        }
    </script>
@endpush
