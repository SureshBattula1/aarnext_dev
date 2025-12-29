@extends('layouts.app')
<style>
    .viewInvoice {
        height: 30px;
        width: 120px;
        background-color: #3c8dbc;
        color: white;
        border: none;
        cursor: pointer;
    }

    .add-new-btn {
        width: 150px;
        height: 30px;
        margin-top:24px;
    }

    .loading-spinner {
        text-align: center;
    }
    .input-group.align{
        height:40px;
    }
</style>
@section('content')
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title" style="text-align:center;">QUOTATION LIST</h4>
                <div class="row">
                    <div class="col-md-12"> <!-- MainContent Col-12 -->
                        <div class="row cust_data_form">
                            <div class="col-md-1">
                                <div class="form-group">
                                    <select class="form-control" style="width: 100%; margin-top:26px;" id="branch_count">
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3 margin pull-right no-m-top">
                                <div class="input-group align">
                                    <input type="text" class="form-control no-border-right height:10px" id="search_user"
                                        placeholder="Search by customer name..." style="margin-top:26px;" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3 margin pull-right no-m-top">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date
                                    date" value="" >
                                </div>
                            </div>
                            <div class="col-md-3 margin pull-right no-m-top">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date
                                    date" value="" >
                                </div>
                            </div>
                            <div class="col-md-2 margin pull-right no-m-top">
                                <button class="nav-link btn btn-primary btn-link add-new-btn" onclick="location.href='{{ url('items-quotation') }}'">
                                    Create Quotation
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
                                                {{-- <th>User Id</th> --}}
                                                <th>Total without Tax</th>
                                                <th>Tax Value</th>
                                                <th>Total with Tax</th>                                                <th>Sales Employee</th>
                                                <th>Status</th>
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
        $(document).ready(function () {
            var userRole = 1; // Assuming role 1 is Admin
            var buttons = [];

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

            var QuotationsTable = $('#quotations_table').DataTable({
                "dom": '<"html5buttons"B>tp',
                "serverSide": true,
                "processing": true,
                "buttons": buttons,
                "paging": true,
                "ordering": true,
                "ajax": {
                    "url": '{{ url('/itemsquotation-json') }}',
                    "type": "GET",
                    "data": function (d) {
                        d.branch_count = $('#branch_count').val() || '';
                        d.search_user = $('#search_user').val() || '';
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();

                    },
                    "beforeSend": function () {
                        $('.loading-spinner').show();
                    },
                    "complete": function () {
                        $('.loading-spinner').hide();
                    }
                },
                "columns": [
                    {
                        "data": "id",
                        "name": "id",
                        "render": function(data, type, row) {
                            var url = `pdf/quotations/quotation_${data}.pdf`;
                            return `<a href="${url}" target="_blank">${data}</a>`;
                        }
                    },

                    {
                        "data": "quotation_pdf_no"
                    },
                    {
                        "data": "date"
                    },
                    {
                        "data": "customer_code",
                        "render": function(data, type, row) {
                            return `<a href="{{ url('quotation-payment-show') }}/${row.id}">${data}</a>`;
                        }
                    },
                    {
                        "data": "card_name"
                    },
                    {
                        "data": "grp_name"
                    },
                    {
                        "data": "quotation_ref_no"
                    },
                    {
                        "data": "sales_type"
                    },
                    // {
                    //     "data": "user_id"
                    // },
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
                        "data": "name"
                    },
                    {
                        "data": "status_display",

                    },

                    {
                        "data": "id",
                        "render": function(data, type, row) {
                            return `<button class="btn btn-primary viewInvoice" onclick="viewInvoice('${data}')">View Quotation</button>`;
                        }
                    },
                    {
                        "data": "code"
                    },
                ]
            });

            $('#search_user').on('keyup', function () {
                QuotationsTable.draw();
            });

            $('#branch_count').change(function () {
                QuotationsTable.page.len($(this).val()).draw();
            });
            $('#start_date, #end_date').on('change', function() {
            QuotationsTable.draw();
        });
        });
w
        function viewInvoice(quotationId) {
            console.log(quotationId);
            var url = `{{ url('pdf/quotations/quotation_') }}${quotationId}.pdf`;
            window.open(url, '_blank');
        }
    </script>
@endpush
