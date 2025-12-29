@extends('layouts.app')
<style>
    .form-control.fields{
        width: 100%;
        height: 35px;
    }
    .modal-content.form{
        width: 700px;
    }
   
</style>
@section('content')
<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Customers</h4>
            <div class="row">
                <div class="col-md-12"> 
                    <div class="row cust_data_form">
                        <div class="col-md-1">
                            <div class="form-group">
                                <select class="form-control" style="width: 100%;" id="page_length">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 margin pull-right no-m-top">
                            <div class="input-group">
                                <input type="text" class="form-control" id="search_customer" placeholder="Search by customer name...">
                            </div>
                        </div>
                        <div class="col-md-3 pull-right">
                            <button class="btn btn-primary" id="add-new-customer">New Customer</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="customers_table" class="table dataTable no-footer">
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <!--<th>Series</th>-->
                                    <th>Customer Code</th>
                                    <th>Customer Name</th>
                                    <!--<th>Card Full Name</th>
                                    <th>Alias Name</th>-->
                                    <th>Customer Type</th>
                                    <th>Currency</th>
                                    <th>Grp COde</th>
                                    <th>Grp Name</th>
                                    <th>Phone 1</th>
                                    <th>Phone 2</th>
                                    <th>Cellular</th>
                                    <th>Email</th>
                                    <th>Grp Number</th>
                                    <th>Credit Line</th>
                                    <th>Price List</th>
                                    <th>Sub Group</th>
                                    <th>NIF No</th>
                                    <th>Store Location</th>
                                    <th>Create Date</th>
                                   
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div> <!-- MainContent Col-12 -->
            </div>
        </div>
    </div>
</div>

