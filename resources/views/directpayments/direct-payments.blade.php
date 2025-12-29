@extends('layouts.app')

<style>
    .inv-details {
        display: flex;
        width: 400px;
    }

    .invoice-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #ccc;

    }

    .invoice-row:last-child {
        border-bottom: none;
    }



    .invoice-row input[type="text"],
    .invoice-row input[type="number"] {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px;
        width: 30%;
        text-align: center;
        background-color: #fff;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .invoice-row input[type="text"]:read-only {
        background-color: #f1f1f1;
    }

    .invoice-container {
        padding: 10px;
    }

    .invoice-heading {
        display: flex;
        justify-content: space-between;
        font-weight: bold;
        padding: 10px 0;
        border-bottom: 2px solid #333;
    }

    .invoice-heading div {
        width: 30%;
        text-align: center;
    }

    #invoice-details-container {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        background-color: #f9f9f9;
        max-width: 100%;
        margin: 20px auto;
    }

    .form-control.bottomFileds {
        width: 138px;
        height: 30px;
        margin-left: 10px;
    }

    /*.form-control.bottomFiledsonline {
        width: 170px;
        height: 30px;
        margin-top: -15px;
    }*/

    /*.form-control.bottomFiledscash {
        width: 170px;
        height: 30px;
    }*/
</style>
@section('content')
    <form id="direct_payments_form" method="POST" autocomplete="off">

        {{ csrf_field() }}

        <div class="content-wrapper">
            <div class="card ">
                <div class="card-header text-black">
                    <h5 class="mb-8" style="text-align: center ">DIRECT PAYMENTS</h5>
                </div>
                <div class="card-body">
                    <!-- Customer Selection -->
                    <div class="form-group row card_bg1">
                        <!--<input type="hidden" name="invoice_id" id="invoice_id" >-->
                        <label class="col-sm-2 col-form-label">Customer:</label>
                        <div class="col-sm-10">
                            <select tabindex="1" required class="form-control select2 md-select2" name="customer_code"
                                id="customer_id" data-live-search="true">
                                <!-- Options will be dynamically added -->
                            </select>
                        </div>
                    </div>


                    <div id="invoice-details-container" style="display:none;" class="invoice-container"></div>


                    <div class="form-group row ">

                        <div class="col-sm-10" style="margin-left: 609px;">
                            <label for="">Total amount </label>
                            <input type="number" class="form-control bottomFileds" id="payment" name="payment"
                                placeholder="Total amount" readonly>
                        </div>

                        </div>


                        <div class="row" style="margin-bottom:10px;">
                        <div class="col-3" >
                            <label for="cash">Cash:</label>
                            <input type="number" id="cash" name="cash" placeholder="Cash amount" value="0"
                                class="form-control bottomFiledscash" style=" width:170px;">
                        </div>


                        <div class="col-2">
                            <!--<div class="form-group">
                            <div>-->
                            <label for="cheque">Payment Method:</label>
                            <select class="form-control bottomFiledsonline" id="payment_method" name="payment_method"
                                onchange="EnablePayment();" style=" width:170px;">
                                <option value="">Select Payment Method</option>
                                <option value="bank">BANK</option>
                                <option value="tpa">TPA</option>
                            </select>
                            <!--</div>-->
                           
                            <!--<br>-->
                            
                            </div>
                            <div class="col-3" style="margin-top: 17px; height:30px;">
                                <input type="number" id="online" name="online" placeholder="Online amount"
                                class="form-control bottomFileds" style="display:none;">
                            </div>
                            
                        </div>
                        <input type="hidden" value= "{{ rand(1, 1000) . time() }}" name="guid" id="guid">

                        <div class="col-3"></div>
                        </div>

                    <!-- Card Footer -->
                    <div class="card-footer text-end">
                        <button class="btn btn-primary mleft5 transaction-submit" type="submit">Submit Payment</button>
                        <button type="button" class="btn btn-danger mleft10" onclick="location.reload();">Cancel</button>
                    </div>
                </div>
            </div>
    </form>
@endsection

