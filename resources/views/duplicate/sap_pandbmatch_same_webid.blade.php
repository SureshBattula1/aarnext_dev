@extends('layouts.app')
<style>
    .form-group .select2-container {
        width: 240px !important;
    }
    .table-responsive {
        overflow-x: scroll;
    }
</style>
@section('content')
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Customers - Same PAN, DB in WebID</h4>
                <div class="row">
                    <div class="col-md-12"> <!-- MainContent Col-12 -->
                        <div class="row cust_data_form" style="visibility: hidden; height:1px">
                            <div class="col-md-1">
                                <div class="form-group">
                                    <select class="form-control" style="width: 100%;" tabindex="-1" aria-hidden="true"
                                        id="branch_count">
                                        <option value="10" selected="selected">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        @if (Auth::user()->email == 'admin@tekroi.com')
                                            <option value="5000">5000</option>
                                            <option value="-1">All</option>
                                        @endif
                                    </select>
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
                                    <table id="branch_table" class="table dataTable no-footer small-padding">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Details</th>
                                                <th>DBName</th>
                                                <th>PAN</th>
                                                <th>WebId</th>
                                                <th>Nof of Cust. DB</th>
                                                <th>CommonName</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>

                                {{-- <div class="loading-spinner" style="display:none;">
                                <div class="jumping-dots-loader">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                  </div>
                            </div> --}}

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
                "paging": false,
                "ordering": false,
                "ajax": {
                    "url": public_path + '/sap-dup-pan-db-webid-json',
                    "type": "GET",
                    "data": function(d) {
                        return $.extend({}, d, {
                            'branch_count': $('#branch_count').val() || '',
                            "search_user": $('#search_user').val() || '',
                            "alphabet": $('#alphabet').val() || '',
                            "sales_emp_name": $('#sales_emp_name').val() || '',
                            "category": $('#category').val() || '',
                            "subcategory": $('#subcategory').val() || '',
                             "created_web": $('#created_web').is(':checked') ? '1' : '0'
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
                        "data": null,
                        "defaultContent": '<button class="btn btn-info btn-sm toggle-details">Details</button>'
                    },
                    {
                        "data": "db_name",
                        "name": "db_name",
                        "defaultContent": '-'
                    },
                    {
                        "data": "gst_pan",
                        "name": "gst_pan",
                        "defaultContent": '-'
                    },
                     {
                        "data": "web_id",
                        "name": "web_id"
                    },
                    {
                        "data": "db_count",
                        "name": "db_count",
                        "defaultContent": '-'
                    },

                    {
                        "data": "card_name",
                        "name": "card_name",
                        "defaultContent": '-'
                    },
                    // {
                    //     "data": "web_id",
                    //     "name": "web_id"
                    // },

                    // {
                    //     "data": "u_csubcat",
                    //     "name": "u_csubcat",
                    //     "defaultContent": '-'
                    // },
                    // {
                    //     "data": "u_csubcat2",
                    //     "name": "u_csubcat2",
                    //     "defaultContent": '-'
                    // },
                    // {
                    //     "data": "sales_emp_name",
                    //     "name": "sales_emp_name",
                    //     "defaultContent": '-'
                    // },
                    // {
                    //     "data": "cntct_prsn",
                    //     "name": "cntct_prsn",
                    //     "defaultContent": '-'
                    // },
                    // {
                    //     "data": "cellular",
                    //     "name": "cellular",
                    //     "defaultContent": '-'
                    // },
                    // {
                    //     "data": "phone1",
                    //     "name": "phone1",
                    //     "defaultContent": '-'
                    // },
                    // {
                    //     "data": "e_mail",
                    //     "name": "e_mail",
                    //     "defaultContent": '-'
                    // },
                    // {
                    //     "data": "street",
                    //     "name": "street",
                    //     "defaultContent": '-'
                    // },
                    // {
                    //     "data": "city",
                    //     "name": "city",
                    //     "defaultContent": '-'
                    // },
                    // {
                    //     "data": "state",
                    //     "name": "state",
                    //     "defaultContent": '-'
                    // },
                    // {
                    //     "data": "gst_regn_no",
                    //     "name": "gst_regn_no",
                    //     "defaultContent": '-'
                    // },
                    // {
                    //     "data": "gst_status",
                    //     "name": "gst_status",
                    //     "defaultContent": '-'
                    // },
                    // {
                    //     "data": "db_master",
                    //     "name": "db_master",
                    //     "defaultContent": '-'
                    // },
                ],
                "order": [
                    [1, "asc"]
                ],
                "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    var page = this.fnPagingInfo().iPage;
                    var length = this.fnPagingInfo().iLength;
                    var index = (page * length + (iDisplayIndex + 1));
                    $('td:eq(0)', nRow).html(index);


                    var url = public_path + '/sap-webid-customer-companies/' + aData['id'];
                    // var pan = '<td>' + aData['u_vsppan'] + '(' + aData['distinct_pan_count'] + ')</td>';
                    // $('td:eq(4)', nRow).html(pan);


                    // var commonName = '<td><a href=' + url + '>' + aData['u_trpbpnm'] + '</a></td>';
                    // var commonName = '<td><a href="' + url + '">' + aData['u_trpbpnm'] + '</a> (' +
                    //     aData['db_master'] + ')</td>';
                    // $('td:eq(4)', nRow).html(commonName);

                    // var dbmaster = '<td><a href="' + url + '">' + aData['db_master'] + '</a></td>';
                    // $('td:eq(18)', nRow).html(dbmaster);

                    // var slpName = '<td>' + aData['slp_name'] + '(' + aData['distinct_slp_count'] + ')</td>';
                    // $('td:eq(10)', nRow).html(slpName);

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

            function format(d) {
                let webIdCustomersHTML = '';
                d.webid_customers.forEach(customers => {
                    webIdCustomersHTML += `
                    <tr>
                        <td>${customers.db_name}</td>
                        <td>${customers.card_code}</td>
                        <td>${customers.u_vsppan}</td>
                        <td>${customers.web_id}</td>
                        <td>${customers.card_name}</td>
                        <td>${customers.u_trpbpnm}</td>
                        <td>${customers.u_csubcat}</td>
                        <td>${customers.u_csubcat2}</td>
                        <td>${customers.sales_emp_name}</td>

                    </tr>`;
                });

                return ` <div class="table-responsive"><table cellpadding="5" cellspacing="0" border="1" style="margin-left:20px;">
                            <tr>
                                <th>DBName</th>
                                <th>CardCode</th>
                                <th>PAN</th>
                                <th>WebId</th>
                                <th>CustomerName</th>
                                <th>CommonName</th>
                                <th>Category</th>
                                <th>SubCategory</th>
                                <th>SalesEmpName</th>

                            </tr>
                            ${webIdCustomersHTML}
                        </table></div>`;
            }

            $('#search_user').on('keyup', function() {
                BranchListTable.draw();
            });


            $('#alphabet').change(function() {
                BranchListTable.draw();
            });

            $('#sales_emp_name').change(function() {
                BranchListTable.draw();
            });
            $('#category').change(function() {
                BranchListTable.draw();
            });
            $('#subcategory').change(function() {
                BranchListTable.draw();
            });

            $('#created_web').change(function() {
                BranchListTable.draw();
            });




            $('#branch_count').change(function() {
                BranchListTable.page.len($('#branch_count').val()).draw();
            });
        });
    </script>
    <script>
        $(document).ready(function() {

            $('#sales_emp_name').change(function() {

                var sales_emp_name = $(this).val();

                if (sales_emp_name) {
                    $.ajax({
                        url: public_path + '/sales-emp-categories/' + sales_emp_name,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            $('#category').empty();
                            $('#category').append('<option value="">Select Category</option>');
                            $.each(data, function(key, value) {
                                $('#category').append('<option value="' + value
                                    .u_csubcat + '">' +
                                    value.u_csubcat + '</option>');
                            });
                            $('#subcategory').empty();
                            $('#subcategory').append('<option value="">Select Sub Category</option>');

                        }
                    });
                } else {
                    $('#category').empty();
                    $('#category').append('<option value="">Select Category</option>');
                    $('#subcategory').empty();
                    $('#subcategory').append('<option value="">Select Sub Category</option>');
                }
            });

            $('#category').change(function() {

                var sales_emp_name = $('#sales_emp_name').val();
                var category = $(this).val();

                if (category) {
                    $.ajax({
                        url: public_path + '/sales-emp-cat-subcats/' + sales_emp_name + '/' + category,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            $('#subcategory').empty();
                            $('#subcategory').append('<option value="">Select Sub Category</option>');
                            $.each(data, function(key, value) {
                                $('#subcategory').append('<option value="' + value
                                    .u_csubcat2 + '">' +
                                    value.u_csubcat2 + '</option>');
                            });
                        }
                    });
                } else {
                    $('#subcategory').empty();
                    $('#subcategory').append('<option value="">Select Sub Category</option>');
                }
            });
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#category").select2();
            $("#subcategory").select2();
            $("#sales_emp_name").select2();
        });
    </script>
@endpush