<!-- New Customer Modal -->
<div class="modal fade form" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel" aria-hidden="true">
<div class="modal-dialog">
    <div class="modal-content">
        <form id="new-customer-form">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="newCustomerModalLabel">Create New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Row 1: Customer Code and Customer Name -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="customerCode" class="form-label">Customer Code</label>
                        <input type="text" class="form-control fields" id="customerCode" name="customerCode" placeholder="Enter or fetch customer code" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="card_name" class="form-label">Customer Name</label>
                        <input type="text" class="form-control fields" id="card_name" name="card_name" required>
                    </div>
                </div>

                <!-- Row 2: Customer Type and Currency -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="customerType" class="form-label">Customer Type</label>
                        <select class="form-control fields" id="customerType" name="customerType" required>
                            <option value="">Select Customer Type</option>
                            <option value="customer">Customer</option>
                            <option value="vendor">Vendor</option>
                            <option value="lead">Lead</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="currency" class="form-label">Currency</label>
                        <input type="text" value="AKZ" class="form-control fields" id="currency" name="currency" readonly>
                    </div>
                </div>

                <!-- Row 3: Group Code and Group Name -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="grp_code" class="form-label">Group Code</label>
                        <!--<input type="text" class="form-control fields" id="grp_code" name="grp_code" required>-->
                        <select class="form-control fields" id="grp_code" name="grp_code" required>
                            <option value="">Select Customer Type</option>
                            <option value="100">100</option>
                            <option value="101">101</option>
                            <option value="103">103</option>
                            <option value="106">106</option>
                            <option value="108">108</option>
                            <option value="109">109</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="grp_name" class="form-label">Group Name</label>
                        <!--<input type="text" class="form-control fields" id="grp_name" name="grp_name" required>-->
                        <select class="form-control fields" id="grp_name" name="grp_name" required>
                            <option value="">Select Customer Type</option>
                            <option value="customers">Customers</option>
                            <option value="cash sales">Cash Sales</option>
                            <option value="credit sales">Credit Sales</option>
                            <option value="inter company - debtors">Inter Company - Debtors</option>
                            <option value="cabinda cash sales">Cabinda Cash Sales</option>
                            <option value="cabinda credit sales">Cabinda Credit Sales</option>
                        </select>
                    </div>
                </div>

                <!-- Row 4: Phone Number 1 and Phone Number 2 -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="phone1" class="form-label">Phone Number 1</label>
                        <input type="text" class="form-control fields" id="phone1" name="phone1" >
                    </div>
                    <div class="col-md-6">
                        <label for="phone2" class="form-label">Phone Number 2</label>
                        <input type="text" class="form-control fields" id="phone2" name="phone2">
                    </div>
                </div>

                <!-- Row 5: Cellular and Email -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="cellular" class="form-label">Cellular</label>
                        <input type="text" class="form-control fields" id="cellular" name="cellular">
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" class="form-control fields" id="email" name="email" >
                    </div>
                </div>

                <!-- Row 6: Group Number and Credit Line -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="grp_num" class="form-label">Group Number</label>
                        <input type="text" class="form-control fields" id="grp_num" name="grp_num">
                    </div>
                    <div class="col-md-6">
                        <label for="credit_line" class="form-label">Credit Line</label>
                        <select class="form-control fields" id="credit_line" name="credit_line" required>
                            <option value="immediate">Immediate</option>
                        </select>
                    </div>
                </div>

                <!-- Row 7: Price List and Sub Group -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="list_num" class="form-label">Price List</label>
                        <input type="text" class="form-control fields" id="list_num" name="list_num">
                    </div>
                    <div class="col-md-6">
                        <label for="sub_grp" class="form-label">Sub Group</label>
                        <!--<input type="text" class="form-control fields" id="sub_grp" name="sub_grp">-->
                        <select class="form-control fields" id="sub_grp" name="sub_grp">
                            <option value="">Select Sub Group Type</option>
                            <option value="general">General</option>
                            <option value="hospital">Hospital</option>
                        </select>
                    </div>
                </div>

                <!-- Row 8: NIF Number and Store Location -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nif_no" class="form-label">NIF Number</label>
                        <input type="text" class="form-control fields" id="nif_no" name="nif_no" required>
                    </div>
                    <div class="col-md-6">
                        <label for="store_location" class="form-label">Store Location</label>
                        <input type="text" class="form-control fields" id="storeLocation" name="store_location" >
                    </div>
                </div>
                <br>
                <h6>Customer Addresses</h6>
                <hr>
                <h6 style="text-align:center;">Shipping Address</h6>
                <div class="row mt-2" id="contact-addresses-input-row">
                    <!-- Shipping Address Section -->
                    <div class="shipping-address-section">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="country">Country:</label>
                                    <mat-form-field>
                                        <input class="form-control fields" name="country" id="country" matInput placeholder="Country" value="ANGOLA">
                                    </mat-form-field>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="state">State:</label>
                                    <select class="form-control fields" name="state" id="states">
                                        <option value="">Select State</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="new_city">City:</label>
                                    <input type="text" class="form-control fields" name="new_city" id="new_city" placeholder="City">
                                </div>
                            </div>
                        </div>    
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address">Address:</label>
                                    <textarea class="form-control fields" name="address" id="address" placeholder="Address"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Radio Buttons for Billing Address -->
                    <div style="display: flex; flex-direction: column; align-items: center;">
                        <label style="margin-bottom: 10px;">Is Your Billing Address Same as Shipping?</label>
                        <div style="display: flex; justify-content: center;">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="billing_same_as_shipping" id="billing_yes" value="1" onclick="toggleBillingAddress(true)">
                                <label class="form-check-label" style="margin-right:30px;" for="billing_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="billing_same_as_shipping" id="billing_no" value="0" onclick="toggleBillingAddress(false)">
                                <label class="form-check-label" for="billing_no">No</label>
                            </div>
                        </div>
                    </div>
                    <hr>

                    <!-- Billing Address Section -->
                    <div id="billing-address-section" style="display: none;">
                        <h6 style="text-align:center;">Billing Address</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="country_billing">Country:</label>
                                    <mat-form-field>
                                        <input class="form-control fields" name="country_billing" id="country_billing" matInput placeholder="Country" value="ANGOLA">
                                    </mat-form-field>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="state_billing">State:</label>
                                    <select class="form-control fields" name="state_billing" id="state_billing">
                                        <option value="">Select State</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="city_billing">City:</label>
                                    <input type="text" class="form-control fields" name="city_billing" id="city_billing" placeholder="City">
                                </div>
                            </div>
                        </div>    
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address_billing">Address:</label>
                                    <textarea class="form-control fields" name="address_billing" id="address_billing" placeholder="Enter Address" rows="3">
                                    </textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Customer</button>
            </div>
        </form>
    </div>
