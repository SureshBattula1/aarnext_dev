@extends('layouts.app')
@section('content')
<style>
    .loading-spinner {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.7); /* light transparent overlay */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
</style>
<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Invoice Items With Batches</h5>
            <div class="row">
                <div class="col-md-1">
                    <div class="form-group">
                        <select class="form-control" id="page_length">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100000000">ALL</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" id="search_items" placeholder="Search by Item Code...">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" id="doc_num_search" placeholder="Search by Doc Number...">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" id="sap_entry_search" placeholder="Search by SAP DOC Entry...">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-4"></div>
                <div class="col-md-2">
                    <label for="start_date">Start Date</label>
                    <input type="date" class="form-control" id="start_date" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label for="end_date">End Date</label>
                    <input type="date" class="form-control" id="end_date" value="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div class="table-responsive mt-3">
                <table id="items_table" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>S No</th>
                            <th>ID</th>
                            <th>Doc Number</th>
                            <th>Hash Key</th>
                            <th>SAP Created ID</th>
                            <th>Customer Code</th>
                            <th>Item Code</th>
                            <th>Batch Number</th>
                            <th>Batch Quantity</th>
                            <th>Expiry Date</th>
                            <th>Doc Date</th>
                        </tr>
                    </thead>
                    <div class="loading-spinner" style="display: none;">
                        <div class="jumping-dots-loader">
                            <span></span> 
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    var itemsTable = $('#items_table').DataTable({
        dom: '<"html5buttons"B>tp',
        processing: false,
        serverSide: true,
        ajax: {
            url: "{{ url('inv-items-by-batches-json') }}",
            type: "GET",
            data: function (d) {
                d.doc_num_search = $('#doc_num_search').val();
                d.sap_entry_search = $('#sap_entry_search').val();
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.length = $('#page_length').val(); 
                d.search = { value: $('#search_items').val() };
            },
            "beforeSend": function() {
                $('.loading-spinner').show();
            },
            "complete": function() {
                $('.loading-spinner').hide();
            }
        },
        columns: [
            {
                data: null,
                name: 'sno',
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                orderable: false,
                searchable: false
            },
            { data: 'id' },
            { data: 'doc_num' },
            { data: 'hash_key' },
            { data: 'sap_created_id' },
            { data: 'customer_id' },
            { data: 'item_no' },
            {
                data: 'batches',
                render: function (data) {
                    return data?.map(b => `<div>${b.batch_num}</div>`).join('') || '';
                }
            },
            {
                data: 'batches',
                render: function (data) {
                    return data?.map(b => `<div>${b.batch_qty}</div>`).join('') || '';
                }
            },
            {
                data: 'batches',
                render: function (data) {
                    return data?.map(b => `<div>${b.expiry_date}</div>`).join('') || '';
                }
            },
            {  "data": 'created_at',
                    "title": "Date",
                    "render": function (data, type, row) {
                        if (data) {
                            const date = new Date(data);
                            // Format the date as 'YYYY-MM-DD'
                            const formattedDate = date.toISOString().split('T')[0];
                            return formattedDate;
                        }
                        return '-'; 
                    },
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-left');
                    }
                },
        ]
    });

    $('#search_items, #page_length, #doc_num_search, #sap_entry_search, #start_date, #end_date')
            .on('input change', function () {
                itemsTable.ajax.reload();
            });
    });

</script>
@endpush

