@extends('layouts.app')
<style>
    .form-group .select2-container {
        width: 240px !important;
    }
</style>
@section('content')
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Sales Employees</h4>
                <div class="row">
                    <div class="col-md-12"> <!-- MainContent Col-12 -->
                        <div class="row cust_data_form">
                            <div class="col-md-1">
                                <div class="form-group">
                                    <select class="form-control" style="width: 100%;" tabindex="-1" aria-hidden="true"
                                        id="branch_count">
                                        <option value="10" selected="selected">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{-- <label for="domain_update">SalesEmpName:</label> --}}
                                    <select class="form-control" name="sales_emp_role" id="sales_emp_role"
                                        data-live-search="true">
                                        <option selected value="">Select Role</option>
                                        <option value="2">Manager</option>
                                        <option value="3">SalesEmployee</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 margin pull-right no-m-top">

                                <div class="input-group">
                                    <input type="text" class="form-control no-border-right" id="search_user"
                                        placeholder="Search...">
                                </div>
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
                                    <table id="branch_table" class="table dataTable no-footer">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>EmployeeName</th>
                                                <th>Email ID</th>
                                                <th>Role</th>
                                                <th>ManagerName</th>
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

            var buttons = [];

            // Check if the user role is 1
            if (userRole === 1) {
                buttons = [
                    {
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


            var BranchListTable = $('#branch_table').DataTable({
                "dom": '<"html5buttons"B>tp',
                "bServerSide": true,
                "serverSide": true,
                "buttons": buttons,
                "processing": true,
                "bRetrieve": true,
                "paging": true,
                "ordering": false,
                "ajax": {
                    "url": public_path + '/sales-employees-json',
                    "type": "GET",
                    "data": function(d) {
                        return $.extend({}, d, {
                            'branch_count': $('#branch_count').val() || '',
                            "search_user": $('#search_user').val() || '',
                            "sales_emp_role": $('#sales_emp_role').val() || ''
                        });
                    },
                    "beforeSend": function() {
                        // Show the loading spinner
                        $('.loading-spinner').show();
                    },
                    "complete": function() {
                        // Hide the loading spinner
                        $('.loading-spinner').hide();
                    }
                },
                "columns": [{
                        "data": "id",
                        "name": "id",
                        "defaultContent": '-'
                    },
                    {
                        "data": "name",
                        "name": "name"
                    },
                    {
                        "data": "email",
                        "name": "email",
                        "defaultContent": '-'
                    },
                    {
                        "data": "role",
                        "name": "role",
                        "defaultContent": '-'
                    },
                    {
                        "data": "manager_name",
                        "name": "manager_name",
                        "defaultContent": '-'
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

                    if (aData['role'] == 2) {
                        var role = '<label class="badge badge-success">Manager</label>';
                    } else if(aData['role'] == 3)  {
                        var role = '<label class="badge badge-info">Sales Employee</label>';
                    } else
                        var role = '-';

                    $('td:eq(3)', nRow).html(role);

                }
            });

            $('#branch_table tbody').on('click', 'button.toggle-details', function() {
                var tr = $(this).closest('tr');
                var row = BranchListTable.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });



            $('#search_user').on('keyup', function() {
                BranchListTable.draw();
            });



            $('#sales_emp_role').change(function() {
                BranchListTable.draw();
            });


            $('#branch_count').change(function() {
                BranchListTable.page.len($('#branch_count').val()).draw();
            });
        });
    </script>


@endpush
