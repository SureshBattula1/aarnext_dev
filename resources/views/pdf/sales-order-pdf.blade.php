
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SALES ORDER</title>
    <style>
        /* @page {
            size: A4;
            margin: 10mm;
        } */

        @page { margin:  5mm 8mm 15mm 8mm; }

        .item-container {
                page-break-inside: avoid; /* Prevents breaking this element across pages */
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
            height: 30px; /* Adjust height as needed */
            text-align: center;
            font-size: 12px;
            line-height: 20px; /* Vertically center the text */
            border-top: 1px solid #ccc; /* Optional: A top border for the footer */
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
            margin-bottom: 40px; /* Leave space for the footer */
        }


    </style>
</head>
<body style="font-family: 'Roboto',sans-serif;">

        <div class="header">
            <table class="noborder" width="100%">
                <tr>
                    <th width="20%"> <img src="{{ url($ware_house_data->store_logo) }}" alt="Company Logo" style="max-width: 100%; max-height: 100%;"></th>
                    <th width="50%">

                        <div class="header-content">
                            <h6>{{ $ware_house_data->company_name }}</h6>

                            @php
                                $address = wordwrap($ware_house_data->address, 35, "\n", true);
                            @endphp
                            <p>{!! nl2br(e($address)) !!}</p>
                            <p>NIF:{{ $ware_house_data->nif_no ?? ''}}</p>
                            <p>Mobile No: {{ $ware_house_data->contact_no ?? '' }}</p>
                            <p>Email : {{ $ware_house_data->email  ?? '' }}</p>
                        </div>
                    </th>
                    <th width="20%">
                        <div class="para-content">
                            <p><strong style="text-decoration: underline">Para:</strong></p>
                            <div style="margin-top:3px; font-size:15px;">{{$order->customer_id}}</div>
                            <div style="margin-top:3px; font-size:14px; font-weight: ;">{{$custAddress->address ?? ''}}</div>
                            <div style="margin-top:3px; font-size:14px; font-weight: ;">{{$order->nif_no }}</div>
                            <div style="margin-top:3px; font-size:14px; font-weight: ;">{{$order->sales_ref_no }}</div>
                        </div>
                    </th>
                    <th width="10%" style="text-align: center; vertical-align: top;">
                        <div class="invoice-title">
                            <span class="title-text">FACTURA PROFORMA</span>
                        </div>

                        <div class="barcode" style="margin-top: 3px;">
                            <img src="{{ $barcodeBase64 }}" alt="Barcode" style="max-width: 75px; height: auto;">
                        </div>
                    </th>
                </tr>
            </table>
        </div>


   <div class="footer item-container">
    <div class="col-sm-12" style="font-family: 'Roboto',sans-serif;text-align:left !important">
        TeB4-Processado por programa validado n.º 96/AGT/2019 | SAP Business One 10.0
    </div>
    <table>
        <tr>
            <td>
                <p class="col-4 highlight-p text-start">
                    <span class="highlight">Criado por:</span> {{ $warehouseCode }}
                </p>
            </td>
            <td>
                <p class="col-4 highlight-p text-center">
                    <span class="highlight">Factura Nº:</span>{{ $order->sales_invoice_no}}
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




    <div style="margin-top: 3px;" class="customer-name">
        <span><strong>Customer Name:</strong></span> {{ $order->customer_name }}
    </div>

    <div style="margin-top: 3px; border: solid 1px #000; border-bottom: none; padding-top: 5px;">
        <table class="invoice-summary">
            <tr>
                <td><span class="highlight">Factura Nº:</span>{{ $order->sales_invoice_no}}</td>
                <td><span class="highlight">Data:</span>{{ \Carbon\Carbon::parse($order->order_date)->format('d-m-Y') }}</td>
                {{-- <td><span class="highlight">Data de Vencimento:</span>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}</td> --}}
                {{-- <td><span class="highlight">Tipo:</span> {{ $order->sales_type }}</td> --}}
                <td style="font-family: 'Roboto',sans-serif;"><span class="highlight">Tipo:</span>
                    @php
                        $salesTypeMap = [
                            'cash' => 'VD',
                            'credit' => 'VC',
                            'hospital' => 'VH',
                            'cash/credit' => 'VS',
                        ];
                    @endphp
                    {{ $salesTypeMap[$order->sales_type] ?? $order->sales_type }}
                </td>
                <td><span class="highlight">Ref:</span> {{ $order->memo }}</td>
            </tr>
        </table>
    </div>

    <div style="min-height: 0px;">
        <table>
            <thead>
                <tr class="invoice-header">
                    <th>Nº</th>
                    <th style="text-align: left;">Artigo</th>
                    <th style="text-align: left;">Descrição</th>
                    <th>Qtd.</th>
                    <th>Preço Unidade</th>
                    <th>Desc %</th>
                    <th>Taxa %</th>
                    <th>Total (AOA)</th>
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


                // $totalItems = count($items);
                // $itemsPerPage = 12; // Items per page
                // $totalPages = ceil($totalItems / $itemsPerPage); // Calculate total pages
                // $pageNumber = 1;
               // print_r($items); exit;
                @endphp


                @foreach($items as $key => $item)
                    @php
                        $gst = $item->gst_rate;
                        $totQty += $item->quantity;
                        $totPrice += $item->price_af_disc;
                        $totalDiscount += $item->disc_value * $item->quantity;
                        $totItemPrice += $item->price;
                        $sumTotalPrice += $item->subtotal;
                        $taxTotalPrice += $item->price_total_af_disc;
                    @endphp

                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->item_no }}</td>
                        <td style="text-align: left">{{ $item->item_desc }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price,  2) }}</td>
                        <td>{{ number_format($item->disc_percent,  2) }}</td>
                        <td>{{ number_format($item->gst_rate,  2) }}</td>
                        <td>{{ number_format($item->subtotal,  2) }}</td>
                    </tr>

                    {{-- @if(!empty($item->batches))
                        @foreach ($item->batches as $batch)
                            <tr style="padding: 1px !important;">
                                <td colspan="1" style="padding: 2px !important;"></td>
                                <td colspan="1" style="text-align: left; padding: 2px !important;">
                                    <span><strong>Qtd. </strong><span style="margin-left: 20px;">{{$batch['quantity']}}</span></span>
                                </td>
                                <td colspan="2" style="text-align: left; padding: 2px !important;">
                                    <span><strong>Data de Validade </strong><span style="margin-left: 20px;">{{ \Carbon\Carbon::parse($batch['expiry_date'])->format('d-m-Y') }}</span></span>
                                </td>
                            </tr>
                        @endforeach
                    @endif --}}
                    <tr>
                        <td colspan="8" style="padding: 2px !important; border-bottom: 1px dotted #000;"></td>
                    </tr>

                @endforeach


            </tbody>


        </table>
    </div>




    <div class="item-container" style="margin-top: 1px; border-top: solid 1px #000; padding-top: 1px;">
        <table>
            <tr>
                <td>
                    <table class="info-table" style="width: 100%; ">
                        <thead>
                            <tr>
                                <th>Código de Imposto</th>
                                <th>Valor Incidência</th>
                                <th style="text-align: right">Total Imposto</th>
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
                                <td colspan="8" style="padding: 2px !important; border-bottom: 1px dotted #000;"></td>
                            </tr>
                            <tr>
                                <td>IVA {{ $rate }}%</td>
                                <td>{{ number_format($summary['subTotal'], 2) }}</td>
                                <td style="text-align: right">{{ number_format($tax, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="8" style="padding: 2px !important;"></td>
                            </tr>
                            @endforeach

                            <tr>
                                <td style="padding: 10px; text-align: left;"><strong></strong></td>
                                <td style="padding: 10px; text-align: right;"><strong>Total Imposto</strong></td>
                                <td style="text-align: right;"><strong>{{ number_format($totalTax, 2) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>

                </td>

                <td style="padding: 10px !important; border-left: 1px dotted #000;">
                    <table class="info-table">
                        <tbody>
                            <tr>
                                <td></td>
                                <th style="text-align:left;"><strong>Total Ilíquido</strong></th>
                                <td style="text-align:right; margin-right:15px;">{{ number_format($taxTotalPrice, 2) }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>

                                <th style="text-align:left;"><strong>Total de descontos</strong></th>
                                <th style="text-align:right; margin-right:15px;">{{ number_format($totalDiscount, 2) }}</th>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <th style="text-align:left;"><strong>Total Imposto</strong></th>
                                <th style="text-align:right; margin-right:15px;">{{ number_format($totalTax, 2) }}</th>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <th style="text-align:left;"><strong>Total AOA</strong></th>
                                <th style="text-align:right; margin-right:15px;">{{ number_format($sumTotalPrice, 2) }}</th>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
        </table>
    </div>



    <div class="item-container">

        <div style="margin-top: 1px; border-top: solid 1px #000; padding-top: 1px;">
            @php
                use Illuminate\Support\Str;
            @endphp

            <table style="width: 100%; border-collapse: collapse;">
                <tbody>
                    @if (Str::startsWith($warehouseCode, 'AAR'))
                    <tr>
                        <td>
                            <div style="text-align: left;">
                                <p style="text-decoration: underline;font-weight: bold;">Banco</p>
                                BIC<br />
                                BFA<br />
                                SOL
                            </div>
                        </td>
                        <td>
                            <div style="text-align: center;">
                                <p style="text-decoration: underline;font-weight: bold;">IBAN</p>
                                AO06 0051 0000 3648 3717 1014 5<br>
                                AO06 0006 0000 6913 4921 3013 5<br>
                                AO06 0044 0000 0030 9337 1019 1
                            </div>
                        </td>
                        <td>
                            <div style="text-align: left;">
                                <p style="text-decoration: underline;font-weight: bold;">Banco</p>
                                BAI<br />
                                ATLANTICO<br />
                                <br />
                            </div>
                        </td>
                        <td>
                            <div style="text-align: center;">
                                <p style="text-decoration: underline;font-weight: bold;">IBAN</p>
                                AO06 0040 0000 9078 8026 1014 2<br />
                                AO06 0055 0000 1491 3581 1011 4<br />
                                <br />
                            </div>
                        </td>
                    </tr>
                    @elseif (Str::startsWith($warehouseCode, 'ALT'))
                        <tr>
                            <td>
                                <div style="text-align: left;">
                                    <p style="text-decoration: underline;font-weight: bold;">Banco</p>
                                    Bch <br />
                                    Bic<br />
                                    Sol
                                </div>
                            </td>
                            <td>
                                <div style="text-align: center;">
                                    <p style="text-decoration: underline;font-weight: bold;">IBAN</p>
                                    AO06 0059 0000 0201 3637 1017 6<br>
                                    AO06 0051 0000 8482 2803 1011 1<br>
                                    AO06 0044 0000 9990 3033 1018 5
                                </div>
                            </td>
                            <td>
                                <div style="text-align: left;">
                                    <p style="text-decoration: underline;font-weight: bold;">Banco</p>
                                    Atlantico <br />
                                    <br />
                                    BAI
                                    <br />
                                </div>
                            </td>
                            <td>
                                <div style="text-align: center;">
                                    <p style="text-decoration: underline;font-weight: bold;">IBAN</p>
                                    AO06 0055 0000 2497 8970 1014 1<br />
                                    <br />
                                    AO06 0040 0000 9032 6750 1014 6
                                    <br />
                                </div>
                            </td>
                        </tr>
                    @elseif (Str::startsWith($warehouseCode, 'EQU'))
                        <tr>
                            <td>
                                <div style="text-align: left;">
                                    <p style="text-decoration: underline;font-weight: bold;">Banco</p>
                                    BCH <br />
                                    Sol<br />
                                    Atlantico
                                </div>
                            </td>
                            <td>
                                <div style="text-align: center;">
                                    <p style="text-decoration: underline;font-weight: bold;">IBAN</p>
                                    AO06 0059 0000 0221 3292 1018 5<br>
                                    AO06 0044 0000 1585 9145 1010 3<br>
                                    AO06 0055 0000 2497 9366 1019 7
                                </div>
                            </td>
                            <!--<td>
                                <div style="text-align: left;">
                                    <p style="text-decoration: underline;font-weight: bold;">Banco</p>
                                    Atlantico <br />
                                    <br />
                                    <br />
                                </div>
                            </td>
                            <td>
                                <div style="text-align: center;">
                                    <p style="text-decoration: underline;font-weight: bold;">IBAN</p>
                                    AO06 0055 0000 2497 9366 1019 7<br />
                                    <br />
                                    <br />
                                </div>
                            </td>-->
                        </tr>
                    @else
                        <tr>
                            <td>
                                <div style="text-align: left;">
                                    <p style="text-decoration: underline;font-weight: bold;">Banco</p>
                                    AIC<br />
                                    BFA<br />
                                    COL
                                </div>
                            </td>
                            <td>
                                <div style="text-align: center;">
                                    <p style="text-decoration: underline;font-weight: bold;">IBAN</p>
                                    AO06 0051 0000 3648 3717 1014 5<br>
                                    AO06 0006 0000 6913 4921 3013 5<br>
                                    AO06 0044 0000 0030 9337 1019 1
                                </div>
                            </td>
                            <td>
                                <div style="text-align: left;">
                                    <p style="text-decoration: underline;font-weight: bold;">Banco</p>
                                    BAI<br />
                                    ATLANTICO<br />
                                </div>
                            </td>
                            <td>
                                <div style="text-align: center;">
                                    <p style="text-decoration: underline;font-weight: bold;">IBAN</p>
                                    AO06 0040 0000 9078 8026 1014 2<br />
                                    AO06 0055 0000 1491 3581 1011 4<br />
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1px; border-top: solid 1px #000; padding-top: 1px;">
            <table class="noborder" style="width: 100%; border: none;">
                <tr>
                    <td style="padding:10px; width: 50%;">
                        <p style="padding-top: 55px;"> <strong>Selo e Assinatura do Cliente</strong></p>
                    </td>
                    <td style="border;">
                        <p style="padding-left:15px; padding-top: 55px;"> <strong>Assinatura Autorizada</strong></p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="declaration">
            <!--<p style="text-decoration: underline"><strong>Declaração:</strong></p>-->
            <div><span style="text-decoration: underline ; font-size:bold; text-indent: 10px; margin-right:10px;"> Declaração:</span><span style="font-size:14px; text-indent: 10px;">Os bens/serviços foram colocados à disposição do adquirente na data de emissão do documento.</span> </div>
            <div style="text-align: lext;text-indent: 86px;"><span style="font-size:14px; text-indent: 10px;">Por favor, confirme a mercadoria no momento da entrega.</span></div>
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
