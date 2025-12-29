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

    .sapPost {
        height: 35px;
        width: 150px;
    }

    .form-control.search {
        height: 30px;
    }
</style>

@section('content')
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">RFT ITEMS LIST</h4>
                <div class="row cust_data_form">
                    <div class="col-md-1">
                        <div class="form-group">
                            <select class="form-control" style="width: 100%; margin-top:26px;" id="branch_count">
                                <option value="10000000">ALL</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="quotations_table" class="table dataTable no-footer">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Requested Qty</th>
                                <th>Issued Qty</th>
                                <th>Received Qty</th>
                                <th>Difference Qty</th>
                                <th>From Store</th>
                                <th>To Store</th>
                                <th>RFT Number</th>
                                <th>Warehouse Code</th>
                                <th>Available Qty</th>
                                <th>Batches</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="update_user">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">BATCH DETAILS LIST</h4>
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Batch Number</th>
                                <th>Expiry Date</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="batch-details-table">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var userRole = 1;
    var buttons = userRole === 1 ? [
        { extend: 'copy', text: 'Copy', className: 'btn btn-secondary' },
        { extend: 'csv', text: 'CSV', className: 'btn btn-secondary' },
        { extend: 'excel', text: 'Excel', className: 'btn btn-secondary' },
        { extend: 'pdf', text: 'PDF', className: 'btn btn-secondary' }
    ] : [];

    var rftId = "{{ $rft_id }}";
    var pageCout = $('#branch_count').val() || '';

    $('#quotations_table').DataTable({
        "dom": '<"html5buttons"B>t',
        "bServerSide": true,
        "serverSide": true,
        "buttons": buttons,
        "processing": true,
        "bRetrieve": true,
        "paging": true,
        "ordering": true,
        ajax: {
            url: '{{ url('/rft-items-list-data') }}',
            type: 'GET',
            data: function(d) {
                d.rft_id = rftId,
                d.branch_count  = pageCout

            },
            beforeSend: function() {
                $('.loading-spinner').show();
            },
            complete: function() {
                $('.loading-spinner').hide();
            }
        },
        columns: [
            { data: null, name: 'serial_no', render: function(data, type, row, meta) {
                return meta.row + 1;
            }},
            { data: 'item_code', name: 'item_code' },
            { data: 'item_name', name: 'item_name' },
            { data: 'requested_qty', name: 'requested_qty' },
            { data: 'rft_required_qty', name: 'rft_required_qty' },
            { data: 'rft_received_qty', name: 'rft_received_qty' },
            { data: null, name: 'difference_qty', render: function(data, type, row) {
                return row.rft_required_qty - row.rft_received_qty;
            }},
            { data: 'from_store', name: 'from_store' },
            { data: 'to_store', name: 'to_store' },
            { data: 'rft_number', name: 'rft_number' },
            { data: 'whs_code', name: 'whs_code' },
            { data: 'available_qty', name: 'available_qty' },
            { data: 'id', name: 'batches', render: function(data, type, row) {
                return '<a href="#" class="open-modal-link" data-rti-id="' + row.rti_id + '" data-id="' + row.id + '"><i class="ti-zoom-in menu-icon"></i></a>';
            }}
        ],
        order: [[1, 'asc']],
        "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    var page = this.fnPagingInfo().iPage;
                    var length = this.fnPagingInfo().iLength;
                    var index = (page * length + (iDisplayIndex + 1));

                },
    });

    $(document).on('click', '.open-modal-link', function(e) {
        e.preventDefault();
        var rti_id = $(this).data('rti-id');
        var id = $(this).data('id');
        var wareHouse = @json(auth()->user()->ware_house);

        $.ajax({
            url: '{{ url('/get_rft_item_batch_details') }}/' + rti_id,
            method: 'GET',
            dataType: 'json',
            data: {
                id: id,
                ware_house: wareHouse,
            },
            success: function(result) {
                if (result.data && result.data.length > 0) {
                    var tableContent = result.data.map(function(batch) {
                        return `<tr>
                                    <td>${batch.item_code || '-'}</td>
                                    <td>${batch.batch_num || '-'}</td>
                                    <td>${batch.expiry_date ? batch.expiry_date.split(' ')[0] : '-'}</td>
                                    <td>${Math.floor(batch.qty || 0)}</td>
                                </tr>`;
                    }).join('');
                    $('#batch-details-table').html(tableContent);
                    $('#update_user').modal('show');
                } else {
                    $('#batch-details-table').html('<tr><td colspan="4" class="text-danger">No batch details available.</td></tr>');
                }
            },
            error: function(error) {
                console.error('Error fetching item details:', error);
                $('#batch-details-table').html('<tr><td colspan="4" class="text-danger">An unexpected error occurred.</td></tr>');
            }
        });
    });
});
</script>
@endpush
