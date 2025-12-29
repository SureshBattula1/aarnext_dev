@extends('layouts.customer-header')
<style>
    .form-control {
        padding: 0.4rem 0.3rem !important;
    }

    /* =========================
   Base / Resets
   ========================= */
    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    .table-responsive-md {
        overflow-x: auto;
        /* desktop/tablet scroll when needed */
    }

    .input-group-text {
        padding: 0 !important;
    }

    /* Make Select2 dropdown appear over modals/cards if needed */
    .select2-container .select2-dropdown {
        z-index: 9999;
    }

    /* Consistent Select2 single select height + text alignment */
    .select2-container--default .select2-selection--single {
        height: 36px;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 6px;
        display: flex;
        align-items: center;
        /* vertically center text */
        padding: 0 32px 0 8px;
        /* leave space for arrow on the right */
    }

    .select2-container--default .select2-selection__rendered {
        color: #333;
        line-height: 1;
        /* let flex centering do the job */
        padding-left: 0;
        /* already padded via parent */
    }

    .select2-container--default .select2-selection__placeholder {
        color: #999;
        line-height: 1;
        /* no weird line-height */
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Right aligned arrow, vertically centered */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 14px;
        height: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }

    /* Dropdown search + list should fit container/view */
    .select2-search--dropdown {
        display: block;
        width: 100%;
        padding: 6px;
        background-color: #fff;
    }

    .select2-container--default .select2-results>.select2-results__options {
        max-height: 220px;
        overflow-y: auto;
        background-color: #fff;
    }

    /* Subtle dropdown styling */
    .select2-container--default .select2-dropdown {
        font-size: 0.875rem;
        border-radius: 6px;
        border: 1px solid #ccc;
        width: auto;
        /* let Select2 decide */
        min-width: 200px;
        /* sensible min */
        max-width: min(90vw, 480px);
        /* avoid overflowing screen */
    }

    /* Fixed-width helper (optional) */
    .fixed-select-wrapper {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .fixed-select {
        width: 200px !important;
        max-width: 200px !important;
        min-width: 200px !important;
    }

    /* =========================
   Mobile-first: Card view
   (<= 767.98px)
   ========================= */
    @media (max-width: 767.98px) {
        .mb-2 {
            padding: 5px !important;
        }

        .table-responsive-md {
            overflow: visible !important;
            /* cards don't need horizontal scroll */
        }

        #branch_table {
            border: none !important;
        }

        #branch_table thead,
        #branch_table tfoot {
            display: none !important;
        }

        /* Row as card */
        #branch_table tbody tr.items {
            display: block !important;
            width: auto;
            background: #ffffff;
            border: 1px solid #e3e6ea;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            margin-bottom: 1rem;
            padding: 0.75rem 0.75rem 0.25rem;
        }

        /* Cells become label:value rows */
        #branch_table tbody tr.items>td {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            padding: 0.45rem 0.25rem !important;
            border: none !important;
            border-bottom: 1px dashed #f0f1f3 !important;
        }

        #branch_table tbody tr.items>td:last-child {
            border-bottom: none !important;
        }

        /* Label on the left via data-col */
        #branch_table tbody tr.items>td::before {
            content: attr(data-col);
            flex: 0 0 48%;
            font-weight: 600;
            color: #6c757d;
            font-size: 0.9rem;
            padding-right: 0.5rem;
        }

        /* Field/value on the right */
        #branch_table tbody tr.items>td>*:not(:first-child) {
            flex: 1;
            text-align: left;
            min-width: 0;
            /* prevent overflow */
        }

        /* Inputs/selects fill width on cards */
        #branch_table input.form-control,
        #branch_table select.form-control,
        #branch_table .form-select,
        #branch_table .input-group,
        #branch_table .select2-container {
            width: 100% !important;
        }

        /* Tax block stacks */
        #branch_table [data-col="Tax"] {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.5rem;
        }

        /* Actions stack & full width buttons */
        #branch_table [data-col="Actions"] .d-flex {
            width: 100%;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        #branch_table [data-col="Actions"] .btn {
            flex: 1 1 48%;
        }

        /* Extra info row under card */
        #branch_table tbody tr.extra-info-row {
            display: block !important;
            width: auto;
            background: #f9fafb;
            border-radius: 10px;
            margin-top: -0.75rem;
            margin-bottom: 1rem;
            padding: 0.5rem 0.75rem;
        }

        /* Hide empty icon column */
        #branch_table td.d-none.d-md-table-cell {
            display: none !important;
        }

        /* Select2: make dropdown as wide as card on phones */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-dropdown {
            max-width: 100vw;
            min-width: 37vw;
            /* feels full-width inside cards */
        }
    }

    /* =========================
   Tablet: 768px–991.98px
   Keep table layout but
   ease widths & spacing
   ========================= */
    @media (min-width: 768px) and (max-width: 991.98px) {

        /* Make selects reasonably wide but flexible */
        .select2-container {
            width: 100% !important;
            /* let form grid control actual width */
            max-width: 280px;
            /* typical tablet column */
        }

        .select2-container--default .select2-dropdown {
            min-width: 240px;
            max-width: min(90vw, 520px);
        }

        /* Table breathes a bit more */
        #branch_table td,
        #branch_table th {
            padding: 0.6rem 0.5rem;
            vertical-align: middle;
        }
    }

    /* =========================
   Desktop: >= 992px
   (traditional table)
   ========================= */
    @media (min-width: 992px) {

        .select2-container {
            width: auto !important;
            min-width: 200px;
            max-width: 260px;
        }

        .select2-container--default .select2-selection--single {
            width: 410px;
            /* tweak to your grid; or remove to let parent control */
        }

        .select2-container--default .select2-dropdown {
            min-width: 410px;
            max-width: 560px;
        }

        #branch_table td,
        #branch_table th {
            padding: 0.6rem 0.5rem;
            vertical-align: middle;
        }

        .customer-card-body {
            height: 80px;
            padding: 8px;
        }
    }

    /* Misc. spacing for any customer details block */
    .customerDetails {
        margin-top: 10px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 12px !important;
    }

    th {
        background-color: #1a71a4 !important;
        color: #ffffff !important;
        margin-left: 5px !important;
    }

    table#branch_table th {
        padding: 0.4rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__clear {
        margin-top: 0px !important;
        font-size: 10px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 7px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        margin-top: 10px !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #bcd6ff !important;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2px;
        /* Adds space between each row */
    }

    .summary-section {
        border: 1px solid gainsboro;
        padding: 15px;
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
</style>
@section('content')
    <form id="new_quotation_form" action="javascript:void(0)" method="POST" autocomplete="off">
        {{ csrf_field() }}

        @php
            $customer_id = Auth::guard('customer')->user()->card_code;
            $customer_name = Auth::guard('customer')->user()->card_name;
            $customer_sub_grp = Auth::guard('customer')->user()->sub_grp;
            $customer_grp_code = Auth::guard('customer')->user()->grp_code;
        @endphp

        <div class="content-wrapper container-fluid px-2 px-md-3">
            {{-- Customer Information Card --}}
            <div class="card mb-2">
                <div class="customer-card-body">

                    <input type="hidden" name="customer_sub_grp" id="customer_sub_grp" value="{{ $customer_sub_grp }}">
                    <input type="hidden" name="customer_grp_code" id="customer_grp_code" value="{{ $customer_grp_code }}">
                    <input type="hidden" name="customer_id" id="customer_id" value="{{ $customer_id }}">

                    {{-- Form Fields in Compact Row --}}
                    <div class="row g-2 mb-2" style="margin-top:3px;">
                        <div class="col-12 col-md-2">
                            <div>
                                <strong>Customer Code:</strong>
                            </div>
                            <div style="border: 1px solid rgb(212, 212, 212); padding:2px;">
                                <span>{{ $customer_id }}</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div>
                                <strong for="ref_no" class="form-label">Reference No</strong>
                            </div>
                            <input type="text" class="form-control" id="ref_no" name="ref_no"
                                placeholder="Enter Ref Number">
                        </div>

                        <div class="col-12 col-md-2">
                            <div class="form-group fixed-select-wrapper">
                                <div>
                                    <strong for="cust_address_id" class="form-label">Customer Address</strong>
                                </div>
                                <select id="cust_address_id" name="cust_address_id" class="form-control fixed-select"
                                    tabindex="3">
                                    <option selected value="">Select Address</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-2">
                            <div>
                                <strong for="sales_type" class="form-label">Sales Type</strong>
                            </div>
                            <select class="form-control" name="sales_type" id="sales_type">
                                <option value="">Select Type</option>
                                <option value="cash">Cash</option>
                                <option value="cash/credit">Cash/Credit</option>
                                <option value="hospital">Hospital</option>
                                <option value="credit">Credit</option>
                            </select>
                        </div>
                    </div>

                    {{-- Customer Info Pills --}}
                    {{-- <div class="info-pills">
                        <div class="info-pill">
                            <span>📅</span>
                            <span id="current-date"></span>
                        </div>
                        <div class="info-pill">
                            <span>💳</span>
                            <span class="customer-pymnt_grp"></span>
                        </div>
                        <div class="info-pill">
                            <span>📊</span>
                            <span class="customer-hike-perc"></span>
                        </div>
                        <div class="info-pill">
                            <span>🏷️</span>
                            <span class="cust_default_discount"></span>
                        </div>
                        <div class="info-pill">
                            <span>🔖</span>
                            <span class="nif_no"></span>
                        </div>
                    </div> --}}
                </div>
            </div>

            {{-- Items Card --}}
            <div class="card items-card mb-2">
                <div class="card-body">

                    {{-- Desktop/Tablet table (becomes stacked cards on xs/sm) --}}
                    <div class="table-responsive-md">
                        <table id="branch_table" class="table table-sm align-middle quotation-form-table">
                            <thead>
                                <tr>
                                    <th width="1%">S No:</th>
                                    <th width="15%">Item No</th>
                                    <th width="7%" class="text-center">Qty</th>
                                    <th width="8%" class="text-center">Unit Price</th>
                                    <th width="7%" class="text-center">Disc.</th>
                                    <th width="7%" class="text-center">Disc. %</th>
                                    <th width="9%" class="text-center">Price A/D</th>
                                    <th width="10%" class="text-center">Tax</th>
                                    <th width="8%" class="text-center">Total</th>
                                    <th width="18%"></th>
                                    <th width="11%" class="text-center">
                                        <button id="addRowButton" type="button" class="btn btn-warning btn-sm addNewRow">
                                            + Add Row
                                        </button>
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="items-row">
                                <tr class="items">
                                    <input type="hidden" name="row_item_id[]">
                                    <td class="s-no text-center"></td>
                                    {{-- Item no --}}
                                    <td class="item_code" data-col="Item No">
                                        <select class="form-control row_item_no select2-item" tabindex="3"
                                            name="row_item_no[]" data-live-search="true">
                                        </select>
                                    </td>

                                    {{-- Qty --}}
                                    <td data-col="Qty">
                                        <input type="text" min="1" onfocus="removeZero(this)"
                                            onblur="restoreZero(this); calculate_total(this);"
                                            tabindex="5" onchange="calculate_total(this);" name="row_item_qty[]"
                                            value="1" class="form-control text-end qty-input" oninput="validateInteger(this)"
                                            inputmode="numeric">
                                    </td>

                                    {{-- Unit Price --}}
                                    <td class="rate" id="rate" data-col="Unit Price">
                                        <input type="number" onblur="calculate_total();" onchange="calculate_total();"
                                            name="row_item_price[]" value="" readonly
                                            class="form-control text-end">
                                    </td>

                                    {{-- Discount value --}}
                                    <td data-col="Discount">
                                        <input type="text" onblur="calculate_total(this);" onchange="calculate_total(this);"
                                            name="row_item_disc[]" id="row_discount" class="form-control text-end disc-amt"
                                            min="0" value="0.00">
                                    </td>

                                    {{-- Discount % --}}
                                    <td data-col="Discount %">
                                        <input type="number" name="row_item_disc_percent[]" min="0"
                                            onblur="calculate_total(this);" onchange="calculate_total(this);"
                                            id="dis_percentage" value="0.00" class="form-control text-end disc-pct">
                                    </td>

                                    {{-- Price after disc --}}
                                    <td data-col="Price after Disc.">
                                        <input type="text" onblur="calculate_total();" onchange="calculate_total();"
                                            name="row_price_af_disc[]" id="price_af_discount" readonly
                                            class="form-control text-end" value="">
                                    </td>

                                    {{-- Tax --}}
                                    <td data-col="Tax">
                                        <div class="d-flex flex-column">
                                            <div class="input-group input-group-sm mb-1">
                                                <span class="input-group-text justify-content-center"
                                                    style="min-width:40px; font-style=6px;">%</span>
                                                <input type="text" name="row_item_gst_rate[]" id="item_tax_percent"
                                                    value="" readonly class="form-control text-end">
                                            </div>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text justify-content-center"
                                                    style="min-width:40px;">Val</span>
                                                <input type="text" name="row_item_gst_rate_value[]" id="tax_value"
                                                    value="" readonly class="form-control text-end">
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Total --}}
                                    <td class="amount text-end" data-col="Total"></td>
 
                                    {{-- Actions / Batch --}}
                                    <td data-col="Actions">
                                        <div class="d-flex align-items-start gap-2 flex-wrap">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn">
                                                <i class="ti-trash"></i>
                                            </button>
                                            <div id="batch-list" class="batchs">
                                                <div class="batch-info small"></div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Empty (was icon column) --}}
                                    <td class="d-none d-md-table-cell"></td>
                                </tr>

                                {{-- Extra info row (full width) --}}
                                <tr class="extra-info-row">
                                    <td colspan="10">
                                        <div class="extra-info"></div>
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot></tfoot>
                        </table>
                    </div>

                    {{-- Summary + Remarks --}}
                    <div class="row g-2 mt-1">
                        <div class="col-12 col-lg-4">
                            <label for="remarks" class="form-label">📝 Remarks</label>
                            <textarea name="remarks" id="remarks" class="form-control" rows="5"
                                placeholder="Add any notes or special instructions..." style="font-size: 0.8rem;"></textarea>
                        </div>
                        <div class="col-12 col-lg-3">
                        </div>

                        <div class="col-12 col-lg-4">
                            <div class="summary-section">
                                <h5 style="border-bottom: 1px solid #c6d2eb">Summary</h5>
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

        {{-- Bottom buttons --}}
        <div class="card">
            <div class="card-body py-2">
                <div class="d-flex gap-2 justify-content-end flex-wrap align-items-center">
                    <button type="button" class="btn btn-outline-danger px-4" onclick="location.reload();">
                        ✕ Cancel
                    </button>
                    <button class="btn btn-primary transaction-submit px-5" type="submit">
                        ✓ Save Order
                    </button>
                </div>
            </div>
        </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        var public_path = "{{ url('/') }}";
    </script>
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
        (function($) {
            const API = {
                itemSearchItemNo: (term, cardCode) => ({
                    url: public_path + '/customer-item-search',
                    data: {
                        search: term,
                        type: 'user_search',
                        card_code: cardCode
                    },
                    dataType: 'json',
                    cache: true,
                }),
                customerAddresses: (cardCode) => `${public_path}/customer-address-list/${cardCode}`,
                calcDueDate: () => `${public_path}/calculate-due-date`,
            };

            let globalCustomerDiscount = window.globalCustomerDiscount || 0;

            // ---------- Select2: Common init ----------
            function initSelect2Item($el) {
                $el.select2({
                        placeholder: 'Search for an Item...',
                        allowClear: true,
                        minimumInputLength: 3,
                        escapeMarkup: (m) => m,
                        ajax: {
                            transport: function(params, success, failure) {
                                const cardCode = $('#customer_id').val();
                                const term = params.data?.term || '';
                                return $.ajax(API.itemSearchItemNo(term, cardCode)).then(success).catch(
                                    failure);
                            },
                            processResults: function(data) {
                                // data expected as [{id,text,result:{item_code,item_name,price,tax,quantity,...}}, ...]
                                return {
                                    results: data
                                };
                            }
                        }
                    })
                    .off('select2:select')
                    .on('select2:select', function(e) {
                        const selected = e.params.data;
                        const r = $(this).closest('tr.items');

                        // fill mapping
                        r.find('input[name="row_item_id[]"]').val(selected.result.id || '');
                        r.find('input[name="row_item_order_id[]"]').val(selected.order_id || '');
                        r.find('input[name="row_item_price[]"]').val((+selected.result.price || 0).toFixed(2));
                        r.find('input[name="row_item_gst_rate[]"]').val(+selected.result.tax || 0);
                        r.find('input[name="row_item_no[]"]').val(selected.result.item_code || '');

                        // Extra info (the next .extra-info-row)
                        const infoRow = r.next('.extra-info-row');
                        infoRow.find('.extra-info').html(
                            `<strong style="color:#4a90e2;">Description:</strong>
                                <span style="color:#000;font-weight:600;">Item Code:</span>
                                <span style="color:#ae700c;font-weight:700;">${selected.result.item_code || 'NULL'}</span>,
                                <span style="color:#000;font-weight:600;">Item Name:</span>
                                <span style="color:#ae700c;font-weight:700;">${selected.result.item_name || 'NULL'}</span>,
                                <span style="color:#000;font-weight:600;">WH Quantity:</span>
                                <span style="color:#ae700c;font-weight:700;">${Math.floor(+selected.result.quantity || 0)}</span>`
                        ).show();

                        // default discount (customer level)
                        if (+globalCustomerDiscount > 0) {
                            const price = +r.find('input[name="row_item_price[]"]').val() || 0;
                            const discAmt = (price * globalCustomerDiscount) / 100;
                            r.find('input[name="row_item_disc_percent[]"]').val(globalCustomerDiscount.toFixed(2));
                            r.find('input[name="row_item_disc[]"]').val(discAmt.toFixed(2));
                        }

                        // Focus qty and compute totals
                        setTimeout(() => r.find('input[name="row_item_qty[]"]').focus(), 150);
                        calculateRowTotals(r);

                        // Auto add a new row if last one is filled
                        const $last = $('.items-row .items:last .select2-item');
                        const v = $last.val();
                        if (typeof v !== 'undefined' && v !== null && v !== '') {
                            addNewRow();
                            updateSerialNumbers();
                        }

                        // Add row button behavior (prevent dupes)
                        $('#addRowButton').off('click').on('click', function() {
                            const lastVal = $('.items-row .items:last .select2-item').val();
                            if (!lastVal) return;
                            addNewRow();
                            updateSerialNumbers();
                        });
                    });
            }

            // ---------- Create a new row ----------
            function rowTemplate(tab1, tab2, tab3, tab4) {
                return `
                <tr class="items">
                    <input type="hidden" name="row_item_id[]">
                    <input type="hidden" name="row_item_order_id[]">
                    <input type="hidden" name="row_total_af_disc[]">

                    <td class="s-no text-center"></td>

                    <td data-col="Item No">
                    <select class="select2-item" name="row_item_no[]" data-live-search="true" tabindex="${tab1}">
                        <option value="">Select Item</option>
                    </select>
                    </td>
            
                    <td data-col="Qty">
                    <input type="text" min="1" tabindex="${tab2}" autocomplete="off"
                        name="row_item_qty[]" value="1"
                        class="form-control text-end qty-input">
                    </td>
            
                    <td data-col="Unit Price" class="rate">
                    <input type="number" name="row_item_price[]" value="" readonly class="form-control text-end">
                    </td>
            
                    <td data-col="Discount">
                    <input type="text" name="row_item_disc[]" class="form-control text-end disc-amt" min="0" value="0.00">
                    </td>
            
                    <td data-col="Discount %">
                    <input type="number" name="row_item_disc_percent[]" min="0" value="0.00" class="form-control text-end disc-pct">
                    </td>
            
                    <td data-col="Price after Disc.">
                    <input type="text" name="row_price_af_disc[]" readonly class="form-control text-end">
                    </td>
            
                    <td data-col="Tax">
                    <div class="d-flex flex-column">
                        <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text justify-content-center" style="min-width:40px;">%</span>
                        <input type="text" name="row_item_gst_rate[]" value="" readonly class="form-control text-end">
                        </div>
                        <div class="input-group input-group-sm">
                        <span class="input-group-text justify-content-center" style="min-width:40px;">Val</span>
                        <input type="text" name="row_item_gst_rate_value[]" value="" readonly class="form-control text-end">
                        </div>
                    </div>
                    </td>
            
                    <td class="amount text-end" data-col="Total"></td>
            
                    <td data-col="Actions">
                    <div class="d-flex align-items-start gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn">
                            <i class="ti-trash"></i>
                        </button>
                    </div>
                    </td>
            
                    <td class="d-none d-md-table-cell"></td>
                </tr>
            
                <tr class="extra-info-row">
                    <td colspan="10">
                    <div class="extra-info"></div>
                    </td>
                </tr>
                `;
            }

            function addNewRow() {
                const rows = $('.items-row tr').length;
                let base = rows * 11 + 2;
                const html = rowTemplate(base++, base++, base++, base++);
                $('.items-row').append(html);
                const $newRow = $('.items-row .items:last');
                initRow($newRow);
            }

            // ---------- Row-level bindings ----------
            function initRow($row) {
                // Item select2
                initSelect2Item($row.find('.select2-item'));

                // Numeric guards
                $row.on('input', 'input[name="row_item_qty[]"]', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });

                // Discount sync and totals
                $row.on('input change blur', '.qty-input, .disc-amt, .disc-pct', function() {
                    calculateRowTotals($row);
                });

                // Remove row
                $row.closest('tbody').off('click', '.remove-row-btn').on('click', '.remove-row-btn', function() {
                    const itemRow = $(this).closest('tr.items');
                    const extraInfoRow = itemRow.next('.extra-info-row');
                    itemRow.remove();
                    extraInfoRow.remove();
                    updateSummary();
                    updateSerialNumbers();
                });
            }

            function calculate_total(el) {
                const $row = $(el).closest('tr.items');
                calculateRowTotals($row);
            }
            window.calculate_total = calculate_total;

            function removeZero(el) {
                if (el.value == '1') el.value = '';
            }
            window.removeZero = removeZero;

            function restoreZero(el) {
                if (!el.value.trim()) el.value = '1';
            }
            window.restoreZero = restoreZero;
            
            

            // ---------- Totals ----------
            function calculateRowTotals($row) {
                const qty = +($row.find('input[name="row_item_qty[]"]').val() || 0);
                const price = +($row.find('input[name="row_item_price[]"]').val() || 0);
                if (!price) {
                    updateSummary();
                    return;
                }

                let discAmt = +($row.find('input[name="row_item_disc[]"]').val() || 0);
                let discPct = +($row.find('input[name="row_item_disc_percent[]"]').val() || 0);

                // clamp %
                if (discPct > 99.99) {
                    discPct = 99.99;
                    $row.find('input[name="row_item_disc_percent[]"]').val(discPct.toFixed(2));
                }
                // compute missing field based on last edited control
                const $active = document.activeElement;
                if ($active && $($active).is('.disc-pct')) {
                    discAmt = (price * discPct) / 100;
                    $row.find('input[name="row_item_disc[]"]').val(discAmt.toFixed(2));
                } else if ($active && $($active).is('.disc-amt')) {
                    discPct = price ? (discAmt / price) * 100 : 0;
                    if (discPct > 99.99) {
                        discPct = 99.99;
                        discAmt = (price * discPct) / 100;
                        $row.find('input[name="row_item_disc[]"]').val(discAmt.toFixed(2));
                    }
                    $row.find('input[name="row_item_disc_percent[]"]').val(discPct.toFixed(4));
                } else {
                    // default behavior (ensure consistent)
                    discAmt = (price * discPct) / 100;
                    $row.find('input[name="row_item_disc[]"]').val(discAmt.toFixed(2));
                }

                const priceAfterDisc = Math.max(price - discAmt, 0);
                $row.find('input[name="row_price_af_disc[]"]').val(priceAfterDisc.toFixed(2));

                const totalAfterDisc = priceAfterDisc * qty;
                $row.find('input[name="row_total_af_disc[]"]').val(totalAfterDisc.toFixed(2));

                const taxPct = +($row.find('input[name="row_item_gst_rate[]"]').val() || 0);
                const taxPerItem = (priceAfterDisc * taxPct) / 100;
                const taxValueTotal = (totalAfterDisc * taxPct) / 100;
                $row.find('input[name="row_item_gst_rate_value[]"]').val(taxPerItem.toFixed(2));

                const rowTotal = totalAfterDisc + taxValueTotal;
                $row.find('.amount').text(rowTotal.toFixed(2));

                updateSummary();
            }

            function updateSummary() {
                let subtotal = 0,
                    totalTax = 0,
                    totalDiscount = 0,
                    totalPriceAfterDiscount = 0,
                    totalAfterDiscount = 0,
                    totalGstValue = 0;

                $('.items').each(function() {
                    const $r = $(this);
                    const qty = +($r.find('input[name="row_item_qty[]"]').val() || 0);
                    const price = +($r.find('input[name="row_item_price[]"]').val() || 0);
                    const discAmt = +($r.find('input[name="row_item_disc[]"]').val() || 0);
                    const priceAfterDisc = +($r.find('input[name="row_price_af_disc[]"]').val() || 0);
                    const totalAfDisc = +($r.find('input[name="row_total_af_disc[]"]').val() || 0);
                    const taxPct = +($r.find('input[name="row_item_gst_rate[]"]').val() || 0);
                    const gstPerItem = +($r.find('input[name="row_item_gst_rate_value[]"]').val() || 0);

                    subtotal += price * qty;
                    totalDiscount += discAmt * qty;
                    totalPriceAfterDiscount += priceAfterDisc * qty;
                    totalAfterDiscount += totalAfDisc;
                    totalGstValue += gstPerItem * qty;
                    totalTax += (totalAfDisc * taxPct) / 100;
                });

                $('#subtotal').text(subtotal.toFixed(2));
                $('#total_discount').text(totalDiscount.toFixed(2));
                $('#total_price_af_disc').text(totalPriceAfterDiscount.toFixed(2));
                $('#total_af_disc').text(totalAfterDiscount.toFixed(2));
                $('#total_gst_value').text(totalGstValue.toFixed(2));
                $('#total_tax').text(totalTax.toFixed(2));

                let g = 0;
                $('.items').each(function() {
                    g += +($(this).find('.amount').text() || 0);
                });
                $('#grand_total').text(g.toFixed(2));
            }

            // ---------- Customer addresses ----------
            function custAddresses() {
                var cardCode = $('#customer_id').val();
                if (cardCode) {
                    $("#cust_address_id").select2({
                        ajax: {
                            url: public_path + "/customer-addresses-list/" + cardCode,
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: function(params) {
                                return {
                                    search: params.term,
                                    type: 'customer_address',
                                };
                            },
                            processResults: function(data) {
                                return {
                                    results: data.addresses.map(function(address) {
                                        return {
                                            id: address.id,
                                            text: [
                                                    address.street || '',
                                                    address.city || '',
                                                    address.state || '',
                                                    address.country || '',
                                                    address.zip_code || '',
                                                ]
                                                .filter(Boolean)
                                                .join(' '),
                                        };
                                    }),
                                };
                            },
                        },
                        cache: true,
                        placeholder: "Select Customer Address",
                        allowClear: true,
                        escapeMarkup: function(markup) {
                            return markup;
                        },

                        minimumInputLength: 0,
                        templateResult: function(data) {
                            if (!data.id) {
                                return data.text;
                            }
                            return $('<span style="font-weight: bold; color: black;">' + data.text + '</span>');
                        }

                    });
                }
            }

            // ---------- Update Serial Numbers ----------
            function updateSerialNumbers() {
                $('.items-row .items').each(function(index) {
                    $(this).find('.s-no').text(index + 1);
                });
            }

            // ---------- Boot ----------
            $(document).ready(function() {
                // init existing first row
                $('.items-row .items').each(function() {
                    initRow($(this));
                });
                updateSerialNumbers();

                // initial select2 for address
                $('#cust_address_id').select2({
                    placeholder: "Select Address",
                    allowClear: true,
                    minimumInputLength: 0,
                }).on('select2:open', function() {
                    $(".select2-container").addClass("form-controls");
                });

                // customer change → load addresses/due date
                $('#customer_id').on('change', custAddresses);

                // call on page load to fetch addresses for current customer id
                custAddresses();

                // Unselect handlers for select2 (clear row)
                $(document).on('select2:unselecting', '.select2-item', function(e) {
                    e.preventDefault();
                    const $r = $(this).closest('tr.items');
                    $r.find('input[name="row_item_id[]"]').val('');
                    $r.find('input[name="row_item_order_id[]"]').val('');
                    $r.find('input[name="row_item_qty[]"]').val('1');
                    $r.find('input[name="row_item_price[]"]').val('');
                    $r.find('input[name="row_item_disc[]"]').val('0.00');
                    $r.find('input[name="row_item_disc_percent[]"]').val('0.00');
                    $r.find('input[name="row_price_af_disc[]"]').val('');
                    $r.find('input[name="row_total_af_disc[]"]').val('');
                    $r.find('input[name="row_item_gst_rate[]"]').val('');
                    $r.find('input[name="row_item_gst_rate_value[]"]').val('');
                    $r.find('.amount').text('');
                    $(this).val(null).trigger('change');
                    updateSummary();
                });
            });

        })(jQuery);


        $(document).ready(function() {
            $("#new_quotation_form").validate({
                errorClass: "state-error",
                validClass: "state-success",
                errorElement: "em",
                ignore: [],

                /* @validation rules
                ------------------------------------------ */
                rules: {
                    sales_type: {
                        required: true
                    },
                },
                /* @validation error messages
                ---------------------------------------------- */

                messages: {
                    sales_type: {
                        required: "Please select a sales Type."
                    },
                },
                submitHandler: function(form) {

                    if ($('select[name="row_item_no[]"]').filter(function() {
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
                        url: public_path + '/store-customer-orders',
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
                                });
                                window.open(result.sales_order_url, '_blank');
                                location.reload();
                            } else {
                                Swal.fire({
                                    type: 'warning',
                                    title: result.message,
                                    showConfirmButton: true,
                                });
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
    </script>
@endpush
