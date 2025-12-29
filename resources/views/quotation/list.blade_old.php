@extends('layouts.app')
<style>
    .viewInvoice {
        height: 30px;
        width: 120px;
        background-color: #3c8dbc;
    }
    .add-new-btn{
        width: 150px;
        height: 30px;
    }

    .animated-border {
    position: relative;
    padding: 10px 20px;
    font-size: 16px;
    color: white;
    background-color: #007bff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    overflow: hidden;
}

.animated-border::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    /*border: 2px solid rgb(240 163 40);*/
    border: 2px solid black;
    /*box-sizing: border-box;*/
    /*z-index: 1;*/
    animation: edge-move 10s linear infinite;
    /*pointer-events: none; Allows clicks to pass through*/
}

@keyframes edge-move {
    0% {
        clip-path: polygon(0% 0%, 0% 0%, 0% 100%, 0% 100%);
    }
    25% {
        clip-path: polygon(0% 0%, 100% 0%, 100% 0%, 0% 0%);
    }
    50% {
        clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 100% 100%);
    }
    75% {
        clip-path: polygon(0% 100%, 100% 100%, 100% 100%, 0% 100%);
    }
    100% {
        clip-path: polygon(0% 0%, 0% 0%, 0% 100%, 0% 100%);
    }
}

.animated-border:hover {
    background-color: #0056b3; /* Optional hover effect */
}

.form-control.search{
    height:30px;
}
</style>
@section('content')
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">INVOICE LIST</h4>
                <div class="row">
                    <div class="col-md-12"> <!-- MainContent Col-12 -->
                        <div class="row cust_data_form">
                            <div class="col-md-1">
                                <div class="form-group">
                                    <select class="form-control" style="width: 100%;" id="branch_count">
                                        <option value="10"  selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 margin pull-right no-m-top">
                                <div class="input-group">
                                    <input type="text" class="form-control search no-border-right" id="search_user"
                                        placeholder="Search by customer name...">
                                </div>
                            </div>
                            <div class="col-md-2 margin pull-right no-m-top">
                            <!--<button class="nav-link btn btn-primary btn-link add-new-btn" onclick="location.href='{{url('new-quotation')}}'">
                                Create Invoice 
                            </button>-->
                            <button 
                                class="nav-link btn btn-primary btn-link add-new-btn animated-border"
                                onclick="location.href='{{url('new-quotation')}}'">
                                Create Invoice
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
                                                <th>Customer id</th>
                                                <th>Customer Name</th>
                                                <th>User Id</th>
                                                <th>Total</th>
                                                <th>Date</th>
                                                <th>Print</th>
                                                <th>Payment Status</th>
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
                    "url": '{{ url('/quotations-json') }}',
                    "type": "GET",
                    "data": function(d) {
                        return $.extend({}, d, {
                            'branch_count': $('#branch_count').val() || '',
                            "search_user": $('#search_user').val() || ''
                        });
                    },
                    "beforeSend": function() {
                        $('.loading-spinner').show();
                    },
                    "complete": function() {
                        $('.loading-spinner').hide();
                    }
                },
                "columns": [{
                        "data": "id",
                        "name": "id"
                    },

                    {
                        "data": "customer_id",
                        "name": "customer_id",
                        "render": function(data, type, row, meta) {
                            return '<a href="' + '{{ url('invoice-payment-show') }}/' + row.invoice_no +
                                '">' + data + '</a>';
                        }
                    },
                    {
                        "data": "card_name",
                        "name": "card_name"
                    },
                    {
                        "data": "user_id",
                        "name": "user_id"
                    },
                    {
                        "data": "total",
                        "name": "total"
                    },
                    {
                        "data": "order_date",
                        "name": "order_date"
                    },
                    {
                        "data": "invoice_no",
                        "name": "invoice_no"
                    },
                    {
                        "data": "status_display",
                        "name": "status_display"
                    }
                ],
                "order": [
                    [1, "asc"]
                ],
                "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    var page = this.fnPagingInfo().iPage;
                    var length = this.fnPagingInfo().iLength;
                    var index = (page * length + (iDisplayIndex + 1));
                    $('td:eq(0)', nRow).html(index);

                    if (aData['invoice_no']) {
                        var invoiceButton =
                            '<button class="btn btn-primary viewInvoice" onclick="viewInvoice(\'' +
                            aData['invoice_no'] + '\')">View Invoice</button>';
                        $('td:eq(6)', nRow).html(invoiceButton);
                    }
                },
            });

            $('#search_user').on('keyup', function() {
                QuotationsTable.draw();
            });

            $('#branch_count').change(function() {
                QuotationsTable.page.len($(this).val()).draw();
            });
        });

        function viewInvoice(invoice_no) {
            var url = '{{ url('pdf/invoices/invoice_copy_') }}' + invoice_no + '.pdf';
            window.open(url, '_blank');
        }
    </script>
@endpush
