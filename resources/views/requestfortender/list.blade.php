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
        margin-top: 24px;
    }
    .sapPost{
        height:35px;
        width: 150px;
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
        background-color: #0056b3;
        /* Optional hover effect */
    }

    .form-control.search {
        height: 30px;
    }
</style>
@section('content')
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">RFT LIST</h4>
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
                                <div class="input-group">
                                    <input type="text" class="form-control search no-border-right"
                                        style="margin-top:26px;" id="search_user" placeholder="Search by customer name..."
                                        autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-2 margin pull-right no-m-top">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date"
                                        name="start_date
                                    date" value="">
                                </div>
                            </div>
                            <div class="col-md-2 margin pull-right no-m-top">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date"
                                        name="end_date
                                    date" value="">
                                </div>
                            </div>
                            <div class="col-md-2" style="margin-top:22px;">
                                <select class="form-control input-sm" name="web_status" id="web_status" >
                                    <option value=""> Select Web Status</option>
                                    <option value="1">From Status Pending</option>
                                    <option value="2">From Status Completed</option>
                                    <option value="3">To Status Pending</option>
                                    <option value="4">To Status Completed</option>
                                    <option value="5">RFT Completed</option>
                                </select>
                            </div> <div class="col-md-2" style="margin-top:22px;">
                               <select class="form-control input-sm" name="sap_status" id="sap_status" >
                                    <option value="">Select SAP Status</option>
                                    <option value="1">From SAP Post Pending </option>
                                    <option value="2">From SAP Post Completed </option>
                                    <option value="3">To SAP Post Pending</option>
                                    <option value="4">To SAP Post Completed</option>
                                    <option value="5">Both SAP Post Completed</option>
                                </select>
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

                                <div class="modal fade modal-lg" id="payment_details_modal" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Payment Details</h5>
                                                <button type="button" class="close" data-bs-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Customer Code</th>
                                                            <th>Total Price</th>
                                                            <th>Paid Amount</th>
                                                            <th>Excess Payment</th>
                                                            <th>Payment Method</th>
                                                            <th>Payment Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="payment-details-table">
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="quotations_table" class="table dataTable no-footer">
                                        <thead>
                                        <tr>
                                                <th>#</th> 
                                                <th>RFT Number</th> 
                                                <th>Doc Entry</th>  
                                                <th>From Store</th>
                                                <th>From Web Status</th>
                                                <th>To Store</th>
                                                <th>To Web Status</th>
                                                <th>Sap Form Created ID</th>
                                                <th>Form SAP Status</th>
                                                <th>From Sap Post</th>
                                                <th>Form Error Msg</th>
                                                <th>Sap To Created ID</th>
                                                <th>To SAP Status</th>
                                                <th>To Sap Post</th>
                                                <th>To Error Msg</th>
                                                <th>RFT Status</th>
                                                <th>Status Update</th>
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
                    "url": '{{ url('/rent-for-tendors-json') }}',
                    "type": "GET",
                    "data": function(d) {
                        return $.extend({}, d, {
                            'branch_count': $('#branch_count').val() || '',
                            "search_user": $('#search_user').val() || '',
                            "start_date": $('#start_date').val() || '',
                            "end_date": $('#end_date').val() || '',
                            "sales_type_search": $('#sales_type_search').val() || '',
                            "sub_grp_search": $('#sub_grp_search').val() || '',
                            "status_search": $('#status_search').val() || '',
                            "web_status": $('#web_status').val() || '',
                            "sap_status": $('#sap_status').val() || '',
                        });
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
    },
    {
        "data": "rft_number",
        "name": "rft_number",
        "render": function(data, type, row, meta) {
            const warehouse = '{{ auth()->user()->ware_house }}';
            const baseUrl = '{{ url('rft-items-list') }}';
            const detailsUrl = '{{ url('rent-tender-details') }}';
            if(row.cancelled_status === 1){
                if ((warehouse === row.from_store && row.from_status === 1) || 
                    (warehouse === row.to_store && row.to_status === 1)) {
                    return `<a href="${baseUrl}/${row.id}">${data}</a>`;
                } else {
                    return `<a href="${detailsUrl}/${row.id}">${data}</a>`;
                }
            } else {
                return `<span style="color: orange; font-weight: bold;">${data}</span>`
            }
            
        }
    },
    {
        "data": "rft_entry",
        "name": "rft_entry",
    },
    {
        "data": "from_store",
        "name": "from_store"
    },
    {
        "data": "from_status",
        "name": "from_status",
        "render": function(data) {
            switch (data) {
                case 0: return '<span style="color: orange; font-weight: bold;">Pending</span>';
                case 1: return '<span style="color: green; font-weight: bold;">Completed</span>';
                default: return '<span style="color: gray; font-weight: bold;">Unknown</span>';
            }
        }
    },
    {
        "data": "to_store",
        "name": "to_store"
    },
    {
        "data": "to_status",
        "name": "to_status",
        "render": function(data) {
            switch (data) {
                case 0: return '<span style="color: orange; font-weight: bold;">Pending</span>';
                case 1: return '<span style="color: green; font-weight: bold;">Completed</span>';
                default: return '<span style="color: gray; font-weight: bold;">Unknown</span>';
            }
        }
    },
    {
        "data": "from_sap_created_id",
        "name": "from_sap_created_id"
    },
    {
        "data": "from_sap_update",
        "name": "from_sap_update",
        "render": function(data) {
            switch (data) {
                case 0: return '<span style="color: orange; font-weight: bold;">Pending</span>';
                case 1: return '<span style="color: green; font-weight: bold;">Completed</span>';
                default: return '<span style="color: gray; font-weight: bold;">Unknown</span>';
            }
        }
    },
    {
        "data": "id",
        "name": "from_sap_post",
        "render": function(data, type, row) {
            const userCode = '{{ auth()->user()->code }}';
            if(row.cancelled_status === 1){
                if (['ADMIN', 'AA-ACC2', 'AA-ACC3', 'AA-AAC8', 'AA-ACC9', 'AA-ACC10'].includes(userCode)) {
                    const disabledAttr = row.from_sap_created_id > 0 ? 'disabled' : '';
                    return `
                        <button id="from-post-btn-${row.id}" class="btn btn-primary sapPost" onclick="fromSapPost(${row.id});" ${disabledAttr}>
                            From Post SAP
                        </button>
                        <div id="from-processing-message-${row.id}" class="processing-message" 
                            style="display: none; font-weight: bold; color: blue;">
                            Processing... Please wait
                        </div>`;
                } else {
                    return '';
                }
            } else {
                return '';
            }
        },
        "createdCell": function(td) {
            $(td).addClass('text-left');
        }
    },
    {
        "data": "sap_error_msg_from",
        "name": "sap_error_msg_from"
    },
    {
        "data": "to_sap_created_id",
        "name": "to_sap_created_id"
    },
    {
        "data": "to_sap_update",
        "name": "to_sap_update",
        "render": function(data) {
            switch (data) {
                case 0: return '<span style="color: orange; font-weight: bold;">Pending</span>';
                case 1: return '<span style="color: green; font-weight: bold;">Completed</span>';
                default: return '<span style="color: gray; font-weight: bold;">Unknown</span>';
            }
        }
    },
    {
        "data": "id",
        "name": "to_sap_post",
        "render": function(data, type, row) {
            const userCode = '{{ auth()->user()->code }}';
            if(row.cancelled_status === 1){
                if (['ADMIN', 'AA-ACC2', 'AA-ACC3', 'AA-AAC8', 'AA-ACC9', 'AA-ACC10'].includes(userCode)) {
                    const disabledAttr = row.to_sap_created_id > 0 ? 'disabled' : '';
                    return `
                        <button id="to-post-btn-${row.id}" class="btn btn-primary sapPost" onclick="toSapPost(${row.id});" ${disabledAttr}>
                            To Post SAP
                        </button>
                        <div id="to-processing-message-${row.id}" class="processing-message" 
                            style="display: none; font-weight: bold; color: blue;">
                            Processing... Please wait
                        </div>`;
                } else {
                    return '';
                }
            } else {
                return '';
            }
        },
        "createdCell": function(td) {
            $(td).addClass('text-left');
        }
    },
    {
        "data": "sap_error_msg",
        "name": "sap_error_msg"
    },
    {
        "data": "cancelled_status",
        "name": "cancelled_status",
        "render": function(data) {
            switch (data) {
                case 0: return '<span style="color: orange; font-weight: bold;">Cancelled</span>';
                case 1: return '<span style="color: green; font-weight: bold;">Active</span>';
                default: return '<span style="color: gray; font-weight: bold;">Unknown</span>';
            }
        }
    },
    {
        "data": "rft_number",
        "name": "rft_number",
        render: function(data, type, row, meta) {
            const userCode = '{{ auth()->user()->code }}';
            if (['ADMIN', 'AA-ACC2', 'AA-ACC3', 'AA-AAC8', 'AA-ACC9', 'AA-ACC10'].includes(userCode)) {
                if (row.cancelled_status === 0) {
                return `<button class="btn btn-sm btn-secondary" disabled>Closed</button>`;
                } else {
                return `<button class="btn btn-sm btn-success doc-status-btn" onclick="DocStatus(${row.rft_number});">Active</button>`;
                }
            }
            return "";
        }
    }
],

                "order": [
                    [1, "asc"]
                ],
                "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    var page = this.fnPagingInfo().iPage;
                    var length = this.fnPagingInfo().iLength;
                    var index = (page * length + (iDisplayIndex + 1));

                },
            });

            $('#search_user').on('keyup', function() {
                QuotationsTable.draw();
            });

            $('#start_date , #end_date').on('change', function() {
                QuotationsTable.draw();
            });
            $('#sap_status').on('change', function() {
                QuotationsTable.draw();
            });
            $('#web_status').on('change', function() {
                QuotationsTable.draw();
            });

        });

        $(document).on('click', '.open-payment-modal', function(e) {
            e.preventDefault();
            var invoiceId = $(this).data('invoice-id');
            openPaymentDetailsModal(invoiceId);
        });

        function openPaymentDetailsModal(invoiceId) {
            $.ajax({
                url: public_path + '/get_inv_payment_details/' + invoiceId,
                method: 'GET',
                dataType: 'json',
                success: function(result) {
                    if (result.data && result.data.length > 0) {
                        var tableContent = '';
                        result.data.forEach(function(payment) {
                            tableContent += '<tr>';
                            tableContent += '<td>' + payment.customer_id + '</td>';
                            tableContent += '<td>' + payment.total + '</td>';
                            tableContent += '<td>' + payment.payment_amount + '</td>';
                            tableContent += '<td>' + payment.exceess_payment + '</td>';
                            tableContent += '<td>' + payment.payment_method + '</td>';
                            tableContent += '<td>' + payment.payment_date + '</td>';
                            tableContent += '</tr>';
                        });
                        $('#payment-details-table').html(tableContent);
                    } else {
                        $('.modal-body').html('<p class="text-danger">No payment details found.</p>');
                    }
                },
                error: function(error) {
                    console.error("Error fetching payment details:", error);
                    $('.modal-body').html('<p class="text-danger">An unexpected error occurred.</p>');
                }
            });
            $('.modal-title').html('Payment Details - Invoice #' + invoiceId);
            $('#payment_details_modal').modal('show');
        }

        function viewInvoice(id) {
            var url = '{{ url('pdf/invoices/invoice_copy_') }}' + id + '.pdf';
            window.open(url, '_blank');
        }

        function fromSapPost(id) {
            const url = public_path + '/sap_from_rft_web/' + id;

            if (url) {
                const $message = $(`#from-processing-message-${id}`);
                const $button = $(`#from-post-btn-${id}`);

                $message.show().text('Processing... Please wait');
                $button.prop('disabled', true); 
                fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to Post SAP');
                    }
                    return response.json();
                })
                .then(data => {
                    $message.text('Post SAP successfully!');
                    // Keep button disabled
                    setTimeout(() => $message.fadeOut(), 3000);
                    $('#quotations_table').DataTable().ajax.reload(null, false);

                })
                .catch(error => {
                    console.error(error);
                    $message.text('');
                    $button.prop('disabled', false); 
                });
            } else {
                console.error('Invalid Post URL');
            }
        }

    function toSapPost(id) {
        const url = public_path + '/sap_to_rft_web/' + id;

        if (url) {
            const $message = $(`#to-processing-message-${id}`);
            const $button = $(`#to-post-btn-${id}`);

            $message.show().text('Processing... Please wait');
            $button.prop('disabled', true);

            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to Post SAP');
                }
                return response.json();
            })
            .then(data => {
                $message.text('Post SAP successfully!');
                setTimeout(() => $message.fadeOut(), 3000);
                $('#quotations_table').DataTable().ajax.reload(null, false);

            })
            .catch(error => {
                console.error(error);
                $message.text('');
                $button.prop('disabled', false);
            });
        } else {
            console.error('Invalid Post URL');
        }
    }

    function DocStatus(rftNumber){
        const baseUrl = public_path + '/rft-block';
        fetch(`${baseUrl}/${rftNumber}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
            alert('RFT Closed successfully.');
            // Optionally reload table here
            })
            .catch(error => {
            console.error('Error closing RFT:', error);
        });
    }
    </script>
@endpush
