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

    .EditBtn{
        margin-top: 15px;
    }

</style>
@section('content')

<div class="container">
    @php
    $invoiceNo = request()->get('sales_invoice_no');
    $userId = auth::user()->role;
    @endphp

    <div class="card"> 

        <div class="card-header">
            <h3 class="d-block p-2 bg-primary text-white">SALES ORDER DETAILS - {{$order->sales_invoice_no}}</h3>
        </div>
        <div id="payment-success-message" class="alert alert-success" style="display:none;"></div>

        <div class="card-body">

            <table class="first-tbl-td"><strong class="first-tbl-font">
                    <thead>
                        <tr>
                            <th>Name:</th>
                            <th>Order Date</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $customer->card_name ?? 'N/A' }}</td>
                            <td>{{ $order->order_date ?? '----' }}</td>
                            <td>{{ number_format($order->total, 2) }}</td>
                        </tr>
                    </tbody>
            </table>

            @if($userId === 5)
            <div class="d-flex justify-content-end mb-2">
                <button class="nav-link btn btn-warning btn-link add-new-btn animated-border EditBtn"
                    onclick="location.href='{{ url('sales-order/edit/' . $order->id) }}'">
                    Edit
                </button>
            </div>
            @endif


            <!-- Order Items -->
            <div class="card">
                <div class="container">
                    <h4 class="header-title">TOTAL ORDER ITEMS</h4>
                    <table class="order-items-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Item Name</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Disc %</th>
                                {{-- <th>Price Total After Discount</th> --}}
                                <th>Tax %</th>
                                <th>Sub Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $totalAmount = 0;
                            $totalDiscount = 0;
                            $totalPriceAfterDiscount = 0;
                            $totalSubtotal = 0;
                            $totTax = 0;
                            $userRole = auth()->user()->role;

                            @endphp

                            @if (isset($orderItems) && $orderItems->count())
                            @foreach ($orderItems as $item)
                            @php
                            $totalAmount += $item->price * $item->quantity - $item->quantity * $item->disc_value;
                            // $totalAmount += $item->price * $item->quantity;
                            $totalDiscount += $item->disc_value * $item->quantity;
                            $totalPriceAfterDiscount += $item->price_total_af_disc;
                            $totalSubtotal += $item->subtotal;
                            $totTax += $item->gst_value * $item->quantity;
                            @endphp
                            <tr>
                                <td>{{ $item->item_no }}</td>
                                <td>{{ $item->item_desc }}</td>
                                <td>{{ number_format($item->price, 2) }}
                                    @if ($userRole == 1 || $userRole == 5 && $order->status == 0)
                                    <button type="button" class="btn btn-sm btn-primary" 
                                        onclick="openPriceModal({{ $item->id }} , {{$item->quantity }} , {{ $item->price }}, {{ $item->disc_percent }})">
                                        Update Price
                                    </button>                                    
                                    @endif
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->disc_percent, 2) }}</td>

                                {{-- <td>{{ number_format($item->disc_value * $item->quantity, 2) }}</td> --}}
                                {{-- <td>{{ number_format($item->price_total_af_disc, 2) }}</td> --}}
                                <td>{{ number_format($item->gst_rate, 2) }}</td>
                                <td>{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="8">No items found for this invoice.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                    <div class="modal fade" id="priceModal" tabindex="-1" aria-labelledby="priceModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="priceModalLabel">Update Price & Discount</h5>
                                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="priceItemId">

                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="newQuantity">Enter New Quantity:</label>
                                            <input type="number" id="newQuantity" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="newPrice">Enter New Price:</label>
                                            <input type="number" id="newPrice" class="form-control" step="0.01">
                                        </div>
                                        <div class="col-md-4">
                                        <label for="newDiscount">Enter New Discount (%):</label>
                                        <input type="number" id="newDiscount" class="form-control" step="0.01" max="20" oninput="validateDiscount(this)">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-success" onclick="savePrice()">Save Price</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <!-- Tax Code Section -->
                        <div class="tax-code-section" style="width: 40%;margin-top: 10px; text-align: left; border: 1px solid #ddd; padding: 20px; border-radius: 8px; background-color: #f9f9f9;">
                            <h5 style="margin-bottom: 20px; font-weight: bold; font-size: 16px; text-align:center;">Tax Details</h5>
                            <table class="tax-table" style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="text-align: left; padding: 10px; border-bottom: 1px solid #ddd; font-size: 14px;">Tax Code</th>
                                        <th style="text-align: left; padding: 10px; border-bottom: 1px solid #ddd; font-size: 14px;">Incidence Value</th>
                                        <th style="text-align: right; padding: 10px; border-bottom: 1px solid #ddd; font-size: 14px;">Total Tax</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalTax = 0;
                                    @endphp

                                    @foreach ($gstSummary as $rate => $summary)
                                        @php
                                            $tax = $rate / 100 * $summary['subTotal'];
                                            $totalTax += $tax;
                                        @endphp
                                        <tr>
                                            <td style="padding: 10px; font-size: 14px;">IVA {{ $rate }}%</td>
                                            <td style="padding: 10px; font-size: 14px;">{{ number_format($summary['subTotal'], 2) }}</td>
                                            <td style="text-align: right; padding: 10px; font-size: 14px;">{{ number_format($tax, 2) }}</td>
                                        </tr>
                                    @endforeach

                                    <tr>
                                        <td></td>
                                        <td colspan="1" style="padding: 10px; font-weight: bold; font-size: 14px;">Total Tax</td>
                                        <td style="text-align: right; padding: 10px; font-weight: bold; font-size: 14px;">{{ number_format($totalTax, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>


                        <!-- Order Summary Section -->
                        <div class="order-summary" style="width: 40%; text-align: center; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                            <h5 style="margin-bottom: 15px; font-weight: bold;">Order Summary</h5>
                            <table class="summary-table" style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="text-align: left; padding: 5px;">Taxable Amount:</td>
                                    <td style="text-align: right; padding: 5px;">{{ number_format($totalPriceAfterDiscount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding: 5px;">Total Discount:</td>
                                    <td style="text-align: right; padding: 5px;">{{ number_format($totalDiscount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding: 5px;">Total Tax:</td>
                                    <td style="text-align: right; padding: 5px;">{{ number_format($totalTax, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="text-align: left; padding: 5px;">Document Total:</td>
                                    <td style="text-align: right; padding: 5px;">{{ number_format($totalSubtotal, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>


                    {{-- <div class="summaryTbl">
                        <!-- Summary Section -->
                        <div class="order-summary" style="width: 325px;text-align: center;">
                            <h5>Order Summary</h5>
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
                                </tr>
                                <tr>
                                    <td>Documenet Total:</td>
                                    <td>{{ number_format($totalSubtotal, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div> --}}

                </div>
            </div>





            <!--<div class="card">
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
                                        <strong class="first-tbl-font"> Amount: </strong> {{ $order->total ?? '0' }}
                                        <div class="paidAmt"> <strong class="first-tbl-font"> Paid Amount: </strong>
                                            {{ $order->paid_amount ?? '00' }}</div>
                                            if( $order->paid_amount != 0){
                                                 $dueAmount = $order->total - $order->paid_amount;
                                                 <div >
                                                    <strong class="first-tbl-font"> Remailning Amount: </strong> {{ $dueAmount ?? '0' }}
                                                 </div>
                                            }
                                    </div> --}}
                                    <div class="totalAmount">
                                        <strong class="first-tbl-font">TOTAL:</strong> {{ $order->total ?? '0' }}
                                        <div class="paidAmt">
                                            <strong class="first-tbl-font">PAID :</strong> {{ $order->paid_amount ?? '0' }}
                                        </div>
                                        @if ($order->paid_amount > 0)
                                            <div>
                                                <strong class="first-tbl-font">BALANCE :</strong>
                                                {{ $order->total - $order->paid_amount ?? '0' }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <table>
                                        @if ($order->total > $order->paid_amount)
                                            <form id="payment-form" action="{{ route('order-payments') }}"
                                                method="POST">
                                                @csrf
                                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                                <div class="form-group">
                                                    <label for="payment_method">Payment Method:</label>
                                                    <select class="form-control" id="payment_method" name="payment_method"
                                                        required>
                                                        <option value="cash">CASH</option>
                                                        <option value="bank">BANK</option>
                                                        <option value="pos">POS</option>
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
                                    </table>


                                </td>
                            </tr>

                        </table>


                    </div>
                </div>-->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function validateDiscount(input) {
        if (input.value > 100) {
            input.value = 100;
        }
    }
    function openPriceModal(itemId, quantity , currentPrice , currentDiscount) {
        document.getElementById("priceItemId").value = itemId; 
        document.getElementById("newQuantity").value = quantity;
        document.getElementById("newPrice").value = currentPrice;
        document.getElementById("newDiscount").value = currentDiscount; 


        var priceModal = new bootstrap.Modal(document.getElementById("priceModal"));
        priceModal.show();
    }

    function savePrice() {
        var itemId = document.getElementById("priceItemId").value;
        var newQuantity = document.getElementById("newQuantity").value;
        var newPrice = document.getElementById("newPrice").value;
        var newDiscount = document.getElementById("newDiscount").value;


        if (newPrice === "" || newPrice <= 0) {
            alert("Please enter a valid price.");
            return;
        }

        fetch("{{ url('update-order-price') }}/" + itemId, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}" 
            },
            //body: JSON.stringify({ price: newPrice })
            body: JSON.stringify({ price: newPrice, quantity: newQuantity, discount: newDiscount })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Price updated successfully!");
                location.reload(); 
            } else {
                alert("Failed to update price.");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred. Please try again.");
        });
    }
</script>

<script>
    $(document).ready(function() {
        var totalAmount = {{ $order->total ?? 0 }};
        var paidAmount  = {{ $order->paid_amount  ?? 0 }};
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

            if (confirm('Are you sure you want to submit the payment?')) {
                $.ajax({
                    url: "{{ route('order-payments') }}"
                    , method: 'POST'
                    , data: $(this).serialize()
                    , success: function(response) {
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

                    }
                    , error: function(xhr) {
                        alert('Error: ' + xhr.responseText);
                    }
                });
            }
        });
    });
</script>

@endpush
