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
                <h4 class="card-title">Customers WebId</h4>
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
                                        @if (Auth::user()->role == 1)
                                            <option value="5000">5000</option>
                                            <option value="-1">All</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <select class="form-control" name="alphabet" id="alphabet">
                                    <option value="">Select Alphabet</option>
                                    <!-- Add alphabet options dynamically -->
                                    <script>
                                        const alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("");
                                        const select = document.getElementById("alphabet");

                                        alphabet.forEach(letter => {
                                            const option = document.createElement("option");
                                            option.value = letter;
                                            option.text = letter;
                                            // if (letter === 'A') {
                                            //     option.selected = true;
                                            // }
                                            select.appendChild(option);
                                        });
                                    </script>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{-- <label for="domain_update">SalesEmpName:</label> --}}
                                    <select class="form-control" name="sales_emp_name" id="sales_emp_name"
                                        data-live-search="true">
                                        <option selected value="">Select SalesEmpName</option>
                                        @foreach ($saleEmpNames as $saleemp)
                                            <option value="{{ $saleemp->sales_emp_name }}">{{ $saleemp->sales_emp_name }}
                                            </option>
                                        @endForeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{-- <label for="domain_update">Category:</label> --}}
                                    <select class="form-control" name="category" id="category" data-live-search="true">
                                        <option selected value="">Select Category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->name }}">{{ $category->name }}</option>
                                        @endForeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 ml-auto">
                                <div class="form-group">
                                    {{-- <label for="domain_update">SubCategory:</label> --}}
                                    <select class="form-control" name="subcategory" id="subcategory"
                                        data-live-search="true">
                                        <option selected value="">Select SubCategory</option>
                                        @foreach ($subcategories as $scategory)
                                            <option value="{{ $scategory->name }}">{{ $scategory->name }}
                                            </option>
                                        @endForeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2 margin pull-right no-m-top">
                                <div class="form-check">
                                    <label class="form-check-label">
                                      <input type="checkbox" name="created_web" id="created_web" value="1" class="form-check-input">
                                       Web Customers
                                    <i class="input-helper"></i></label>
                                  </div>

                                {{-- <div class="input-group">
                                    <input type="text" class="form-control no-border-right" id="search_user"
                                        placeholder="Search...">
                                </div> --}}
                            </div>
                            <div class="col-md-3 margin pull-right no-m-top">

                                <div class="input-group">
                                    <input type="text" class="form-control no-border-right" id="search_user"
                                        placeholder="Search...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{-- <label for="domain_update">SalesEmpName:</label> --}}
                                    <select class="form-control" name="active_status" id="active_status"
                                        data-live-search="true">
                                        <option selected value="all">All Customers</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">InActive</option>
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
                                    <table id="branch_table" class="table dataTable no-footer">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>WebId</th>
                                                <th>CustomerName</th>
                                                <th>PAN</th>
                                                <th>CommonName</th>
                                                <th>Category</th>
                                                <th>SubCategory</th>
                                                <th>SalesEmpName</th>
                                                <th>CntPerson</th>
                                                <th>ContactNo</th>
                                                <th>Phone1</th>
                                                <th>Email</th>
                                                <th>Address</th>
                                                <th>City</th>
                                                <th>State</th>
                                                <th>GSTIN</th>
                                                <th>GST Status</th>
                                                <th>DB Master</th>
                                                <th>Status</th>
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


            let dashboardSaleEmpName = sessionStorage.getItem("dashboardSaleEmpName");
            let dashboardCategory = sessionStorage.getItem("dashboardCategory");
            let dashboardSubCategory = sessionStorage.getItem("dashboardSubCategory");
            let dashboardStatus = sessionStorage.getItem("dashboardStatus");

            if (dashboardSaleEmpName) {
                $('#sales_emp_name').val(dashboardSaleEmpName)
                $('#active_status').val('active')
                sessionStorage.removeItem('dashboardSaleEmpName');

            }
            if (dashboardCategory) {
                $('#category').val(dashboardCategory)
                $('#active_status').val('active')
                sessionStorage.removeItem('dashboardCategory');
            }
            if (dashboardSubCategory) {
                $('#subcategory').val(dashboardSubCategory)
                $('#active_status').val('active')
                sessionStorage.removeItem('dashboardSubCategory');
            }
            if (dashboardStatus) {
                $('#active_status').val(dashboardStatus)
                sessionStorage.removeItem('dashboardStatus');
            }

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
                    "url": public_path + '/sap-customers-webid-json',
                    "type": "GET",
                    "data": function(d) {
                        return $.extend({}, d, {
                            'branch_count': $('#branch_count').val() || '',
                            "search_user": $('#search_user').val() || '',
                            "alphabet": $('#alphabet').val() || '',
                            "sales_emp_name": $('#sales_emp_name').val() || '',
                            "category": $('#category').val() || '',
                            "subcategory": $('#subcategory').val() || '',
                            "active_status": $('#active_status').val() || '',
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
                        "data": "web_id",
                        "name": "web_id"
                    },
                    {
                        "data": "card_name",
                        "name": "card_name",
                        "defaultContent": '-'
                    },
                    {
                        "data": "u_vsppan",
                        "name": "u_vsppan",
                        "defaultContent": '-'
                    },
                    {
                        "data": "u_trpbpnm",
                        "name": "u_trpbpnm",
                        "defaultContent": '-'
                    },
                    {
                        "data": "new_u_csubcat",
                        "name": "new_u_csubcat",
                        "defaultContent": '-'
                    },
                    {
                        "data": "new_u_csubcat2",
                        "name": "new_u_csubcat2",
                        "defaultContent": '-'
                    },
                    {
                        "data": "sales_emp_name",
                        "name": "sales_emp_name",
                        "defaultContent": '-'
                    },
                    {
                        "data": "cntct_prsn",
                        "name": "cntct_prsn",
                        "defaultContent": '-'
                    },
                    {
                        "data": "cellular",
                        "name": "cellular",
                        "defaultContent": '-'
                    },
                    {
                        "data": "phone1",
                        "name": "phone1",
                        "defaultContent": '-'
                    },
                    {
                        "data": "e_mail",
                        "name": "e_mail",
                        "defaultContent": '-'
                    },
                    {
                        "data": "street",
                        "name": "street",
                        "defaultContent": '-'
                    },
                    {
                        "data": "city",
                        "name": "city",
                        "defaultContent": '-'
                    },
                    {
                        "data": "state",
                        "name": "state",
                        "defaultContent": '-'
                    },
                    {
                        "data": "gst_regn_no",
                        "name": "gst_regn_no",
                        "defaultContent": '-'
                    },
                    {
                        "data": "gst_status",
                        "name": "gst_status",
                        "defaultContent": '-'
                    },
                    {
                        "data": "db_master",
                        "name": "db_master",
                        "defaultContent": '-'
                    },
                    {
                        "data": "active_status",
                        "name": "active_status",
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


                    var url = public_path + '/sap-webid-customer-companies/' + aData['id'];
                    // var pan = '<td>' + aData['u_vsppan'] + '(' + aData['distinct_pan_count'] + ')</td>';
                    // $('td:eq(4)', nRow).html(pan);


                    if (aData['u_trpbpnm'] == 'OTC') {
                        var commonName = '<td>' + aData['u_trpbpnm'] + '(' + aData['db_master'] + ')</td>';
                        var dbmaster = '<td>' + aData['db_master'] + '</td>';
                    } else {
                        var commonName = '<td><a href="' + url + '">' + aData['u_trpbpnm'] + '</a> (' +
                        aData['db_master'] + ')</td>';
                        var dbmaster = '<td><a href="' + url + '">' + aData['db_master'] + '</a></td>';
                    }
                    $('td:eq(4)', nRow).html(commonName);

                    // var dbmaster = '<td><a href="' + url + '">' + aData['db_master'] + '</a></td>';
                    $('td:eq(18)', nRow).html(dbmaster);

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

            // function format(d) {
            //     let addressesHtml = '';
            //     d.addresses.forEach(address => {
            //         addressesHtml += `
        //             <tr>
        //                 <td>${address.address}</td>
        //                 <td>${address.street}</td>
        //                 <td>${address.block}</td>
        //                 <td>${address.zip_code}</td>
        //                 <td>${address.city}</td>
        //                 <td>${address.state}</td>
        //                 <td>${address.gst_regn_no}</td>
        //             </tr>`;
            //     });

            //     return `<table cellpadding="5" cellspacing="0" border="1" style="margin-left:20px;">
        //                     <tr>
        //                         <th>Address</th>
        //                         <th>Street</th>
        //                         <th>Block</th>
        //                         <th>Zip Code</th>
        //                         <th>City</th>
        //                         <th>State</th>
        //                         <th>GST Registration No</th>
        //                     </tr>
        //                     ${addressesHtml}
        //                 </table>`;
            // }

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

            $('#active_status').change(function() {
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
                                    .new_u_csubcat + '">' +
                                    value.new_u_csubcat + '</option>');
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
                                    .new_u_csubcat2 + '">' +
                                    value.new_u_csubcat2 + '</option>');
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
