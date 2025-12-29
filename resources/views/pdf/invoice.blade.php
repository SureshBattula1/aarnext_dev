<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sales Invoice</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            padding: 10px;
            margin: 0;
            background-color: #f6f6f6;
            font-size: 12px;
        }

        body,
        p,
        table {
            font-size: 12px;
            letter-spacing: 0;
        }

        p {
            padding: 1px;
            margin: 0;
        }

        table.border {
            border-collapse: collapse;
            width: 100%;
            border:
                1px solid black;
                background-color: #fff;
        }

        table.border th,
        table.border td {
            border: 1px solid black;
            padding: 0px;
            text-align: right;
            
        }

        table.noborder {
            border-collapse: collapse;
            width: 100%;
            border: 0;
        }

        table.noborder th,
        table.noborder td {
            border: 0;
            padding: 0px;
            text-align: left;
            
        }

        table.paymentsTable {
            border-collapse: collapse;
            /* border: 1px solid #131313; */
            width: 100%;
           
        }

        table.paymentsTable th,
        table.paymentsTable td {
            border-bottom: 1px solid #ccc;
            padding: 2px;
            text-align: center;
          
        }

        table.productborder {
            border-collapse: collapse;
            border: 1px solid #131313;
            width: 100%;
        }

        table.productborder th,
        table.productborder td {
            border: 1px solid #131313;
            padding: 2px;
            text-align: right;
        }

        .productlist {
            vertical-align: top;
            height:
                100px;
            background-color: #a59e9e;
            color: rgb(255, 255, 255)
        }

        table.sideborder {
            width: 100%;
            padding: 10px 0;
            background-color: #e4e2e2;
            color: rgb(0, 0, 0)
        }

        table.sideborder th {
            border: solid 0px #ccc;
        }

        .padding-right {
            padding-right: 10px;
        }

        table.padding-4 tr td,
        table.padding-4 tr th {
            padding: 4px;
        }

        table.padding-2 tr td,
        table.padding-2 tr th {
            padding: 4px;
        }
    </style>
</head>

