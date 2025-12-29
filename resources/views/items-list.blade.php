@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Items List</h4>
            <div class="row">
                <div class="col-md-12">
                    <div class="row cust_data_form">
                        <div class="col-md-1">
                            <div class="form-group">
                                <select class="form-control" style="width: 100%;" id="page_length">
                                    <option value="10" selected>10</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="200">200</option>
                                    <option value="400">400</option>
                                    <option value="4500">All</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 margin pull-right no-m-top">
                            <div class="input-group">
                                <input type="text" class="form-control" id="search_items" placeholder="Search by  name..." autocomplete="off">
                            </div>
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
                        <table id="items_table" class="table dataTable no-footer">
                            <thead>
                            <tr>
                                        <th>Id</th>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th>Foreign Name</th>
                                        {{-- <th>Item Group </th> --}}
                                        <th>Item Group Name</th>
                                        {{-- <th>frim_code</th> --}}
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <!--<th>Stock Value</th>-->
                                        <th>Manufacturer</th>
                                        <th>Sales Pack</th>
                                        <th>IVA</th>
                                        <th>Cabinda IVA</th>
                                        {{-- <th>Additional Identifier</th>
                                    <th>Valid For</th>
                                    <th>Frozen For</th>
                                    <th>Created Date</th>
                                    <th>Craeted </th>
                                    <th>update_date</th>
                                    <th>update_ts</th> --}}
                                    </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="update_user">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">BATCH DETAILS LIST- </h4>
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Batch Number</th>
                            <th>Quantity</th>
                            <th>In Date</th>
                            <th>Expiry Date</th>
                            <th>Invoice Qty</th>
                            <th>Credit Qty</th>
                        </tr>
                    </thead>
                    <tbody id="batch-details-table">
                    </tbody>
                </table>
            </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
 $(document).ready(function() {
    var itemsTable = $('#items_table').DataTable({
        "dom": '<"html5buttons"B>tp',
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "{{ url('items-json') }}",
            "type": "GET",
            "data": function(d) {
                d.search = $('#search_items').val();
                d.length = $('#page_length').val();
            },
            "beforeSend": function() {
                                $('.loading-spinner').show();
                            },
                            "complete": function() {
                                $('.loading-spinner').hide();
                            }
        },
        "columns": [{
                            "data": 'id'
                        },
                        {
                            "data": 'item_code'
                        },
                        {
                            "data": 'item_name'
                        },
                        {
                            "data": 'frgn_name'
                        },
                        {
                            "data": 'items_grp_name'
                        },
                        {
                            "data": 'on_hand',
                            "className": "on-hand-clickable",
                            "render": function(data, type, row) {
                                //const integerData = Math.floor(data);

                                return '<div style="display: flex; align-items: center; justify-content: space-between;">' +
                                    '<span>' + Math.floor(data) + '</span>' +
                                    '<a href="#" class="open-modal-link" data-item-code="' + row
                                    .item_code + '" style="margin-left: 10px;"><i class="ti-zoom-in menu-icon"></i></a>' +
                                    '</div>';
                            }
                        },
                        {
                            "data": 'price'
                        },
                        //{
                        //    "data": 'stock_value'
                        //},                                               
                        {
                            "data": 'manufacturer'
                        },
                        {
                            "data": 'sale_pack'
                        },
                        {
                            "data": 'others_iva'
                        },
                        {
                            "data": 'cabinda_iva'
                        },
                    ],
        "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            var page = this.api().page.info().page;
            var length = this.api().page.info().length;
            var index = (page * length + (iDisplayIndex + 1));

            $('td:eq(0)', nRow).html(index);
        }
    });

    $(document).on('click', '.open-modal-link', function(e) {
        e.preventDefault();
        var itemCode = $(this).data('item-code');
        openModalWithDetails(itemCode);
    });

    $('#search_items').on('keyup', function() {
        itemsTable.draw();
    });


    $('#page_length').change(function() {
        itemsTable.page.len($(this).val()).draw();
    });
});

function openModalWithDetails(itemCode) {
    $.ajax({
        url: public_path + '/get_item_batch_details/' + itemCode,
        method: 'GET',
        dataType: 'json',
        success: function (result) {
            if (result.data && result.data.length > 0) {
                var tableContent = ''; 
                result.data.forEach(function (batch) {
                    tableContent += '<tr>';
                    tableContent += '<td>' + batch.batch_num + '</td>';
                    tableContent += '<td>' + Math.floor(batch.quantity) + '</td>';
                    //tableContent += '<td>' + batch.in_date + '</td>';
                    //tableContent += '<td>' + batch.exp_date + '</td>';
                    tableContent += '<td>' + batch.in_date.split(' ')[0]   + '</td>';
                    tableContent += '<td>' + batch.exp_date.split(' ')[0]  + '</td>';
                    tableContent += '<td>' + batch.invoice_qty + '</td>';
                    tableContent += '<td>' + batch.credit_qty + '</td>';
                    tableContent += '</tr>';
                });
                $('#batch-details-table').html(tableContent);
            } else {
                $('.modal-body').html('<p class="text-danger">Error: ' + result.message + '</p>');
            }
        },
        error: function (error) {
            console.error("Error fetching item details:", error);
            $('.modal-body').html('<p class="text-danger">An unexpected error occurred.</p>');
        }
    });
    $('.modal-title').html('BATCH DETAILS LIST - ' + itemCode);

    $('#update_user').modal('show');
}

    $('#update_user').on('hidden.bs.modal', function () {
        $('#batch-details-table').html(''); // Clear table contents
        $('.modal-body').html('<table class="table table-striped">' +
            '<thead>' +
            '<tr>' +
            '<th>Batch Number</th>' +
            '<th>Quantity</th>' +
            '<th>In Date</th>' +
            '<th>Expiry Date</th>' +
            '<th>Invoice Qty</th>' +
            '<th>Credit Qty</th>' +
            '</tr>' +
            '</thead>' +
            '<tbody id="batch-details-table"></tbody>' +
            '</table>'); // Reset modal body
    });


</script>
@endpush
