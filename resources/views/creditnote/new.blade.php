@extends('layouts.app')
<style>
    .quotation-form-table .form-control {
        font-size: 5px;
        padding: 7px;
        height: 23px;
        width: 63px;
    }


    .select2-container--default .select2-dropdown {
        font-size: 0.8125rem;
        display: inline-grid;
    }



    .row-item-group {
        display: flex;
        flex-direction: column;
        width: 882px;
    }

    /* First line layout */
    .row-item-top {
        display: flex;
        justify-content: space-between;
        gap: 25px;
        /* 05-11-2024 */
        width: 115%;
        background-color: rgb(253, 255, 248);

        /* Adds space between the two rows */
    }

    /* Second line layout */
    .row-item-bottom {
        display: flex;
        justify-content: space-between;
        /*gap: 15px;*/
        /* 05-11-2024 */
        /*width: 120%;*/
        margin-top: 3px;
        /*margin-bottom: 0.5%;*/
        background-color: rgb(253, 255, 248);
    }



    .first-order {
        /*margin: 10px;*/
        /*display: flex;*/
    }

    .row_item_qty {
        width: 100px;

    }

    .rate input,
    .row_discount,
    .price_af_discount,
    .total_af_dis,
    .item_tax_percent,
    .tax_value {
        width: 100px;

    }

    .btn-danger {
        margin-left: 10px;

    }

    .lable {
        padding: 10px;

        font-size: 12px;
        font-weight: bold;
        color: rgb(47, 85, 86)
    }

    .tot-amt {
        display: flex;
        flex-direction: column;
        align-items: flex-end;

    }

    .table-light td {
        padding: 8px;
        font-weight: bold;
        text-align: right;
        white-space: nowrap;
    }

    .select2-search--dropdown {
        display: block;
        width: 902px;
        padding: 4px;
        background-color: white;
    }

    .select2-container--default .select2-results>.select2-results__options {
        max-height: 200px;
        overflow-y: auto;
        background-color: white;
    }

    .items-first-order {
        margin-bottom: 40px;
        margin-top: 40px;
        background-color: gray;
    }

    .highlight {
        font-weight: bold;
        color: rgb(56 80 86)
    }

    table {
        border-radius: 1px solid black;
    }

    .blue {
        color: rgb(45, 144, 250);
    }

    /*.customer-details {
        background-color: aliceblue;
        padding-top: 10px;
    }*/
    .card .card-title {
        color: #787878;
        text-transform: capitalize;
        font-size: 0.875rem;
        text-align: center;
        font-weight: 500;
        width: 1000px;
    }

    .align-items-center,
    .navbar .navbar-menu-wrapper .navbar-nav.navbar-nav-right .nav-item,
    .navbar .navbar-menu-wrapper .navbar-nav .nav-item.dropdown .navbar-dropdown .dropdown-item,
    .navbar .navbar-menu-wrapper .navbar-nav .nav-item.nav-profile,
    .navbar .navbar-menu-wrapper .navbar-nav .nav-item.nav-settings,
    .navbar .navbar-menu-wrapper .navbar-nav,
    .email-wrapper .message-body .attachments-sections ul li .details .buttons,
    .email-wrapper .message-body .attachments-sections ul li .thumb,
    .list-wrapper ul li,
    .loader-demo-box {
        align-items: center !important;
        margin-bottom: 8px;
    }

    .content {
        text-align: center;
    }


    .row-item-top .item_code,
    .row-item-top .row_item_qty {
        flex: 1;
        gap: 300px;
    }

    .quotation-item_code {
        width: 350px;
    }


    .header-body {
        margin-top: 5px;
    }


    .quotation-form-table .select2-container--default .select2-selection--single {
        height: 25px;
    }

    .batch-align {
        display: flex;
        align-items: center;
        color: rgb(27 141 27);
        gap: 4px;
        font-size: 8px;
    }

    .batchs {
        width: 100px;
        background-color: honeydew;
    }


    .table-header {
        display: flex;
        background-color: #dceefd;
        font-weight: bold;
        padding: 1px 0;
        border-bottom: 1px solid #ddd;
        font-size: 12px;
        border-right: 1px solid #c9ccd7;
        background: #126680 !important;
        color: #ffffff !important;
        font-size: 14px !important;
        font-weight: bold !important;
        padding: 0px 8px !important;
    }

    .header-item {
        text-align: center;
        padding: 8px 1px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .header-item:first-child {
        text-align: left;

    }

    .form-control.qty {
        width: 50px;
    }

    .sub-tot-amt {
        margin-right: 80px;
    }

    .batche-details {
        font-size: 10px;
        font-weight: bold;
        color: green;
    }

    .batch-info {
        font-size: 7px;
        color: #149b20;
    }

    .item-dropdown {
        width: 600px;
        height: 28px;
        border-color: #d3d7be;
        font-size: 10px;
        font-weight: bold;
    }

    .extra-info {
        font-size: 9px;
        background-color: aliceblue;
        font-weight: 500px;
    }

    .second-row-container {
        display: flex;
        gap: 2px;
        justify-content: flex-start;
        /* Align items to the left */
        align-items: center;
        /* Vertically center the items */
    }

    .quantities {
        width: 30px;
        height: 10px;
    }

    .form-control.tax {
        height: 2px !important;
        width: 60px;
    }

    /*.summary-section{
        margin-top: 15px;
        width: 400px;
        height: 300px;
        background-color: #f0f0f0;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0);
        text-align:center;
    }*/

    .summary-section {
        margin-top: 20px;
        padding: 15px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
        width: 300px;
        /* Set the width of the summary section */
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2px;
        /* Adds space between each row */
    }

    .summary-label {
        font-weight: solid;
        font-size: 12px;
        flex-basis: 80%;
        /* Label takes up 60% of the width */
        text-align: left;
    }

    .summary-value {
        text-align: right;
        width: 150px;
        min-width: 70px;
        /* Ensure a minimum width */
        background-color: #f4f4f4;
        font-size: 11px;
        padding: 5px 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }

    .summaryTbl {
        margin-left: 30px;
        margin-top: 20px;
        text-align: right;
        /*justify-self: right;*/
    }

    .btn.btn-primary.addNewRow {
        width: 3px;
        height: 4px;
        font-size: 8px;
        max-width: 3px;
        display: flex;
        flex-wrap: nowrap;
        justify-content: space-around;
        align-items: center;
    }

    .select2-selection__clear {
        display: none;
    }
    .add-batch{
        margin-left: 90px;
        text-align:center;
    }
    button.batch-details:disabled {
        background-color: lightgray !important;
        border-color: lightgray !important;
        color: #ffffff !important;
        cursor: not-allowed;
    }

    button.batch-details {
        background-color: var(--bs-primary) !important;
        border-color: var(--bs-primary) !important;
    }
    .bottom-transaction{
        margin: 1rem !important;
    }
</style>
@section('content')
<form id="new_quotation_form" action="javascript:void(0)" method="POST" autocomplete="off">
    {{ csrf_field() }}


    <div class="content-wrapper quo-item-dop">
        <div class="content">
            <h4 class="page_lable_title1">CREDIT NOTE</h4>
        </div>
        <div class="card">
            <div class="card-body card_bg1">
                <div class="row">
                    <div class="col-md-7">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="20%" class="pada0">
                                    <div class="sec_bor1 fs14" for="customer_id" style="padding:4px 10px;">Customer:</div>
                                </td>
                                <td width="80%" class="pada0 customerSec">
                                    <select required class="form-control md-select2 fs-12" name="customer_id" id="customer_id" onchange="fetchInvoiceOrder(this.value);" data-live-search="true" tabindex="1">
                                    </select>
                                    <div id="customerError" class="error-message"></div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-5 d-flex">
                        <div class="row">
                        <input type="hidden" id="selected_invoice_no" name="invoice_no" value="">
                        <input type="hidden" id="address_id" name="address_id" value="">

                            <div class="col-md-12">
                                <select id="sales_order_id" class="form-control select2-quotation" onchange="fillInvoiceOrderData();" tabindex="3">
                                    <option selected value="">Select Invoice Order</option>
                                    <!-- Options will be populated dynamically -->
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row details2row">
                        <div class="col-md-2">
                            <input  type="text" id="sales_emp_id" name="sales_emp_id" readonly placeholder="Sales Employee Name">
                        </div>
                        <div class="col-md-4">
                            <table class="" width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="5%" class="pada0"></td>
                                    <td width="15%" class="pada0">
                                        <div class="sec_bor1 address" style="">Motivo:</div>
                                    </td>
                                    <td width="85%" class="pada0">
                                        <select class="form-control" id="motivo" name="motivo" tabindex="">
                                            <option value="" disabled selected>Select Motivo</option>
                                            <option value="Bill Made By Error">Bill Made By Error</option>
                                            <option value="Client Don't have sufficient Amount">Client Don't have sufficient Amount</option>
                                            <option value="We Don't Have Physical Products Available for Delivery">We Don't Have Physical Products Available for Delivery</option>
                                            <option value="Client Want Other Product">Client Want Other Product</option>
                                            <option value="Due to Near Expiry">Due to Near Expiry</option>
                                            <option value="Due to Damaged">Due to Damaged</option>
                                            <option value="TPA Machine Is Not Working">TPA Machine Is Not Working</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <input type="hidden" name="customer_sub_grp" id="customer_sub_grp">
                        </div>

                        <div class="customer-details">

                            <div id="current-date" class="value_bg1"> </div>
                            <div class="customer-pymnt_grp value_bg1"></div>
                            <div class=" invoice-no value_bg1"></div>
                            <div class=" nif_no value_bg1 col-md-2"></div>
                        </div>
                        <div class="col-md-6">

                        </div>

                    </div>
                </div>
            </div>

            <div class="card items-card">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="branch_table" class="table no-footer small-padding small-heading quotation-form-table">
                                <thead>
                                    <tr class="table-primary">

                                        <th width="2%"><input type="checkbox" id="select-all" onclick="toggleAllCheckboxes(this)"></th>
                                        <th width="10%" align="left">Item No</th>
                                        <th width="8%" align="center">&nbsp;Qty&nbsp;</th>
                                        <th width="8%" align="center">&nbsp;Unit Price&nbsp;&nbsp;</th>
                                        <th width="8%" align="center">Discount</th>
                                        <th width="8%" align="center">Discount %</th>
                                        <th width="8%" align="center">Price after Disc.</th>
                                        <th width="8%" align="center">&nbsp;&nbsp;Tax&nbsp;&nbsp;</th>
                                        <th width="8%" align="center">&nbsp;&nbsp;Total&nbsp;&nbsp;</th>
                                        <th width="26%" align="center"></th>
                                        <th width="3%" align="center">

                                            <div class="col-md-3">
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="items-row">
                                    <tr class="items">
                                        {{-- <td>
                                            <input onclick="
                                                ()" type="checkbox" name="row_item_id[]">
                                        </td> --}}
                                        <input type="hidden" name="row_item_id[]">
                                </tbody>
                                <tfoot>

                                </tfoot>
                            </table>

                            <div id="update_user" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"></h5>
                                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Batch Number</th>
                                                        <th>Quantity</th>
                                                        <th>Expiry Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="batch-details-table">
                                                    <!-- Batch details will be dynamically populated here -->
                                                </tbody>
                                            </table>
                                            <!-- Optional: Add new batch fields -->
                                             @if(Auth::user()->role == 1)
                                             <div id="new-batch-fields" style="display: flex;gap:10px">
                                                <div class="form-group col-6">
                                                    <label for="new-batch-num">Batch Number</label>
                                                    <input type="text" id="new-batch-num" class="form-control">
                                                </div>
                                                <div class="form-group col-6">
                                                    <label for="new-batch-qty">Quantity</label>
                                                    <input type="number" id="new-batch-qty" class="form-control">
                                                </div>
                                            </div>
                                             @endif
                                            
                                            <!--<input type="hidden" id="expiry_date" class="form-control">-->
                                            <input type="hidden" name="random_number_ge" id="random_number_ge">
                                            <button type="button" id="add-new-batch" class="btn btn-secondary">Add New Batch</button>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" id="submit-batches" class="btn btn-primary add-batch" data-bs-dismiss="modal">Add Batch</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="summaryTbl">
                                <div class="row summaryTable">

                                    <div class="remarks col-4">
                                        <div class="remarks-section ">
                                            <label for="remarks">Remarks:</label>
                                            <textarea name="remarks" id="remarks" class="form-control" rows="3"></textarea>
                                        </div>
                                        <input type="hidden" name="ref_id" id="ref_id">
                                    </div>
                                    <div class="col-3"></div>
                                    <div class="summary-section col-4">
                                        <h5>Summary</h5>
                                        <div class="summary-item">
                                            <div class="summary-label"><strong>Sub Total:</strong></div>
                                            <div id="subtotal" class="summary-value">0.00</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="summary-label"><strong>Total Discount:</strong></div>
                                            <div id="total_discount" class="summary-value">0.00</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="summary-label"><strong>Price Total After Discount:</strong></div>
                                            <div id="total_price_af_disc" class="summary-value">0.00</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="summary-label"><strong>Total Tax Value:</strong></div>
                                            <div id="total_gst_value" class="summary-value">0.00</div>
                                        </div>
                                        <div class="summary-item">
                                            <div class="summary-label"><strong>Grand Total:</strong></div>
                                            <div id="grand_total" class="summary-value">0.00</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn-bottom-toolbar bottom-transaction text-right mt-3">
                <button class="btn btn-primary mleft5 transaction-submit" type="submit">Save</button>
                <button type="button" class="btn btn-danger mleft10" onclick="location.reload();"> Cancel </button>
            </div>
        </div>
</form>
@endsection

@push('scripts')

<script>
    $(document).on('focus', '.select2.select2-container', function(e) {
        var isOriginalEvent = e.originalEvent
        var isSingleSelect = $(this).find(".select2-selection--single").length > 0
        if (isOriginalEvent && isSingleSelect) {
            $(this).siblings('select:enabled').select2('open');
        }
    });
    document.querySelectorAll('.select2-selection__clear').forEach(button => button.remove());

</script>

<script>
    $(document).ready(function() {
    $("#new_quotation_form").validate({
        errorClass: "state-error",
        validClass: "state-success",
        errorElement: "em",
        ignore: [],
        rules: {
            
        motivo: {
            required: true
        },

            // Add validation rules here if needed
        },
        messages: {
            
        motivo : {
            required: "Please select a Motivo."
        }
            // Add validation messages here if needed
        },

        submitHandler: function(form) {
            // Find and collect selected items
            var selectedItems = $('input[name="row_item_id[]"]:checked');
            console.log("Selected items:", selectedItems.map(function() { return $(this).val(); }).get());

            // Check if at least one item is selected
            if (selectedItems.length === 0) {
                Swal.fire({
                    type: "warning",
                    title: "At least one item is required.",
                    showConfirmButton: true
                });
                return false;
            }

            // Remove unselected items from the form
            $('input[name="row_item_id[]"]').not(':checked').each(function() {
                $(this).remove();
            });

            // Confirm submission
            if (!confirm("Are you sure to Submit?")) return false;

            $('#processingTextSubmit').show();

            let batches = [];

            $('#batch-details-table tr').each(function () {
                let random_idd = $(this).find('input[name="random_numb"]').val();
                console.log('random_numb::' , random_numb);
                let batchNum = $(this).find('td:first-child').text();
                let quantity = $(this).find('.batch-qty').val();
                let Exp = $(this).find('.batch-exp').val();

                if (batchNum && quantity) {
                    batches.push({ batch: batchNum, qty: parseInt(quantity) , Exp: Exp });
                }
            });

            console.log('batches::' , batches);

            //$('input[name="row_item_batches[]"]').each(function() {
            //    let batchValue = $(this).val();
            //    if (!batchValue || batchValue.trim() === '') {
            //        $(this).remove();
            //    }
            //});

            // Create FormData with only the selected items
            var formData = new FormData($("#new_quotation_form")[0]);
            formData.append('batches', JSON.stringify(batches));
            console.log("Form data being submitted:", formData);

                $.ajax({
                    url: public_path + '/store-credit-memos',
                    method: 'post',
                    data: formData,
                    dataType: 'json',
                    async: false,
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        $('#processingTextSubmit').hide();
                        console.log("Server response:", result);

                        if (result.success == 1) {
                            Swal.fire({
                                type: "success",
                                title: result.message,
                                showConfirmButton: true
                            });
                            window.open(result.pdfUrl, '_blank');
                            location.reload();
                        } else {
                            Swal.fire({
                                type: "warning",
                                title: result.message,
                                showConfirmButton: true
                            });
                        }
                    },
                    error: function(error) {
                        $('#processingTextSubmit').hide();
                        console.log("AJAX error response:", error);

                        if (error.responseJSON) {
                            Swal.fire({
                                type: "error",
                                title: "Error",
                                text: error.responseJSON.message
                            });
                        }
                    }
                });
            }


        });
    });

         function openModalWithDetails(invoiceId, itemCode, randomId) {
            $.ajax({
                url: public_path + '/invoice_batch_details/' + invoiceId + '/' + itemCode,
                method: 'GET',
                dataType: 'json',
                success: function(result) {
                    if (Array.isArray(result) && result.length > 0) {
                        let tableContent = '';
                        let batches = [];

                        result.forEach(function(batch) {

                            tableContent += `
                            <tr>
                                <td>${batch.batch_num}</td>
                                <td>
                                    <input type="number" value="${batch.quantity}" max="${ batch.quantity }" min="0"
                                        class="form-control batch-qty"
                                        data-batch-id="${batch.id}">
                                </td>
                                <td>
                                    <input type="text" value="${batch.expiry_date}"
                                        class="form-control batch-exp">
                                </td>
                            </tr>`;
                            batches.push({
                                batch: batch.batch_num,
                                qty: batch.quantity,
                                expiry_date: batch.expiry_date
                            });

                            console.log('batch.expiry_date::', batch.quantity);
                        });
                        $('#batch-details-table').html(tableContent);

                        $('#random_numb').val(randomId);
                        displayBatches(batches, itemCode);


                        console.log('batches::', batches);
                    } else {
                        $('#batch-details-table').html('<tr><td colspan="2" class="text-danger">Error: ' + (
                            result.message || 'No data found') + '</td></tr>');
                        $('#batch-list .batch-info').html(
                            '<div class="batch-info">No available batches found for this item.</div>');
                    }
                },
                error: function(error) {
                    console.error("Error fetching item details:", error);
                    $('#batch-details-table').html(
                        '<tr><td colspan="2" class="text-danger">An unexpected error occurred.</td></tr>');
                    $('#batch-list .batch-info').html(
                        '<div class="batch-info">An unexpected error occurred.</div>');
                }
            });
            $('.modal-title').html('BATCH DETAILS LIST - ' + invoiceId);
            $('#update_user').modal('show');
        }

  

        $(document).ready(function() {
            $('#add-new-batch').on('click', function() {
                $('#new-batch-fields').toggle();
            });

            $('#submit-batches').on('click', function() {
                let batches = [];
                let totQuantity = 0;

                // let $row = $(this).closest('tr');

                $('#batch-details-table tr').each(function() {
                    let batchNum = $(this).find('.batch-num').val() || $(this).find(
                        'td:first-child').text();
                    let quantity = $(this).find('.batch-qty').val();
                    let Exp = $(this).find('.batch-exp').val();
                    let randomId = $(this).find('.random-id').val();
                    let randomNumber = $('#random_numb').val();

                    if (quantity) {
                        totQuantity += parseInt(quantity);
                    }

                    if (batchNum && quantity) {
                        batches.push({
                            batch: batchNum,
                            qty: parseInt(quantity),
                            Exp: Exp
                        });
                    }
                });

                console.log('Final totQuantity', totQuantity);

                let newBatchNum = $('#new-batch-num').val();
                let newBatchQty = $('#new-batch-qty').val();
                if (newBatchNum && newBatchQty) {
                    batches.push({
                        batch: newBatchNum,
                        qty: parseInt(newBatchQty)
                    });
                }

                displayBatches(batches);
            });
        });


        function displayBatches(batches, itemCode) {
            console.log('itemCode', itemCode);
            console.log('batches suresh', batches.quantity);
            let randomNumber = $('#random_numb').val();
            console.log('randomsuresh', randomNumber);
            $TotBatchQty = 0;
            if (batches && batches.length > 0) {
                let tableRows = batches.map(batch => {
                    $TotBatchQty += batch.qty;
                    return `
                        <div class="column" style="display: flex; align-items: center; font-size: 8px;">
                            <div>Batch Number: ${batch.batch}</div>
                            <div style="margin-left: 3px;">Quantity: ${batch.qty}</div>
                            <div style="margin-left: 3px;">Expiry Date: ${batch.Exp}</div>
                        </div>
                    `;
                }).join('');
                batchList = $(`#batch-info_${randomNumber}`)
                batchList.html('');

                const rowItemQtyField = $(`#${randomNumber}`).find('input[name="row_item_qty[]"]');
                rowItemQtyField.val($TotBatchQty);
                if (rowItemQtyField) {
                    rowItemQtyField.focus();
                    rowItemQtyField.prop('readonly', true);
                }

                $(`#batch-info_${randomNumber}`).append(tableRows);
                const responseData = [
                    null,
                    {
                        itemCode: itemCode,
                        batches: batches
                    }
                ];

                const serializedBatches = JSON.stringify({
                    itemCode,
                    batches
                });
                console.log('serializedBatches::', serializedBatches);
                $(`#batch-item_${randomNumber}`).val(serializedBatches);
            } else {
                batchList.html('<div class="batch-info">No available batches found for this item.</div>');
            }
        }


    $(document).ready(function() {
        $("#customer_id").select2({
            ajax: {
                url: public_path + "/creditnote-customer-search"
                , dataType: 'json'
                , data: function(params) {
                    var query = {
                        search: params.term
                        , type: 'user_search'
                    }
                    return query;
                }
                , processResults: function(data) {
                    return {
                        results: data
                    };
                }
            }
            , cache: true
            , placeholder: 'Search for a Customer...'
            , allowClear: true
            , debug: true
            , minimumInputLength: 3
            , escapeMarkup: function(markup) {
                return markup; // Disable HTML escaping
            },

        });
        $("#customer_id").on("select2:select", function(e) {
            var data = e.params.data;
            var cardCode = $(this).val();
            if (!cardCode) {
                $('.customerSec').addClass('validation-error');
                $('#customerError').text('Please select a customer.').show();
                $('#customer_id').attr('required', 'required');
            } else {
                $('.customerSec').removeClass('validation-error');
                $('#customerError').hide();
                $('#customer_id').removeAttr('required');
            }
            var today = new Date();
            var formattedDate = today.getFullYear() + '-' + ('0' + (today.getMonth() + 1))
                .slice(-2) + '-' + ('0' + today.getDate()).slice(-2);
            $('.customer-product-list').html(
                "<span class='label blue'>Product List: </span><span class='value'>" + data
                .product_list + "</span>").addClass('highlight');
            $('.customer-name').html(
                "<span class='label blue'>Name: </span><span class='value black'>" + data
                .card_name + "</span>").addClass('highlight');
            $('.customer-code').html("<span class='label blue'>Code: </span><span class='value'>" + data
                .card_code + "</span>").addClass('highlight');
            $('.nif_no').html("<span class='label blue'>NIF No: </span><span class='value'>" + data
                .nif_no + "</span>").addClass('highlight');

            $('#customer_sub_grp').val(data.sub_grp);
            console.log('data', data.sub_grp)
            var txt = data.pymnt_grp;
            var numb = txt.match(/\d/g);
            if (numb != null && numb.length > 0) {
                numb = numb.join("");
            } else {
                numb = 0;
            }
            var today = new Date();
            var expiry_date = today.addDays(numb);
            $('.customer-pymnt_grp').html(
                "<span class='label blue'>Due Date: </span><span class='value'>" +
                expiry_date + "</span>").addClass('highlight');
            $('.invoice-no').html(
                "<span class='label blue'>Invoice Number: </span><span class='value'> </span>").addClass('highlight');
            $('#current-date').html("<span class='label blue'>Date: </span><span class='value'>" +
                formattedDate + "</span>").addClass('highlight');
            console.log('customers data::', data).addClass('highlight');
        });
    });

    function calculate_total(caller = 0, $row = null) {
        // var $row = $(event.target).closest('tr');
        var $row = $row || $(event.target).closest('tr');
        var qty = parseFloat($row.find('input[name="row_item_qty[]"]').val()) || 0;
        var price = parseFloat($row.find('input[name="row_item_price[]"]').val()) || 0;
        if (price == 0)
            return;

        var discount = parseFloat($row.find('input[name="row_item_disc[]"]').val()) || 0;
        var discountPercent = parseFloat($row.find('input[name="row_item_disc_percent[]"]').val()) || 0;
        if (caller === 2) {
            discount = (price * discountPercent) / 100;
            $row.find('input[name="row_item_disc[]"]').val(discount.toFixed(2));
        }

        if (caller === 1) {
            // if (price == 0)
            discountPercent = (discount / price) * 100;
            $row.find('input[name="row_item_disc_percent[]"]').val(discountPercent.toFixed(2));
        }
        var priceAfterDiscount = price - discount;
        $row.find('input[name="row_price_af_disc[]"]').val(priceAfterDiscount.toFixed(2));

        var totalAfterDiscount = priceAfterDiscount * qty;
        $row.find('input[name="row_total_af_disc[]"]').val(totalAfterDiscount.toFixed(2));

        var taxPercent = parseFloat($row.find('input[name="row_item_gst_rate[]"]').val()) || 0;
        var taxPerItem = (priceAfterDiscount * taxPercent) / 100;
        var taxValue = (totalAfterDiscount * taxPercent) / 100;
        $row.find('input[name="row_item_gst_rate_value[]"]').val(taxPerItem.toFixed(2));
        $row.find('.amount').text((totalAfterDiscount + taxValue).toFixed(2));
        //updateSummary();
        grandTotalsummary();
    }

    function updateSummary() {
        var subtotal = 0;
        var totalTax = 0;
        var totalDiscount = 0;
        var totalPriceAfterDiscount = 0;
        var totalAfterDiscount = 0;
        var totalGstValue = 0;

        $('.items').each(function() {
            var qty = parseFloat($(this).find('input[name="row_item_qty[]"]').val()) || 0;
            var price = parseFloat($(this).find('input[name="row_item_price[]"]').val()) || 0;
            var discount = parseFloat($(this).find('input[name="row_item_disc[]"]').val()) || 0;
            var priceAfterDiscount = parseFloat($(this).find('input[name="row_price_af_disc[]"]').val()) || 0;
            var totalAfDisc = parseFloat($(this).find('input[name="row_total_af_disc[]"]').val()) || 0;
            var taxPercent = parseFloat($(this).find('input[name="row_item_gst_rate[]"]').val()) || 0;
            var gstValue = parseFloat($(this).find('input[name="row_item_gst_rate_value[]"]').val()) || 0;

            // Calculate totals
            subtotal += price * qty;
            totalDiscount += discount * qty;
            totalPriceAfterDiscount += priceAfterDiscount * qty;
            totalAfterDiscount += totalAfDisc;
            totalGstValue += gstValue * qty;
            totalTax += (totalAfDisc * taxPercent) / 100;
        });

        // Update the UI with calculated totals
        $('#subtotal').text(subtotal.toFixed(2));
        $('#total_discount').text(totalDiscount.toFixed(2));
        $('#total_price_af_disc').text(totalPriceAfterDiscount.toFixed(2));
        $('#total_af_disc').text(totalAfterDiscount.toFixed(2));
        $('#total_gst_value').text(totalGstValue.toFixed(2));
        $('#total_tax').text(totalTax.toFixed(2));
        grandTotal(); // Update grand total as well
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
        // add_item_to_table();
        $("#new_subcategory").select2();
        $("#address").select2();
        $("#contact").select2();
    });