</div>


</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var customersTable = $('#customers_table').DataTable({
        "dom": '<"html5buttons"B>tp',
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "{{ url('/customers-json') }}",
            "type": "GET",
            "data": function(d) {
                d.search_customer = $('#search_customer').val();
                d.page_length = $('#page_length').val();
            }
        },
        "columns": [
            {"data": "id"},
            {"data": "card_code"},
            {"data": "card_name"},
            {"data": "card_typ_nm"},
            {"data": "currency"},
            {"data": "grp_code"},
            {"data": "grp_name"},
            {"data": "phone1"},
            {"data": "phone2"},
            {"data": "cellular"},
            {"data": "email"},
            {"data": "grp_num"},
            {"data": "credit_line"},
            {"data": "list_num"},
            {"data": "sub_grp"},
            {"data": "nif_no"},
            {"data": "store_location"},
            {"data": "create_date"},
        ]
    });

    $('#search_customer').on('keyup', function() {
        customersTable.draw();
    });

    $('#page_length').change(function() {
        customersTable.page.len($(this).val()).draw();
    });

    $('#add-new-customer').click(function() {
        $('#newCustomerModal').modal('show');
    });

    $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        // Add the class to maintain custom styling
        $("#cust_address_id").on("select2:open", function () {
            $(".select2-container").addClass("form-controls");
        });

    $('#new-customer-form').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
        url: "{{ route('store.new.customer') }}",
        type: "POST",
        data: formData,
        success: function(response) {
            if (response.success) {

                $('#newCustomerModal').modal('hide');
               customersTable.ajax.reload();
                    
                    location.reload();

            } else {
                alert(response.message);
            }
        },
        error: function(xhr) {
            alert(xhr.responseJSON.message);
        }
    });
});

});

$(document).ready(function () {
   
        $.ajax({
            url: 'get-next-customer-code', 
            method: 'GET',
            success: function (response) {
                if (response.customerCode) {
                    $('#customerCode').val(response.customerCode);
                    $('#storeLocation').val(response.wareHouse);

                }
            },
            error: function (xhr, status, error) {
                console.error('Error fetching customer code:', error);
            }
        });
    });

    function toggleBillingAddress(sameAsShipping) {
    if (sameAsShipping) {
        $('#country_billing').val($('#country').val()).prop('disabled', true);
        $('#state_billing').val($('#states').val()).prop('disabled' , true);
        $('#city_billing').val($('#new_city').val()).prop('disabled', true);
        $('#address_billing').val($('#address').val()).prop('disabled', true);

        $('#billing-address-section').show();

        $('#country, #state, #new_city, #address').on('change', function () {
            if ($('input[name="billing_same_as_shipping"]:checked').val() === "1") {
                $('#country_billing').val($('#country').val());
                $('#state_billing').val($('#states').val());
                $('#city_billing').val($('#new_city').val());
                $('#address_billing').val($('#address').val());
            }
        });
    } else {
        $('#billing-address-section').find('input, select, textarea').val('').prop('disabled', false);
        $('#billing-address-section').show();
    }
}


$(document).ready(function() {
    fetchStates();

    function fetchStates() {
        $.ajax({
            url: public_path + '/get-states',
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response && Array.isArray(response)) {
                    populateStatesDropdown('#states', response);
                    populateStatesDropdown('#state_billing', response);
                } else {
                    console.error('Unexpected response format:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching states:', error);
            }
        });
    }

    function populateStatesDropdown(selector, states) {
        let dropdown = $(selector);
        dropdown.empty(); // Clear any existing options
        dropdown.append('<option value="">Select State</option>'); // Add default option
        states.forEach(function(state) {
            dropdown.append('<option value="' + state.id + '">' + state.name + '</option>');
        });
    }
});

</script>
@endpush
