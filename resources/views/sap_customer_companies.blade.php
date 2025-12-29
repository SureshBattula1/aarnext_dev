@extends('layouts.app')
@section('content')
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-1">
                        <button type="button" onclick="history.back()" class="btn btn-inverse-dark btn-icon">
                            <i class="ti-arrow-circle-left"></i></button>
                    </div>
                    <div class="col-md-9">
                        <h4 class="card-title company-heading">
                            {{ $commonName }}
                        </h4>
                    </div>
                    <div class="col-md-2">
                        <a class="btn btn-primary" href="{{ url('get-customer-contacts/' . $commonName) }}">
                            <i class="ti-user"></i>
                            Contacts</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12"> <!-- MainContent Col-12 -->
                        <div class="row cust_data_form visibility-hidden">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select class="form-control" style="width: 100%;" tabindex="-1" aria-hidden="true"
                                        id="branch_count">
                                        <option value="5000" selected="selected">5000</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3"></div>
                            <div class="col-md-6 margin pull-right no-m-top">
                                <div class="input-group">
                                    <input type="text" class="form-control no-border-right" value="{{ $commonName }}"
                                        id="search_user" placeholder="Search...">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="branch_table" class="table dataTable no-footer">
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
                                                <th>Cellular</th>
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
        <div class="card update-customer-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <!-- Edit -->
                        <h4 class="card-title">Update Customer <button style="margin-left:700px" type="button"
                            class="btn btn-secondary" id="edit-button"><i class="ti-pencil-alt"></i> &nbsp;Edit</button> </h4>

                                <!-- Edit button -->
                                <form id="update_pan_form" action="javascript:void(0)" method="POST">
                                    {{ csrf_field() }}
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="domain_update">Card Name:</label>
                                                <input readonly type="text" class="form-control" id="card_name"
                                                    name="card_name" value="{{ $cust->card_name }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4 ml-auto">
                                            <div class="form-group">
                                                <label for="domain_update">PAN No:</label>
                                                <input readonly type="text" class="form-control"
                                                    value="{{ $cust->gst_pan }}" id="gst_pan" name="gst_pan">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="domain_update">GST Registration No:</label>
                                                <input readonly type="text" class="form-control" id="gst_reg_no"
                                                    value="{{ $gst_reg_no }}" name="gst_reg_no">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="domain_update">Category:</label>
                                                <select class="form-control" name="category" id="category"
                                                    data-live-search="true">
                                                    <option selected value="">Select Category</option>
                                                    @foreach ($categories as $category)
                                                        <option @if ($category->name == $dbcategory) SELECTED @endif
                                                            value="{{ $category->name }}">{{ $category->name }}</option>
                                                    @endForeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 ml-auto">
                                            <div class="form-group">
                                                <label for="domain_update">SubCategory:</label>
                                                <select class="form-control" name="subcategory" id="subcategory"
                                                    data-live-search="true">
                                                    <option selected value="">Select SubCategory</option>
                                                    @foreach ($subcategories as $scategory)
                                                        <option @if ($scategory->name == $subcategory) SELECTED @endif
                                                            value="{{ $scategory->name }}">{{ $scategory->name }}
                                                        </option>
                                                    @endForeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="domain_update">SalesEmpName:</label>
                                                <select class="form-control" name="sales_emp_name" id="sales_emp_name"
                                                    data-live-search="true">
                                                    <option selected value="">Select SalesEmpName</option>
                                                    @foreach ($saleEmpNames as $saleemp)
                                                        <option @if ($saleemp->slp_name == $salesemp) SELECTED @endif
                                                            value="{{ $saleemp->slp_name }}">{{ $saleemp->slp_name }}
                                                        </option>
                                                    @endForeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" name="common_name" id="common_name"
                                        value="{{ $commonName }}">
                                    <button type="submit" class="btn btn-primary" name="button">Submit</button>

                                </form>

                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#edit-button').on('click', function() {
                // Toggle the readonly and disabled attributes for input and select fields
                $(' #update_pan_form select').each(function() {
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
            $('#update_pan_form select').each(function() {
                $(this).attr('readonly', 'readonly').attr('disabled', 'disabled');
            });

            // Initially disable the submit button
            $('#update_pan_form button[type="submit"]').attr('disabled', 'disabled');


            $('#update_pan').on('shown.bs.modal', function() {
                $('#update_pan_form select').each(function() {
                    $(this).attr('readonly', 'readonly').attr('disabled', 'disabled');
                });
                $('#update_pan_form button[type="submit"]').attr('disabled', 'disabled');
            });
        });




        $(document).ready(function() {
            var BranchListTable = $('#branch_table').DataTable({
                "dom": '<"html5buttons"B>tp',
                "bServerSide": true,
                "serverSide": true,
                "processing": true,
                "bRetrieve": true,
                "ordering": false,
                "paging": false,
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
                        '<td><a href="#" onclick="editPan(' +
                        aData['id'] + ');">' + aData['u_vsppan'] +
                        '</a></td>';

                    $('td:eq(4)', nRow).html(updatePan);

                    if (aData['sales_emp_name'] != null) {
                        slpname =
                            '<td>' + aData['sales_emp_name'] + '</td>';
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

                    if (!confirm('Are you sure to Update?'))
                        return false;

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
@endpush
