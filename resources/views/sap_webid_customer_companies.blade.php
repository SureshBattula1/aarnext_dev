@extends('layouts.app')
@section('content')
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-1">
                        <button type="button" class="btn btn-inverse-dark btn-icon" onclick="window.location.href='{{ url('sap-webid-customers') }}'">
                            <i class="ti-arrow-circle-left"></i>
                        </button>

                        {{-- <a href="{{ url('sap-webid-customers')}}" class="btn btn-inverse-dark btn-icon">
                            <i class="ti-arrow-circle-left"></i></a> --}}
                            {{-- onclick="history.back()" --}}
                    </div>
                    <div class="col-md-9">
                        <h4 class="card-title company-heading">
                            {{ $commonName }} - {{ $cust->web_id}}
                        </h4>
                    </div>
                    <div class="col-md-2">

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
                                    <input type="text" class="form-control no-border-right" value="{{ $web_id }}"
                                        id="search_user" placeholder="Search...">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="branch_table" class="table dataTable no-footer small-padding">
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
                                                {{-- <th>CntPerson</th>
                                                <th>Phone1</th>
                                                <th>Phone2</th>
                                                <th>Email</th>
                                                <th>Cellular</th> --}}
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

        <div class="card addresses-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="card-title">Addresses  <a href="#update-address-card" onclick="displayNewForm('address')" style="margin-left:800px" type="button"
                            class="btn btn-success btn-sm" id="edit-button"><i class="ti-pencil-alt"></i>
                            &nbsp;New</a></h4>
                        <div class="table-responsive">
                            <table id="branch_table" class="table dataTable no-footer small-padding" >
                                <thead>
                                    <tr role="row">
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 32px;">S.No
                                        </th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 68px;">DBName
                                        </th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 66px;">Address Type
                                        </th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 66px;">Address
                                        </th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 49px;">Street
                                        </th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 70px;">
                                            Block</th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 85px;">
                                            Zip Code</th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 68px;">City
                                        </th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 98px;">
                                            State</th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 29px;">GST Registration No
                                        </th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 82px;">GST Status
                                        </th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 82px;">Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($customerAddresses as $address)
                                    <tr role="row" class="odd">
                                        <td>{{ $loop->index + 1 }} <a href="#update-address-card" onclick="editAddress('{{ $address->id }}')">Edit</a></td>
                                        <td>{{ $address->db_name}} </td>
                                        <td>{{ $address->adres_type}} </td>
                                        <td>{{ $address->address}} </td>
                                        <td>{{ $address->street}} </td>
                                        <td>{{ $address->block}} </td>
                                        <td>{{ $address->zip_code}} </td>
                                        <td>{{ $address->city}} </td>
                                        <td>{{ $address->state}} </td>
                                        <td>{{ $address->gst_regn_no}} </td>
                                        <td>{{ $address->gst_status}} </td>
                                        <td>{{ $address->active}} </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="card contact-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="card-title">Contacts <a href="#update-contact-card" onclick="displayNewForm('contact')" style="margin-left:800px" type="button"
                            class="btn btn-success btn-sm" id="edit-button"><i class="ti-pencil-alt"></i>
                            &nbsp;New</a></h4>
                        <div class="table-responsive">
                            <table id="branch_table" class="table dataTable no-footer small-padding" >
                                <thead>
                                    <tr role="row">
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 32px;">S.No
                                        </th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 68px;">DBName
                                        </th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 66px;">CardCode
                                        </th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 49px;">Name
                                        </th>
                                        {{-- <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 70px;">
                                            FirstName</th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 85px;">
                                            MiddleName</th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 68px;">LastName
                                        </th> --}}
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 98px;">
                                            Designation</th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 29px;">Tel1
                                        </th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 82px;">Cellular
                                        </th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 194px;">Email
                                        </th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 43px;">Status
                                        </th>
                                        {{-- <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 87px;">Created
                                            Date</th>
                                        <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 89px;">
                                            Created Time</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($customerContacts as $contact)
                                    <tr role="row" class="odd">
                                        <td>{{ $loop->index + 1 }} <a href="#update-contact-card" onclick="editContact('{{ $contact->id }}')">Edit</a></td>
                                        <td>{{ $contact->db_name}} </td>
                                        <td>{{ $contact->card_code}} </td>
                                        <td>{{ $contact->name}} </td>
                                        {{-- <td>{{ $contact->first_name}} </td>
                                        <td>{{ $contact->middle_name}} </td>
                                        <td>{{ $contact->last_name}} </td> --}}
                                        <td>{{ $contact->designation}} </td>
                                        <td>{{ $contact->tel1}} </td>
                                        <td>{{ $contact->cellolar}} </td>
                                        <td>{{ $contact->e_mail_l}} </td>
                                        <td>{{ $contact->active}} </td>
                                        {{-- <td>{{ $contact->create_date}} </td>
                                        <td>{{ $contact->create_ts}} </td> --}}
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="update-customer-card" class="card update-customer-card"  style="display: none">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <!-- Edit -->
                        <h4 class="card-title">Update Customer
                            {{-- <button style="margin-left:800px" type="button"
                                class="btn btn-secondary" id="edit-button"><i class="ti-pencil-alt"></i>
                                &nbsp;Edit</button> --}}

                            </h4>

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
                                        <input readonly type="text" class="form-control" value="{{ $cust->gst_pan }}"
                                            id="gst_pan" name="gst_pan">
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

                            <input type="hidden" name="common_name" id="common_name" value="{{ $commonName }}">
                            <button type="submit" class="btn btn-primary" name="button">Submit</button>

                        </form>

                    </div>

                </div>
            </div>
        </div>

        <div id="update-address-card" class="card update-address" style="display: none">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <!-- Edit -->
                        <h4 class="card-title">Update Address</h4>

                        <!-- Edit button -->
                        <form id="update_address_form" action="javascript:void(0)" method="POST">
                            {{ csrf_field() }}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="adres_type">Address Type:</label>
                                        <select required class="form-control input-sm" name="adres_type" id="adres_type">
                                            <option value="">Select Type</option>
                                            <option value="B">Bill To</option>
                                            <option value="S">Ship To</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="street">Street:</label>
                                        <textarea required rows="7" class="form-control" id="street" name="street" placeholder="Address"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="category">GSTIN:</label>
                                        <input required type="text" class="form-control" id="gst_regn_no" name="gst_regn_no" placeholder="GSTIN">
                                    </div>

                                    {{-- <div class="form-group adres-upd-option">
                                        <div class="inline form-check">
                                        <label class="form-check-label">
                                            <input type="radio" name="update_option" id="update_option" value="current" checked class="form-check-input">
                                             Current Record Only
                                          <i class="input-helper"></i><i class="input-helper"></i></label>
                                        </div>
                                        <div class="inline form-check">
                                            <label class="form-check-label">
                                                <input type="radio" name="update_option" id="update_option"  value="all" class="form-check-input">
                                                 All
                                              <i class="input-helper"></i><i class="input-helper"></i></label>
                                            </div>
                                    </div> --}}

                                </div>
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="subcategory">Country:</label>
                                                <select required class="form-control" onchange="defaultGSTStateSection()" name="country"
                                                    id="country" data-live-search="true">
                                                    <option selected value="">Select Country</option>
                                                    @foreach ($countries as $country)
                                                        <option
                                                          value="{{ $country->iso2 }}">{{ $country->name }}
                                                        </option>
                                                    @endForeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="category">State:</label>
                                                <select required class="form-control" id="state" name="state" data-live-search="true">
                                                    <option selected value="">Select State</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="category">City:</label>
                                                <input required type="text" class="form-control" id="city" name="city"
                                                    placeholder="City">
                                            </div>
                                        </div>


                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="salesEmpName">ZipCode:</label>
                                                <input required type="text" class="form-control" id="zip_code" name="zip_code"
                                                    placeholder="ZipCode">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="card_name">Block:</label>
                                                <input type="text" class="form-control" id="block" name="block" placeholder="Block">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="category">Status:</label>
                                                <select class="form-control" id="status" name="status" data-live-search="true">
                                                    <option value="Y">Active</option>
                                                    <option value="N">InActive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <input type="hidden" name="web_id" id="web_id" value="{{ $web_id }}">
                            <input type="hidden" name="address_id" id="address_id">
                            <button type="submit" class="btn btn-primary" name="button">Submit</button>

                        </form>

                    </div>

                </div>
            </div>
        </div>

        <div id="update-contact-card" class="card update-contact"  style="display: none">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <!-- Edit -->
                        <h4 class="card-title contact-title">New Contact</h4>

                        <!-- Edit button -->
                        <form id="update_contact_form" action="javascript:void(0)" method="POST">
                            {{ csrf_field() }}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Name">Name:</label>
                                        <input type="text" required class="form-control" id="name" name="name" placeholder="Name">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Designation">Designation:</label>
                                        <input required type="text" class="form-control" id="designation" name="designation" placeholder="Designation">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Name">Email:</label>
                                        <input type="text" class="form-control" id="email" name="email" placeholder="Email">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Name">Contact No:</label>
                                        <input required type="text" class="form-control" id="cellolar" name="cellolar" placeholder="Contact No">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Name">Alternate No:</label>
                                        <input type="text" class="form-control" id="tel1" name="tel1" placeholder="Alternate No">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="category">Status:</label>
                                        <select class="form-control" id="contact_status" name="status" data-live-search="true">
                                            <option value="Y">Active</option>
                                            <option value="N">InActive</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- <div class="col-md-4 cnt-update-option">
                                    <div class="form-group">
                                        <div class="inline form-check">
                                        <label class="form-check-label">
                                            <input type="radio" name="update_option" id="update_option" value="current" checked class="form-check-input">
                                             Current Record Only
                                          <i class="input-helper"></i><i class="input-helper"></i></label>
                                        </div>
                                        <div class="inline form-check">
                                            <label class="form-check-label">
                                                <input type="radio" name="update_option" id="update_option"  value="all" class="form-check-input">
                                                 All
                                              <i class="input-helper"></i><i class="input-helper"></i></label>
                                            </div>
                                    </div>
                                </div> --}}

                            </div>


                            <input type="hidden" name="web_id" id="web_id" value="{{ $web_id }}">
                            <input type="hidden" name="contact_id" id="contact_id">
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
                // $(' #update_pan_form select').each(function() {
                //     if ($(this).attr('readonly') || $(this).attr('disabled')) {
                //         $(this).removeAttr('readonly').removeAttr('disabled');
                //     } else {
                //         $(this).attr('readonly', 'readonly').attr('disabled', 'disabled');
                //     }
                // });

                // Toggle the disabled attribute for the submit button
                // var submitButton = $('#update_pan_form button[type="submit"]');
                // if (submitButton.attr('disabled')) {
                //     submitButton.removeAttr('disabled');
                // } else {
                //     submitButton.attr('disabled', 'disabled');
                // }
            });

            // Initially set all input and select fields to readonly and disabled
            // $('#update_pan_form select').each(function() {
            //     $(this).attr('readonly', 'readonly').attr('disabled', 'disabled');
            // });

            // Initially disable the submit button
            // $('#update_pan_form button[type="submit"]').attr('disabled', 'disabled');


            // $('#update_pan').on('shown.bs.modal', function() {
            //     $('#update_pan_form select').each(function() {
            //         $(this).attr('readonly', 'readonly').attr('disabled', 'disabled');
            //     });
            //     $('#update_pan_form button[type="submit"]').attr('disabled', 'disabled');
            // });
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
                    "url": public_path + '/sap-webid-customers-companies-json',
                    "type": "GET",
                    "data": function(d) {
                        return $.extend({}, d, {
                            'branch_count': $('#branch_count').val() || '',
                            "search_user": $('#search_user').val() || '',
                        });
                    }
                },
                "columns": [
                    {"data": "id", "name": "id", "defaultContent": '-'},
                    // {
                    //     "data": null,
                    //     "defaultContent": '<button class="btn btn-info btn-sm toggle-details">Details</button>'
                    // },
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


                ],
                "order": [
                    [1, "asc"]
                ],
                "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    var page = this.fnPagingInfo().iPage;
                    var length = this.fnPagingInfo().iLength;
                    var index = (page * length + (iDisplayIndex + 1));
                    $('td:eq(0)', nRow).html(index);

                    updatePan =
                        '<td><a href="#update-customer-card" onclick="editPan(' +
                        aData['id'] + ');">' + aData['u_vsppan'] +
                        '</a></td>';

                    $('td:eq(4)', nRow).html(updatePan);


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

                    if (!confirm('Are you sure to Submit?'))
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
                                 location.reload();


                                // $('#branch_table').DataTable().ajax.reload();

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


            $("#update_address_form").validate({
                errorClass: "state-error",
                validClass: "state-success",
                errorElement: "em",
                ignore: [],

                /* @validation rules
                ------------------------------------------ */
                rules: {

                },
                /* @validation error messages
                ---------------------------------------------- */

                messages: {

                },
                submitHandler: function(form) {

                    if (!confirm('Are you sure to Submit?'))
                        return false;

                    $.ajax({
                        url: public_path + '/update_customer_address',
                        method: 'post',
                        data: new FormData($("#update_address_form")[0]),
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
                                 location.reload();

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

            $("#update_contact_form").validate({
                errorClass: "state-error",
                validClass: "state-success",
                errorElement: "em",
                ignore: [],

                /* @validation rules
                ------------------------------------------ */
                rules: {

                },
                /* @validation error messages
                ---------------------------------------------- */

                messages: {

                },
                submitHandler: function(form) {

                    if (!confirm('Are you sure to Submit?'))
                        return false;

                    $.ajax({
                        url: public_path + '/update_customer_contact',
                        method: 'post',
                        data: new FormData($("#update_contact_form")[0]),
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
                                 location.reload();

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

            $('#update-customer-card').show();
            $('#update-address-card').hide();
            $('#update-contact-card').hide();
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

        function editAddress(id) {
                $('#update-address-card').show();
                $('#update-customer-card').hide();
                $('#update-contact-card').hide();
                $('.adres-upd-option').show();
                $('#update-address-card .card-title').html('Update Address');

                $('#address_id').val(id);
                $.ajax({
                    url: public_path + '/get-address-detail/' + id,
                    method: 'get',
                    dataType: 'json',
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        if (result.success == 1) {
                            // Populate form fields with the retrieved data
                            $('#country').val(result.address.country).trigger('change');
                            $('#adres_type').val(result.address.adres_type);
                            $('#street').val(result.address.street);
                            $('#block').val(result.address.block);
                            $('#city').val(result.address.city);
                            $('#zip_code').val(result.address.zip_code);
                            $('#status').val(result.address.active);
                            $('#gst_regn_no').val(result.address.gst_regn_no);
                            defaultGSTStateSection(result.address.state)

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

        function editContact(id) {
                $('#update-contact-card').show();
                $('#update-customer-card').hide();
                $('#update-address-card').hide();
                $('.cnt-update-option').show();
                $('.contact-title').html('Update Contact');

                $('#contact_id').val(id);
                $.ajax({
                    url: public_path + '/get-contact-detail/' + id,
                    method: 'get',
                    dataType: 'json',
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        if (result.success == 1) {
                            // Populate form fields with the retrieved data
                            $('#name').val(result.contact.name);
                            $('#designation').val(result.contact.designation);
                            $('#email').val(result.contact.e_mail_l);
                            $('#cellolar').val(result.contact.cellolar);
                            $('#tel1').val(result.contact.tel1);
                            $('#contact_status').val(result.contact.active);

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

        function defaultGSTStateSection(selectedState = '') {
            var countryID = $('#country').val();
                if (countryID) {
                    $.ajax({
                        url: public_path + '/states_iso2/' + countryID,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            $('#state').empty();
                            $('#state').append('<option value="">Select State</option>');
                            $.each(data, function(key, value) {
                                $('#state').append('<option value="' + value.iso2 + '">' +
                                    value.name + '</option>');
                            });

                            $('#state').val(selectedState).trigger('change');
                        }
                    });
                } else {
                    $('#state').empty();
                }
        }





        function displayNewForm(type) {
            if (type == 'address') {
                $('#update-address-card').show();
                $('.adres-upd-option').hide();
                $('#update-address-card .card-title').html('New Address');
                $('#update_address_form').find('input').not('#web_id, [name="_token"]').val('');
                $('#update_address_form').find('textarea').val('');
                $('#update_address_form #state').val('').trigger('change');
                $('#update_address_form #country').val('IN').trigger('change');

                $('#update-customer-card').hide();
                $('#update-contact-card').hide();
                $('#address_id').val('');
            }

            if (type == 'contact') {
                $('#update-contact-card').show();
                $('.cnt-update-option').hide();

                $('#update_contact_form').find('input').not('#web_id, [name="_token"]').val('');
                $('#update-contact-card .card-title').html('New Contact');

                $('#update-address-card').hide();
                $('#update-customer-card').hide();
                $('#contact_id').val('');
            }
        }

    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            $("#category").select2();
            $("#subcategory").select2();
            $("#sales_emp_name").select2();
            $("#country").select2();
            $("#state").select2();
        });
    </script>
@endpush
