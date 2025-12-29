@extends('layouts.app')

@section('content')
<style>
    .content-wrapper {
        /* max-width: 1200px; */
        margin: 10px auto;
        background-color: #fefefe;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .customer-details {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        background-color: #e9f5f9;
        padding: 15px;
        border-radius: 6px;
        font-size: 14px;
        color: #333;
    }
    .table td {
        padding: 2px ;
    }
    .customer-details label {
        font-weight: 600;
        margin-bottom: 5px;
        display: block;
        color: #007b8f;
    }
    .customer-details input {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #f9f9f9;
        font-size: 14px;
    }
    table.table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
        margin-bottom: 20px;
    }
    table.table thead tr {
        background-color: #007b8f;
        color: white;
        font-weight: 600;
        font-size: 14px;
    }
    table.table thead th {
        padding: 10px 12px;
        text-align: left;
        border-radius: 6px 6px 0 0;
    }
    table.table tbody tr.items {
        background-color: #f0f8fb;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    table.table tbody tr.items td select.form-control,
    table.table tbody tr.items td input.form-control {
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 6px 8px;
        font-size: 14px;
        width: 100%;
        background-color: white;
    }
    .btn-sm.btn-success {
        background-color: #027b90;
        border: none;
        margin:25px;
        font-weight: 700;
        font-size: 18px;
        line-height: 25px;
        padding: 4px 10px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .btn-sm.btn-success:hover {
        background-color: #218838;
    }
    .btn-sm.btn-danger {
        background-color: #dc3545;
        border: none;
        font-weight: 700;
        font-size: 18px;
        line-height: 1;
        padding: 4px 10px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .btn-sm.btn-danger:hover {
        background-color: #c82333;
    }
    .summary-section {
        max-width: 350px;
        background-color: #e9f5f9;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 0 8px rgba(0,0,0,0.1);
        font-size: 14px;
        color: #333;
        margin-left: auto;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-weight: 600;
    }
    .summary-label {
        color: #007b8f;
    }
    .summary-value {
        background-color: white;
        padding: 4px 10px;
        border-radius: 4px;
        min-width: 80px;
        text-align: right;
        border: 1px solid #ccc;
        font-weight: 700;
    }
    button.btn-primary {
        background-color: #007b8f;
        border: none;
        padding: 10px 25px;
        font-size: 16px;
        font-weight: 700;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        color: white;
        display: block;
        margin: 0 auto 30px auto;
    }
    button.btn-primary:hover {
        background-color: #005f6b;
    }
    .select2-container--default .select2-selection--single {
        width: 420px !important;
    }
    .select2-container--default .select2-selection--single {
        height: 36px !important;
        padding-right: 24px; /* space for the arrow */
        position: relative;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        position: absolute;
        top: 0 !important;
        right: 0 !important;
        width: 24px !important;
        pointer-events: none; /* makes sure arrow doesn't block input focus */
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .select2-container--default .select2-dropdown {
        width: 600px !important;
    }
    .updateBtn {
            max-width: 350px;
            padding: 15px 20px;
            margin-left: auto;
    }
</style>

<div class="content-wrapper">
    <form id="salesOrderForm" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" id="order_id" name="order_id" value="{{ $order->id }}">
        {{-- <input type="hidden" name="removed_item_ids" id="removed_item_ids" value=""> --}}
        <input type="hidden" name="removed_item_codes" id="removed_item_codes" value="">

        <div>
            <h4 class="page_lable_title1"> EDIT SALES ORDER</h4>
        </div>
        <div class="customer-details">
            <div style="flex:1;">
                <label>Customer Name</label>
                <input type="text" value="{{ $customer->card_name }}" readonly>
            </div>
            <div style="flex:1;">
                <label>Customer Code</label>
                <input type="text" id="customer_id" name="customer_id" value="{{ $customer->card_code }}" readonly>
            </div>
            {{-- <div style="flex:1;">
                <label>Order Date</label>
                <input type="date" name="order_date" value="{{ $order->order_date }}">
            </div> --}}
        </div>

        <div class="d-flex justify-content-end mb-2">
            <button type="button" id="addRow" class="btn btn-sm btn-success">
                New Row +
            </button>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 40%;">Item</th>
                    <th style="width: 8%; text-align: center;">Qty</th>
                    <th style="width: 12%; text-align: right;">Price</th>
                    <th style="width: 10%; text-align: center;">Discount %</th>
                    <th style="width: 10%; text-align: right;">Discount Amt</th>
                    <th style="width: 5%; text-align: center;">Tax %</th>
                    <th style="width: 20%; text-align: right;">Total</th>
                    <th style="width: 10%; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody class="items-row">
                @foreach ($orderItems as $item)
                <tr class="items">
                    <input type="hidden" name="row_item_id[]" value="{{ $item->id }}">
                <td>
                    <select name="row_item_no[]" class="form-control select2-item" style="width: 100%;">
                        <option value="{{ $item->item_no }}" selected>{{$item->item_no}}-{{ $item->item_desc }}</option>
                    </select>
                </td>
                    <td><input type="number" name="row_item_qty[]" class="form-control" value="{{ $item->quantity }}"></td>
                    <td><input type="text" name="row_item_price[]" class="form-control" value="{{ $item->price }}"></td>
                    <td><input type="text" name="row_item_disc_percent[]" class="form-control" value="{{ $item->disc_percent }}"></td>
                    <td><input type="text" name="row_item_disc[]" class="form-control" value="{{ $item->disc_value }}"></td>
                    <td><input type="text" name="row_item_gst_rate[]" class="form-control" value="{{ $item->gst_rate }}" readonly></td>
                    <td><input type="text" name="row_item_total[]" class="form-control" value="{{ $item->price_total_af_disc }}" readonly></td>
                    <td>
                    <button type="button" class="bor_none remove-row-btn removeRow">
                        <img src="{{ asset('images/close-icon.png') }}" alt="Close" title="Close" style="width:22px; height:22px;">
                    </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- <div class="summary-section">
            <div class="summary-item">
                <div class="summary-label">Sub Total:</div>
                <div id="subtotal" class="summary-value">0.00</div>
                <input type="hidden" id="subtotal" name="subtotal" value="">


            </div>
            <div class="summary-item">
                <div class="summary-label">Total Discount:</div>
                <div id="total_discount" class="summary-value">0.00</div>
                <input type="hidden" id="total_discount" name="total_discount" value="">

            </div>
            <div class="summary-item">
                <div class="summary-label">Price Total After Discount:</div>
                <div id="total_price_af_disc" class="summary-value">0.00</div>
                <input type="hidden" id="total_price_af_disc" name="total_price_af_disc" value="">
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Tax Value:</div>
                <div id="total_gst_value" class="summary-value">0.00</div>
                <input type="hidden" id="total_gst_value" name="total_gst_value" value="">
            </div>
            <div class="summary-item">
                <div class="summary-label">Grand Total:</div>
                <div id="grand_total" class="summary-value">0.00</div>
                <input type="hidden" id="grand_total" name="grand_total" value="">
            </div>
        </div> --}}
        <div class="updateBtn">
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    const baseUrl = window.location.origin;

    function initializeSelect2(element, type) {

    element.select2({
        ajax: {
            url: public_path + "/item-search/item_no",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                const cardCode = $('#customer_id').val();

                if (!cardCode) {
                    $('.customerSec').addClass('validation-error');
                    $('#customerError').text('Please select a customer.').show();
                    $('#customer_id').attr('required', 'required');
                } else {
                    $('.customerSec').removeClass('validation-error');
                    $('#customerError').hide();
                    $('#customer_id').removeAttr('required');
                }
                return {
                    search: params.term,
                    type: 'user_search',
                    card_code: cardCode
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(item => ({
                        id: item.result?.item_code,
                        text: item.item_name, // fallback if template fails
                        item_code: item.result?.item_code,
                        item_name: item.result?.item_name,
                        price: item.result?.price,
                        tax: item.result?.tax
                    }))
                };
            },
            cache: true
        },
        placeholder: 'Search item...',
        minimumInputLength: 3,
        // templateResult: function (item) {
        //     if (item.loading) return item.text;
        //     return $('<span>' + (item.item_code || item.text) + '</span>');
        // },
        templateResult: function (item) {
            if (item.loading) return item.text;

            const code = item.item_code || '';
            const name = item.item_name || item.text || '';
            const price = item.price ? `₹${item.price}` : '₹0';

            return $(`<span>${code} - ${name} - ${price}</span>`);
        },
        templateSelection: function (item) {
            if (item.item_code && item.item_name && item.price) {
                return item.item_code + ' - ' + item.item_name + ' - ' + item.price;
            }
            return item.text || item.id;
        }
    }).on('select2:select', function (e) {
        const selectedData = e.params.data;
        const row = $(this).closest('tr');

        row.find('input[name="row_item_no[]"]').val(selectedData.item_code);
        row.find('input[name="row_item_price[]"]').val(selectedData.price);
        row.find('input[name="row_item_gst_rate[]"]').val(selectedData.tax);

        const discPercent = 0;
        const discValue = (selectedData.price * discPercent) / 100;

        row.find('input[name="row_item_disc_percent[]"]').val(discPercent.toFixed(2));
        row.find('input[name="row_item_disc[]"]').val(discValue.toFixed(2));

        calculate_total(row);
    });
}

    function calculate_total(row) {
        const qty = parseFloat(row.find('input[name="row_item_qty[]"]').val()) || 0;
        const price = parseFloat(row.find('input[name="row_item_price[]"]').val()) || 0;
        let disc = parseFloat(row.find('input[name="row_item_disc[]"]').val()) || 0;
        let discPercent = parseFloat(row.find('input[name="row_item_disc_percent[]"]').val()) || 0;

        // Validate discount values
        if (discPercent > 99.99) {
            alert('Discount percentage cannot exceed 99.99%.');
            discPercent = 99.99;
            row.find('input[name="row_item_disc_percent[]"]').val(discPercent.toFixed(2));
        }
        if (disc > price) {
            alert('Discount amount cannot exceed price.');
            disc = price;
            row.find('input[name="row_item_disc[]"]').val(disc.toFixed(2));
        }

        // Sync discount amount and percentage
        if (document.activeElement === row.find('input[name="row_item_disc_percent[]"]')[0]) {
            disc = (price * discPercent) / 100;
            row.find('input[name="row_item_disc[]"]').val(disc.toFixed(2));
        } else if (document.activeElement === row.find('input[name="row_item_disc[]"]')[0]) {
            discPercent = (disc / price) * 100;
            row.find('input[name="row_item_disc_percent[]"]').val(discPercent.toFixed(2));
        }

        const netPrice = price - disc;
        const tax = parseFloat(row.find('input[name="row_item_gst_rate[]"]').val()) || 0;
        const taxAmt = (netPrice * tax / 100) * qty;
        const total = (netPrice * qty) + taxAmt;

        row.find('input[name="row_item_total[]"]').val(total.toFixed(2));

        updateSummary();
    }

    function updateSummary() {
        let subtotal = 0;
        let totalDiscount = 0;
        let totalPriceAfterDiscount = 0;
        let totalTaxValue = 0;
        let grandTotal = 0;

        $('.items').each(function () {
            const qty = parseFloat($(this).find('input[name="row_item_qty[]"]').val()) || 0;
            const price = parseFloat($(this).find('input[name="row_item_price[]"]').val()) || 0;
            const disc = parseFloat($(this).find('input[name="row_item_disc[]"]').val()) || 0;
            const tax = parseFloat($(this).find('input[name="row_item_gst_rate[]"]').val()) || 0;

            const netPrice = price - disc;
            const taxAmt = (netPrice * tax / 100) * qty;
            const total = (netPrice * qty) + taxAmt;

            subtotal += price * qty;
            totalDiscount += disc * qty;
            totalPriceAfterDiscount += netPrice * qty;
            totalTaxValue += taxAmt;
            grandTotal += total;
        });

        $('#subtotal').text(subtotal.toFixed(2));
        $('#total_discount').text(totalDiscount.toFixed(2));
        $('#total_price_af_disc').text(totalPriceAfterDiscount.toFixed(2));
        $('#total_gst_value').text(totalTaxValue.toFixed(2));
        $('#grand_total').text(grandTotal.toFixed(2));
    }

    function addRow() {
        const row = $(`
            <tr class="items">
                <input type="hidden" name="row_item_id[]" value="">
                <td><select name="row_item_no[]" class="form-control select2-item" style="width: 100%;"></select></td>
                <td><input type="number" name="row_item_qty[]" class="form-control" value="1"></td>
                <td><input type="text" name="row_item_price[]" class="form-control" value=""></td>
                <td><input type="text" name="row_item_disc_percent[]" class="form-control" value=""></td>
                <td><input type="text" name="row_item_disc[]" class="form-control" value=""></td>
                <td><input type="text" name="row_item_gst_rate[]" class="form-control" value="" readonly></td>
                <td><input type="text" name="row_item_total[]" class="form-control" value="" readonly></td>
                <td>
                    <button type="button" class="bor_none remove-row-btn removeRow">
                        <img src="{{ asset('images/close-icon.png') }}" alt="Close" title="Close" style="width:22px; height:22px;">
                    </button>
                </td>
            </tr>
            <tr>
                <td colspan="8">
                    <div class="batchs"></div>
                </td>
            </tr>
        `);
        $('.items-row').append(row);
        initializeSelect2(row.find('.select2-item'));
    }

    $(document).on('click', '#addRow', function () {
        addRow();
    });

    $(document).on('click', '.removeRow', function () {
        const row = $(this).closest('tr');
        const removedId = row.find('[name="row_item_no[]"]').val(); // fixed

        if (removedId) {
            let removedIds = $('#removed_item_codes').val();
            removedIds = removedIds ? removedIds.split(',') : [];

            removedIds.push(removedId);
            $('#removed_item_codes').val(removedIds.join(','));
        }

        row.remove();
        updateSummary();
    });

    $(document).on('input', 'input[name="row_item_qty[]"], input[name="row_item_price[]"], input[name="row_item_disc[]"], input[name="row_item_disc_percent[]"], input[name="row_item_gst_rate[]"]', function () {
        calculate_total($(this).closest('tr'));
    });

    $('#salesOrderForm').on('submit', function (e) {
        e.preventDefault();

        const orderId = $('#order_id').val();
        const formData = $(this).serialize();

        $.ajax({
            url: public_path + '/salesorder/update/' + orderId,
            type: 'POST',
            data: formData,
            success: function (response) {
                alert('Sales order updated successfully!');
                window.location.href = public_path + '/order-payments-list/' + orderId;
            },
            error: function (xhr) {
                alert('Failed to update. Check console.');
                console.error(xhr.responseText);
            }
        });
    });

    $(document).ready(function () {
        $('.select2-item').each(function () {
            const selectElement = $(this);
            // If the select has a selected option, initialize select2 with that option
            if (selectElement.find('option:selected').length) {
                const selectedOption = selectElement.find('option:selected');
                const optionData = {
                    id: selectedOption.val(),
                    text: selectedOption.text()
                };
                selectElement.select2({
                    data: [optionData],
                    placeholder: 'Search item...',
                    minimumInputLength: 2,
                    ajax: {
                        url: baseUrl + "/sales-item-search/item_no",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            const cardCode = $('#customer_id').val();
                            if (!cardCode) {
                                alert('Please select a customer first.');
                                return false;
                            }
                            return {
                                search: params.term,
                                card_code: cardCode
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.map(item => ({
                                    id: item.item_id,
                                    text: item.item_desc,
                                    price: item.price,
                                    tax: item.tax,
                                    item_code: item.item_code || item.item_desc
                                }))
                            };
                        },
                        cache: true
                    },
                    templateResult: function (item) {
                        if (item.loading) return item.text;
                        return $('<span>' + (item.item_code || item.text) + '</span>');
                    },
                    templateSelection: function (item) {
                        return item.item_code || item.text || item.id;
                    }
                }).on('select2:select', function (e) {
                    const selectedData = e.params.data;
                    const row = $(this).closest('tr');

                    $.getJSON(baseUrl + "/get-item/" + selectedData.id + "/" + $('#customer_id').val(), function (data) {
                        row.find('input[name="row_item_price[]"]').val(data.price);
                        row.find('input[name="row_item_gst_rate[]"]').val(data.tax);

                        const discPercent = 0;
                        const discValue = (data.price * discPercent) / 100;

                        row.find('input[name="row_item_disc_percent[]"]').val(discPercent.toFixed(2));
                        row.find('input[name="row_item_disc[]"]').val(discValue.toFixed(2));

                        calculate_total(row);
                    });
                });
            } else {
                initializeSelect2(selectElement);
            }
        });
        updateSummary();
    });
</script>
@endpush
