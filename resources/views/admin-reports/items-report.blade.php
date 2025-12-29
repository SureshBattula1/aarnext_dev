@extends('layouts.app')

<style>
    .text-right {
        text-align: right;
    }
    .text-left {
        text-align: left;
    }
</style>

@section('content')
<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Item Stocks Reports</h5>
            <div class="row">
                <div class="col-md-1">
                    <div class="form-group">
                        <select class="form-control" id="page_length">
                            <!--<option value="10" selected>10</option>
                            <option value="50">50</option>-->
                            <option value="100000000">All</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="search_items" placeholder="Search by name...">
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="items_table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    @foreach($warehouses as $warehouse)
                                        <th>{{ $warehouse->whs_code }}</th>
                                    @endforeach
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">BATCH DETAILS LIST-</h4>
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Ware House</th>
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
    $(document).ready(function () {
    let warehouseColumns = [
        {
            "data": null,
            "name": "serial_no",
            "render": function (data, type, row, meta) {
                return meta.row + 1;
            }
        },
        { "data": "item_code", "title": "Item Code" },
        { "data": "item_name", "title": "Item Name" },
    ];

    // Dynamically append warehouse columns (based on Laravel data passed as JSON)
    @foreach($warehouses as $index => $warehouse)
        warehouseColumns.push({
            data: 'whs_qty{{ $index + 1 }}',
            render: function (data, type, row, meta) {
                return `<a href="#" class="batch-link" data-item="${row.item_code}" data-whs="${row.whs_code{{ $index + 1 }}}">${data}</a>`;
            }
        });
    @endforeach

    let itemsTable = $('#items_table').DataTable({
        dom: '<"html5buttons"B>tp',
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('admin-reports-items-json') }}",
            type: "GET",
            data: function (d) {
                d._token = "{{ csrf_token() }}";
                d.search = $('#search_items').val();
                d.length = $('#page_length').val();
            }
        },
        columns: warehouseColumns,
        fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            var index = itemsTable.page.info().start + iDisplayIndex + 1;
            $('td:eq(0)', nRow).html(index);
        }
    });


        $('#search_items').on('keyup', function () {
            itemsTable.draw();
        });

        $('#page_length').change(function () {
            itemsTable.page.len($(this).val()).draw();
        });
    });
   

    $(document).on('click', '.batch-link', function (e) {
        e.preventDefault();
        
        let itemCode = $(this).data('item');
        let whsCode = $(this).data('whs');

        // Fetch batch details
        $.ajax({
            url: `{{ url('admin-items-btaches-show') }}/${itemCode}/${whsCode}`,
            type: 'GET',
            success: function (response) {
                let tbody = $('#batch-details-table');
                tbody.empty(); // Clear old data

                if (response.data.length > 0) {
                    response.data.forEach(batch => {
                        tbody.append(`
                            <tr>
                                <td>${batch.item_code}</td>
                                <td>${batch.whs_code}</td>
                                <td>${batch.batch_num ?? 'N/A'}</td>
                                <td>${batch.quantity ?? 0}</td>
                                <td>${batch.in_date ? new Date(batch.in_date).toLocaleDateString() : 'N/A'}</td>
                                <td>${batch.exp_date ? new Date(batch.exp_date).toLocaleDateString() : 'N/A'}</td>
                                <td>${batch.invoice_qty ?? 0}</td>
                                <td>${batch.credit_qty ?? 0}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append('<tr><td colspan="6" class="text-center">No batch details found.</td></tr>');
                }
                $('#update_user').modal('show');
            },
            error: function () {
                alert('Failed to load batch details.');
            }
        });
    });

</script>
@endpush
