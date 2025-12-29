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
        max-width: 600px;
        margin: 20px auto;
    }

    .form-control.bottomFileds {
        /* margin-left: 40px; */
        width: 250px;
        height: 40px;
    }

    .form-control.bottomFiledsTot {
        text-align: center;
        width: 250px;
        height: 40px;
    }
</style>
@section('content')
    <form id="direct_payments_form" method="POST">

        {{ csrf_field() }}

        <div class="content-wrapper">
            <div class="card ">
                <div class="card-header text-black">
                    <h5 class="mb-8" style="text-align: center ">DIRECT PAYMENTS</h5>
                </div>
                <div class="card-body">
                    <!-- Customer Selection -->
                    <div class="form-group row card_bg1">
                        <label class="col-sm-2 col-form-label">Customer:</label>
                        <div class="col-sm-10">
                            <select tabindex="1" required class="form-control select2 md-select2" name="customer_code"
                                id="customer_id" data-live-search="true">
                                <!-- Options will be dynamically added -->
                            </select>
                        </div>
                    </div>


                    <div id="invoice-details-container" style="display:none;" class="invoice-container"></div>



                    <!-- Payment Method -->
                    {{-- <div class="form-group row">
                    <label for="payment_method" class="col-sm-2 ">Payment Method:</label>
                    <div class="col-sm-4">
                        <select class="form-control" id="payment_method" name="payment_method" required>
                            <option value="cash">CASH</option>
                            <option value="bank">BANK</option>
                            <option value="tpa">TPA</option>
                        </select>
                    </div>
                </div> --}}

                    <!-- Payment Type -->
                    {{-- <div class="form-group row">
                    <label for="payment_type" class="col-sm-2">Payment Type:</label>
                    <div class="col-lg-4">
                        <select class="form-control" id="payment_type" name="payment_type" required>
                            <option value="partial">Partial Payment</option>
                            <option value="full">Full Payment</option>
                        </select>
                    </div>
                </div> --}}

                    <!-- Amount -->
                    {{-- <div class="form-group row">
                    <label for="amount" class="col-sm-2 col-form-label">Total Amount:</label>
                    <div class="col-sm-4">
                        <input type="number" class="form-control" id="amount" name="amount"
                            placeholder="Enter payment amount" required>
                    </div>
                </div>
            </div> --}}



                    <div class="form-group row ">
                        <div class="col-5"></div>
                        <div class="col-sm-4">
                            <label for="amount">Total Amount:</label>
                            <input type="number" class="form-control bottomFileds" id="amount" name="amount"
                                placeholder="Total amount">
                        </div>
                        <div class="col-3"></div>
                    </div>


                    <div class="row" style="margin-bottom: 10px">
                        <div class="col-3"></div>
                        <div class="col-3">
                            <label for="cash">Cash:</label>
                            <input type="number" id="cash" name="cash" placeholder="Cash amount"
                                class="form-control bottomFileds">
                        </div>
                        <div class="col-3">
                            <label for="online">Online:</label>
                            <input type="number" id="online" name="online" placeholder="Online amount"
                                class="form-control bottomFileds">
                        </div>
                        <div class="col-3"></div>
                    </div>


                    <!-- Card Footer -->
                    <div class="card-footer text-center">
                        <button class="btn btn-primary mleft5 transaction-submit" type="submit">Submit Payment</button>
                        <button type="button" class="btn btn-danger mleft10" onclick="location.reload();">Cancel</button>
                    </div>
                </div>
            </div>
    </form>
@endsection

@push('scripts')
    <script>
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
                    payment_method: {
                        required: true
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
                    const amount = $('#amount').val();
                    const cash = $('#cash').val();
                    const online = $('#online').val();

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
                $('input[name="select_invoice[]"]:checked').each(function() {
                    let invoiceAmount = $(this).closest('.invoice-row').find('.invoice-total').val();
                    totalAmount += parseFloat(invoiceAmount) || 0;
                });
                $('#amount').val(totalAmount.toFixed(2));
            }

            // function updatePaymentFields() {
            //     let totalAmount = parseFloat($('#amount').val()) || 0;
            //     let cashAmount = parseFloat($('#cash').val()) || 0;
            //     let onlineAmount = parseFloat($('#online').val()) || 0;

            //     if (cashAmount >= 0 && cashAmount <= totalAmount) {
            //         $('#online').val((totalAmount - cashAmount).toFixed(2));
            //     }

            //     if (onlineAmount >= 0 && onlineAmount <= totalAmount) {
            //         $('#cash').val((totalAmount - onlineAmount).toFixed(2));
            //     }
            // }

            // $('#cash').on('input', function() {
            //     let cashAmount = parseFloat($(this).val()) || 0;
            //     let totalAmount = parseFloat($('#amount').val()) || 0;

            //     if (cashAmount >= 0 && cashAmount <= totalAmount) {
            //         $('#online').val((totalAmount - cashAmount).toFixed(2));
            //     } else {
            //         $('#online').val('');
            //     }
            // });

            // $('#online').on('input', function() {
            //     let onlineAmount = parseFloat($(this).val()) || 0;
            //     let totalAmount = parseFloat($('#amount').val()) || 0;

            //     if (onlineAmount >= 0 && onlineAmount <= totalAmount) {
            //         $('#cash').val((totalAmount - onlineAmount).toFixed(2));
            //     } else {
            //         $('#cash').val('');
            //     }
            // });


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
                                    '<div class="invoice-heading"><div>Select</div><div>Invoice Number</div><div>Total Amount</div></div>'
                                );

                                let totalAmount = 0;
                                response.invoices.forEach(function(invoice) {
                                    $('#invoice-details-container').append(
                                        `<div class="invoice-row">
                                    <input type="checkbox" name="select_invoice[]" value="${invoice.invoice_no}" class="invoice-checkbox" checked>
                                    <input type="text" name="invoice_no[]" value="${invoice.invoice_no}" readonly>
                                    <input type="number" name="total[]" value="${invoice.total}" class="form-control invoice-total" readonly>
                                </div>`
                                    );

                                    $('input[name="select_invoice[]"]').on('change',
                                        calculateTotal);
                                    $('input[name="total[]"]').on('input',
                                        calculateTotal);
                                });
                                calculateTotal();
                            } else {
                                $('#invoice-details-container').hide();
                                $('#amount').val('');
                            }
                        },
                        error: function() {
                            $('#invoice-details-container').hide();
                            $('#amount').val('');
                            alert("Error fetching invoice details.");
                        }
                    });
                } else {
                    $('#invoice-details-container').hide();
                    $('#amount').val('');
                }
            });

        });
    </script>
@endpush
