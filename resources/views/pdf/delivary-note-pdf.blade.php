<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">



    <title>INVOICE</title>
    <style>
        /* @page {
            size: A4;
            margin: 10mm;
        } */

        @page {
            margin: 5mm 8mm 15mm 8mm;
        }

        .item-container {
            page-break-inside: avoid;
            /* Prevents breaking this element across pages */
        }
        .bottomTbl{
            position: absolute;
            bottom: 50px;

            width: 100%;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            /*margin-bottom: 10px;*/
        }

        .logo {
            width: 150px;
            height: auto;
            padding-right: 3%;
        }

        .header-content {
            text-align: left;
        }

        .header-content h6 {
            margin: 0;
            font-size: 16px;
            color: #333;
            font-family: 'Roboto', sans-serif;
            padding-right: 30%;
            color: #000;
            font-weight: bold
        }

        .header-content p {
            margin: 5px 0;
            /*color: #666;*/
            font-size: 12px;
            padding-right: 30%;
        }

        hr {
            border: 1px solid #e2e2e2;
        }

        .invoice-summary {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-summary td {
            padding: 5px 10px;
            border: 1px solid #ddd;
        }

        .highlight {
            font-weight: bold;
            color: #333;
        }


        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }


        table.invoice-summary,
        table.invoice-summary th,
        table.invoice-summary td {
            border: none;
            padding: none;
            margin-bottom: none;
        }

        table.info-table,
        table.info-table th,
        table.info-table td {
            border: none;
            margin-bottom: none;
        }

        table.noborder,
        table.noborder th,
        table.noborder td {
            border: none;
            margin-bottom: none;
        }

        th,
        td {
            padding: 10px;
            text-align: center;
            font-size: 12px;
        }

        th {
            /*background-color: #f2f2f2;*/
            font-weight: bold;
            color: #333;
        }

        .invoice-header {
            background-color: #151313;
            font-weight: bold;
            color: #333;
        }

        .invoice-header th {
            background-color: #151313;
            color: #fff;
        }

        .line-footer {
            margin-top: 5px;
            text-align: center;
            font-size: 13px;
        }


        .summary table {
            width: 100%;
            border-collapse: collapse;
        }


        /* .footer {
            margin-top: 5px;
            text-align: start;
            font-size: 12px;
        } */

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            /* Adjust height as needed */
            text-align: center;
            font-size: 12px;
            line-height: 20px;
            /* Vertically center the text */
            border-top: 1px solid #ccc;
            /* Optional: A top border for the footer */
        }

        footer .pagenum:before {
            content: counter(page);
        }

        footer {

            position: fixed;

            bottom: -90px;

            left: 0px;

            right: 0px;

            height: 40px;

            font-size: 20px !important;

            background-color: #000;

            color: white;

            text-align: center;

            line-height: 35px;

        }

        .declaration p {
            margin: 10px;
            padding: 0;
            text-align: left;
            text-indent: 80px;

        }

        /*.declaration p:first-of-type {
            font-weight: bold;
            text-indent: 0;
        }*/


        .declaration p:first-of-type {
            font-weight: bold;
            text-indent: 0;
        }

        .para-content {
            text-align: left;
            font-size: 12px;
            color: #333;
            padding-right: 5%;
            margin: 1px;
            font-weight: 900;
        }

        /* .invoice-details {
            min-height: 300px;
        } */

        .footer-content {
            width: 100%;
        }

        .row.footer-cont {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .invoice-title {
            display: flex;
            align-items: center;
        }

        .title-text {
            font-size: 20px;
            font-weight: bold;
            color: #000;
        }

        .customer-name {
            margin-top: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 14px;
        }

        .content {
            margin-bottom: 40px;
            /* Leave space for the footer */
        }
    </style>
</head>

<body style="font-family: 'Roboto',sans-serif;">

    <div class="header">
        <table class="noborder" width="100%">
            <tr>
                <th width="20%"> <img src="{{ url($ware_house_data->store_logo) }}" alt="Company Logo"
                        style="max-width: 100%; max-height: 100%;"></th>
                <th width="50%">
                    <div class="header-content">
                        <h6>{{ $ware_house_data->company_name }}</h6>

                        @php
                            $address = wordwrap($ware_house_data->address, 35, "\n", true);
                        @endphp
                        <p>{!! nl2br(e($address)) !!}</p>
                        <p>NIF-NO:{{ $ware_house_data->nif_no }}</p>
                        <p>Mobile No: {{ $ware_house_data->contact_no ?? '' }}</p>
                        <p>Email : {{ $ware_house_data->email ?? '' }}</p>
                    </div>
                </th>
                <th width="20%">
                    <div class="para-content">
                        <p><strong style="text-decoration: underline">Para:</strong></p>
                        <div style="margin-top:3px; font-size:15px;">{{ $invoice->customer_id }}</div>
                        <div style="margin-top:3px; font-size:14px; font-weight: ;">{{ $custAddress->address ?? '' }}
                        </div>
                        <div style="margin-top:3px; font-size:14px; font-weight: ;">{{ $invoice->nif_no }}</div>
                        <div style="margin-top:3px; font-size:14px; font-weight: ;">{{ $invoice->ref_id }}</div>
                    </div>
                </th>
                <th width="10%" style="text-align: center; vertical-align: top;">
                    <div class="invoice-title">
                        <span class="title-text">NOTA DE ENTREGA</span>
                    </div>
                </th>
            </tr>
        </table>
    </div>


    <div class="footer item-container">
        
        <table>
            <tr>
                <td style="text-align: left;">
                    <p class="col-4 highlight-p">
                        <span class="highlight">Criado por:</span> {{ $userCode }}
                    </p>
                </td>
                <td>
                    <p class="col-4 highlight-p text-center">

                    </p>
                </td>
                <td>
                    <p class="col-4 highlight-p text-end">
                        <!-- Page numbers will be dynamically inserted here -->
                    </p>
                </td>
            </tr>
        </table>
    </div>






    <div style="margin-top: 3px; border: solid 1px #000; border-bottom: none; padding-top: 5px;">
        <table class="invoice-summary">
            <tr>
                <td style="width: 33%;text-align: left;"><span class="highlight">Nota de Entrega
                        Nº.:</span>{{ $invoice->invoice_no }}</td>
                <td style="width: 33%;text-align: center;"><span
                        class="highlight">Data:</span>{{ \Carbon\Carbon::parse($invoice->order_date)->format('d-m-Y') }}
                </td>

                <td style="width: 33%;text-align: right;"><span class="highlight">Ref:</span> {{ $invoice->memo }}</td>
            </tr>
        </table>
    </div>

    <div style="min-height: 0px;">
        <table>
            <thead>
                <tr class="invoice-header">
                    <th style="width: 10%;">Nº</th>
                    <th style="width: 10%;text-align: left;">Artigo</th>
                    <th style="width: 70%;text-align: left;">Descrição</th>
                    <th style="width: 10%;">Qtd.</th>

                </tr>
            </thead>

            <tbody class="item-container" style="margin-bottom: 20px;">
                @php
                    $totQty = 0;
                    $totGst = 0;
                    $totPrice = 0;
                    $totItemPrice = 0;
                    $totalDiscount = 0;
                    $sumTotalPrice = 0;
                    $taxTotalPrice = 0;
                    $totalQuantity = 0;

                @endphp

                @foreach ($items as $key => $item)
                    @php
                        $gst = $item->gst_rate;
                        $totQty += $item->quantity;
                        $totPrice += $item->price_af_disc;
                        $totalDiscount += $item->disc_value * $item->quantity;
                        $totItemPrice += $item->price;
                        $sumTotalPrice += $item->subtotal;
                        $taxTotalPrice += $item->price_total_af_disc;
                        $totalQuantity += $item->quantity;

                    @endphp

                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->item_no }}</td>
                        <td style="text-align: left">{{ $item->item_desc }}</td>
                        <td>{{ $item->quantity }}</td>

                    </tr>


                    <tr>
                        <td colspan="8" style="padding: 2px !important; border-bottom: 1px dotted #000;"></td>
                    </tr>
                @endforeach

            </tbody>


        </table>
        <div style="text-align:right;margin-right:30px;">
            <p style="font-size: 12px;">Total:{{ $totalQuantity }}</p>
        </div>
    </div>








    <div class="item-container bottomTbl">



        <div style="margin-top: 1px; border-top: solid 1px #000; padding-top: 1px;">
            <table class="noborder" style="width: 100%; border: none;">
                <tr>
                    <td style="padding:10px; width: 50%;">
                        <p style="padding-top: 0px;"> <strong>Selo e Assinatura do Cliente</strong></p>
                    </td>
                    <td style="border;">
                        <p style="padding-left:15px; padding-top: 0px;"> <strong>Assinatura Autorizada</strong></p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="declaration">

            <div><span style="text-decoration: underline ; font-size:bold; text-indent: 10px; margin-right:10px;">
                    Declaração:</span><span style="font-size:14px; text-indent: 10px;">Por favor, confirme a mercadoria
                    no momento da entrega.</span> </div>

        </div>

    </div>




    <script type="text/php">
    if ( isset($pdf) ) {
        $font = Font_Metrics::get_font("helvetica", "bold");
        $pdf->page_text(72, 18, "Header: {PAGE_NUM} of {PAGE_COUNT}", $font, 6, array(0,0,0));
    }
    </script>


</body>

</html>
