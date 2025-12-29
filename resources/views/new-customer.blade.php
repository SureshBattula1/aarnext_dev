@extends('layouts.app')
<style>
    .form-control.fields {
        width: 100%;
        height: 35px;
    }

    .modal-content.form {
        width: 700px;
    }
    .req-fields{
        font-weight:bold;
        color:red;
    }
    .text-right {
        text-align: right;
    }
    .text-left {
        text-align: left;
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
                                    <input type="text" class="form-control" id="search_customer" autocomplete="off"
                                        placeholder="Search by customer name...">
                                </div>
                            </div>
                            @if(auth()->user()->role !== 1 && auth()->user()->role !== 5)
                            <div class="col-md-3 pull-right">
                                <button class="btn btn-primary" id="add-new-customer">New Customer</button>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-12">
                            <div class="loading-spinner" style="display:none;">
                                <div class="jumping-dots-loader">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
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
                                        <th>NIF No</th>
                                        <th>Group Name</th>
                                        <th>Sub Group</th>
                                        <th>Credit Limit</th>
                                        <th>Price List</th>
                                        <th>Factor </th>
                                        <th>Store Location</th>
                                        <th>Mobile Number</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Customer Update</th>
                                       {{-- <th>City</th> --}}
                                       
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
    <div class="modal fade form" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="new-customer-form" autocomplete="off" >
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="newCustomerModalLabel">Create New Customer</h5>
                        
                        <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="removeModel()" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Row 1: Customer Code and Customer Name -->
                        <div class="row mb-3">
                            <!--<div class="col-md-6">
                                <input type="hidden" id="custId" name="custId">
                                <label for="customerCode" class="form-label">Customer Code </label>
                                <input type="text" class="form-control fields" id="customerCode" name="customerCode"
                                    placeholder="Enter or fetch customer code" readonly>
                            </div>-->
                            <div class="col-md-6">
                                <label for="customerType" class="form-label">Customer Type </label>
                                <select class="form-control fields" id="customerType" name="customerType" required>
                                    <option value="customer">Customer</option>
                                    <!--<option value="vendor">Vendor</option>
                                    <option value="lead">Lead</option>-->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="card_name" class="form-label">Customer Name <span class="req-fields">*</span></label>
                                <input type="text" class="form-control fields" id="card_name" name="card_name" autocomplete="off" required>
                            </div>
                        </div>

                        <!-- Row 2: Customer Type and Currency -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="sub_grp" class="form-label">Sub Group <span class="req-fields">*</span></label>
                                <!--<input type="text" class="form-control fields" id="sub_grp" name="sub_grp">-->
                                <select class="form-control fields" id="sub_grp" name="sub_grp" required>
                                    <option value="">Select Sub Group Type</option>
                                    <option value="GENERAL">General</option>
                                    <option value="HOSPITAL">Hospital</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="currency" class="form-label">Currency</label>
                                <input type="text" value="{{ $customer_data->currency }}" class="form-control fields" id="currency"
                                    name="currency" readonly>
                            </div>
                        </div>


                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="grp_name" class="form-label">Group Name </label>
                                <!--<select class="form-control fields" id="grp_name" name="grp_name" required>
                                    <option value="">Select Customer Type</option>
                                    @foreach ($group_data as $group)
                                        <option value="{{ $group->name }}" data-code="{{ $group->code }}">
                                            {{ $group->name }}</option>
                                    @endforeach
                                </select>-->
                                <input type="text" value="{{ $customer_data->group_name }}" class="form-control fields" id="grp_name" name="grp_name" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="grp_code" class="form-label">Group Code </label>
                                <!--<select class="form-control fields" id="grp_code" name="grp_code" required>
                                    <option value="">Select Group Code</option>
                                </select>-->
                                <input type="text" value="{{ $customer_data->group_code }}" class="form-control fields" id="grp_code" name="grp_code" readonly>
                            </div>
                        </div>

                        <!-- Row 4: Phone Number 1 nd Phaone Number 2 -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone1" class="form-label">Phone Number <span class="req-fields">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend" style="height: 35px;">
                                        <span class="input-group-text">(+244)</span>
                                    </div>
                                    <input type="text" 
                                        class="form-control fields" 
                                        id="phone1" 
                                        name="phone1" 
                                        pattern="^9[1-9][0-9]{7}$" 
                                        title="Enter a valid Angolan phone number starting with 9 followed by 8 digits." 
                                        required>
                                    <div class="invalid-feedback">
                                        Please enter a valid Angolan phone number (e.g., 9XXXXXXXX).
                                    </div>
                                </div>
                                
                            </div>
                            <!--<div class="col-md-6">
                                <label for="phone2" class="form-label">Phone Number 2</label>
                                <input type="text" class="form-control fields" id="phone2" name="phone2">
                            </div>-->
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" class="form-control fields" id="email" name="email">
                            </div>
                        </div>

                        <!-- Row 5: Cellular and Email -->
                        <div class="row mb-3">
                            <!--<div class="col-md-6">
                                <label for="cellular" class="form-label">Cellular</label>
                                <input type="text" class="form-control fields" id="cellular" name="cellular">
                            </div>-->
                            
                            <div class="col-md-6">
                                <label for="credit_line" class="form-label">Payment Term </label>
                                    <!--<select class="form-control fields" id="credit_line" name="credit_line" required>
                                        <option value="immediate">Immediate</option>
                                    </select>-->
                                    <input type="text" value="{{ $customer_data->payment_term_name }}" class="form-control fields" id="credit_line" name="credit_line" readonly>
                            </div>
                             <!-- Row 6: Group Number and Credit Line -->
                             <div class="col-md-6">
                                <label for="store_location" class="form-label">Store Location</label>
                                <input type="text" class="form-control fields" id="storeLocation"
                                    name="store_location" readonly>
                            </div>
                        </div>

                        <!-- Row 7: Price List and Sub Group -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="list_num" class="form-label">Price List</label>
                                <input type="text" class="form-control fields" id="product_list" name="product_list" value="{{ $customer_data->price_list_name }}" readonly>
                            </div>
                            <input type="hidden" class="form-control fields" id="list_num" name="list_num" value="{{ $customer_data->price_list_code }}" readonly>
                            <div class="col-md-6">
                                <label for="nif_no" class="form-label">NIF Number <span class="req-fields">*</span></label>
                                <input type="text" class="form-control fields" id="nif_no" name="nif_no"
                                    required>
                            </div>
                            
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="statusCheckbox">Customer <span id="statusLabel">Active</span></label>
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" id="statusCheckbox" value="1" name="is_active" checked aria-checked="true">
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
                                                <input class="form-control fields" name="country" id="country" matInput
                                                    placeholder="Country" value="ANGOLA">
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
                                            <input type="text" class="form-control fields" name="new_city"
                                                id="new_city" placeholder="City">
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
                                        <input class="form-check-input" type="radio" name="billing_same_as_shipping"
                                            id="billing_yes" value="1" onclick="toggleBillingAddress(true)">
                                        <label class="form-check-label" style="margin-right:30px;"
                                            for="billing_yes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="billing_same_as_shipping"
                                            id="billing_no" value="0" onclick="toggleBillingAddress(false)">
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
                                                <input class="form-control fields" name="country_billing"
                                                    id="country_billing" matInput placeholder="Country" value="ANGOLA">
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
                                            <input type="text" class="form-control fields" name="city_billing"
                                                id="city_billing" placeholder="City">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="address_billing">Address:</label>
                                            <textarea class="form-control fields" name="address_billing" id="address_billing" placeholder="Enter Address"
                                                rows="3">
                                    </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="removeModel()" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('phone1').addEventListener('input', function () {
        if (this.validity.valid) {
            this.style.borderColor = 'green';
        } else {
            this.style.borderColor = 'red';
        }
    });
</script>
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
                    },
                    "beforeSend": function() {
                        $('.loading-spinner').show();
                    },
                    "complete": function() {
                        $('.loading-spinner').hide();
                    }
                },
                "columns": [
                    {
                        "data": "id"
                    },
                    {
                        "data": "card_code"
                    },
                    {
                        "data": "card_name"
                    },
                    {
                        "data": "nif_no"
                    },
                    {
                        "data": "grp_name"
                    },
                    {
                        "data": "sub_grp"
                    },
                    {
                        "data": "credit_line"
                    },
                    {
                        "data": "list_num"
                    },
                    {
                        "data": "factor"
                    },                   
                    {
                        "data": "store_location"
                    },
                    {
                        "data": "phone1"
                    },
                    {
                        "data": "email"
                    },
                    {
                       "data": "status",
                       "render": function(data, type, full, meta) {
                            if(data == 1){
                                return 'Active';
                            }else{
                                return 'Inactive';
                            }
                        }

                    },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            return '<button class="btn btn-warning edit-customer" data-id="' + row.id + '">Edit</button>';
                        }
                    },
                ]
            });

            $('#search_customer').on('keyup', function() {
                customersTable.draw();
            });

            $('#page_length').change(function() {
                customersTable.page.len($(this).val()).draw();
            });

            $('#add-new-customer').click(function() {
                //$('#new-customer-form')[0].reset(); // Reset the form
                //location.reload();

                $('#newCustomerModal').modal('show');

            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            // Add the class to maintain custom styling
            $("#cust_address_id").on("select2:open", function() {
                $(".select2-container").addClass("form-controls");
            });


            $('#grp_name').on('change', function() {
                var selectedOption = $(this).find(':selected');
                var groupCode = selectedOption.data('code');
                var grpCodeDropdown = $('#grp_code');
                grpCodeDropdown.empty();
                if (groupCode) {
                    grpCodeDropdown.append('<option value="' + groupCode + '">' + groupCode + '</option>');
                } else {
                    grpCodeDropdown.append('<option value="">Select Group Code</option>');
                }
            });

            $('#new-customer-form').on('submit', function (e) {
                e.preventDefault();
                var formData = $(this).serialize();
                var customerId = $(this).attr('data-id');
                var $submitButton = $(this).find('button[type="submit"]');
                var url = customerId 
                    ? "{{ url('customer-update') }}/" + customerId 
                    : "{{ route('store.new.customer') }}";
                $submitButton.prop('disabled', true).text('Processing...');

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    success: function (response) {
                        if (response.success) {
                            $('#newCustomerModal').modal('hide');
                            alert(customerId ? "Customer updated successfully!" : "Customer created successfully!");
                            location.reload();
                        } else {
                            alert(response.message || "An error occurred.");
                        }
                        $submitButton.prop('disabled', false).text(customerId ? 'Update Customer' : 'Save Customer');

                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON?.message || "An error occurred.");
                        $submitButton.prop('disabled', false).text(customerId ? 'Update Customer' : 'Save Customer');
                    }
                });
            });

        });

        $(document).ready(function() {
            $.ajax({
                url: 'get-next-customer-code',
                method: 'GET',
                success: function(response) {
                    if (response.customerCode) {
                        $('#customerCode').val(response.customerCode);
                        $('#storeLocation').val(response.wareHouse);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching customer code:', error);
                }
            });
        });

        function toggleBillingAddress(sameAsShipping) {
            if (sameAsShipping) {
                $('#country_billing').val($('#country').val()).prop('disabled', true);
                $('#state_billing').val($('#states').val()).prop('disabled', true);
                $('#city_billing').val($('#new_city').val()).prop('disabled', true);
                $('#address_billing').val($('#address').val()).prop('disabled', true);

                $('#billing-address-section').show();

                $('#country, #state, #new_city, #address').on('change', function() {
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
                dropdown.empty(); 
                dropdown.append('<option value="">Select State</option>'); 
                states.forEach(function(state) {
                    dropdown.append('<option value="' + state.id + '">' + state.name + '</option>');
                });
            }
        });
        function removeModel(){
            location.reload();
        }
    </script>
    <script>
       $(document).on('click', '.edit-customer', function() {
            var customerId = $(this).data('id');
            
            $.ajax({
                url: "{{ url('/customer-show') }}/" + customerId, 
                type: "GET",
                success: function(response) {
                    if (response.success) {
                        var customer = response.customer; 
                        //$('#custId').val(customer.id);
                        $('#new-customer-form').attr('data-id', customer.id);
                        $('#customerCode').val(customer.card_code);
                        $('#card_name').val(customer.card_name);
                        $('#customerType').val(customer.card_typ_nm).prop('disabled', true);
                        $('#currency').val(customer.currency).prop('disabled', true);;
                        $('#grp_name').val(customer.grp_name).change().prop('disabled', true); 
                        $('#phone1').val(customer.phone1).prop('disabled', true);
                        $('#email').val(customer.email).prop('disabled', true);
                        $('#cellular').val(customer.cellular).prop('disabled', true);
                        $('#credit_line').val(customer.credit_line).prop('disabled', true);
                        $('#list_num').val(customer.list_num).prop('disabled', true);
                        $('#sub_grp').val(customer.sub_grp).prop('disabled', true);
                        $('#nif_no').val(customer.nif_no).prop('disabled', true);
                        $('#storeLocation').val(customer.store_location).prop('disabled', true);
                        $('#address').val(customer.address).prop('disabled', true);
                        $('#new_city').val(customer.city).prop('disabled', true);
                        $('#states').val(customer.state).change().prop('disabled', true); 
                        $('#country').val(customer.country).prop('disabled', true);
                        if (customer.status == 1) {
                            $('#statusCheckbox').prop('checked', true).val(1);
                            $('#statusLabel').text('Active');
                            $('#statusCheckbox').attr('aria-checked', 'true');
                        } else {
                            $('#statusCheckbox').prop('checked', false).val(0);
                            $('#statusLabel').text('Inactive');
                            $('#statusCheckbox').attr('aria-checked', 'false');
                        }
                        $('#billing-address-section').hide(); 
                        
                        $('#newCustomerModal').modal('show');
                        
                    } else {
                        alert("Failed to fetch customer data.");
                    }
                },
                error: function(xhr) {
                    alert("Failed to fetch customer data.");
                }
            });
        });
        
    </script>
     <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkbox = document.getElementById('statusCheckbox');
            const statusLabel = document.getElementById('statusLabel');

            function updateStatusLabel() {
                if (checkbox.checked) {
                    checkbox.value = 1;
                    statusLabel.textContent = 'Active';
                    checkbox.setAttribute('aria-checked', 'true');
                } else {
                    checkbox.value = 0;
                    statusLabel.textContent = 'Inactive';
                    checkbox.setAttribute('aria-checked', 'false');
                }
            }

            checkbox.addEventListener('change', updateStatusLabel);
        });
    </script>
@endpush
