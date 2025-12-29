@extends('layouts.app')
@section('content')
 <form id="new_quotation_form" action="javascript:void(0)" method="POST">
    {{ csrf_field() }}

    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">New Quotation</h4>
                <div class="row">
                    <div class="col-md-12"> <!-- MainContent Col-12 -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="customer_id">Customer:</label>
                                    <select required onchange="customerSection()" class="form-control "
                                        name="customer_id" id="customer_id" data-live-search="true">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address">Address:</label>
                                    <select required class="form-control " name="address" id="address"
                                        data-live-search="true">
                                        <option selected value="">Select Address</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="contact">Contact:</label>
                                    <select required class="form-control " name="contact" id="contact"
                                        data-live-search="true">
                                        <option selected value="">Select Contct</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                    </div> <!-- MainContent Col-12 -->
                </div>
            </div>
        </div>



        <div class="card items-card">
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="branch_table" class="table no-footer small-padding">
                            <thead>
                                <tr class="table-primary">
                                    <th width="10%" align="left"><i class="fa fa-exclamation-circle"
                                            aria-hidden="true" data-toggle="tooltip"
                                            data-title="New lines are not supported for item description. Use the item long description instead."
                                            data-original-title="" title=""></i> Item No</th>

                                    <th width="20%" align="left">Description</th>
                                    <th width="10%" class="qty" align="center">QTY<br />UOM</th>
                                    <th width="13%" align="right">Unit Price</th>
                                    <th width="10%" align="right">Discount<p>Discount %</p></th>
                                    <th width="15%" align="right">Price after Disc. <p>Total After Disc.
                                        </p></th>
                                    <th width="10%" align="right">Tax % <br /> Tax Value</th>
                                    <th width="20%" style="text-align: right" align="right">Total</th>
                                    <th width="5%" align="right"></th>
                                </tr>
                            </thead>
                            <tbody class="items-row">

                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="7" align="right"><strong>Total </strong></td>
                                    <td id="grand_total" align="right">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <br />
        <div class="btn-bottom-toolbar bottom-transaction text-right">
            <button class="btn btn-primary mleft5 transaction-submit" type="submit">Save</button>
            <button type="button" class="btn btn-danger mleft10"> Clear </button>
       </div>

    </div>
</form>

@endsection




