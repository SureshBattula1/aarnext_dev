<form id="gst_customer_create_form" action="javascript:void(0)" method="POST">
    {{ csrf_field() }}

    <div class="row" id="contactNumberGroup">
        <div class="col-md-2 form-group">
            <label class="inlinelabel" for="contactNumber">GSTIN</label>
        </div>

        <div class="col-md-4">
            <div class="input-group">
                <input type="text" class="form-control" id="gstNumber" name="gstNumber"
                    placeholder="Enter GSTIN Number">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="button" id="searchGST"><i class="ti-search"></i></button>
                </div>
            </div>
            <span id="processingTextGST" style="display: none;">Processing...</span>
        </div>
    </div>

    <div id="resultGST"></div>
    <div id="companyCustomerCreationGroup" style="display: none;">
        <p class="text-danger">Customer <span id="gstnodisp"></span> GSTIN does not exist.</p>
        <hr>
        <div class="row mt-2">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="card_name">CustomerName:</label>
                    <input type="text" class="form-control" id="gst_card_name" name="gst_card_name"
                        placeholder="Customer Name">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="salesEmpName">SalesEmpName:</label>
                    <select class="form-control" name="gst_sales_emp_name" id="gst_sales_emp_name"
                        data-live-search="true">
                        <option selected value="">Select SaleEmpName</option>
                        @foreach ($saleEmpNames as $saleemp)
                            <option
                            @if ($data['username'] == $saleemp->slp_name)
                                selected
                            @endif
                            value="{{ $saleemp->slp_name }}">{{ $saleemp->slp_name }}
                            </option>
                        @endForeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select class="form-control" name="gst_category" id="gst_category" data-live-search="true">
                        <option selected value="">Select Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->name }}">{{ $category->name }}
                            </option>
                        @endForeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="subcategory">SubCategory:</label>
                    <select class="form-control" name="gst_subcategory" id="gst_subcategory" data-live-search="true">
                        <option selected value="">Select SubCategory</option>
                        @foreach ($subcategories as $scategory)
                            <option value="{{ $scategory->name }}">{{ $scategory->name }}
                            </option>
                        @endForeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="card_code">GST Status:</label>
                    <input type="text" readonly class="form-control" id="gst_status" name="gst_status"
                        placeholder="GST Status">
                </div>
            </div>
        </div>
        <hr>
        <h6>Customer Addresses <button type="button" class="btn btn-success btn-rounded" onclick="addGSTAddressRow()"><i
                    class="ti-plus"></i></button> </h6>

        <div class="row  mt-2">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="gst_adres_type">Address Type:</label>
                    <select required class="form-control input-sm" name="gst_adres_type" id="gst_adres_type">
                        <option value="">Select Type</option>
                        <option value="B">Bill To</option>
                        <option value="S">Ship To</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="street">Street:</label>
                    <textarea rows="5" class="form-control" id="gst_street" name="gst_street" placeholder="Address"></textarea>
                </div>
                <div class="form-group">
                    <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" name="copy_address" id="copy_address" class="form-check-input">
                         Copy Address
                      <i class="input-helper"></i></label>
                    </div>
                </div>
            </div>
            {{-- <div class="col-md-4">
                <div class="form-group">
                    <label for="card_name">Block:</label>
                    <input type="text" class="form-control" id="gst_block" name="gst_block" placeholder="Block">
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="category">Address2:</label>
                    <input type="text" class="form-control" id="gst_address2" name="gst_address2"
                        placeholder="Address2">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="category">Address3:</label>
                    <input type="text" class="form-control" id="gst_address3" name="gst_address3"
                        placeholder="Address3">
                </div>
            </div> --}}
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="subcategory">Country:</label>
                            <select class="form-control" onchange="defaultGSTStateSection()" name="gst_country"
                                id="gst_country" data-live-search="true">
                                <option selected value="">Select Country</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->name }}">{{ $country->name }}
                                    </option>
                                @endForeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category">State:</label>
                            <select class="form-control" id="gst_state" name="gst_state" data-live-search="true">
                                <option selected value="">Select State</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category">City:</label>
                            <select class="form-control" id="gst_city" name="gst_city" data-live-search="true">
                                <option selected value="">Select City</option>
                            </select>
                        </div>
                    </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="salesEmpName">ZipCode:</label>
                            <input type="text" class="form-control" id="gst_zip_code" name="gst_zip_code"
                                placeholder="ZipCode">
                        </div>
                    </div>
                    <div class="col-md-2"></div>
                </div>
            </div>


            <div id="gst-addresses-input-row"></div>

            <!-- Contacts Person -->
            <hr>

            <h6>Customer Contacts <button type="button" class="btn btn-success btn-rounded" onclick="addRow()"><i class="ti-plus"></i></button> </h6>

            <div id="gst-input-row">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <input type="text" class="form-control" name="gst_cnt_name[]" placeholder="Name">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <input type="text" class="form-control" name="gst_cnt_desig[]"
                                placeholder="Designation">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <input type="text" class="form-control" name="gst_cnt_contactno[]"
                                placeholder="ContactNo">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <input type="text" class="form-control" name="gst_cnt_tel1[]"
                                placeholder="Alter. ContactNo">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <input type="text" class="form-control" name="gst_cnt_email[]" placeholder="Email">
                        </div>
                    </div>
                    <div class="col-md-1">
                    </div>
                </div>
            </div>



            <div class="col-md-4">
                <div class="form-group">

                    <button class="btn btn-primary" type="submit" id="gst_submitForm">Submit</button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        function addRow() {
            const rowId = generateUUID(); // Generate a unique ID for the row
            const row = `
        <div class="row form-row" id="${rowId}">
                    <div class="col-md-3">
                        <div class="form-group">
                            <input type="text" class="form-control" name="gst_cnt_name[]"
                                placeholder="Name">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <input type="text" class="form-control" name="gst_cnt_desig[]"
                                placeholder="Designation">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <input type="text" class="form-control"  name="gst_cnt_contactno[]"
                                placeholder="ContactNo">
                        </div>
                    </div>
                     <div class="col-md-2">
                        <div class="form-group">
                            <input type="text" class="form-control" name="gst_cnt_tel1[]"
                                placeholder="Alter. ContactNo">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <input type="text" class="form-control"  name="gst_cnt_email[]"
                                placeholder="Email">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger" onclick="removeRow('${rowId}')">X</i></button>

                    </div>
                </div>
    `;

            $("#gst-input-row").append(row);
        }

        function addGSTAddressRow() {
            const rowId = generateUUID(); // Generate a unique ID for the row
            const row = `
            <div class="row" id="${rowId}">
                <hr />
                    <div class="col-md-4">
                        <div class="form-group">
                        <label for="adres_type">Address Type:</label>
                        <select required class="gst_addtl_adres_type form-control input-sm" name="gst_addtl_adres_type[]">
                            <option value="">Select Type</option>
                            <option value="B">Bill To</option>
                            <option value="S">Ship To</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="street">Street:</label>
                        <textarea rows="7" class="gst_addtl_street form-control" name="gst_addtl_street[]" placeholder="Address"></textarea>

                    </div>
                </div>
                 <div class="col-md-8">
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subcategory">Country:</label>
                                <select class="form-control searchSelect gst-addtl-country" name="gst_addtl_country[]"
                                    data-live-search="true">
                                    <option selected value="">Select Country</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->name }}">{{ $country->name }}
                                        </option>
                                    @endForeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">State:</label>
                                <select class="form-control gst_addtl_state" name="gst_addtl_state[]" data-live-search="true">
                                    <option selected value="">Select State</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">City:</label>
                                <select class="form-control gst_addtl_city" name="gst_addtl_city[]" data-live-search="true">
                                    <option selected value="">Select City</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="salesEmpName">ZipCode:</label>
                                <input type="text" class="form-control gst_addtl_zip_code" name="gst_addtl_zip_code[]"
                                    placeholder="ZipCode">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <br />
                                <button type="button" class="btn btn-danger" onclick="removeGSTAddressesRow('${rowId}')">X</i></button>

                            </div>
            </div>
            `;

            $('#gst-addresses-input-row').append(row);
            $(`#${rowId} .gst-addtl-country`).select2();
            $(`#${rowId} .gst_addtl_state`).select2();
            $(`#${rowId} .gst_addtl_city`).select2();
        }

        function generateUUID() {
            return uuid.v4(); // Generate a random UUID
        }

        function removeRow(rowId) {
            $("#" + rowId).remove();
        }
        function removeGSTAddressesRow(rowId) {
            $("#" + rowId).remove();
        }

        $(document).ready(function() {
            $('#copy_address').change(function() {

                    if ($(this).is(':checked')) {

                        $('.gst-addtl-country:first').val($('#gst_country').val()).trigger('change');

                        $(document).ajaxComplete(function(event, xhr, settings) {
                            if (settings.url.includes('/states/')) {
                                $('.gst_addtl_state:first').val($('#gst_state').val()).trigger('change');
                            }
                        });

                        $(document).ajaxComplete(function(event, xhr, settings) {
                            if (settings.url.includes('/cities/')) {
                                $('.gst_addtl_city:first').val($('#gst_city').val()).trigger('change');
                            }
                        });

                        $('.gst_addtl_street:first').val($('#gst_street').val());
                        $('.gst_addtl_zip_code:first').val($('#gst_zip_code').val());

                    } else {
                        $('.gst-addtl-country:first').val('').trigger('change');
                        $('.gst_addtl_state:first').val('').trigger('change');
                        $('.gst_addtl_city:first').val('').trigger('change');
                        $('.gst_addtl_street:first').val('');
                        $('.gst_addtl_zip_code:first').val('');
                    }
            });
        });
    </script>
@endpush
