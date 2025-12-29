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
    .form-control.qty{
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
    .extra-info{
        font-size:9px;
        background-color:aliceblue;
        font-weight: 500px;
    }
    
        .second-row-container {
        display: flex;
        gap:2px;
        justify-content: flex-start; /* Align items to the left */
        align-items: center; /* Vertically center the items */
    }
    .quantities{
        width: 30px;
        height: 10px;
    }
    .form-control.tax{
        height:2px !important;
        width: 60px;
    }
    .summary-section {
        margin-top: 20px;
        padding: 15px;
        text-align:center;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
        width: 300px; /* Set the width of the summary section */
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2px; /* Adds space between each row */
    }

    .summary-label {
        font-weight: solid;
        font-size: 12px;
        flex-basis: 80%; /* Label takes up 60% of the width */
        text-align: left;
    }

    .summary-value {
        text-align: right;
        width: 70px; 
        min-width: 70px; /* Ensure a minimum width */
        background-color: #f4f4f4;
        font-size:11px;
        padding: 5px 10px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }

    .summaryTbl{
        margin-left: 30px;
        margin-top: 20px;
        text-align:right;
        justify-self: right;
    }
</style>
@section('content')
<form id="new_quotation_form" action="javascript:void(0)" method="POST">
    {{ csrf_field() }}

    <div class="content-wrapper">
        <div class="card">
            <div class="card-body card_bg1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            
                    <div class="col-md-8">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="20%" class="pada0">
                                    <div class="sec_bor1 fs14" for="customer_id" style="padding:4px 10px;">Customer:</div>
                                </td>
                                <td width="80%" class="pada0">
                                   <select required class="form-control md-select2" name="customer_id" id="customer_id"
                                    onchange="fetchQuotations(this.value);" data-live-search="true" tabindex="1">
                                </select>
                                </td>
                            </tr>
                        </table>
                        </div>
                            <!--<div class="col-4 text-end">
                                <select id="quotation_id" class="form-control select2-quotation" tabindex="2"
                                    onchange="fillQuotationData();">
                                    <option selected value="">Select Quotation</option>
                                </select>
                            </div>-->
                            <div class="customer-details">
                                <div class="row">
                                    <div class="col-md-3">
                                        <p id="current-date"> </p>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="customer-pymnt_grp"></p>
                                    </div>
                                    <div class="col-md-3">
                                        <p class=" sales-no"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">

                            </div>

                        </div>

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
                                        <!--<th width="12%" align="left"><i class="fa fa-exclamation-circle"
                                                aria-hidden="true" data-toggle="tooltip"
                                                data-title="New lines are not supported for item description. Use the item long description instead."
                                                data-original-title="" title=""></i> Item No</th>-->

                                        <th width="10%" align="left">Item No</th>
                                        <th width="8%" align="center">&nbsp;Qty&nbsp;</th>
                                        <th width="8%" align="center">&nbsp;Unit Price&nbsp;&nbsp;</th>
                                        <th width="8%" align="center">Discount</th>
                                        <th width="8%" align="center">Discount %</th>
                                        <th width="8%" align="center">Price after Disc.</th>
                                        <!--<th width="10%" align="center"> Total After Disc.</th>-->
                                        <th width="8%" align="center">&nbsp;&nbsp;Tax&nbsp;&nbsp;</th>
                                        <!--<th width="10%" align="center">Tax Value</th>-->
                                        <th width="8%" align="center">&nbsp;&nbsp;Total&nbsp;&nbsp;</th>
                                        <th width="26%" align="center"></th>
                                    </tr>
                                </thead>
                                <tbody class="items-row">
                                    <tr class="items">
                                        <input type="hidden" name="row_item_id[]">
                                       
                                        <td class="item_code">
                                            <!--<select class="select2-item row_item_no" name="row_item_no[]" data-live-search="true" tabindex="4" >
                                                <option selected value="">Select Item</option>
                                            </select>-->
                                            <select class="form-control row_item_no select2-item"
                                                    tabindex="3" name="row_item_no[]" data-live-search="true">
                                                        <!-- <option selected value="">Select Item</option> -->
                                            </select>
                                        </td>
            
                                        <td>
                                            <!--<input type="text" min="1" onblur="calculate_total();" onchange="calculate_total();" data-quantity="" name="row_item_qty[]" value="1" class="form-control">-->
                                            <input type="text" min="1" onblur="calculate_total(); triggerBatchQuantity();" tabindex="5"
                                                        onchange="calculate_total();" name="row_item_qty[]"
                                                        value="0" class="form-control">
                                        </td>
                                       
                                        <td class="rate" id="rate">
                                            <input type="number" width:100px data-toggle="tooltip" title="" onblur="calculate_total();" onchange="calculate_total();" name="row_item_price[]" value="" readonly class="form-control">
                                        </td>
                                        <td>
                                            <div class="row_discunt_val">
                                                <input type="text" onblur="calculate_total(1);" onchange="calculate_total(1);" name="row_item_disc[]" id="row_discount" class="form-control" min="0" value="0.00">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dis_percentage_val">
                                                <input type="number" name="row_item_disc_percent[]"  min="0" onblur="calculate_total(2);" onchange="calculate_total(2);" id="dis_percentage" value="0.00" class="form-control text-left">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="price_af_discount">
                                                <input type="text" onblur="calculate_total();" onchange="calculate_total();" name="row_price_af_disc[]" id="price_af_discount" readonly class="form-control" value="" style="margin-bottom: 4px;">
                                            </div>
                                        </td>
                                        <!--<td>
                                            <div class="total_af_dis">
                                                <input type="number" name="row_total_af_disc[]" onblur="calculate_total();" onchange="calculate_total();" id="total_af_dis" value="" readonly class="form-control text-left">
                                            </div>
                                        </td>-->
                                        <td>
                                            <div class="item_tax_pecent">
                                            <div style="display:flex;flex-direction:row; ">
                                            <lable class="sec_bor1" style="height: 16px;font-size: 9px; width:24px !important;">%</lable>
                                            <input type="text" name="row_item_gst_rate[]" id="item_tax_percent" value="" readonly class="form-control tax text-left">
                                            </div>
                                               <div style="display:flex;">
                                                    <lable class="sec_bor1" style="height: 16px;font-size: 9px;">Value</lable>
                                                    <input type="text" name="row_item_gst_rate_value[]" id="tax_value" value="" readonly class="form-control tax text-left">
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

                                            <!-- Batch List -->
                                            <div id="batch-list" class="batchs">
                                                <div class="batch-info"></div>
                                            </div>
                                        </div>
                                            
                                        </td>
                                    </tr>
                                    <tr class="extra-info-row">
                                        <td colspan="6">
                                            <div class="extra-info">
                                                <!-- Extra item information will be displayed here -->
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                <!--<tr class="table-light">
                                    <td colspan="8" style="text-align: right;">
                                        <strong>Total</strong>
                                    </td>
                                    <td id="grand_total" style="text-align: right;">0.00</td>
                                    <td></td>
                                </tr>-->
                            </tfoot>
                            </table>
                            <div class="summaryTbl">
                                <div class="summary-section">
                                    <h5>Summary</h5>
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
    </script>
    
    <script>
        $(document).ready(function () {
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
                submitHandler: function (form) {

                    if ($('select[name="row_item_no[]"]').filter(function () {
                        return this.value !== "";
                    }).length === 0) {
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
                        url: public_path + '/store-orders',
                        method: 'post',
                        data: new FormData($("#new_quotation_form")[0]),
                        dataType: 'json',
                        async: false,
                        cache: false,
                        processData: false,
                        contentType: false,
                        success: function (result) {
                            $('#processingTextSubmit').hide();
                            if (result.success == 1) {

                                Swal.fire({
                                    type: 'success',
                                    title: result.message,
                                    showConfirmButton: true,
                                    // timer: 1500
                                });
                                window.open(result.sales_order_url, '_blank');
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
                        error: function (error) {
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

        $(document).ready(function () {
            $("#customer_id").select2({
                ajax: {
                    url: public_path + "/sales-customer-search",
                    dataType: 'json',
                    data: function (params) {
                        var query = {
                            search: params.term,
                            type: 'user_search'
                        }
                        return query;
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    }

                },
                cache: true,
                placeholder: 'Search for a Customer...',
                allowClear: true,
                debug: true,
                minimumInputLength: 3,
                escapeMarkup: function (markup) {
                    return markup; // Disable HTML escaping
                },
            });
            $("#customer_id").on("select2:select", function (e) {
                var data = e.params.data;
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
                // var str = data.pymnt_grp; 
                var txt = data.pymnt_grp;
                var numb = txt.match(/\d/g);
                //console.log(numb.length);
                if (numb != null && numb.length > 0) {
                    numb = numb.join("");
                } else {
                    numb = 0;
                }
                var today = new Date();
                var expiry_date = today.addDays(numb);
                // console.log(numb.addDays(5));

                //var due_date = str.slice(str.indexof('_') + 1);
                $('.customer-pymnt_grp').html(
                    "<span class='label blue'>Due Date: </span><span class='value'>" +
                    expiry_date + "</span>").addClass('highlight');
                $('.sales-no').html(
                    "<span class='label blue'>Sales Order No: </span><span class='value'></span>").addClass('highlight');
                $('#current-date').html("<span class='label blue'>Date: </span><span class='value'>" +
                    formattedDate + "</span>").addClass('highlight');
                console.log('customers data::', data).addClass('highlight');
            });
        });

        $(document).ready(function () {
            function initializeSelect2(element, type) {
                let url = '';

                if (type === 'item_no') {
                    url = public_path + "/sales-item-search/item_no";
                }
                element.select2({
                    ajax: {
                        url: url,
                        dataType: 'json',
                        data: function (params) {
                            var cardCode = $('#customer_id').val();
                            var query = {
                                search: params.term,
                                type: 'user_search',
                                card_code: cardCode
                            };
                            return query;
                        },
                        processResults: function (data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    },
                    placeholder: 'Search for an Item...',
                    allowClear: true,
                    minimumInputLength: 3,
                    escapeMarkup: function (markup) {
                        return markup; // Disable HTML escaping
                    },
                })
                    .on('select2:select', function (e) {
                        let selectedData = e.params.data;
                        let currentRow = element.closest('tr');
                        console.log('selectedData::' , selectedData)
                    var $extraInfoRow = $(this).closest('tr.items').next('.extra-info-row');
                    var $extraInfoDiv = $extraInfoRow.find('.extra-info');

                    $extraInfoDiv.html(`
                            <strong style="color: #4a90e2; font-weight: bold;">Description:</strong> 
                            <span style="color: black; font-weight: bold;">Item Code:</span> <span style="color: #ae700c; font-weight: bold;">${selectedData.item_code || 'NULL'}</span>, 
                            <span style="color: black; font-weight: bold;">Item Name:</span> <span style="color: #ae700c; font-weight: bold;">${selectedData.item_name || 'NULL'}</span>, 
                            <span style="color: black; font-weight: bold;">WH Quantity:</span> <span style="color: #ae700c; font-weight: bold;">${selectedData.quantity || '0'}</span>
                        </span>                
                            `).show();

                        $.ajax({
                            url: public_path + "/get-sales-item/" + selectedData.id, 
                            dataType: 'json',
                            success: function (data) {
                                currentRow.find('input[name="row_item_id[]"]').val(data.id);
                                currentRow.find('input[name="row_item_price[]"]').val(data.price);
                                currentRow.find('input[name="row_item_uom_code[]"]').val(data
                                    .uom_code);
                                currentRow.find('input[name="row_item_gst_rate[]"]').val(data
                                    .gst_rate);

                                //if (type === 'item_no') {
                                //    let newOption = new Option(data.item_desc, data.id, true, true);
                                //    currentRow.find('.select2-desc').append(newOption).trigger(
                                //        'change');
                                //} else if (type === 'item_desc') {
                                //    let newOption = new Option(data.item_no, data.id, true, true);
                                //    currentRow.find('.select2-item').append(newOption).trigger(
                                //        'change');
                                //}

                                let newOption = new Option(data.item_code, data.item_code, true, true);
                            currentRow.find('.select2-item').append(newOption).trigger('change');

                            // The following line was changed to use item_code instead of item_id
                            currentRow.find('input[name="row_item_no[]"]').val(data.item_code);

                                calculate_total(0, currentRow);
                            }
                        });

                        if ($('.items-row .items:last .select2-item').val() !== '' ||
                            $('.items-row .items:last .select2-desc').val() !== '') {
                            addNewRow();
                        }
                    });

            }

            function addNewRow(isAddNewRow = true) {
            let rowCount = $('.items-row tr').length;
            console.log("Total number of rows:", rowCount);
            //let baseCount = rowCount * 4 + 1  + 1;
            let baseCount = isAddNewRow ? rowCount * 4 + 1 + 1 : rowCount * 4 + 10;
            let tab1 = baseCount++;
            let tab2 = baseCount++;
            let tab3 = baseCount++;
            let tab4 = baseCount++;
            let newRow = `
            <tr class="items items-first-order">
                <input type="hidden" name="row_item_id[]">
                <td class="">
                                            <select class=" select2-item" name="row_item_no[]" data-live-search="true">
                                                <option selected value="">Select Item</option>
                                            </select>
                                        </td>
                                        {{--<td>
                                            <select class="form-control row_item_desc select2-desc" name="row_item_desc[]" data-live-search="true">
                                                <option selected value="">Select Description</option>
                                            </select>
                                        </td>--}}
                                        <td>
                                            <input type="text" min="1" onblur="calculate_total(); triggerBatchQuantity();"
                                                        onchange="calculate_total();" name="row_item_qty[]"
                                                        value="0" class="form-control">
                                        </td>
                                        {{--<td>
                                            <input type="text" readonly name="row_item_uom_code[]" class="form-control input-transparent">
                                        </td>--}}
                                        <td class="rate" id="rate">
                                            <input type="number" width:100px data-toggle="tooltip" title="" onblur="calculate_total();" onchange="calculate_total();" name="row_item_price[]" value="" readonly class="form-control">
                                        </td>
                                        <td>
                                            <div class="row_discunt_val">
                                                <input type="text" onblur="calculate_total(1);" onchange="calculate_total(1);" name="row_item_disc[]" id="row_discount" class="form-control" min="0" value="0.00">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dis_percentage_val">
                                                <input type="number" name="row_item_disc_percent[]"  min="0" onblur="calculate_total(2);" onchange="calculate_total(2);" id="dis_percentage" value="0.00" class="form-control text-left">
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
                                            <input type="text" name="row_item_gst_rate[]" id="item_tax_percent" value="" readonly class="form-control tax text-left">
                                            </div>
                                               <div style="display:flex;">
                                                    <lable class="sec_bor1" style="height: 16px;font-size: 9px;">Value</lable>
                                                    <input type="text" name="row_item_gst_rate_value[]" id="tax_value" value="" readonly class="form-control tax text-left">
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

                                            <!-- Batch List -->
                                            <div id="batch-list" class="batchs">
                                                <div class="batch-info"></div>
                                            </div>
                                        </div>
                                            
                                        </td>
                                    </tr>
                                    <tr class="extra-info-row">
                                        <td colspan="6">
                                            <div class="extra-info">
                                                <!-- Extra item information will be displayed here -->
                                            </div>
                                        </td>
                                    </tr>`;
            

            $('.items-row').append(newRow);
            initializeSelect2($('.items-row .items:last .select2-item'), 'item_no');
            initializeSelect2($('.items-row .items:last .select2-desc'), 'item_desc');
        }

            initializeSelect2($('.select2-item'), 'item_no');
            initializeSelect2($('.select2-desc'), 'item_desc');

            $('.items-row').on('click', '.remove-row-btn', function () {
                $(this).closest('.items').remove();
                grandTotal();
            });
        });

        $(document).on('select2:unselecting', '.select2-desc, .select2-item', function (e) {
            var $row = $(this).closest('tr');
            e.preventDefault();

            $row.find('input[name="row_item_id[]"]').val('');
            $row.find('input[name="row_item_qty[]"]').val('1');
            //$row.find('input[name="row_item_uom_code[]"]').val('');
            $row.find('input[name="row_item_price[]"]').val('');
            $row.find('input[name="row_item_disc[]"]').val('0.00');
            $row.find('input[name="row_item_disc_percent[]"]').val('0.00');
            $row.find('input[name="row_price_af_disc[]"]').val('');
            $row.find('input[name="row_total_af_disc[]"]').val('');
            $row.find('input[name="row_item_gst_rate[]"]').val('');
            $row.find('input[name="row_item_gst_rate_value[]"]').val('');
            $row.find('.amount').text('');

            $row.find('.select2-item').val(null).trigger('change');
            $row.find('.select2-desc').val(null).trigger('change');

            grandTotal();
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

            updateSummary();
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
                subtotal += totalAfDisc;
                totalDiscount += discount;
                totalPriceAfterDiscount += priceAfterDiscount;
                totalAfterDiscount += totalAfDisc;
                totalGstValue += gstValue;
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
            $('.items').each(function () {
                var finalAmount = parseFloat($(this).find('.amount').text()) || 0;
                grandTotal += finalAmount;
            });
            $('#grand_total').text(grandTotal.toFixed(2));

        }
    </script>

    <script type="text/javascript">
        $(document).ready(function () {

            // add_item_to_table();


            $("#new_subcategory").select2();
            $("#address").select2();
            $("#contact").select2();

        });
    </script>
    <script>
        function triggerBatchQuantity() {
            var $row = $(event.target).closest('tr');
            var itemId = $row.find('select[name="row_item_no[]"]').val();
            if (!itemId) {
                console.error('Item ID not found.');
                return;
            }

            fetch(`get-batches/${itemId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
                .then(response => response.json())
                .then(data => {
                    displayBatches(data, $row);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }


        function displayBatches(batches, $row) {
            var batchList = $row.find('.batchs');
            batchList.html('');

            if (batches && batches.length > 0) {
                batches.forEach(function (batch) {
                    var batchInfo = `
                        <div class="batch-info">
                            Batch Number: ${batch.batch_num}, Expiry Date: ${batch.exp_date}
                        </div>`;
                    batchList.append(batchInfo);
                });
            } else {
                batchList.html('<div class="batch-info">No available batches found for this item.</div>');
            }
        }
        $(document).ready(function () {

            let isQuotationDataFetched = false;

            $('#customer_select').on('change', function () {
                var customerId = $('#customer_id').val();
                if (customerId) {
                    fetchQuotations(customerId);
                    alert('works')
                } else {
                    clearQuotationDropdown();
                    alert('hello')
                }
            });

            $('#quotation_id').on('change', function () {
                var quotationId = $(this).val();
                if (quotationId) {
                    fillQuotationData(quotationId);
                }
            });
        });

        // Fetch customer quotations and populate the dropdown
        function fetchQuotations(customerId) {
            $.ajax({
                url: `customer-quotations`,
                method: 'GET',
                data: {
                    customer_id: customerId
                },
                success: function (response) {
                    populateQuotationDropdown(response);
                    // alert('hello')
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching quotations:', error);
                }
            });
        }

        function populateQuotationDropdown(quotations) {
            var $quotationDropdown = $('#quotation_id');
            $quotationDropdown.html('<option value="">Select Quotation</option>');

            if (quotations.length > 0) {
                quotations.forEach(function (quotation) {
                    $quotationDropdown.append(
                        `<option value="${quotation.id}">Quotation #${quotation.quotation_pdf_no}</option>`
                    );
                });
            } else {
                $quotationDropdown.html('<option value="">No quotations available</option>');
            }

            // Reset the flag so that new data fetch is allowed when a new quotation is selected
            isQuotationDataFetched = false;
        }

        function clearQuotationDropdown() {
            $('#quotation_id').html('<option value="">Select Quotation</option>');
        }

        function fillQuotationData(quotationId) {
            $.ajax({
                url: public_path + `/get-quotation-details/${quotationId}`,
                method: 'GET',
                success: function (response) {
                    if (response) {
                        autoFillQuotationFields(response);
                    } else {
                        console.warn('No data found for this quotation.');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching quotation data:', error);
                }
            });
        }

        function autoFillQuotationFields(quotations) {
            quotations.forEach((quotation, index) => {
                // console.log('quotations::', quotations)
                const randomId = generateRandomId();
                let newRow = `
                                <tr class="items items-first-order" id="${randomId}">
                                <input type="hidden" name="row_item_id[]" value="${quotation.item_id}">
                                <td>
                                    <div id="batch-list" class="batchs col-md-12"></div>

                                    <div class="row-item-group">
                                        <!-- First Line: Item No and Quantity -->
                                        <div class="row-item-top single-line">
                                            
                                            <div class="item_code">

                                                <label class="sec_bor3 fs15" style="margin-right: 1px; background: #c0bcbc; padding:0px 10px;" for="">Items</label>
                                                <select class=" row_item_no select2-item item-dropdown" name="row_item_no[]" data-live-search="true">
                                                    <option selected value="${quotation.item_id}">${quotation.item_no}-${quotation.item_desc}</option>
                                                </select>
                                            </div>

                                         <div style="display: flex; align-items: center;"> 
                                                    <div class="sec_bor1">Quantity</div>
                                                    <input type="text" min="1" onblur="calculate_total(); triggerBatchQuantity();"
                                                        onchange="calculate_total();" name="row_item_qty[]"
                                                        value="${quotation.quantity}" class="form-control row_item_qty">
                                            </div>

                                                <div class="wid10p">&nbsp;</div>
                                                
                                        </div>

                                        <!-- Second Line: Remaining fields -->
                                        <div class="row-item-bottom">
                                            <div class="rate">
                                                <input type="number" onblur="calculate_total();" name="row_item_price[]" value="${quotation.price}" readonly class="form-control" placeholder="Price">
                                            </div>
                                            <div>
                                                <input type="text" onblur="calculate_total(1);" onchange="calculate_total(1);" name="row_item_disc[]" value="${quotation.disc_value}" class="form-control row_discount">
                                            </div>
                                            <div>
                                                <input type="number" name="row_item_disc_percent[]" min="0" onblur="calculate_total(2);" onchange="calculate_total(2);" value="${quotation.discount_percent}" class="form-control">
                                            </div>
                                            <div>
                                                <input type="text" name="row_price_af_disc[]" readonly class="form-control price_af_discount" placeholder="Price af Disc">
                                            </div>
                                            <div>
                                                <input type="number" name="row_total_af_disc[]" onblur="calculate_total();" onchange="calculate_total();" readonly class="form-control total_af_dis" placeholder="Total af Disc">
                                            </div>
                                            <div>
                                                <input type="text" name="row_item_gst_rate[]" value="${quotation.gst_rate}" readonly class="form-control item_tax_percent" placeholder="Tax Percent">
                                            </div>
                                            <div>
                                                <input type="text" name="row_item_gst_rate_value[]" value="${quotation.gst_value}" readonly class="form-control tax_value" placeholder="Tax Value">
                                            </div>

                                            <div class="tot-amt">
                                                <div>

                                                    <p class="amount"></p>
                                                </div>   
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                 <td>

                                        <div style="text-align:right;">
                                        <button type="button"class="bor_none remove-row-btn"><img src="{{ asset('images/close-icon.png') }}" alt="Close" title="Close" style="width:22px; height:22px;"></button>
                                        {{-- <button type="button" class="btn btn-danger remove-row-btn">X</button> --}}
                                        </div>
                                        <div class="clear"></div>
                                     
                                         <div class="tot-amt1">
                                                    {{-- <label class="lable" for="">Amount</label> --}}
                                                        <div class="amount" style="font-size:12px ; text-align:left;"></div>
                                                </div>
                                </td>
                            </tr>`;
                $('.items-row').prepend(newRow);



                let tablerow = $(`tr#${randomId}`); // Select the tr with the given ID
                console.log('tablerow', tablerow)
                calculate_total(0, tablerow);

            });

            // Add event listener for removing a row
            $('.items-row').on('click', '.remove-row-btn', function () {
                $(this).closest('.items').remove();
                calculate_total();
            });
        }

        function generateRandomId() {
            const randomString = Math.random().toString(36).substr(2, 10); // Generate random string
            const timestamp = Date.now(); // Get current timestamp
            return randomString + timestamp; // Concatenate random string with timestamp

        }

       

        Date.prototype.addDays = function (days) {

            var date = new Date(this.valueOf());
            date.setDate(date.getDate() + parseInt(days));
            var date = new Date(date),
                mnth = ("0" + (date.getMonth() + 1)).slice(-2),
                day = ("0" + date.getDate()).slice(-2);
            var myssy = [date.getFullYear(), mnth, day].join("-");

            console.log(myssy);
            return myssy;

        }

    </script>
@endpush