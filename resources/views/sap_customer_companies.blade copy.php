@extends('layouts.app')
<style>
    body table {
        font-size: 13px !important;
    }

    .modal-footer {
        justify-content: flex-start !important;
    }

    .select2-container--default .select2-selection--single {
        background-color: #fff;
        border: 2px solid #aaa;
        border-radius: 4px;
        width: 368px;
    }

</style>
@section('content')
    <main class="app-content">
        <div class="container-fluid">
            <div class="col-md-12 user-right">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 style="color:#A04000">CommonName : {{ $commonName }}</h4>
                        <button class="btn btn-secondary" id="backButton" onclick="history.back()">
                            < Back</button>

                                {{-- <button class="btn btn-secondary" id="backButton" onclick="history.back()">
                                &lt; Back
                            </button> --}}


                                <div class="row cust_data_form" style="visibility: hidden">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <select class="form-control" style="width: 100%;" tabindex="-1"
                                                aria-hidden="true" id="branch_count">
                                                <option selected="selected">500</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3"></div>
                                    <div class="col-md-6 margin pull-right no-m-top">
                                        <div class="input-group">
                                            <input type="text" class="form-control no-border-right"
                                                value="{{ $commonName }}" id="search_user" placeholder="Search...">
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
                                    {{-- <table id="branch_table" class="table table-striped table-bordered dt-responsive nowrap branch_table" cellspacing="0" width="100%" data-page-length='10'> --}}
                                    <table id="branch_table" class="table table-striped table-bordered nowrap"
                                        style="overflow-x: auto;display: block;">
                                        <thead>
                                            <tr>
                                                {{-- <th>S.no</th> --}}
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
                                                <th>Cellular</th>
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

    <!-- Edit -->
    <div class="modal fade" id="update_pan">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Update Customer</h4>
                    <button style="margin-left: 150px" type="button" class="btn btn-secondary"
                        id="edit-button">Edit</button> <!-- Edit button -->
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form id="update_pan_form" action="javascript:void(0)" method="POST">
                        {{ csrf_field() }}


                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="domain_update">Card Name:</label>
                                    <input readonly type="text" class="form-control" id="card_name" name="card_name">
                                </div>
                            </div>
                            <div class="col-md-4 ml-auto">
                                <div class="form-group">
                                    <label for="domain_update">PAN No:</label>
                                    <input type="text" class="form-control" id="gst_pan" name="gst_pan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="domain_update">GST Registration No:</label>
                                    <input type="text" class="form-control" id="gst_reg_no" name="gst_reg_no">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="domain_update">Category:</label>
                                    <select class="form-control" name="category" id="category" data-live-search="true">
                                        <option selected value="">Select Category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->name }}">{{ $category->name }}</option>
                                        @endForeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="domain_update">SubCategory:</label>
                                    <select class="form-control" name="subcategory" id="subcategory"
                                        data-live-search="true">
                                        <option selected value="">Select SubCategory</option>
                                        @foreach ($subcategories as $scategory)
                                            <option value="{{ $scategory->name }}">{{ $scategory->name }}</option>
                                        @endForeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="domain_update">SaleEmpName:</label>
                                    <select class="form-control" name="sales_emp_name" id="sales_emp_name"
                                        data-live-search="true">
                                        <option selected value="">Select SaleEmpName</option>
                                        @foreach ($saleEmpNames as $saleemp)
                                            <option value="{{ $saleemp->slp_name }}">{{ $saleemp->slp_name }}</option>
                                        @endForeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="domain_update">ContactPerson:</label>
                                    <input type="text" class="form-control" id="cntct_prsn" name="cntct_prsn">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="domain_update">Phone 1:</label>
                                    <input type="text" class="form-control" id="phone1" name="phone1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="domain_update">Phone2:</label>
                                    <input type="text" class="form-control" id="phone2" name="phone2">
                                </div>
                            </div>
                        </div> --}}

                        {{-- <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="domain_update">Cellular:</label>
                                    <input type="text" class="form-control" id="cellular" name="cellular">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="domain_update">Email:</label>
                                    <input type="text" class="form-control" id="e_mail" name="e_mail">
                                </div>
                            </div>
                        </div> --}}
                        <input type="hidden" name="customer_id" id="customer_id">
                        <input type="hidden" name="common_name" id="common_name" value="{{ $commonName }}">

                        <button type="submit" class="btn btn-primary" name="button">Submit</button>

                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#edit-button').on('click', function() {
                // Toggle the readonly and disabled attributes for input and select fields
                $('#update_pan_form input, #update_pan_form select').each(function() {
                    if ($(this).attr('readonly') || $(this).attr('disabled')) {
                        $(this).removeAttr('readonly').removeAttr('disabled');
                    } else {
                        $(this).attr('readonly', 'readonly').attr('disabled', 'disabled');
                    }
                });

                // Toggle the disabled attribute for the submit button
                var submitButton = $('#update_pan_form button[type="submit"]');
                if (submitButton.attr('disabled')) {
                    submitButton.removeAttr('disabled');
                } else {
                    submitButton.attr('disabled', 'disabled');
                }
            });

            // Initially set all input and select fields to readonly and disabled
            $('#update_pan_form input, #update_pan_form select').each(function() {
                $(this).attr('readonly', 'readonly').attr('disabled', 'disabled');
            });

            // Initially disable the submit button
            $('#update_pan_form button[type="submit"]').attr('disabled', 'disabled');

            // Set fields to readonly and disable submit button when modal is shown
            $('#update_pan').on('shown.bs.modal', function() {
                $('#update_pan_form input, #update_pan_form select').each(function() {
                    $(this).attr('readonly', 'readonly').attr('disabled', 'disabled');
                });
                $('#update_pan_form button[type="submit"]').attr('disabled', 'disabled');
            });
        });




        $(document).ready(function() {
            var BranchListTable = $('#branch_table').DataTable({
                "dom": '',
                "bServerSide": true,
                "serverSide": true,
                "processing": true,
                "bRetrieve": true,
                "ordering": false,
                "paging": true,
                "ajax": {
                    "url": public_path + '/sap-customers-companies-json',
                    "type": "GET",
                    "data": function(d) {
                        return $.extend({}, d, {
                            'branch_count': $('#branch_count').val() || '',
                            "search_user": $('#search_user').val() || '',
                        });
                    }
                },
                "columns": [
                    // {"data": "id", "name": "id", "defaultContent": '-'},
                    {
                        "data": null,
                        "defaultContent": '<button class="btn btn-info btn-sm toggle-details">Details</button>'
                    },
                    {
                        "data": "db_name",
                        "name": "db_name"
                    },
                    {
                        "data": "card_code",
                        "name": "card_code",
                        "defaultContent": '-'
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
                        "data": "u_csubcat",
                        "name": "u_csubcat",
                        "defaultContent": '-'
                    },
                    {
                        "data": "u_csubcat2",
                        "name": "u_csubcat2",
                        "defaultContent": '-'
                    },
                    {
                        "data": "slp_name",
                        "name": "slp_name",
                        "defaultContent": '-'
                    },
                    {
                        "data": "cntct_prsn",
                        "name": "cntct_prsn",
                        "defaultContent": '-'
                    },
                    {
                        "data": "phone1",
                        "name": "phone1",
                        "defaultContent": '-'
                    },
                    {
                        "data": "phone2",
                        "name": "phone2",
                        "defaultContent": '-'
                    },
                    {
                        "data": "e_mail",
                        "name": "e_mail",
                        "defaultContent": '-'
                    },
                    {
                        "data": "cellular",
                        "name": "cellular",
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
                    // $('td:eq(0)', nRow).html(index);

                    updatePan =
                        '<td><a href="#" data-toggle="modal" data-target="#update_pan"  onclick="editPan(' +
                        aData['id'] + ');">' + aData['u_vsppan'] +
                        '</a></td>';

                    $('td:eq(4)', nRow).html(updatePan);

                    if (aData['sales_emp_name'] != null) {
                        slpname =
                        '<td>' + aData['sales_emp_name'] + 'x</td>';
                    } else {
                        slpname =
                        '<td>' + aData['slp_name'] + '</td>';
                    }

                    $('td:eq(9)', nRow).html(slpname);
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

            $('#branch_count').change(function() {
                BranchListTable.page.len($('#branch_count').val()).draw();
            });


            $("#update_pan_form").validate({
                errorClass: "state-error",
                validClass: "state-success",
                errorElement: "em",
                ignore: [],

                /* @validation rules
                ------------------------------------------ */
                rules: {
                    new_pan: {
                        required: true,
                    },
                },
                /* @validation error messages
                ---------------------------------------------- */

                messages: {
                    new_pan: {
                        required: 'This field is required'
                    }

                },
                submitHandler: function(form) {

                    $.ajax({
                        url: public_path + '/update_customer_pan',
                        method: 'post',
                        data: new FormData($("#update_pan_form")[0]),
                        dataType: 'json',
                        async: false,
                        cache: false,
                        processData: false,
                        contentType: false,
                        success: function(result) {
                            if (result.success == 1) {
                                // alert("Your Password Updated Successfully.!")
                                Swal.fire({
                                    type: 'success',
                                    title: result.message,
                                    showConfirmButton: true,
                                    // timer: 1500
                                });
                                //  location.reload();

                                $('#update_pan').modal('hide');
                                $('#branch_table').DataTable().ajax.reload();

                            } else {
                                swal("Error", result.message, "warning");
                            }
                        },
                        error: function(error) {
                            if (error) {
                                var error_status = error.responseText;
                                alert(error_status.message);
                            }
                        }
                    });

                }
            });

        });


        function editPan(id) {
            $('#customer_id').val(id);
            $.ajax({
                url: public_path + '/get-customer-detail/' + id,
                method: 'get',
                dataType: 'json',
                cache: false,
                processData: false,
                contentType: false,
                success: function(result) {
                    if (result.success == 1) {
                        // Populate form fields with the retrieved data
                        $('#card_name').val(result.customer.card_name);
                        $('#gst_pan').val(result.customer.gst_pan);

                        $('#cntct_prsn').val(result.customer.cntct_prsn);
                        $('#phone1').val(result.customer.phone1);
                        $('#phone2').val(result.customer.phone2);
                        $('#cellular').val(result.customer.cellular);
                        $('#e_mail').val(result.customer.e_mail);

                        if (result.salesemp) {
                            $('#sales_emp_name').val(result.salesemp).trigger('change');

                        }
                        $('#gst_reg_no').val(result.gst_reg_no);

                        $('#category').val(result.category).trigger('change'); // Update Select2
                        $('#subcategory').val(result.subcategory).trigger('change');;
                        $('#cntct_prsn').val(result.customer.cntct_prsn);


                    } else {
                        swal("Error", result.message, "warning");
                    }
                },
                error: function(error) {
                    if (error) {
                        var error_status = error.responseText;
                        alert(error_status.message);
                    }
                }
            });
        }
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            $("#category").select2();
            $("#subcategory").select2();
            $("#sales_emp_name").select2();
        });
    </script>
@endsection
