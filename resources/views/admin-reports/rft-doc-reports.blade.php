@extends('layouts.app')
@section('content')
<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">RFT Documents Reports</h5>
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
                <div class="col-md-2">
                    <input type="text" class="form-control" id="doc_num_search" placeholder="Search by Doc Number...">
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" id="rft_entry_search" placeholder="Search by RFT Entry...">
                </div>
                <div class="col-md-3">
                    <select class="form-control input-sm" name="web_status" id="web_status" >
                        <option value=""> Select Web Status</option>
                        <option value="1">From Status Pending</option>
                        <option value="2">From Status Completed</option>
                        <option value="3">To Status Pending</option>
                        <option value="4">To Status Completed</option>
                        <option value="5">RFT Completed</option>
                    </select>
                </div> <div class="col-md-3">
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


            <div class="row mt-3">
                
                <div class="col-md-2"></div>

                <div class="col-md-3">
                    <label for="start_date">Start Date</label>
                    <input type="date" class="form-control" id="start_date" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date">End Date</label>
                    <input type="date" class="form-control" id="end_date" value="{{ date('Y-m-d') }}">
                </div>

                <div class="col-md-1"></div>
                <div class="col-md-2">
                    <select name="search_ware_house[]" id="search_ware_house" class="form-control" multiple="multiple">
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->whs_code }}">{{ $warehouse->whs_code }}</option>
                        @endforeach
                        </select>
                </div>
            </div>

            <div class="table-responsive mt-3">
                <table id="items_table" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>RFT Entry</th>
                            <th>RFT Number</th>
                            <th>From Store</th>
                            <th>To Store</th>
                            <th>Created At</th>
                            <th>From Status</th>
                            <th>From SAP ID</th>
                            <th>From SAP Error Msg</th>
                            <th>To SAP ID</th>
                            <th>To Status</th>
                            <th>To SAP Error Msg</th>
                            <th>Cancelled Status</th>
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
    $(document).ready(function(){
        $('#search_ware_house').select2({
            placeholder: "Select Store",
            allowClear: true,
            width: '100%'
        });
    });
</script>
<script>
$(document).ready(function() {
    var itemsTable = $('#items_table').DataTable({
        "dom": '<"html5buttons"B>tp',
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "{{ url('admin-reports-rft-doc-json') }}",
            "type": "GET",
            "data": function(d) {
                d.length = $('#page_length').val();
                d.doc_num_search = $('#doc_num_search').val();
                d.rft_entry_search = $('#rft_entry_search').val();
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.search_ware_house = $('#search_ware_house').val() ? $('#search_ware_house').val().join(',') : ''; 
                d.web_status = $('#web_status').val() || '';
                d.sap_status = $('#sap_status').val() || '';

            },
        },
        "columns": [
            { data: 'id' },
            { data: 'rft_entry' },
            { data: 'rft_doc_num' },
            { data: 'from_store'},
            { data: 'to_store'},
            { 
                data: 'created_at',
                render: function(data, type, row) {
                    if (data) {
                        const date = new Date(data);
                        const formattedDate = date.toISOString().split('T')[0];
                        return formattedDate;
                    }
                    return '-';
                }
            },
            {
                "data": 'from_sap_created_id',
                "title": "from_sap_created_id",
                "render": function(data, type, row) {
                    if (data !== null) {
                        return '<span class="text-success">Completed</span>';
                    } else {
                        return '<span class="text-warning">Pending</span>';
                    }
                },
                "createdCell": function(td, cellData, rowData, row, col) {
                    $(td).addClass('text-left');
                }
            },
            { data: 'from_sap_created_id',
                render: function(data, type, row) {
                    return data ? data : '-';
                }
            },
            {
                "data": "sap_error_msg_from",
                "name": "sap_error_msg_from",
                "render": function(data, type, row) {
                    if (data) {

                        const maxLength = 50;
                        const regex = new RegExp(`.{1,${maxLength}}`, 'g');
                        const lines = data.match(regex) || [];
                        return lines.map(line => `<div>${line}</div>`).join(
                        '');
                    }
                    return '-';
                }
            },
            {
                "data": 'to_sap_created_id',
                "title": "to_sap_created_id",
                "render": function(data, type, row) {
                    if (data !== null) {
                        return '<span class="text-success">Completed</span>';
                    } else {
                        return '<span class="text-warning">Pending</span>';
                    }
                },
                "createdCell": function(td, cellData, rowData, row, col) {
                    $(td).addClass('text-left');
                }
            },
            { data: 'to_sap_created_id',
                render: function(data, type, row) {
                    return data ? data : '-';
                }
            },
            {
                "data": "sap_error_msg_to",
                "name": "sap_error_msg_to",
                "render": function(data, type, row) {
                    if (data) {

                        const maxLength = 50;
                        const regex = new RegExp(`.{1,${maxLength}}`, 'g');
                        const lines = data.match(regex) || [];
                        return lines.map(line => `<div>${line}</div>`).join(
                        '');
                    }
                    return '-';
                }
            },
            {
                "data": 'cancelled_status',
                "title": "cancelled_status",
                "render": function(data, type, row) {
                    if (data === 1) {
                        return '<span class="text-success">Active</span>';
                    } else {
                        return '<span class="text-warning">Closed</span>';
                    }
                },
                "createdCell": function(td, cellData, rowData, row, col) {
                    $(td).addClass('text-left');
                }
            },
        ],
        "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            var page = this.api().page.info().page;
            var length = this.api().page.info().length;
            var index = (page * length + (iDisplayIndex + 1));
            $('td:eq(0)', nRow).html(index);
        }
    });

    $('#page_length, #doc_num_search, #rft_entry_search, #start_date, #end_date , #web_status , #sap_status').on('input change', function() {
        itemsTable.draw();
    });
    $('#search_ware_house').on('select2:select select2:unselect', function() {
        itemsTable.draw();
    });
});
</script>
@endpush
