@extends('layouts.app')
<style>
    .viewCredit {
        height: 30px;
        width: 120px;
        background-color: #3c8dbc;
    }
    .add-new-btn{
        width: 150px;
        height: 30px;
        margin-top:24px;
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

.sapPost{
    height:30px;
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
                <h4 class="card-title">DIRECT PAYMENT LIST</h4>
                <div class="row">
                    <div class="col-md-12"> <!-- MainContent Col-12 -->
                        <div class="row cust_data_form">
                            <div class="col-md-1">
                                <div class="form-group">
                                    <select class="form-control" style="width: 100%; margin-top:26px;" id="branch_count">
                                        <option value="10"  selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3 margin pull-right no-m-top">
                                <div class="input-group">
                                    <input type="text" class="form-control search no-border-right" id="search_user"
                                        placeholder="Search by customer name..." style="margin-top:26px;" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date"
                                        name="start_date
                                date" value="" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date"
                                        name="end_date
                                date" value="" required>
                                </div>
                            </div>
                            <div class="col-md-2 margin pull-right no-m-top">
                            <!--<button class="nav-link btn btn-primary btn-link add-new-btn" onclick="location.href='{{url('new-quotation')}}'">
                                Create Invoice
                            </button>-->
                                <button class="nav-link btn btn-primary btn-link add-new-btn" onclick="location.href='{{url('direct-payments')}}'">
                                   Create Payments 
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
                                    <table id="credit_note_table" class="table dataTable no-footer">
                                        <thead>
                                            <tr>
                                                <th>Sno</th>
                                                <th>DocNo</th>
                                                <th>DocDate</th>
                                                <th>Customer Code</th>
                                                <th>Customer Name</th>
                                                <th>Group Name</th>
                                                <th>Payment Method</th>
                                                <th>Payment Total</th>
                                                <th>Sap Updated</th>
                                                <th>Sap Updated Date</th>
                                                <th>Sap Updated Id</th>
                                                <th>SAP Error Message</th>
                                                <th>Post Sap</th>
                                                <th>Approval Status</th>
                                                <th>Approval At</th>
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

            var QuotationsTable = $('#credit_note_table').DataTable({
                "dom": '<"html5buttons"B>tp',
                "bServerSide": true,
                "serverSide": true,
                "buttons": buttons,
                "processing": true,
                "bRetrieve": true,
                "paging": true,
                "ordering": true,
                "ajax": {
                    "url": '{{ url('direct-payments-json') }}',
                    "type": "GET",
                    "data": function(d) {
                        return $.extend({}, d, {
                            'branch_count': $('#branch_count').val() || '',
                            "search_user": $('#search_user').val() || '' ,
                            "start_date": $('#start_date').val() || '' ,
                            "end_date": $('#end_date').val() || '' ,

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
                        "data": "id",
                        "name": "id",
                    },

                    {
                        "data": "created_at",
                        "name": "created_at",
                        "render": function(data) {
                            return data.split(" ")[0];
                        }
                    },
                    {
                        "data": "card_code",
                        "name": "card_code",
                    },
                    {
                        "data": "card_name",
                        "name": "card_name",
                    },
                    {
                        "data": "grp_name",
                        "name": "grp_name",
                    },
                    {
                        "data": "payment_method",
                        "name": "payment_method",
                    },
                    {
                        "data": "amount",
                        "name": "amount",
                        "render": function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        "data": "is_sap_updated",
                        "name": "is_sap_updated",
                        "createdCell": function (td, cellData, rowData, row, col) {
                            $(td).addClass('text-left').css("font-weight", "bold");
                            
                            if (cellData == 0) {
                                $(td).text("Pending").css("color", "orange"); 
                            } else if (cellData == 1) {
                                $(td).text("Completed").css("color", "green"); 
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
                        "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                    },
                    {
                        "data": "sap_created_id",
                        "name": "sap_created_id",
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
                            },
                            "createdCell": function (td, cellData, rowData, row, col) {
                            $(td).addClass('text-left');
                        }
                    },
                    {
                        "data": "id",
                        "name": "sap_post",
                        "render": function(data, type, row) {
                            let disabledAttr = row.sap_created_id > 0 ? 'disabled' : '';
                            if(row.is_payment_approved === 1){
                                return `
                                    <button class="btn btn-primary sapPost" 
                                        onclick="sapPost(${row.id} , '${row.payment_method}');" ${disabledAttr}>
                                        Post SAP
                                    </button>
                                    <div id="processing-message-${row.id}" class="processing-message" 
                                        style="display: none; font-weight: bold; color: blue;">
                                        Processing... Please wait
                                    </div> `;
                            } else {
                                return `
                                 <button class="btn btn-danger sapPost"> - </button>`
                            }

                        },
                        "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                    },
                    {
                        "data": "is_payment_approved",
                        "name": "is_payment_approved",
                        "render": function(data, type, row) {
                            let html = '';
                            if (userRole == 1) {
                                if (row.is_payment_approved == 0) {
                                    html += `
                                        <button class="btn btn-success btn-sm viewCredit" 
                                            onclick="updatePaymentsStatus('${row.id}' , 'accept')">
                                            Approve
                                        </button>
                                        <button class="btn btn-danger btn-sm viewCredit" 
                                            onclick="updatePaymentsStatus('${row.id}' , 'reject')">
                                            Reject
                                        </button>
                                    `;
                                } else if (row.is_payment_approved == 1){
                                    html += `
                                        <button class="btn btn-warning btn-sm viewCredit" disabled>
                                            Approved
                                        </button>
                                    `;
                                } else if (row.is_payment_approved == 2){
                                    html += `
                                        <button class="btn btn-danger btn-sm viewCredit" disabled>
                                            Rejected
                                        </button>
                                    `;
                                }
                            }  else {
                                    html += `
                                        <button class="btn btn-primary btn-sm viewCredit" disabled>
                                            No Payments Request
                                        </button>
                                    `;
                            }
                                return html; 
                        },
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-left');
                        }
                    },
                    {
                        "data": "approved_at",
                        "name": "approved_at",
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                return data ? data.split(' ')[0] : '';
                            }
                            return data;
                        },
                    },
                ],
                "order": [
                    [1, "asc"]
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
        function viewCredit(credit_no) {
            var url = '{{ url('pdf/credits/credit_copy') }}' + credit_no + '.pdf';
            window.open(url, '_blank');
        }

        //function sapPost(id, payment_method) {

        //    $('.loading-spinner').show();
        //    $('.processing-message').text('Processing...').show();

        //    let url = ''; 

        //    if (payment_method == 'cash') {
        //        url = public_path + '/cash_payments_post_web/' + id;
        //        console.log('url', url);
        //    }
        //    if (payment_method == 'tpa') {
        //        url = public_path + '/tpa_payments_post_web/' + id;
        //    }
        //    if (payment_method == 'bank') {
        //        url = public_path + '/bank_payments_post_web/' + id;
        //    }

        //    if (url) {
        //        $('.loading-spinner').show();
        //        fetch(url, {
        //            method: 'GET',
        //            headers: {
        //                'Accept': 'application/json',
        //                'X-Requested-With': 'XMLHttpRequest'
        //            }
        //        })
        //        .then(response => {
        //            if (response.ok) {
        //                return response.json();
        //            } else {
        //                throw new Error('Failed to Post SAP');
        //            }
        //        })
        //        .then(data => {
        //            alert('Post SAP successfully!');
                   
        //        })
        //        .catch(error => {
        //            console.error('Error:', error);
        //        })
        //        .finally(() => {
        //            $('.loading-spinner').hide();
        //            $('.processing-message').hide();
        //        });
        //    } else {
        //        console.error('Invalid payment method or URL');
        //    }
        //}
        function sapPost(id, payment_method) {
    let url = ''; 

    console.log(payment_method);


    if (payment_method == 'cash') {
        url = public_path + '/cash_payments_post_web/' + id;
    }
    if (payment_method == 'tpa' || payment_method == 'online') {
        url = public_path + '/tpa_payments_post_web/' + id;
    }
    if (payment_method == 'tpa, cash') {

        url = public_path + '/sap_cash_tpa_web/' + id;
        //console.log("BOTH");   
    }
    if(payment_method == 'bank') {
       url = public_path + '/bank_payments_post_web/' + id;
    }

    if (url) {
        // Show processing message
        $(`#processing-message-${id}`).show();

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            } else {
                throw new Error('Failed to Post SAP');
            }
        })
        .then(data => {
            $('.processing-message').text('Post SAP successfully!');
            setTimeout(() => $('.processing-message').fadeOut(), 3000); // Hide message after 3 sec
        })
        .catch(error => {
            $('.processing-message').text('Error: ' + error.message).css('color', 'red');
            console.error('Error:', error);
        });
    } else {
        console.error('Invalid payment method or URL');
    }
}
        function updatePaymentsStatus(id, action) {
            let remark = '';
            $.ajax({
                url: '{{ url("approve-payments-request") }}/' + id + '/' + action,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                data: { remarks: remark },
                success: function (data) {
                    if (data.success) {
                        alert(data.message);
                        // location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                },
                error: function () {
                    alert('Server error.');
                }
            });
        }    

    </script>
@endpush
