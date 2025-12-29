@extends('layouts.app')
<style>
    .viewInvoice {
        height: 30px;
        width: 150px;
        background-color: #3c8dbc;
    }
    .regenerate-pdf{
        height: 30px;
        width: 120px;
        background-color: #3c8dbc;
    }
    .add-new-btn{
        width: 150px;
        height: 30px;
        margin-top:24px;
    }
    .text-success {
    color: green;
    }

    .text-warning {
        color: orange;
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
                                    <select class="form-control" style="width: 100%; margin-top:26px;" id="branch_count">
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
                                    <input type="text" class="form-control search no-border-right" style="margin-top:26px;" id="search_user"
                                        placeholder="Search by customer name..." autocomplete="off" >
                                </div>
                            </div>
                            <div class="col-md-2 margin pull-right no-m-top">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date"
                                        name="start_date
                                    date"
                                        value="">
                                </div>
                            </div>
                            <div class="col-md-2 margin pull-right no-m-top">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date"
                                        name="end_date
                                    date"
                                        value="">
                                </div>
                            </div>
                            <div class="col-md-2" style="margin-top:26px;">
                               <select class="form-control input-sm" name="sales_type_search" id="sales_type_search"  tabindex="6">
                                    <option value=""> Select Sales Type</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Cash/Credit">Cash/Credit</option>
                                    <option value="Hospital">Hospital</option>
                                    <option value="Credit">Credit</option>
                                </select>
                            </div>
                            <div class="col-md-2" style="margin-top:26px;">
                                <select class="form-control fields" id="sub_grp_search" name="sub_grp_search">
                                    <option value="">Select Sub Group Type</option>
                                    <option value="GENERAL">General</option>
                                    <option value="HOSPITAL">Hospital</option>
                                </select>
                            </div>
                            
                        </div>
                        <div class="row" style="margin-bottom: 10px;">
                        <div class="col-md-2">
                                <select class="form-control fields" id="status_search" name="status_search">
                                    <option value="">Select Status</option>
                                    <option value="0">Open</option>
                                    <option value="1">Closed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control fields" id="sync_status" name="sync_status">
                                    <option value="">Select Sync Status</option>
                                    <option value="1">Synced</option>
                                    <option value="2">Pending</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control fields" id="sap_err_msgs" name="sap_err_msgs" type="text" placeholder="SAP Error Messages">
                            </div>
                            @if(Auth::user()->code == 'ADMIN')
                                <div class="col-md-1" id="sap_sync_container" style="display: none;">
                                    <button class="btn btn-success update-sap-status" 
                                            data-status="0">
                                        Update SAP
                                    </button>
                                </div>
                            @endif

                            <div class="col-md-2">
                                <select class="form-control fields" id="invoice_generate" name="invoice_generate">
                                    <option value="">Select Generate Status</option>
                                    <option value="1">Generate</option>
                                    <option value="2">Pending</option>
                                </select>
                            </div>

                            <div class="col-md-2 margin pull-right no-m-top">
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

                                <div class="modal fade modal-lg" id="payment_details_modal" tabindex="-1" role="dialog" >
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Payment Details</h5>
                                                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
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
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="quotations_table" class="table dataTable no-footer">
                                        <thead>

                                            <tr>
                                                <th>S.No</th>
                                                <th>#</th>
                                                <th>Document Number</th>
                                                <th>Date</th>
                                                <th>Due Date</th>
                                                <th>Customer Code</th>
                                                <th>Customer Name</th>
                                                <th>Group Name</th>
                                                <th>Reference Number</th>
                                                <th>Sales Type</th>
                                                <th>User Id</th>
                                                <th>Store Location</th>
                                                <th>Total without Tax</th>
                                                <th>Tax Value</th>
                                                <th>Total with Tax</th>                                                
                                                <th>Balance Amount</th>
                                                <th>Paid Amount</th>
                                                <th>Sales Employee</th>
                                                <th>Memo</th>
                                                <th>Print</th>
                                                <th>Payment Status</th>
                                                <th>User Name</th>
                                                <th>Sub Group</th>
                                                <th>Sap Updated</th>
                                                <th>Sap Updated At</th>
                                                <th>Sap Created Id</th>
                                                <th>Sap Error Message</th>
                                                <th>Regenerate PDF</th>
                                                <th>Post SAP</th>
                                                <th>Resync</th>
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
        $('#sync_status').on('change', function() {
            if ($(this).val() == "2") {
                $('#sap_sync_container').show();
            } else {
                $('#sap_sync_container').hide();
            }
        });
    });
    </script>

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
                            "search_user": $('#search_user').val() || '' ,
                            "start_date": $('#start_date').val() || '' ,
                            "end_date": $('#end_date').val() || '' ,
                            "sales_type_search": $('#sales_type_search').val() || '' ,
                            "sub_grp_search": $('#sub_grp_search').val() || '' ,
                            "status_search": $('#status_search').val() || '' ,
                            "sync_status": $('#sync_status').val() || '' ,
                            "sap_err_msgs": $('#sap_err_msgs').val() || '' ,
                            "sap_sync_update": $('#sap_sync_update').val() || '' ,
                            "invoice_generate": $('#invoice_generate').val() || '' ,
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
                            if (row.hash_key && row.hash_key !== '') {
                                var url = `pdf/invoices/invoice_copy_${data}.pdf`;
                                return `<a href="#" onclick="printPDFDirectly('${url}'); return false;">${data}</a>`;
                            } else {
                                return `<a href="javascript:void(0);">Not Generated</a>`;
                            }
                        }

                    },

                    {
                        "data": "invoice_no",
                        "name": "invoice_no"
                    },
                    {
                        "data": "order_date",
                        "name": "order_date"
                    },
                    {
                        "data": "due_date",
                        "name": "due_date"
                    },

                    {
                        "data": "customer_code_display",
                        "name": "customer_code_display",
                        "render": function(data, type, row, meta) {

                            let status = row.status_display;
                            let color = "";

                            if (status === 1) {
                                color = "green"; 
                            } else if (status === 0) {
                                color = "orange";
                            }
                                return '<a href="{{ url('invoice-payment-show') }}/' + row.id +
                            `" style="color:${color}; font-weight: bold;">` + data + '</a>';
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
                        "data": "ref_id",
                        "name": "ref_id"
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
                        "data": "store_code",
                        "name": "store_code"
                    },
                    {
                        "data": "total_price",
                        "render": function(data, type, row) {
                            return isNaN(parseFloat(data)) ? "0.00" : parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        "data": "total_tax",
                        "render": function(data, type, row) {
                            return isNaN(parseFloat(data)) ? "0.00" : parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        "data": "total_with_tax_sum",
                        "render": function(data, type, row) {
                            return isNaN(parseFloat(data)) ? "0.00" : parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        "data": "balance_amount",
                        "name": "balance_amount",
                        "render": function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        "data": "paid_amount",
                        "name": "paid_amount",
                        "render": function(data, type, row) {
                            return '<div style="display: flex; align-items: center; justify-content: space-between;">' +
                                '<span>' + parseFloat(data).toFixed(2) + '</span>' +
                                '<a href="#" class="open-payment-modal" data-invoice-id="' + row.id + '" style="margin-left: 10px;">Details</a>' +
                                '</div>';
                        }
                    },
                    {
                        "data": "name",
                        "name": "name"
                    },
                    {
                        "data": "memo",
                        "name": "memo"
                    },
                    {
                        "data": "id",
                        "render": function(data, type, row) {
                            return `<button class="btn btn-primary viewInvoice" onclick="viewInvoice('${data}')">View Invoice</button>`;
                        }
                    },
                    {
                        "data": 'status_display',
                        "title": "status_display",
                       "render": function(data, type, row) {
                            if (data === 1) {
                                return '<span class="text-success">Completed</span>';
                            } else {
                                return '<span class="text-warning">Pending</span>';
                            }
                        },
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-left');
                        }
                    },
                    {
                        "data": "code",
                        "name": "code"
                    },
                    {
                        "data": "sub_grp",
                        "name": "sub_grp"
                    },
                    {
                        "data": 'sap_status',
                        "title": "sap_status",
                        "render": function(data, type, row) {
                            if (data === 1) {
                                return '<span class="text-success">Completed</span>';
                            } else {
                                return '<span class="text-warning">Pending</span>';
                            }
                        },
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-left');
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
                    },
                    {
                        "data": "id",
                        "name": "regenerate",
                        "render": function(data, type, row) {
                            if (row.hash_key && row.hash_key !== '') {

                            return `
                                <button class="btn btn-primary regenerate-pdf" 
                                    onclick="regeneratePDF(${row.id})">
                                    Regenerate PDF
                                </button>`;

                            }else{

                            return `

                                <button class="btn btn-primary regenerate-pdf" 
                                    onclick="GeneratePdf(${row.id})">
                                    Generate PDF
                                </button>`;

                            }



                        }
                    },
                    {
                        "data": "id",
                        "name": "sap_post",
                        "render": function(data, type, row) {

                            if (row.hash_key && row.hash_key !== '') {
                            let disabledAttr = row.sap_created_id > 0 ? 'disabled' : '';
                            return `
                                <button class="btn btn-primary regenerate-pdf" 
                                    onclick="sapPost(${row.id})" ${disabledAttr}>
                                     Post SAP
                                </button>`;                                 
                         
                            }else{

                                return `
                                <button class="btn btn-primary regenerate-pdf">
                                     Not Generated
                                </button>`;                               

                            }

                        },
                        "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                    },
                    {
                        "data": "id",
                        "name": "resync",
                        "render": function(data, type, row) {
                            if (row.sap_created_id === null && row.hash_key !== '' && row.sap_status === 1) {
                                return `
                                    <button class="btn btn-warning regenerate-pdf " 
                                        onclick="reSync(${row.id}, 'invoice')">
                                        Resync SAP
                                    </button>`;
                            } else {
                                return '';
                            }
                        },
                        "createdCell": function (td, cellData, rowData, row, col) {
                            $(td).addClass('text-left');
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
                    // $('td:eq(0)', nRow).html(index);
                    //if (aData[id]) {
                    //    var invoiceButton =
                    //        '<button class="btn btn-primary viewInvoice" onclick="viewInvoice(aData[id] )">View Invoice</button>';
                    //    $('td:eq(10)', nRow).html(invoiceButton);
                    //}
                },
                "drawCallback": function(settings) {
                    var pageInfo = QuotationsTable.page.info();
                    var startIndex = pageInfo.start + 1;
                    $('#quotations_table tbody tr').each(function(index) {
                        $(this).find('td:first').text(startIndex + index);
                    });
                }
            });

            $('#search_user').on('keyup', function() {
                QuotationsTable.draw();
            });

            $('#branch_count').change(function() {
                QuotationsTable.page.len($(this).val()).draw();
            });

            $('#sales_type_search').on('change', function() {
                QuotationsTable.draw();
            });

            $('#sub_grp_search').on('change', function() {
                QuotationsTable.draw();
            });
            $('#status_search').on('change', function() {
                QuotationsTable.draw();
            });
            $('#start_date , #end_date').on('change', function() {
                QuotationsTable.draw();
            });
            $('#sync_status').on('change', function() {
                QuotationsTable.draw();
            });
            $('#sap_err_msgs').on('keyup', function() {
                QuotationsTable.draw();
            });
            $('#sap_sync_update').on('click', function() {
                QuotationsTable.draw();
            });
            $('#invoice_generate').on('change', function() {
                QuotationsTable.draw();
            });
        });

        $(document).on('click', '.open-payment-modal', function(e) {
            e.preventDefault();
            var invoiceId = $(this).data('invoice-id');
            openPaymentDetailsModal(invoiceId);
        });

        function openPaymentDetailsModal(invoiceId) {
            $('#payment-details-table').html('');
            $.ajax({
                url: public_path + '/get_inv_payment_details/' + invoiceId,
                method: 'GET',
                dataType: 'json',
                success: function (result) {
                    if (result.data && result.data.length > 0) {
                        var tableContent = ''; 
                        result.data.forEach(function (payment) {
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
                        $('#payment-details-table').html('<tr><td colspan="6" class="text-danger text-center">No payment details found.</td></tr>');
                    }
                },
                error: function (error) {
                    console.error("Error fetching payment details:", error);
                    $('#payment-details-table').html('<tr><td colspan="6" class="text-danger text-center">An unexpected error occurred.</td></tr>');
                }
            });

            $('.modal-title').html('Payment Details - Invoice #' + invoiceId);
            $('#payment_details_modal').modal('show');
        }

        $('#payment_details_modal').on('hidden.bs.modal', function () {
            $('#payment-details-table').html(''); // Clear only table content, not the structure
        });


        function viewInvoice(id) {
            var url = '{{ url('pdf/invoices/invoice_copy_') }}' + id + '.pdf';
            window.open(url, '_blank');
        }

        //function regeneratePDF(id) {
        //    const url = `pdf/invoices/invoice_${id}`;
        //    fetch(url, {
        //        method: 'GET',
        //        headers: {
        //            'Accept': 'application/json',
        //            'X-Requested-With': 'XMLHttpRequest'
        //        }
        //    })
        //    .then(response => {
        //        if (response.ok) {
        //            return response.json(); 
        //        } else {
        //            throw new Error('Failed to regenerate the PDF');
        //        }
        //    })
        //    .then(data => {
        //        alert('PDF regenerated successfully!');
        //        const pdfUrl = `pdf/invoices/invoice_${id}.pdf`; 
        //        window.open(pdfUrl, '_blank');
        //    })

        //}

        function GeneratePdf(invoice_id) {
            $.ajax({
                url: public_path + '/generate_invoice_pdf/' + invoice_id,
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.success == 1) {
                     const pdfUrl = response.pdf_url;

                      window.open(pdfUrl, '_blank');

                    } 
                },
                error: function (error) {



                    
                }
            });
            
        }




        function regeneratePDF(id) {
          
          const pdfUrl = public_path + '/pdf/invoices/invoice_' + id + '.pdf';

          fetch(pdfUrl, {
                  method: 'GET',
                  headers: {
                      'Accept': 'application/pdf',
                      'X-Requested-With': 'XMLHttpRequest'
                  }
              })
              .then(response => {
                  if (response.ok) {
                      alert('✅ PDF regenerated successfully!');
                      const pdfUrl = public_path + '/pdf/invoices/invoice_copy_' + id + '.pdf';
                      window.open(pdfUrl, '_blank');
                  } else {
                      throw new Error('❌ Failed to regenerate the PDF');
                  }
              })
              .catch(error => {
                  alert('❌ Error: ' + error.message);
              });
      }

        function sapPost(id) {
            const url = public_path + '/sap_invoice_post_web' +'/'+ id;
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
                const postSap = `sap_invoice_post_web/${id}`; 
                window.open(postSap, '_blank');
            })
        }

        $(document).on("click", ".update-sap-status", function() {
            var QuotationsTable = $('#quotations_table').DataTable();
            let ids = [];

            QuotationsTable.rows().every(function() {
                let rowData = this.data(); 
                if (rowData.id) {
                    ids.push(rowData.id); 
                }
            });

            if (ids.length > 0) {
                updateSAPStatus(ids); 

            } else {
                alert("No records found to update.");
            }
        });

        function updateSAPStatus(ids) {
            $.ajax({
                url: public_path + "/invoice_sap_status_update", 
                type: "POST",
                data: {
                    ids: ids,
                    _token: "{{ csrf_token() }}" 
                },
                success: function(response) {
                    alert("SAP status updated successfully!");
                    location.reload();                },
                error: function(xhr) {
                    alert("Error updating SAP status: " + xhr.responseText);
                }
            });
        }

        function reSync(id, type) {
            const url = public_path + '/resync_documents';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    id: id,
                    type: type
                })
            })
            .then(response => {
                if (response.ok) {
                    return response.json(); 
                } else {
                    throw new Error('Failed to Resync');
                }
            })
            .then(data => {
                alert(data.message);
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
    </script>
    <script>
        function printPDFDirectly(pdfUrl) {
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = pdfUrl;
            document.body.appendChild(iframe);
        
            iframe.onload = function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                // Optional: remove iframe after printing
                setTimeout(() => iframe.remove(), 2000);
            };
        }
        </script>
        
@endpush
