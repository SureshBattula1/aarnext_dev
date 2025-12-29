@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Multi. PAN in WEBID</h4>
                <div class="row">
                    <div class="col-md-12"> <!-- MainContent Col-12 -->
                        <div class="row cust_data_form" style="visibility: hidden; height: 1px;">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select class="form-control input-sm" style="width: 100%;" tabindex="-1" aria-hidden="true"
                                        id="branch_count">
                                        <option value="10" selected="selected">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
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
                                            if (letter === 'A') {
                                                option.selected = true;
                                            }
                                            select.appendChild(option);
                                        });
                                    </script>
                                </select>
                            </div>
                            <div class="col-md-6 margin pull-right no-m-top">
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
                                                        <th>WebID</th>
                                                        <th>CommonName</th>
                                                        <th>SlpNameCount</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
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
                "buttons": buttons,
                "serverSide": true,
                "processing": true,
                "bRetrieve": true,
                "paging": false,
                "ordering": false,
                "ajax": {
                    "url": public_path + '/sap-duplicate-slpname-customers-json',
                    "type": "GET",
                    "data": function(d) {
                        return $.extend({}, d, {
                            'branch_count': $('#branch_count').val() || '',
                            "search_user": $('#search_user').val() || '',
                            "alphabet": $('#alphabet').val() || ''
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
                        "data": "u_trpbpnm",
                        "name": "u_trpbpnm",
                        "defaultContent": '-'
                    },
                    {
                        "data": "slpcount",
                        "name": "slpcount",
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


                    // var commonName = '<td><a href=' + url + '>' + aData['u_trpbpnm'] + '</a></td>';
                    var commonName = '<td><a href="' + url + '">' + aData['u_trpbpnm'] + '</a> (' +
                        aData['slpcount'] + ')</td>';
                    $('td:eq(2)', nRow).html(commonName);

                    // var slpName = '<td>' + aData['slp_name'] + '(' + aData['distinct_slp_count'] + ')</td>';
                    // $('td:eq(10)', nRow).html(slpName);

                }
            });




            $('#search_user').on('keyup', function() {
                BranchListTable.draw();
            });




            $('#alphabet').change(function() {
                BranchListTable.draw();
            });


            $('#branch_count').change(function() {
                BranchListTable.page.len($('#branch_count').val()).draw();
            });
        });
    </script>
@endpush
{{-- <main class="app-content">
    <div class="container-fluid">
        <div class="col-md-12 user-right">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 style="color:#A04000">Multi. SalesEmpName</h4>
                    <div class="row cust_data_form">
                        <div class="col-md-2">
                            <div class="form-group">
                                <select class="form-control" style="width: 100%;" tabindex="-1" aria-hidden="true" id="branch_count">
                                    <option value="10" selected="selected">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
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
                                        if (letter === 'A') {
                                            option.selected = true;
                                        }
                                        select.appendChild(option);
                                    });
                                </script>
                            </select>
                        </div>
                        <div class="col-md-6 margin pull-right no-m-top">
                            <div class="input-group">
                                <input type="text" class="form-control no-border-right" id="search_user" placeholder="Search...">
                                <div class="input-group-addon">
                                    <i class="fa fa-search sear"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div>
                                    <table id="branch_table" class="table table-striped table-bordered nowrap"
                                    style="overflow-x: auto;display: block;">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>DBName</th>
                                            <th>CardCode</th>
                                            <th>CardName</th>
                                            <th>PAN</th>
                                            <th>CommonName</th>
                                            <th>Category</th>
                                            <th>SubCategory</th>
                                            <th>SalesEmpName</th>
                                            <th>CntPerson</th>
                                            <th>Phone1</th>
                                            <th>Phone2</th>
                                            <th>Email</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        var BranchListTable = $('#branch_table').DataTable({
            "dom": '<"html5buttons"B>tp',
            "bServerSide": true,
            "serverSide": true,
            "processing": true,
            "bRetrieve": true,
            "paging": true,
            "ordering":false,
            "ajax": {
                "url": public_path + '/sap-duplicate-slpname-customers-json',
                "type": "GET",
                "data": function(d) {
                    return $.extend({}, d, {
                        'branch_count': $('#branch_count').val() || '',
                        "search_user": $('#search_user').val() || '',
                        "alphabet": $('#alphabet').val() || ''
                    });
                }
            },
            "columns": [
                {"data": "id", "name": "id", "defaultContent": '-'},
                {"data": "db_name", "name": "db_name"},
                {"data": "card_code", "name": "card_code", "defaultContent": '-'},
                {"data": "card_name", "name": "card_name", "defaultContent": '-'},
                {"data": "u_vsppan", "name": "u_vsppan", "defaultContent": '-'},
                {"data": "u_trpbpnm", "name": "u_trpbpnm", "defaultContent": '-'},
                {"data": "u_csubcat", "name": "u_csubcat", "defaultContent": '-'},
                {"data": "u_csubcat2", "name": "u_csubcat2", "defaultContent": '-'},
                {"data": "distinct_slp_count", "name": "distinct_slp_count", "defaultContent": '-'},
                {"data": "cntct_prsn", "name": "cntct_prsn", "defaultContent": '-'},
                {"data": "phone1", "name": "phone1", "defaultContent": '-'},
                {"data": "phone2", "name": "phone2", "defaultContent": '-'},
                {"data": "e_mail", "name": "e_mail", "defaultContent": '-'},
            ],
            "order": [[1, "asc"]],
            "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                var page = this.fnPagingInfo().iPage;
                var length = this.fnPagingInfo().iLength;
                var index = (page * length + (iDisplayIndex + 1));
                $('td:eq(0)', nRow).html(index);


                var url = public_path + '/sap-customer-companies/' + aData['id'] ;
                var pan = '<td>' + aData['u_vsppan'] + '(' + aData['distinct_pan_count'] + ')</td>';
                $('td:eq(4)', nRow).html(pan);


                // var commonName = '<td><a href=' + url + '>' + aData['u_trpbpnm'] + '</a></td>';
                var commonName = '<td><a href="' + url + '">' + aData['u_trpbpnm'] + '</a> (' + aData['total_count'] + ')</td>';
                $('td:eq(6)', nRow).html(commonName);

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
            let addressesHtml = '';
            d.addresses.forEach(address => {
                addressesHtml += `
                    <tr>
                        <td>${address.address}</td>
                        <td>${address.street}</td>
                        <td>${address.block}</td>
                        <td>${address.zip_code}</td>
                        <td>${address.city}</td>
                        <td>${address.state}</td>
                        <td>${address.gst_regn_no}</td>
                    </tr>`;
            });

            return `<table cellpadding="5" cellspacing="0" border="1" style="margin-left:20px;">
                            <tr>
                                <th>Address</th>
                                <th>Street</th>
                                <th>Block</th>
                                <th>Zip Code</th>
                                <th>City</th>
                                <th>State</th>
                                <th>GST Registration No</th>
                            </tr>
                            ${addressesHtml}
                        </table>`;
        }

        $('#search_user').on('keyup', function() {
            BranchListTable.draw();
        });




            $('#alphabet').change(function() {
                BranchListTable.draw();
            });


        $('#branch_count').change(function() {
            BranchListTable.page.len($('#branch_count').val()).draw();
        });
    });
</script> --}}