@push('scripts')
    <script>
        $(document).ready(function() {
            // Handle radio button change

            $("#new_quotation_form").validate({
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

                    if ($('tr.items').length === 0) {
                        Swal.fire({
                            type: 'warning',
                            title: 'At least one item is required.',
                            showConfirmButton: true,
                        });
                        return false;
                    }


                    if (!confirm('Are you sure to Submit?'))
                        return false;

                    $('#processingTextSubmit').show();
                    $.ajax({
                        url: public_path + '/store-quotation',
                        method: 'post',
                        data: new FormData($("#new_quotation_form")[0]),
                        dataType: 'json',
                        async: false,
                        cache: false,
                        processData: false,
                        contentType: false,
                        success: function(result) {
                            $('#processingTextSubmit').hide();
                            if (result.success == 1) {

                                Swal.fire({
                                    type: 'success',
                                    title: result.message,
                                    showConfirmButton: true,
                                    // timer: 1500
                                });
                                location.reload();

                            } else {
                                Swal.fire({
                                    type: 'warning',
                                    title: result.message,
                                    showConfirmButton: true,
                                    // timer: 1500
                                });
                                // swal("Error", result.message, "warning");
                            }
                        },
                        error: function(error) {
                            if (error) {
                                var error_status = error.responseText;
                                alert(error_status.message);
                            }
                            $('#processingTextSubmit').hide();
                        }
                    });

                }
            });


        });

        $(document).ready(function() {
            $("#customer_id").select2({
                ajax: {
                    url: public_path + "/quotation-customer-search",
                    dataType: 'json',
                    data: function(params) {
                        var query = {
                            search: params.term,
                            type: 'user_search'
                        }

                        // Query parameters will be ?search=[term]&type=user_search
                        return query;
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    }
                },
                cache: true,
                placeholder: 'Search for a Customer...',
                allowClear: true,
                debug: true,
                minimumInputLength: 3
            });
        });

        $("#item").select2({
            ajax: {
                url: public_path + "/item-search",
                dataType: 'json',
                data: function(params) {
                    var query = {
                        search: params.term,
                        type: 'user_search'
                    }

                    // Query parameters will be ?search=[term]&type=user_search
                    return query;
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                }
            },
            cache: true,
            placeholder: 'Search for a Item...',
            allowClear: true,
            minimumInputLength: 3
        });

        function customerSection() {
            var customerId = $('#customer_id').val();
            if (customerId) {
                $.ajax({
                    url: public_path + '/get-cust-addrs-contacts/' + customerId,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('#address').empty();
                        $('#contact').empty();
                        $('#address').append('<option value="">Select Address</option>');
                        $('#contact').append('<option value="">Select Contact</option>');
                        $.each(data.addresses, function(key, value) {
                            $('#address').append('<option value="' + value.id + '">' +
                                value.street + ' ' + value.city + ' ' + value.state + ' ' + value
                                .country + '-' + value.zip_code + '</option>');
                        });

                        $.each(data.contacts, function(key, value) {
                            $('#contact').append('<option value="' + value.id + '">' +
                                value.name + ' ' + value.cellolar + ' ' + value.e_mail_l +
                                '</option>');
                        });
                    }
                });
            } else {
                // $('#address').empty();
                // $('#contact').empty();
            }
        }

        function itemSelected() {
            var itemId = $('#item').val();
            if (itemId) {
                $.ajax({
                    url: public_path + '/get-item/' + itemId,
                    type: "GET",
                    dataType: "json",
                    success: function(res) {

                        if (res.success) {
                            $('#item_id').val(res.item.id);
                            $('#item_no').val(res.item.item_no);
                            $('#item_desc').val(res.item.item_desc);
                            $('#uom_code').val(res.item.uom_code);
                            $('#gst_rate').val(res.item.gst_rate);
                            $('#price').val(res.item.price);

                        } else {
                            alert(res.message)
                        }

                    }
                });
            } else {
                // $('#address').empty();
                // $('#contact').empty();
            }
        }



        function delete_item(item) {

            var $row = $(event.target).closest('tr');
            var itemId = $row.find('.row_item_id').val();

            $.ajax({
                url: public_path + '/delete-cart-item',
                method: 'POST',
                data: {
                    item_id: itemId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(result) {
                    if (result.success == 1) {
                        $(item).closest('tr').remove();
                        grandTotal();

                        Swal.fire({
                            type: 'success',
                            title: result.message,
                            timer: 1000
                        });
                    } else {
                        Swal.fire({
                            type: 'warning',
                            title: result.message,
                            showConfirmButton: true,
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Something went wrong. Please try again later.',
                    });
                }
            });


        }




        function calculate_total(caller = 0) {
            var $row = $(event.target).closest('tr');
            var qty = parseFloat($row.find('.qty-input').val()) || 0;
            var price = parseFloat($row.find('.row_item_price').text()) || 0;

            if (price == 0)
                return;

            var discount = parseFloat($row.find('.row_discunt_val input').val()) || 0;
            var discountPercent = parseFloat($row.find('.dis_percentage_val input').val()) || 0;

            if (caller === 2) {
                discount = (price * discountPercent) / 100;
                $row.find('.row_discunt_val input').val(discount.toFixed(2));
            }

            if (caller === 1) {
                if (price > 0) {
                    discountPercent = (discount / price) * 100;
                    $row.find('.dis_percentage_val input').val(discountPercent.toFixed(2));
                }
            }

            var priceAfterDiscount = price - discount;
            $row.find('.price_af_discount').text(priceAfterDiscount.toFixed(2));

            var totalAfterDiscount = priceAfterDiscount * qty;
            $row.find('.total_af_dis').text(totalAfterDiscount.toFixed(2));

            var taxPercent = parseFloat($row.find('.item_tax_pecent').text()) || 0;
            var taxValue = (totalAfterDiscount * taxPercent) / 100;
            var taxValuePerItem =  (priceAfterDiscount * taxPercent) / 100;
            $row.find('.item_tax_value').text(taxValuePerItem.toFixed(2));

            $row.find('.amount').text((totalAfterDiscount + taxValue).toFixed(2));

            grandTotal();
        }

        function grandTotal() {
            var grandTotal = 0;
            $('.items').each(function() {
                var finalAmount = parseFloat($(this).find('.amount').text()) || 0;
                grandTotal += finalAmount;
            });
            $('#grand_total').text(grandTotal.toFixed(2));
        }

    </script>

    <script type="text/javascript">
        $(document).ready(function() {

            $("#new_subcategory").select2();
            $("#address").select2();
            $("#contact").select2();

        });
    </script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let items = @json($items);
        items.forEach(item => {
            displayItemToTable(item);
        });
    });

    function displayItemToTable(item) {
        let item_no = item.item_no;
        let item_desc = item.item_desc;
        let quantity = item.quantity;
        let uom_code = item.uom_code;
        let price = parseFloat(item.price).toFixed(2);
        let gst_rate = item.gst_rate;

        let price_af_disc = price;
        let price_total_af_disc = quantity * price;
        let tax_value = price * gst_rate / 100;
        let total = price_total_af_disc * (gst_rate / 100 + 1);

        let row = `
        <tr class="items">
                <input type="hidden" class="row_item_id" value="${item.id}">
            <td class="bold"><p class="row_item_code">${item_no}</p></td>
            <td><textarea  class="form-control noborder row_item_desc" rows="3">${item_desc}</textarea></td>
            <td><input type="number" min="1" onblur="calculate_total();" name="row_item_qty" value="${quantity}" class="form-control qty-input"><p>${uom_code}</p>
            </td>
            <td class="rate" id="rate"><p class="row_item_price">${price}</p></td>
            <td>
                <div class="row_discunt_val">
                    <input type="number" onblur="calculate_total(1);"  name="row_item_disc[]" id="row_discount" class="form-control qty-input" min="0" value="0.00">
                </div>
                    <div class="dis_percentage_val">
                        <input type="number" name="row_item_disc_percent[]" min="0" onblur="calculate_total(2);" onchange="calculate_total(2);" id="dis_percentage" value="0.00" class="form-control qty-input">
                    </div>
            </td>
            <td><p class="price_af_discount"> ${price_af_disc}<p>
                <p class="total_af_dis">${price_total_af_disc}<p>
            </td>
            <td>
                <p class="item_tax_pecent">${gst_rate}</p>
                <p class="item_tax_value">${tax_value}</p>
            </td>
            <td align="right"><p class="amount">${total.toFixed(2)}<p></td>
            <td>
                <button type="button" onclick="delete_item(this); return false;" class="btn pull-right btn-danger btn-sm">
                    <i class="ti-trash"></i>
                </button>
            </td>
        </tr>`;

        $('.items-row').append(row);
        grandTotal(); // Call the function to update the grand total
    }
</script>

@endpush
