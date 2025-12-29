@extends('layouts.app')

@section('content')
    <div class="container">
        <form id="customer_create_form" action="{{ route('store.new.customer') }}" method="POST">
            {{ csrf_field() }}



            <div id="result"></div>

            <div id="indCustomerCreationGroup" style="display: none">
                <p class="text-danger"><span id="cntnodisp"></span> Contact number does not exist.</p>
                <hr>

                <div class="row mt-2">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="card_name">Customer Name:</label>
                            <input type="text" class="form-control" id="card_name" name="card_name" placeholder="Customer Name">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="name">Sales Employee Name:</label>
                            <select class="form-control" name="name" id="name">
                                <option selected value="">Select Sales Employee</option>
                               
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="category">Category:</label>
                            <select class="form-control" name="category" id="category">
                                <option selected value="">Select Category</option>
                                {{--@foreach ($categories as $category)
                                    <option value="{{ $category->name }}">{{ $category->name }}</option>
                                @endforeach--}}
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="subcategory">SubCategory:</label>
                            <select class="form-control" name="subcategory" id="subcategory">
                                <option selected value="">Select SubCategory</option>
                                {{--@foreach ($subcategories as $subcategory)
                                    <option value="{{ $subcategory->name }}">{{ $subcategory->name }}</option>
                                @endforeach--}}
                            </select>
                        </div>
                    </div>
                </div>
                <hr>

                <h6>Customer Addresses</h6>
                <div class="row mt-2">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="street">Street:</label>
                            <textarea rows="7" class="form-control" id="street" name="street" placeholder="Street"></textarea>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Country:</label>
                                    <select class="form-control" name="country" id="country" onchange="defaultContactStateSection()">
                                        <option selected value="">Select Country</option>
                                        {{--@foreach ($countries as $country)
                                            <option value="{{ $country->name }}">{{ $country->name }}</option>
                                        @endforeach--}}
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state">State:</label>
                                    <select class="form-control" id="state" name="state">
                                        <option selected value="">Select State</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="city">City:</label>
                                    <select class="form-control" id="city" name="city">
                                        <option selected value="">Select City</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="zip_code">Zip Code:</label>
                                    <input type="text" class="form-control" id="zip_code" name="zip_code" placeholder="Zip Code">
                                    <a class="location-link" href="javascript:void(0)" onclick="getLocationByZipcode()">Get Location</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <button class="btn btn-primary" type="submit" id="submitForm">Submit</button>
                            <span id="processingTextSubmit" style="display: none;">Processing...</span>
                        </div>
                    </div>
                </div>

                <div id="contact-addresses-input-row"></div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function defaultContactStateSection() {
        // Your JS code to handle state changes
    }

    function getLocationByZipcode() {
        // Your JS code to fetch location data by zipcode
    }

    function addContactAddressRow() {
        const rowId = generateUUID(); // Generate a unique ID for the row
        const row = `
            <div class="row" id="${rowId}">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="gst_addtl_street[]">Street:</label>
                        <input type="text" class="form-control" name="gst_addtl_street[]" placeholder="Street">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="gst_addtl_block[]">Block:</label>
                        <input type="text" class="form-control" name="gst_addtl_block[]" placeholder="Block">
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-danger" onclick="removeRow('${rowId}')">X</button>
                </div>
            </div>
        `;
        $("#contact-addresses-input-row").append(row);
    }

    function removeRow(rowId) {
        $(`#${rowId}`).remove();
    }

    function generateUUID() {
        return 'xxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
</script>
@endpush
