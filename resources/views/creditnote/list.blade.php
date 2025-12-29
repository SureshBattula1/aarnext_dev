
@extends('layouts.app')
<style>
    .viewCredit {
        height: 35px;
        width: 150px;
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
.approvel-btn{
    display: flex;
    justify-content: end;

    
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
.credit-approvel-btn{
    padding: 10px;
    margin: 10px;
    display: flex;
    justify-content: end;
}
/* model */
/* 🔹 Common width for Select2 (applies to both the input and dropdown) */
.select2-container--default .select2-selection--single,
.select2-search--dropdown,
.select2-dropdown--below {
    min-width: 330px;  /* Can shrink if smaller */
    width: 330px !important; /* Fixed width */
    box-sizing: border-box;
}

/* 🔹 Keep arrow aligned to the right */
.select2-container--default .select2-selection--single .select2-selection__arrow {
    position: absolute;
    top: 50%;
    right: 8px; /* Adjust space from right */
    transform: translateY(-50%);
    pointer-events: none;
}

/* 🔹 Prevent text from overlapping the arrow */
.select2-container--default .select2-selection--single {
    padding-right: 28px !important;
}

/* 🔹 Make search field inside dropdown match width */
.select2-search--dropdown .select2-search__field {
    width: 100% !important;
    box-sizing: border-box;
}


#creditNoteModal .modal-content {
  border-radius: 8px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  padding: 1rem;
  font-family: "Roboto", sans-serif;
  color: #333;
}

#creditNoteModal .modal-header {
  border-bottom: 1px solid #e9ecef;
  padding-bottom: 0.75rem;
}

#creditNoteModal .modal-title {
  font-weight: 600;
  font-size: 1.25rem;
  color: #248afd;
}

#creditNoteModal .btn-close {
  background: transparent;
  border: none;
  font-size: 1.25rem;
  opacity: 0.7;
  transition: opacity 0.3s ease;
}

#creditNoteModal .btn-close:hover {
  opacity: 1;
}

#creditNoteModal .modal-body {
  padding-top: 1rem;
  padding-bottom: 1rem;
}

#creditNoteModal label {
  font-weight: 500;
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
  display: block;
  color: #555;
}

#creditNoteModal select.form-control,
#creditNoteModal select#customer_id,
#creditNoteModal select#invoice_id {
  width: 100%;
  padding: 0.5rem 0.75rem;
  font-size: 0.9rem;
  border: 1px solid #ced4da;
  border-radius: 4px;
  transition: border-color 0.3s ease;
}

#creditNoteModal select.form-control:focus,
#creditNoteModal select#customer_id:focus,
#creditNoteModal select#invoice_id:focus {
  border-color: #248afd;
  outline: none;
  box-shadow: 0 0 5px rgba(36, 138, 253, 0.5);
}

#creditNoteModal .modal-footer {
  border-top: 1px solid #e9ecef;
  padding-top: 0.75rem;
  display: flex;
  justify-content: flex-end;
}

#creditNoteModal .btn-success {
  background-color: #71c016;
  border-color: #71c016;
  padding: 0.5rem 1.5rem;
  font-weight: 600;
  font-size: 1rem;
  border-radius: 4px;
  transition: background-color 0.3s ease;
}

#creditNoteModal .btn-success:hover,
#creditNoteModal .btn-success:focus {
  background-color: #569211;
  border-color: #569211;
  color: #fff;
  outline: none;
  box-shadow: 0 0 8px rgba(113, 192, 22, 0.7);
}

#creditNoteModal button.btn.btn-primary {
  background-color: #248afd;
  border-color: #248afd;
  padding: 0.5rem 1.5rem;
  font-weight: 600;
  font-size: 1rem;
  border-radius: 4px;
  transition: background-color 0.3s ease;
}

#creditNoteModal button.btn.btn-primary:hover,
#creditNoteModal button.btn.btn-primary:focus {
  background-color: #1a5edb;
  border-color: #1a5edb;
  color: #fff;
  outline: none;
  box-shadow: 0 0 8px rgba(36, 138, 253, 0.7);
}

/* Responsive adjustments */
@media (max-width: 576px) {
  #creditNoteModal .modal-dialog {
    max-width: 95%;
    margin: 1.5rem auto;
  }
  #creditNoteModal .modal-content {
    padding: 0.75rem;
  }
  #creditNoteModal .btn-success,
  #creditNoteModal button.btn.btn-primary {
    width: 100%;
    padding: 0.75rem;
    font-size: 1.1rem;
  }
}

