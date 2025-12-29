@extends('layouts.app')
@section('content')
<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">RFT Reports</h5>
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
                    <input type="text" class="form-control" id="rft_entry_search" placeholder="Search by RFT Entry...">
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
                            <th>ID</th>
                            <th>RFT Number</th>
                            <th>From Store</th>
                            <th>To Store</th>
                            <th>Item Code</th>
                            <th>Requested Quantity</th>
                            <th>From Quantity</th>
                            <th>From Posted Date</th>
                            <th>From Batch Numbers</th>
                            <th>From Batch Qty</th>
                            <th>To Quantity</th>
                            <th>To Posted Date</th>
                            <th>To Batch Numbers</th>
                            <th>To Batch Qty</th>
                        </tr>
                    </thead>
                    
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var itemsTable = $('#items_table').DataTable({
        "dom" : '<"html5buttons"B>tp',
        "processing": true,
        "processing": true, 
        "serverSide": true,
        "ajax": {
            "url": "{{ url('rft-batches-reports-json') }}",
            "type": "GET",
            "data": function(d) {
                d.search = $('#search_items').val();
                d.length = $('#page_length').val();
                d.doc_num_search = $('#doc_num_search').val();
                d.rft_entry_search = $('#rft_entry_search').val();
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
            },
        },
      "columns": [

        { "data": 'id' },
        { "data": 'rft_doc_num' },
        { "data": 'from_store' },
        { "data": 'to_store' },
        { "data": 'item_code' },
        { "data": 'requested_qty' },
        // From Store
        { "data": 'rft_required_qty' },
        { "data": 'from_posted_at' },
        {
            'data': "batches",
            render: function (data) {
            if (!data || !data.length) return "";
            return data.map(b => `<div>${b.batch_num}</div>`).join("");
            }
        },
        {
            'data': "batches",
            render: function (data) {
            if (!data || !data.length) return "";
            return data.map(b => `<div>${b.from_store_qty}</div>`).join("");
            }
        },
        // To Store
        { "data": 'rft_received_qty' },
        { "data": 'to_posted_at' },
        {
            'data': "batches",
            render: function (data) {
            if (!data || !data.length) return "";
            return data
                .map(b => b.batch_num ?? "")
                .map(q => `<div>${q}</div>`)
                .join("");
            }
        },
        {
            'data': "batches",
            render: function (data) {
            if (!data || !data.length) return "";
            return data
                .map(b => b.to_store_qty ?? "")
                .map(q => `<div>${q}</div>`)
                .join("");
            }
        },
    ]

    });

    $('#search_items, #page_length , #doc_num_search, #rft_entry_search, #start_date, #end_date').on('input change', function() {
        itemsTable.draw();
    });
});
</script>
@endpush