</script>
<script>

    $(document).ready(function() {
        let isQuotationDataFetched = false;

        $('#customer_select').on('change', function() {
            var customerId = $('#customer_id').val();
            if (customerId) {
                fetchQuotations(customerId);
                alert('works')
            } else {
                clearQuotationDropdown();
                alert('hello')
            }
        });


        $('#sales_order_id').on('change', function() {
            var orderId = $(this).val();
            if (orderId) {
                fillInvoiceOrderData(orderId);
            }
        });
    });

    function generateRandomId() {
        const randomString = Math.random().toString(36).substr(2, 10); // Generate random string
        const timestamp = Date.now(); // Get current timestamp
        return randomString + timestamp; // Concatenate random string with timestamp
    }

    // --------Customer Sales Order Details---------

    function fetchInvoiceOrder(customerId) {
        $.ajax({
            url: `customer-credit-sales`
            , method: 'GET'
            , data: {
                customer_id: customerId
            }
            , success: function(response) {
                populateInvoiceDropdown(response);
                // alert('hello')
            }
            , error: function(xhr, status, error) {
                console.error('Error fetching Sales Order:', error);
            }
        });
    }

    function populateInvoiceDropdown(orders) {
        var $salesOrderDropdown = $('#sales_order_id');
        $salesOrderDropdown.html('<option value="">Select Invoice</option>');
        if (orders.length > 0) {
            orders.forEach(function(order) {
                $salesOrderDropdown.append(
                    `<option value="${order.id}">${order.invoice_no}</option>`
                );
            });
        } else {
            $salesOrderDropdown.html('<option value="">No Invoice available</option>');
        }
        // Reset the flag so that new data fetch is allowed when a new quotation is selected
        isQuotationDataFetched = false;
    }

    function clearSalesOrderDropdown() {
        $('#sales_order_id').html('<option value="">Select Invoice Order</option>');
    }

    

    function toggleAllCheckboxes(selectAll) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
            handleCheckboxChange(checkbox); // Call existing function if needed
            toggleRow(checkbox); // Call existing function if needed
        });
    }

    document.querySelectorAll('.row-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const allCheckboxes = document.querySelectorAll('.row-checkbox');
            const allChecked = [...allCheckboxes].every(cb => cb.checked);
            document.getElementById('select-all').checked = allChecked;
        });
    });

    function fillInvoiceOrderData(orderId) {
        $.ajax({
            url: public_path + `/get-invoice-order-details/${orderId}`
            , method: 'GET',
            success: function(response) {
                if (response) {
                    console.log(response);
                    let  orders = response.order;
                       let ref_id = response.ref_id;
                       let address_id = response.address_id;
                        let remarks = response.remarks;
                        let salesname = response.sales_employee_name;
                    autoFillInvoiceOrderFields(orders);
                   // triggerBatchQuantity(orderId);
                $('#remarks').val(remarks);
                $('#ref_id').val(ref_id);
                $('#address_id').val(address_id);
                $('#sales_emp_id').val(salesname);




                let $salesEmpSelect = $('#sales_emp_id');
               // console.log("PavanMANANA")
               // alert(salesname);
                $salesEmpSelect.empty();
                $salesEmpSelect.append(`<option value="${salesname}" selected>${salesname}</option>`);

                var selectedInvoice = $('#sales_order_id').find(':selected').text();
                var selectedInvoiceValue = $('#sales_order_id').val();

                if (selectedInvoiceValue) {
                    $('#selected_invoice_no').val(selectedInvoice);
                } else {
                    $('#selected_invoice_no').val('');
                }
                 if (selectedInvoiceValue) {
                    $('#selected_invoice_no').val(selectedInvoice);
                    $('.invoice-no').html(
                        `<span class='label blue'>Invoice Number: </span><span class='value'>${selectedInvoice}</span>`
                    ).addClass('highlight');
                } else {
                    $('#selected_invoice_no').val('');
                    $('.invoice-no').html(
                        `<span class='label blue'>Invoice Number: </span><span class='value'>Not Available</span>`
                    ).addClass('highlight');
                }

                } else {
                    console.warn('No data found for this Invoice Order.');
                }
            }
            , error: function(xhr, status, error) {
                console.error('Error fetching Invoice Order data:', error);
            }
        });
    }


    function autoFillInvoiceOrderFields(orders) {
        $('.items-row').empty();
        orders.forEach((order, index) => {
            const randomId = generateRandomId();
            let rowCount = $('.items-row tr').length;
            console.log("Total number of rows:", rowCount);
            //let baseCount = rowCount * 4 + 1 + 1;
            let baseCount = rowCount * 4 + 100;
            let tab1 = baseCount++;
            let tab2 = baseCount++;
            let tab3 = baseCount++;
            let newRow = `
                            <tr class="items items-first-order" id="${randomId}">
                                <input type="hidden" name="row_item_id[]" value="${order.item_id}">
                                <input type="hidden" name="row_item_order_id[]" value="${order.order_id}">
                                <input type="hidden" name="random_numb" id="random_numb" >
                                 <input type="hidden" name="invoice_number" id="invoice_number" value="${order.invoice_id}" >

                                <td>
                                   {{-- <input onclick="handleCheckboxChange(this)" type="checkbox" name="row_item_id[]" value="${order.id}" class="item-checkbox"> --}}
                                        <input onclick="handleCheckboxChange(this); toggleRow(this)" type="checkbox" name="row_item_id[]" value="${order.id}" class="row-checkbox">
                                </td>

                                 <td class="">
                                            <select class="select2-container form-control" name="row_item_no[]" data-live-search="true">
                                                    <option selected value="${order.item_no}">${order.item_no}-${order.item_desc}-(WHS QTY/ ${ order.ws_qty})</option>
                                            </select>
                                        </td>

                                        <td>
                                            <input type="text" min="1" onblur="calculate_total(); triggerBatchQuantity();"
                                                        onchange="calculate_total();" name="row_item_qty[]"
                                                        value="${order.quantity}" class="form-control row_item_qty" tabindex="${tab1}">
                                            <input type="hidden" value="${order.quantity}" name="row_actual_invoice_item_qty[]"
                                        </td>

                                        <td class="rate" id="rate">
                                            <input type="number" width:100px data-toggle="tooltip" title="" onblur="calculate_total();" onchange="calculate_total();" name="row_item_price[]" value="${order.price}" readonly class="form-control">
                                        </td>
                                        <td>
                                            <div class="row_discunt_val">
                                                <input type="text" onblur="calculate_total(1);" onchange="calculate_total(1);" name="row_item_disc[]" id="row_discount" class="form-control" min="0" value="${order.disc_value}" tabindex="${tab2}" readonly>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dis_percentage_val">
                                                <input type="number" name="row_item_disc_percent[]"  min="0" onblur="calculate_total(2);" onchange="calculate_total(2);" id="dis_percentage" value="${order.disc_percent}" class="form-control text-left" tabindex="${tab3}" readonly>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="price_af_discount">
                                                <input type="text" onblur="calculate_total();" onchange="calculate_total();" name="row_price_af_disc[]" id="price_af_discount" readonly class="form-control" value="" style="margin-bottom: 4px;">
                                            </div>
                                        </td>


                                         <td>
                                            <div class="item_tax_pecent">
                                            <div style="display:flex;flex-direction:row; ">
                                            <lable class="sec_bor1" style="height: 16px;font-size: 9px; width:24px !important;">%</lable>
                                            <input type="text" name="row_item_gst_rate[]" id="item_tax_percent" value="${order.gst_rate}" readonly class="form-control tax text-left">
                                            </div>
                                               <div style="display:flex;">
                                                    <lable class="sec_bor1" style="height: 16px;font-size: 9px;">Value</lable>
                                                    <input type="text" name="row_item_gst_rate_value[]" id="tax_value" value="${order.gst_value}" readonly class="form-control tax text-left">
                                               </div>
                                            </div>
                                        </td>
                                        <!--<td>
                                            <div class="item_tax_value">
                                            </div>
                                        </td>-->
                                        <td class="amount" align="left"></td>

                                        <td>
                                        <div class="second-row-container">
                                            <!-- Remove Button -->
                                            <button type="button" class="bor_none remove-row-btn">
                                                <img src="{{ asset('images/close-icon.png') }}" alt="Close" title="Close" style="width:22px; height:22px;">
                                            </button>

                                            <div class="batchs">
                                            <input type="hidden" name="row_item_batches[]" id="batch-item_${randomId}">
                                                <div id="batch-info_${randomId}" >
                                                    <div class="batch-info"></div>
                                                </div>
                                                <div>
                                                    <button style="padding: 2px 8px; font-size: 12px; line-height: 1;" type="button" class="batch-details btn btn-primary"
                                                        onclick="openModalWithDetails('${order.invoice_id}', '${order.item_no}', '${randomId}')"
                                                            disabled>
                                                        Batch Details
                                                    </button>
                                                </div>
                                            </div>


                                        </div>

                                        </td>
                                    </tr>

                                    <tr class="extra-info-row items">
                                        <td colspan="6">
                                            <div class="extra-info">
                                             <strong style="color: #4a90e2; font-weight: bold;">Description:</strong>
                                             <span style="color: black; font-weight: bold;">Item Code:</span> <span style="color: #ae700c; font-weight: bold;">${order.item_no || 'NULL'}</span>,
                                             <span style="color: black; font-weight: bold;">Item Name:</span> <span style="color: #ae700c; font-weight: bold;">${order.item_desc || 'NULL'}</span>,
                                             <span style="color: black; font-weight: bold;">WH Quantity:</span> <span style="color: #ae700c; font-weight: bold;">${order.ws_qty || '0'}</span>
                                            </div>
                                        </td>
                                </tr>`;
            $('.items-row').prepend(newRow);


            let tablerow = $(`tr#${randomId}`);
            console.log('tablerow', tablerow);
            console.log('orders::', orders);
            calculate_total(0, tablerow);

        });

        $('.items-row').on('click', '.remove-row-btn', function() {
            //$(this).closest('.items').remove();

            const itemRow = $(this).closest('.items');
            const extraInfoRow = itemRow.next('.extra-info-row');
            // Remove both the item row and its associated extra-info-row
            itemRow.remove();
            extraInfoRow.remove();
            calculate_total();
        });
        $('.items-row tr').each(function() {
            var checkbox = $(this).find('.row-checkbox');
            if (checkbox.length) {
                checkbox.prop('checked', true);
                handleCheckboxChange(checkbox);
            }
        });

    }


    Date.prototype.addDays = function(days) {
        var date = new Date(this.valueOf());
        date.setDate(date.getDate() + parseInt(days));
        var date = new Date(date)
            , mnth = ("0" + (date.getMonth() + 1)).slice(-2)
            , day = ("0" + date.getDate()).slice(-2);
        var myssy = [date.getFullYear(), mnth, day].join("-");
        console.log(myssy);
        return myssy;
    }

    function toggleRow(checkbox) {
        let row = checkbox.closest("tr");
        console.log('row' , row);

        let inputs = row.querySelectorAll("input:not(.row-checkbox)"); 
        inputs.forEach(input => {
            input.disabled = !checkbox.checked;
        });
    }

    
    function handleCheckboxChange(checkbox)
    {
            var row = $(checkbox).closest('tr');
            var isChecked = $(checkbox).is(':checked');
            var batchDetailsButton = row.find('.batch-details');

            if (isChecked) {
                batchDetailsButton.prop('disabled', false);
            } else {
                batchDetailsButton.prop('disabled', true);
            }

            var itemId = row.find('input[name="row_item_no"]');
            var qtyField = row.find('input[name="row_item_qty[]"]');
            var discountValueField = row.find('input[name="row_item_disc[]"]');
            var discountPercentField = row.find('input[name="row_item_disc_percent[]"]');

            if (isChecked) {
                itemId.prop('readonly', false);
                qtyField.prop('readonly', false);
                //discountValueField.prop('readonly', false);
                //discountPercentField.prop('readonly', false);
            } else {
                itemId.prop('readonly', true);
                qtyField.prop('readonly', true);
                discountValueField.prop('readonly', true);
                discountPercentField.prop('readonly', true);
            }

            grandTotalsummary();
    }

    function grandTotalsummary() {
    var grandTotal = 0;
    var totalTax = 0;
    var totalQty = 0;
    var totalAfterDisc = 0;
    var subtotal = 0;
    var totalDiscount = 0;
    var totalPriceAfterDiscount = 0;
    var totalGstValue = 0;

    $('.items').each(function() {
        var isChecked = $(this).find('input[type="checkbox"]').is(':checked');

        if (isChecked) {
            var qty = parseFloat($(this).find('input[name="row_item_qty[]"]').val()) || 0;
            var price = parseFloat($(this).find('input[name="row_item_price[]"]').val()) || 0;
            var discount = parseFloat($(this).find('input[name="row_item_disc[]"]').val()) || 0;
            var priceAfterDiscount = parseFloat($(this).find('input[name="row_price_af_disc[]"]').val()) || 0;
            var totalAfDisc = parseFloat($(this).find('input[name="row_total_af_disc[]"]').val()) || 0;
            var taxPercent = parseFloat($(this).find('input[name="row_item_gst_rate[]"]').val()) || 0;
            var gstValue = parseFloat($(this).find('input[name="row_item_gst_rate_value[]"]').val()) || 0;
            var finalAmount = parseFloat($(this).find('.amount').text()) || 0;

            // Accumulate values for the grand total
            subtotal += price * qty;
            totalDiscount += discount * qty;
            totalPriceAfterDiscount += priceAfterDiscount * qty;
            totalAfterDisc += totalAfDisc;
            totalGstValue += gstValue;
            totalTax += (totalAfDisc * taxPercent) / 100;

            grandTotal += finalAmount;

            var itemNo = $(this).find('select[name="row_item_no[]"]').val();
            if (itemNo !== '' && itemNo !== null) {
                totalQty += qty;
            }
        }
    });

    // Update the UI with calculated totals
    $('#subtotal').text(subtotal.toFixed(2));
    $('#total_discount').text(totalDiscount.toFixed(2));
    $('#total_price_af_disc').text(totalPriceAfterDiscount.toFixed(2));
    $('#total_af_disc').text(totalAfterDisc.toFixed(2));
    $('#total_gst_value').text(totalGstValue.toFixed(2));
    $('#total_tax').text(totalTax.toFixed(2));
    $('#grand_total').text(grandTotal.toFixed(2));
    $('#total_qty').text(totalQty);
}


</script>
@endpush
