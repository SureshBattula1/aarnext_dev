<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PDF;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// use PDF;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
// use PDF;
// use Barryvdh\DomPDF\Facade as PDF;

class PDFController extends Controller
{
    public function generatePDF()

    {

        $data = [
            [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],    [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],    [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],    [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],  [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],  [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],            [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],    [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],    [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],    [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],  [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],  [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],            [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],    [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],    [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],    [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],  [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],  [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],            [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],    [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],    [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],    [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],  [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],  [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],            [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],    [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],    [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],    [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],  [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ],  [
                'quantity' => 1,
                'description' => '1 Year Subscription',
                'price' => '129.00'
            ]
        ];

    //     $invoiceNo = 35;
    //     $warehouseCode = Auth::user()->ware_house;

    // // Fetch the invoice details
    // $invoice = DB::table('invoices')
    //     ->join('customers as c', 'invoices.customer_id', '=', 'c.card_code')
    //     ->select('invoices.*', 'c.card_name as customer_name', 'c.nif_no')
    //     ->where('invoices.id', $invoiceNo)
    //     ->first();

    // if (!$invoice) {
    //     throw new \Exception("Invoice number $invoiceNo not found.");
    // }

    // // Fetch the invoice items
    // $items = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
    // $invoiceBatches = DB::table('invoice_batches')->where('invoice_id', $invoice->id)->get();

    // $total = 0;
    // $gstSummary = [];

    // foreach ($items as $item) {
    //     $price = $item->price;
    //     $quantity = $item->quantity;
    //     $disc_percent = $item->disc_percent;
    //     $disc_value = $price * $disc_percent / 100;
    //     $price_af_disc = $price - $disc_value;
    //     $price_total_af_disc = $quantity * $price_af_disc;

    //     $gstRate = $item->gst_rate;
    //     $gstValue = $item->gst_value;

    //     $subtotal = $price_total_af_disc + ($quantity * $gstValue);
    //     $total += $subtotal;

    //     if (!isset($gstSummary[$gstRate])) {
    //         $gstSummary[$gstRate] = [
    //             'count' => 0,
    //             'sum' => 0,
    //             'subTotal' => 0,
    //         ];
    //     }
    //     $gstSummary[$gstRate]['count'] += $quantity;
    //     $gstSummary[$gstRate]['subTotal'] += $price_total_af_disc;
    //     $gstSummary[$gstRate]['sum'] += ($quantity * $gstValue);

    //     $item->batches = collect($invoiceBatches)
    //         ->where('item_code', $item->item_no)
    //         ->map(function($batch) {
    //             return [
    //                 'quantity' => $batch->quantity,
    //                 'expiry_date' => $batch->expiry_date
    //             ];
    //         })
    //         ->values()
    //         ->all();
    // }

    // ksort($gstSummary);



        $pdf = PDF::loadView('myPDF',  ['data' => $data]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $pdf->output();

        $domPdf = $pdf->getDomPDF();



        $canvas = $domPdf->get_canvas();

        $canvas->page_text(10, 10, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10, [0, 0, 0]);



        /* Set Page Number to Footer */

        /* $canvas->page_text(10, $canvas->get_height() - 20, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10, [0, 0, 0]); */



        return $pdf->stream('itsolutionstuff.pdf');

    }

}
