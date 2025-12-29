@extends('layouts.app')

<style>
    .viewInvoice {
        height: 30px;
        width: 120px;
        background-color: #3c8dbc;
    }

    .add-new-btn {
        width: 150px;
        height: 30px;
        margin-top:24px;
    }

    .form-control.search {
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
        border: 2px solid black;
        animation: edge-move 10s linear infinite;
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
        background-color: #0056b3;
    }

    .dt-buttons {
        margin-top: 20px;
    }
</style>

@section('content')
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">EXPENSES LIST</h4> 
                <div class="row">
                    <div class="col-md-12">
                        <div class="row cust_data_form">
                            <div class="col-md-1">
                                <select class="form-control" id="branch_count" style="width:100%;margin-top:26px">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>

                            <div class="col-md-3 margin pull-right no-m-top">
                                <input type="text" class="form-control search no-border-right" id="search_user"
                                    placeholder="Search by User Id..." style="margin-top:26px" autocomplete="off">
                            </div>

                            <div class="col-md-3 margin pull-right no-m-top">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date"
                                        name="start_date
                                    date" value="">
                                </div>
                            </div>
                            <div class="col-md-3 margin pull-right no-m-top">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date"
                                        name="end_date
                                    date" value="">
                                </div>
                            </div>

                            <div class="col-md-2 margin pull-right no-m-top">
                                <button class="btn btn-primary add-new-btn" onclick="location.href='{{ url('expenses') }}'">
                                    Create Expenses
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="loading-spinner" style="display:none;">
                                <div class="jumping-dots-loader">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="expenses_table" class="table dataTable no-footer">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User Id</th>
                                        <th>User Name</th>
                                        <th>Total Amount</th>
                                        <th>Created At</th>
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

            var QuotationsTable = $('#expenses_table').DataTable({
                "dom": '<"html5buttons"B>tp',
                "bServerSide": true,
                "serverSide": true,
                "buttons": buttons,
                "processing": true,
                "bRetrieve": true,
                "paging": true,
                "ordering": true,
                "ajax": {
                    "url": '{{ url('/expenses-json') }}',
                    "type": "GET",
                    "data": function(d) {
                        return $.extend({}, d, {
                            'branch_count': $('#branch_count').val() || '',
                            "search_user": $('#search_user').val() || '',
                            "start_date": $('#start_date').val() || '',
                            "end_date": $('#end_date').val() || '',
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
                        "data": "id"
                    },
                    {
                        "data": "user_id"
                    },
                    // { "data": "name" },
                    {
                        "data": "name",
                        "render": function(data, type, row) {
                            return `<a href="./expenses-payments/${row.id}" class="user-name">${data}</a>`;
                        }
                    },
                    {
                        "data": "total_amount"
                    },
                    {
                        "data": "created_at"
                    }
                ],
                "order": [
                    [0, "asc"]
                ],
                "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    var page = this.fnPagingInfo().iPage;
                    var length = this.fnPagingInfo().iLength;
                    var index = (page * length + (iDisplayIndex + 1));
                    $('td:eq(0)', nRow).html(index);
                    if (aData['credit_no']) {
                        var invoiceButton =
                            '<button class="btn btn-primary viewCredit" onclick="viewCredit(\'' +
                            aData['credit_no'] + '\')">View Credit</button>';
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

            $('#start_date, #end_date').on('change', function() {
                    QuotationsTable.draw();
                });
        });
    </script>
@endpush
