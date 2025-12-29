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

</style>
@section('content')

<div class="container">
    @php
    $invoiceNo = request()->get('quotation_pdf_no');
    @endphp

    <div class="card">

        <div class="card-header">
            <h3 class="d-block p-2 bg-primary text-white">QUOTATION ORDER DETAILS - {{$order->quotation_pdf_no}}</h3>
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
                            <td>{{ $order->date ?? '----' }}</td>
                            <!--<td>{{ $order->total ?? '00' }}</td>-->
                            <td>{{ number_format($order->total, 2) }}</td>
                        </tr>
                    </tbody>

            </table>

            <!-- Order Items -->
            <div class="card">
                <div class="container">
                    <h4 class="header-title">TOTAL QUOTATION ITEMS</h4>
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
                            @endphp

                            @if (isset($orderItems) && $orderItems->count())
                            @foreach ($orderItems as $item)
                            @php
                            $totalAmount += $item->price * $item->quantity - $item->quantity * $item->disc_value;
                            $totalDiscount += $item->quantity * $item->disc_value;
                            $totalPriceAfterDiscount += $item->price_total_af_disc;
                            $totalSubtotal += $item->subtotal;
                            $totTax += $item->gst_value * $item->quantity;
                            @endphp
                            <tr>
                                <td>{{ $item->item_no }}</td>
                                <td>{{ $item->item_desc }}</td>
                                <td>{{ number_format($item->price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                {{-- <td>{{ number_format($item->quantity * $item->disc_value, 2) }}</td> --}}
                                <td>{{ number_format($item->disc_percent, 2) }}</td>
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



                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var totalAmount = {
            {
                $order - > total
            }
        };
        var paidAmount = {
            {
                $order - > paid_amount ? ? 0
            }
        };
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
