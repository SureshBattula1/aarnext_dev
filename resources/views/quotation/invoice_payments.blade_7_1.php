@extends('layouts.app')
<style>
    /* Global Styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
    }

    /*.container {
        width: 100%;
        max-width: 800px;
        margin: 40px auto;
        padding: 20px;
        background-color: #fff;
        border: 1px solid #ddd;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }*/

    /* Card Styles */
    .card {
        margin: 20px auto;
        padding: 20px;
        background-color: #fff;
        border: 1px solid #ddd;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    .card-header {
        background-color: #333;
        color: #fff;
        padding: 10px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }

    /*.card-body {
        padding: 20px;
    }*/

    /* Table Styles */
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #f0f0f0;
    }

    /* Invoice Details Styles */
    .first-tbl-td {
        padding: 30px;
        background-color: #ADD8E6;
        width: 100%;
    }

    .first-tbl-font {
        font-size: 18px;
        color: green;
        font-weight: bold;
        margin-right: 5px;
        margin-left: 30px;
    }

    /* Payment Styles */
    .payments {
        width: 100%;
    }

    .payment-completed {
        color: green;
        font-weight: bold;
        font-size: 20px;
        text-align: center;
        margin-top: 20px;
        display: flex;
        margin-right: 15px;

    }

    .payments-details {
        padding: 30px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        margin-bottom: 20px;
        /*display: flex;*/
        flex-direction: column;
    }

    .totalAmount {
        font-size: 20px;
        font-weight: bold;
        color: #333;
        text-decoration: underline;
        margin-bottom: 10px;
        display: flex;
        margin-right: 15px;
        padding: 30px;
    }

    .paidAmt {
        padding-left: 10px;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 10px;
    }

    input[type="number"],
    select {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
    }

    button[type="submit"] {
        background-color: #4CAF50;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    button[type="submit"]:hover {
        background-color: #3e8e41;
    }
    /*.table.table-striped.field{
        display:block;
        overflow-x: scroll;
     }*/

     .order-summary {
        margin-top: 20px;
        padding: 10px;
        border: 1px solid #ddd;
        background-color: #f9f9f9;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .summary-table {
        width: 100%;
        border-collapse: collapse;
    }

    .summary-table td {
        padding: 8px 10px;
        font-size: 14px;
    }

    .summary-table tr td:first-child {
        font-weight: bold;
    }

    .summary-table tr:nth-child(even) {
        background-color: #f0f0f0;
    }


    .summaryTbl {
        margin-left: 30px;
        margin-top: 20px;
        text-align: right;
        justify-self: right;
    }
    .invoice-summary {
        display: flex;
        justify-content: space-around;
        align-items: center;
        border: 1px solid #ddd;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 5px;
        margin-top: 20px;
    }

    .invoice-column {
        display: flex;
        flex-direction: column;
        text-align: center;
        margin: 0 10px;
    }

    .label {
        font-size: 16px;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }

    .value {
        font-size: 18px;
        color: #007bff;
    }