</style>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
@section('content')

@php
  $userRole = Auth::user()->role;
@endphp
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">CREDIT LIST</h4>
                <div class="row">
                    <div class="col-md-12"> <!-- MainContent Col-12 -->
                        <div class="row cust_data_form">
                            <div class="col-md-1">
                                <div class="form-group">
                                    <select class="form-control" style="width: 100%; margin-top:26px" id="branch_count">
                                        <option value="10"  selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="1000000">All</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3 margin pull-right no-m-top">
                                <div class="input-group">
                                    <input type="text" class="form-control search no-border-right" id="search_user"
                                        placeholder="Search by customer name..." style="margin-top:26px" autocomplete="off">
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
                            <button
                                class="nav-link btn btn-primary btn-link add-new-btn animated-border"
                                onclick="location.href='{{url('credit-note')}}'">
                                Credit Note
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
                                                <th>S.No</th>
                                                <th>#</th>
                                                <th>Document Number</th>
                                                <th>Date</th>
                                                <th>Customer Code</th>
                                                <th>Customer Name</th>
                                                <th>Group Name</th>
                                                <th>Reference Number</th>
                                                <th>User Id</th>
                                                <th>Total without Tax</th>
                                                <th>Tax Value</th>
                                                <th>Total with Tax</th>
                                                <th>Sales Employee</th>
                                                <th>Memo</th>
                                                <th>Invoice Doc Num</th>
                                                <th>Sales Type</th>
                                                {{-- <th>Credit Note No</th> --}}
                                                <th>Print</th>
                                                <th>Payment Status</th>
                                                <th>User Name</th>
                                                <th>Sap Updated</th>
                                                <th>Sap Created Id</th>
                                                <th>Sap Updated At</th>
                                                <th>Sap Error Message</th>
                                                <th>Post SAP</th>
                                                <th>Recreate Status</th>
                                                <th>Recreate Request Date</th>
                                                <th>Recreate Request By</th>
                                                <th>Recreate Approval Remarks</th>
                                                <th>Recreate Approved By</th>
                                                <th>Recreate Approved Date</th>
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
                    "url": '{{ url('/credit-note-json') }}',
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
               "columns": [
                    {
                        "data": null, 
                        "name": "serial_no",
                        "render": function(data, type, row, meta) {
                            return meta.row + 1; 
                        }
                    },
                    {
                        "data": "id",
                        "name": "id",
                        "render": function(data, type, row) {
                            var url = `pdf/credits/credit_copy${data}.pdf`;
                            return `<a href="${url}" target="_blank">${data}</a>`;
                        }
                    },
                    {
                        "data": "credit_no",
                        "name": "credit_no",
                    },
                    {
                        "data": "order_date",
                        "name": "order_date",
                    },

                    {
                        "data": "customer_code_display",
                        "name": "customer_code_display",
                        "render": function(data, type, row, meta) {
                            return '<a href="' + '{{ url('credit-items-list') }}/' + row.id +
                                '">' + data + '</a>';
                        }

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
                        "data": "ref_id",
                        "name": "ref_id",
                    },
                    {
                        "data": "user_id",
                        "name": "user_id",
                    },
                    {
                        "data": "total_price"
                    },
                    {
                        "data": "total_tax"
                    },
                    {
                        "data": "total_with_tax_sum"
                    },
                    {
                        "data": "name",
                        "name": "name",
                    },
                    {
                        "data": "memo",
                        "name": "memo",
                    },
                    {
                        "data": "sales_invoice_no",
                        "name": "sales_invoice_no",
                    },
                    {
                        "data": "sales_type",
                        "name": "sales_type",
                    },
                    {
                        "data": "id",
                        "render": function(data, type, row) {
                            return `<button class="btn btn-primary viewCredit" onclick="viewCredit('${data}')">View Credit Note</button>`;
                        }
                    },
                    {
                        "data": "status_display",
                        "name": "status_display",
                    },
                    {
                        "data": "code",
                        "name": "code",
                    },
                    {
                        "data": "sap_updated",
                        "name": "sap_updated",
                        "createdCell": function (td, cellData, rowData, row, col) {
                            $(td).addClass('text-left').css("font-weight", "bold");
                            
                            if (cellData == 0) {
                                $(td).text("Pending").css("color", "orange"); // Warm color for pending
                            } else if (cellData == 1) {
                                $(td).text("Completed").css("color", "green"); // Green color for completed
                            }
                        }
                    },
                    {
                        "data": "sap_created_id",
                        "name": "sap_created_id",
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                return data ? data.split(' ')[0] : '';
                            }
                            return data;
                        },
                        "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-right');
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
                            return `
                                <button class="btn btn-primary viewCredit" 
                                    onclick="sapPost(${row.id})" ${disabledAttr}>
                                     Post SAP
                                </button>`;
                        },
                            "createdCell": function (td, cellData, rowData, row, col) {
                            $(td).addClass('text-left');
                        }
                    },
                    // {
                    //     data: "id",
                    //     render: function(data, type, row) {
                    //         let html = '';

                    //         // If user is admin (role_id = 1)
                    //         if (userRole == 1) {
                    //             if (row.is_credit_approved == 1) {
                    //                 html += `
                    //                     <button class="btn btn-success btn-sm viewCredit" 
                    //                         onclick="approveCredit('${row.sales_invoice_no}')">
                    //                         Approve
                    //                     </button>
                    //                     <button class="btn btn-danger btn-sm viewCredit" 
                    //                         onclick="rejectCredit('${row.sales_invoice_no}')">
                    //                         Reject
                    //                     </button>
                    //                 `;
                    //             } else {
                    //                 html += `
                    //                     <button class="btn btn-warning btn-sm viewCredit" disabled>
                    //                         No Recredit Request
                    //                     </button>
                    //                 `;
                    //             }
                    //         }
                    //         // If not admin, show only request button when not requested
                    //         else {
                    //             if (row.credit_approval_req != 1) {
                    //                 html += `
                    //                     <button class="btn btn-warning btn-sm viewCredit" 
                    //                         onclick="requestCreditApproval('${row.sales_invoice_no}')">
                    //                         Recredit Request
                    //                     </button>
                    //                 `;
                    //             } else {
                    //                 html += `
                    //                     <button class="btn btn-secondary btn-sm viewCredit" disabled>
                    //                         Already Requested
                    //                     </button>
                    //                 `;
                    //             }
                    //         }
                    //         return html;
                    //     }
                    // }
                    
                    {
                        "data": "is_credit_approved",
                        "name": "is_credit_approved",
                        render: function(data, type, row) {
                            let html = '';

                            if (row.is_credit_approved == 1) {
                                html = '<button class="btn btn-warning btn-sm viewCredit">Requested</button>';
                            } else if (row.is_credit_approved == 2) {
                                html = '<button class="btn btn-success btn-sm viewCredit">Approved</button>';
                            } else if (row.is_credit_approved == 3) {
                                html = '<button class="btn btn-danger btn-sm viewCredit">Rejected</button>';
                            } else {
                                html = '<button class="btn btn-secondary btn-sm viewCredit" disabled>No Credit Request</button>';
                            }

                            return html;
                        }
                    },
                    {
                        "data": "credit_date_req",
                        "name": "credit_date_req",
                        render: function(data, type, row) { 
                                if (type === 'display' || type === 'filter') {
                                    return data ? data.split(' ')[0] : '';
                                }
                                return data;
                            },
                            "createdCell": function (td, cellData, rowData, row, col) {
                            $(td).addClass('text-right');
                        }
                    },
                    {
                        "data": "credit_req_uid",
                        "name": "credit_req_uid",
                    },
                    {
                        "data": "approval_remarks",
                        "name": "approval_remarks",
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
                    },
                    {
                        "data": "approved_by",
                        "name": "approved_by",
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
                            "createdCell": function (td, cellData, rowData, row, col) {
                            $(td).addClass('text-right');
                        }
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
        function viewCredit(id) {
            var url = '{{ url('pdf/credits/credit_copy') }}' + id + '.pdf';
            window.open(url, '_blank');
        }
    </script>
    <script>
        function sapPost(id) {
            const url = `sap_credit_post_web/${id}`;
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
                alert('Post SAP successfully!');
                const postSap = `sap_credit_post_web_${id}`; 
                window.open(postSap, '_blank');
            })
        }
    </script>
    <script>
        function requestCreditApproval(invoiceId) {
            if (!confirm("Are you sure you want to raise a second credit request for this invoice")) {
                return;
            }

            $.ajax({
                url: public_path + '/approve-credit-note',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    invoice_id: invoiceId
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#credit_note_table').DataTable().ajax.reload();
                    } else {
                        alert("Something went wrong: " + response.message);
                    }
                },
                error: function(xhr) {
                    alert("Error: " + xhr.responseJSON?.message || "Server error");
                }
            });
        }
    </script>
    
@endpush