@push('scripts')
    <script>
        function EnablePayment() {
            var lead_val = $('#payment_method').val();
            if (lead_val == "bank" || lead_val == "tpa") {
                $('#online').show();
            } else if (lead_val == "tpa") {
                $('#online').show();
            }
        }
        $(document).ready(function() {
            $("#direct_payments_form").validate({
                errorClass: "state-error",
                validClass: "state-success",
                errorElement: "em",
                ignore: [],

                rules: {
                    amount: {
                        required: true,
                        number: true,
                        min: 1
                    },
                   
                    payment_type: {
                        required: true
                    },
                    customer_code: {
                        required: true
                    }
                },
                messages: {
                    amount: {
                        required: "Please enter an amount.",
                        number: "Please enter a valid number.",
                        min: "Amount must be at least 1."
                    },
                    payment_method: {
                        required: "Please select a payment method."
                    },
                    payment_type: {
                        required: "Please select a payment type."
                    },
                    customer_code: {
                        required: "Please select a customer."
                    }
                },
                submitHandler: function(form) {
                    if (!confirm('Are you sure to Submit?')) return false;

                    $('#processingTextSubmit').show();
                    const amount = parseFloat($('#payment').val());
                    const cash = parseFloat($('#cash').val() || 0);
                    const online = parseFloat($('#online').val() || 0);

                    if ((cash + online) < amount) {
                        alert("Cash and Online value should not be less than the total amount!");
                        return false;
                    }

                    var selectedInvoices = [];
                    $(".invoice-checkbox:checked").each(function() {
                        selectedInvoices.push($(this).val());
                    });

                    const formData = new FormData(form);
                    formData.append('cash', cash);
                    formData.append('online', online);
                    formData.append('selected_invoices', JSON.stringify(selectedInvoices));
                    $.ajax({
                        url: "{{ route('store-direct-payment') }}",
                        method: 'POST',
                        data: new FormData(form),
                        dataType: 'json',
                        async: false,
                        cache: false,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(result) {
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
                        }
                    });
                }
            });

            $("#customer_id").select2({
                ajax: {
                    url: "{{ url('/store-customer-search') }}",
                    dataType: 'json',
                    data: function(params) {
                        return {
                            search: params.term,
                            type: 'user_search'
                        };
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
                minimumInputLength: 3,
                escapeMarkup: function(markup) {
                    return markup;
                }
            });


            function calculateTotal() {
                let totalAmount = 0;
                let PaymentAmunt = 0;
                $('input[name="select_invoice[]"]:checked').each(function() {
                    let invoiceAmount = $(this).closest('.invoice-row').find('.invoice-payment_amount').val();
                    totalAmount += parseFloat(invoiceAmount) || 0;
                });
                $('#payment').val(totalAmount.toFixed(2));
            }


            $("#customer_id").on('change', function() {
                var customerId = $(this).val();
                if (customerId) {
                    $.ajax({
                        url: "{{ url('customer-invoices') }}/" + customerId,
                        method: "GET",
                        success: function(response) {
                            if (response && response.invoices && response.invoices.length > 0) {
                                $('#invoice-details-container').empty().show();
                                $('#invoice-details-container').append(
                                    '<div class="invoice-heading"><div>Invoice Number</div><div>Total Amount</div><div>Paid</div><div>Balace</div><div>Payment</div><div>Doc Date</div><div>Due Date</div></div>'
                                );

                                let totalAmount = 0;
                                let i =0;
                                response.invoices.forEach(function(invoice) {
                                    $('#invoice-details-container').append(
                                `<div class="invoice-row">
                                    <input type="hidden" value="${invoice.id}" name="invoice_id[${i}]">
                                    <input type="checkbox" name="select_invoice[]" value="${invoice.invoice_no}_${i}" class="invoice-checkbox">
                                    <input type="text" name="invoice_no[]" value="${invoice.invoice_no}" readonly>
                                    <input type="number" name="total[]" value="${invoice.total}" class="form-control invoice-total" readonly>
                                    <input type="number" name="paid_amount[]" value="${invoice.paid_amount}" class="form-control invoice-paid_amount" readonly>
                                    <input type="number" name="balance_amount[]" value="${invoice.balance_amount}" class="form-control invoice-balance_amount" readonly>
                                    <input type="number" name="payment_amount[]" value="${invoice.balance_amount}" class="form-control invoice-payment_amount">
                                    <input type="text" name="order_date[]" value="${invoice.order_date}" readonly>
                                    <input type="text" name="due_date[]" value="${invoice.due_date}" readonly>
                                    <input type="hidden" name="payment[]" value="${invoice.balance_amount}" class="form-control invoice-payment" readonly>

                                </div>`
                                    );

                                    $('input[name="select_invoice[]"]').on('change',
                                        calculateTotal);
                                    $('input[name="payment_amount[]"]').on('input',
                                        calculateTotal);
                                        i++;
                                });
                                calculateTotal();
                            } else {
                                $('#invoice-details-container').hide();
                                $('#payment').val('');
                            }
                        },
                        error: function() {
                            $('#invoice-details-container').hide();
                            $('#payment').val('');
                            alert("Error fetching invoice details.");
                        }
                    });
                } else {
                    $('#invoice-details-container').hide();
                    $('#payment').val('');
                }
            });

        });
    </script>
@endpush