</style>
@section('content')

    <div class="container">
        @php
            $invoiceNo = request()->get('invoice_no');
        @endphp

        <div class="card">

            <div class="card-header">
                <h3 class="d-block p-2 bg-primary text-white">INVOICE DETAILS -  {{$invoice->invoice_no}} </h3>
            </div>
            <div id="payment-success-message" class="alert alert-success" style="display:none;"></div>


            <div class="card-body">

                <table class="first-tbl-td"><strong class="first-tbl-font">
                        <thead>
                            <tr>
                                <th>Name:</th>
                                <th>Invoice Date</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $customer->card_name ?? 'N/A' }}</td>
                                <td>{{ $invoice->order_date ?? '----' }}</td>
                                <td>{{ $invoice->total ?? '00' }}</td>
                            </tr>
                        </tbody>

                </table>

                <!-- INVOICE ITEMS -->
                <div class="card">
                    <div class="container">
                        <h4 class="header-title">ITEMS</h4>
                        <table class="table table-striped field">
                            <thead>
                                <tr>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Batch Details</th>
                                    <th>Desc %</th>
                                    <th>Tax %</th>
                                    <th>Sub Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($invoiceItems) && $invoiceItems->count())
                                    @foreach ($invoiceItems as $item)
                                        <tr>
                                            <td>{{ $item->item_no }}</td>
                                            <td>{{ $item->item_desc }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ $item->price }}</td>
                                            <!--<td>{{ $item->batch_number }}</td>

                                            <td>{{ $item->batch_qty }}</td>-->
                                            <td>
                                                <button type="button" onclick="getBatches('{{ $item->invoice_id }}' , '{{ $item->item_no }}')">View Batch Details</button>
                                            </td>
                                            <td>{{$item->disc_percent}}</td>
                                            <td>{{$item->gst_rate}}</td>
                                            <td>{{$item->subtotal}}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3">No items found for this invoice.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal fade" id="update_user" tabindex="-1" role="dialog" aria-labelledby="updateUserModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updateUserModalLabel">Batch Details</h5>
                                <button type="button" class="close" data-bs-dismiss="modal"  aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Batch details will be dynamically injected here -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $totalAmount = 0;
                    $totalDiscount = 0;
                    $totalPriceAfterDiscount = 0;
                    $totalSubtotal = 0;
                    $totTax = 0;

                @endphp
                @if (isset($invoiceItems) && $invoiceItems->count())
                    @foreach ($invoiceItems as $item)
                        @php
                            // $totalAmount += $item->price * $item->quantity;
                            $totalAmount += $item->price * $item->quantity - $item->quantity * $item->disc_value;
                            $totalDiscount += $item->quantity * $item->disc_value;
                            $totalPriceAfterDiscount += $item->price_total_af_disc;
                            $totalSubtotal += $item->subtotal;
                            $totTax += $item->gst_value * $item->quantity;
                        @endphp
                    @endforeach
                @endif

                <div class="summaryTbl">
                    <!-- Summary Section -->
                    <div class="order-summary" style="width: 325px;text-align: center; margin-right:40px;">
                        <h5 style="color:#000000">Order Summary</h5>
                        <table class="summary-table">
                            <tr>
                                <td>Taxble Amount:</td>
                                <td>{{ number_format($totalAmount, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Total Discount:</td>
                                <td>{{ number_format($totalDiscount, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Total Tax:</td>
                                <td>{{ number_format($totTax, 2) }}</td>
                                {{-- <td>{{ number_format($totalPriceAfterDiscount, 2) }}</td> --}}
                            </tr>
                            <tr>
                                <td>Documenet Total:</td>
                                <td>{{ number_format($totalSubtotal, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>


                <div class="card">
                    <div class="container">
                        <h4 class="header-title">PAYMENTS</h4>

                        <table class="table table-striped">
                            <tr>
                                <td>
                                    <table class="payments-details">
                                        <thead>
                                            <tr>
                                                <th>Payment Date</th>
                                                <th>Amount</th>
                                                <th>Payment Method</th>
                                            </tr>
                                        </thead>
                                        <tbody id="payments-table-body">
                                            @if (isset($payments) && $payments->count())
                                                @foreach ($payments as $payment)
                                                    <tr>
                                                        <td>{{ $payment->created_at }}</td>
                                                        <td>{{ $payment->amount }}</td>
                                                        <td class="text-uppercase">{{ $payment->payment_method }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="3">No payments found for this invoice.</td>
                                                </tr>
                                            @endif
                                        </tbody>

                                    </table>
                                    {{-- <div class="totalAmount">
                                        <strong class="first-tbl-font"> Amount: </strong> {{ $invoice->total ?? '0' }}
                                        <div class="paidAmt"> <strong class="first-tbl-font"> Paid Amount: </strong>
                                            {{ $invoice->paid_amount ?? '00' }}</div>
                                            if( $invoice->paid_amount != 0){
                                                 $dueAmount = $invoice->total - $invoice->paid_amount;
                                                 <div >
                                                    <strong class="first-tbl-font"> Remailning Amount: </strong> {{ $dueAmount ?? '0' }}
                                                 </div>
                                            }
                                    </div> --}}
                                    <!--<div class="totalAmount">
                                        <strong class="first-tbl-font">TOTAL:</strong> {{ $invoice->total ?? '0' }}
                                        <div class="paidAmt">
                                            <strong class="first-tbl-font">PAID :</strong> {{ $invoice->paid_amount ?? '0' }}
                                        </div>
                                        @if ($invoice->paid_amount > 0)
                                            <div>
                                                <strong class="first-tbl-font">BALANCE :</strong>
                                                {{ $invoice->total - $invoice->paid_amount ?? '0' }}
                                            </div>
                                        @endif
                                    </div>-->
                                    <div class="invoice-summary">
                                        <div class="invoice-column">
                                            <strong class="label">TOTAL:</strong>
                                            <span class="value">{{ $invoice->total ?? '0' }}</span>
                                        </div>
                                        <div class="invoice-column">
                                            <strong class="label">PAID:</strong>
                                            <span class="value">{{ $invoice->paid_amount ?? '0' }}</span>
                                        </div>
                                        @if ($invoice->paid_amount > 0)
                                            <div class="invoice-column">
                                                <strong class="label">BALANCE:</strong>
                                                <span
                                                    class="value">{{ $invoice->total - $invoice->paid_amount ?? '0' }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <table>
                                    @if ($showPayments)
                                        @if ($invoice->total > $invoice->paid_amount)
                                            <form id="payment-form" action="{{ route('invoice-payments-store') }}"
                                                method="POST">
                                                @csrf
                                                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                                                <input type="hidden" name="customer_id" value="{{ $invoice->customer_id }}">
                                                <div class="form-group">
                                                    <label for="payment_method">Payment Method:</label>
                                                    <select class="form-control" id="payment_method" name="payment_method"
                                                        required>
                                                        <option value="cash">CASH</option>
                                                        <option value="bank">BANK</option>
                                                        <option value="tpa">TPA </option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="payment_type">Payment Type:</label>
                                                    <select class="form-control" id="payment_type" name="payment_type"
                                                        required>
                                                        <option id="1" value="partial">Partial Payment</option>
                                                        <option id="2" value="full">Full Payment</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="amount">Amounts:</label>
                                                    <input type="number" class="form-control" id="amount" name="amount"
                                                        placeholder="Enter payment amount" required>
                                                </div>

                                                <button type="submit" class="btn btn-primary">Submit Payment</button>
                                            </form>
                                        @else
                                            <div class="payment-completed">
                                                Full Payment is Completed
                                            </div>
                                        @endif
                                        @else
                                            <div class="payment-restricted">
                                                You do not have permission to make payments.
                                            </div>
                                        @endif
                                    </table>


                                </td>
                            </tr>

                        </table>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var totalAmount = {{ $invoice->total }};
            var paidAmount = {{ $invoice->paid_amount ?? 0 }};
            var remainingAmount = totalAmount - paidAmount;
            $('#payment_type').change(function() {
                if ($(this).val() === 'full') {
                    var remainingAmount = totalAmount - paidAmount;
                    $('#amount').val(remainingAmount);
                    $('#amount').prop('readonly', true);
                } else {
                    $('#amount').val('');
                    $('#amount').prop('readonly', false);
                }
            });

            $('#payment-form').on('submit', function(e) {
                e.preventDefault();

                var enteredAmount = parseFloat($('#amount').val());
                var paymentType = $('#payment_type').val();

                // if (paymentType === 'partial') {
                //     if (enteredAmount < totalAmount) {
                //         alert('Partial payment must be greater than or equal to the total amount (' +
                //             totalAmount + ').');
                //         return;
                //     }
                // }

                if (enteredAmount <= 0) {
                    alert(' Please enter a valid payment amount greater than zero.');
                    return;
                }

                if (confirm('Are you sure you want to submit the payment?')) {
                    $.ajax({
                        url: "{{ route('invoice-payments-store') }}",
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            //alert('Payment successfully submitted.' );

                            if (response.success) {

                                var paymentRow = '<tr>' +
                                    '<td>' + response.payment.created_at + '</td>' +
                                    '<td>' + response.payment.amount + '</td>' +
                                    '<td>' + response.payment.payment_method + '</td>' +
                                    '</tr>';
                                $('#payments-table-body').append(paymentRow);

                                $('#payment-form')[0].reset();
                            }

                            alert('Payment successfully submitted.')
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        },
                        error: function(xhr) {
                            alert('Error: ' + xhr.responseText);
                        }
                    });
                }
            });
        });

        function getBatches(invoiceId, itemCode) {
            $.ajax({
                url: public_path + '/get-inv-batches-list/' + invoiceId + '/' + itemCode,
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.length > 0) {
                        var tableContent = `
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Batch Number</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

                        response.forEach(function (item) {
                            tableContent += `
                                <tr>
                                    <td>${item.batch_num}</td>
                                    <td>${item.quantity}</td>
                                </tr>
                            `;
                        });
                        tableContent += '</tbody></table>';

                        $('.modal-body').html(tableContent);
                    } else {
                        $('.modal-body').html('<p class="text-danger">Error: ' + (result.message || 'No data available') + '</p>');
                    }
                },
                error: function (error) {
                    console.error("Error fetching item details:", error);
                    $('.modal-body').html('<p class="text-danger">An unexpected error occurred.</p>');
                }
            });

            // Set modal title
            $('.modal-title').html('BATCH DETAILS LIST - ' + itemCode);

            // Show the modal
            $('#update_user').modal('show');
        }


</script>


@endpush
