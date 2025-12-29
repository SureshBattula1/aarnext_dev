@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Sales Order Reports</h5>
            <div class="row">
            <div class="row cust_data_form">
                        <div class="col-md-1">
                            <div class="form-group">
                                <select class="form-control" style="width: 100%;" id="page_length">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="1000">1000</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 margin pull-right no-m-top">
                            <div class="input-group">
                                <input type="text" class="form-control" id="search_items" placeholder="Search by  name...">
                                
                            </div>
                        </div>
                        <div class="col-md-3">
                                <!--<label class="" for="sales_type">Sales Type</label>-->
                                <select class="form-control" name="sales_type" id="sales_type">
                                            <option value=""> Select Sales Type</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Cash/Credit">Cash/Credit</option>
                                            <option value="Hospital">Hospital</option>
                                            <option value="credit">Credit</option>
                                </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control fields" id="sub_grp_search" name="sub_grp_search">
                                    <option value="">Select Sub Group Type</option>
                                    <option value="GENERAL">General</option>
                                    <option value="HOSPITAL">Hospital</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control fields" id="status_search" name="status_search">
                                    <option value="">Select Status</option>
                                    <option value="0">Open</option>
                                    <option value="1">Closed</option>
                                </select>
                            </div>
                    </div>
                
            </div>
            <div class="row">
                <div class="col-md-1"></div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date
                        date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date
                        date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="col-md-2" style="margin-top: 23px;">
                    <div>
                        <button id="exportExcel" class="btn btn-success">Export to Excel</button>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    

                    <div class="table-responsive">
                        <table id="items_table" class="table dataTable no-footer">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    
                                    <th>Customer Id</th>
                                    <th>Sales Type</th>
                                    <th>Order Date</th>
                                    <th>Total</th>
                                    <!--<th>Total Amount</th>-->
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="4" style="text-align:right">Total Amount:</th>
                                    <th id="total_amount" style="text-align:right"></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var itemsTable = $('#items_table').DataTable({
            "dom": '',
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ url('quotation-reports-json') }}",
                "type": "GET",
                "data": function(d) {
                    d.search = $('#search_items').val();
                    d.sales_type = $('#sales_type').val();
                    d.length = $('#page_length').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.sub_grp_search = $('#sub_grp_search').val();
                    d.status_search = $('#status_search').val();
                },
               
            },
            "columns": [
                { "data": 'id', "title": "ID" },
                //{ "data": 'day_series', "title": "Day series" },
                { "data": 'customer_code', "title": "Customer Id" },
                { "data": 'sales_type', "title": "Sales Type" },
                { "data": 'date', "title": "Order Date" },
                { "data": 'total', "title": "Total" },
                { "data": 'status', "title": "Status" }
            ],
            
            "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                var page = this.api().page.info().page;
                var length = this.api().page.info().length;
                var index = (page * length + (iDisplayIndex + 1));
                $('td:eq(0)', nRow).html(index);
            },
            "drawCallback": function(data) {
                    // Access the total amount from the server response
                    var response = data.json;
                    if (response && response.total_amount) {
                        $('#total_amount').text(response.total_amount.toFixed(2));
                    } else {
                        $('#total_amount').text('0.00');
                    }
                }
            });

        $('#search_items').on('keyup', function() {
            itemsTable.draw();
        });

        $('#page_length').change(function() {
            itemsTable.page.len($(this).val()).draw();
        });
        $('#sales_type').on('change', function() {
            itemsTable.draw();
        });
        $('#start_date, #end_date').on('change', function() {
            itemsTable.draw();
        });
        $('#sub_grp_search').on('change', function() {
            itemsTable.draw();
        })
        ;$('#status_search').on('change', function() {
            itemsTable.draw();
        });
    });
    $('#exportExcel').on('click', function() {
        var salesType = $('#sales_type').val();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        var searchItems = $('#search_items').val();
        var pageLength = $('#page_length').val();
        var subGrpSearch = $('#sub_grp_search').val();
        var statusSearch = $('#status_search').val();
        window.location.href = "{{ url('export-quotation-report') }}?sales_type=" + salesType + "&start_date=" + startDate + "&end_date=" + endDate + "&search_user="  + searchItems + "&sub_grp_search=" + subGrpSearch + "&status_search=" + statusSearch + "&page_length=" + pageLength;
    });
</script>  
@endpush