<body>
    <table class="border padding-4">
        <tr>
            <td>
                <table class="noborder"  style="background-color: #b2b2b2;">

                    <tr>
                        <td width="10%">
                        </td>
                        <td width="80%" style="text-align: center">
                            <p style="text-align: center; font-weight:bold">TAX INVOICE</p>
                        </td>
                        <td width="10%">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                {{--<table class="noborder">
                    <tr>
                        <td width="10%">
                            <img src="{{ asset('images/arrnext-logo.png') }}"  style="max-width: 80px; height: auto;" alt="aarnext">
                        </td>
                        <td width="80%" style="text-align: center">
                            <p><strong>AARNEXT</strong></p>
                            <p>GST NO: 1234567890</p>
                            <p> Bowenpally, Secunderabad, Telangana 500011,
                            </p>
                            <p>CONT: 040-29330105</p>
                        </td>
                       
                        <td width="10%">
                        </td>
                    </tr>
                </table>--}}
                <table class="noborder" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 20%; text-align: left; vertical-align: top;">
                            <img src="{{ asset('images/arrnext-logo.png') }}" style="width: 120px; height: auto;" alt="aarnext">
                        </td>
                        <td style="width: 60%; text-align: center;">
                            <p style="margin: 0; font-size: 18px; font-weight: bold;">AARNEXT</p>
                            <p style="margin: 0;">GST NO: 1234567890</p>
                            <p style="margin: 0;">Bowenpally, Secunderabad, Telangana 500011</p>
                            <p style="margin: 0;">CONT: 040-29330105</p>
                        </td>
                        <td style="width: 20%;"></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table class="noborder">
                    <tr>
                        <td>Invoice No: INV000{{ $order->id }} </td>
                        <td>Name: {{ $order->contact_name }}</td>
                    </tr>
                    <tr>
                        <td>Invoice Date: {{ $order->order_date }}</td>
                        <td>Ph No: {{ $order->contact_no }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="productborder padding-4" >
        <tr style="background-color: #d2d0d0;">
             <th width="5%" >Sr.</th>
             <th width="31%">Item Name.</th>
             <th width="5%">Item Code.</th>
             <th width="5%">Qty.</th>
             <th width="8%">Price</th>
            @if (
                $items->contains(function ($item) {
                    return $item->disc_value > 0;
                }))
                 <th width="7%" style="text-align: center">Discount</th>
            @endif
             <th width="15%">Taxable Value</th>
             <th width="10%">GST %</th>
             <th width="15%">Total</th>
        </tr>
        @php
            $totQty = 0;
            $totGst = 0;
            $totPrice = 0;
            $totItemPrice = 0;
            $totalDiscount = 0;
            $sumTotalPrice = 0;
        @endphp
        @foreach ($items as $item)
            @php
                $gst = $item->gst_rate;
                $totQty += $item->quantity;
                $totPrice += $item->price_af_disc;
                $totalDiscount += $item->disc_value * $item->quantity;
                $totItemPrice += $item->price;
                $sumTotalPrice += $item->price_total_af_disc + $item->gst_value * $item->quantity;
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->item_desc }}</td>
                <td>{{ $item->item_no }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
                @if ($order->discount_status > 0)
                    <td style="text-align: center">
                        {{ $item->disc_value > 0 ? number_format($item->disc_value, 2) : 0 }}
                    </td>
                @endif

                <td>{{ number_format($item->price_af_disc, 2) }}</td>
                <td>{{ number_format($item->gst_rate, 2) }}</td>
                <td>{{ number_format($item->price_total_af_disc + $item->gst_value * $item->quantity, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <table class="noborder">
        <tr>
            <td>
                <table class="noborder">
                    <tr>
                        <td>
                            <table class="sideborder">
                                <tr>
                                    <td width="5%" style="text-align: right">Total</td>
                                    <td width="36%" style="text-align: right"></td>
                                    <td width="13%" style="text-align: right">{{ number_format($totQty) }}</td>
                                    <td width="10%" style="text-align: right">{{ number_format($totItemPrice, 2) }}</td>
                                    @if($totalDiscount > 0)
                                    <td width="7%" style="text-align: right">{{ number_format($totalDiscount, 2) }}</td>
                                    @endif
                                    <td width="15%"  style="text-align: right">{{ number_format($totPrice, 2) }}</td>
                                    <td width="8%"  style="text-align: right"></td>
                                    <td width="15%" style="text-align: right">{{ number_format($sumTotalPrice , 2) }}</td>

                                </tr>
                            </table>
                        </td>
                    </tr>
            </td>
        </tr>
        <tr>
            <td style="border-top:solid 1px #131313; border-left:solid 1px #131313; border-bottom:solid 1px #131313">
                <table class="noborder">
                    <tr>
                        <td width="50%" style="vertical-align:top;">
                        </td>
                        <td width="50%" style="text-align: right">
                            <table class="noborder">
                                <tr>
                                    <td
                                        style="padding: 10px; height:20px;  border-left:solid 1px #131313;border-right:solid 1px #131313; text-align:right;  border-bottom:solid 1px #131313">
                                        <strong style="float: left !important; margin-left:50%">Total
                                            Amount:</strong>
                                        {{ number_format($sumTotalPrice , 2) }}
                                        <br>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td
                style="padding:10px 10px 0 10px; border-left:solid 1px #131313;border-right:solid 1px #131313;border-bottom:solid 1px #131313;">
                <table class="noborder">
                    <tr>
                        <td width="50%" style="vertical-align:top;">
                            {{-- <p>Remarks: test</p> --}}
                        </td>
                        <td width="50%" style="text-align: right">
                            {{-- <p><b>Paid Amount:</b> {{ number_format($totPrice + $totGst, 2) }}</p> --}}
                            <br />
                            <p>For the AARNEXT Sign </p>
                            <br /><br /><br /><br />
                            Authorised sign
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>

</html>
