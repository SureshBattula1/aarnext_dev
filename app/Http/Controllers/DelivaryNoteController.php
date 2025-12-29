<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Auth;
use Log;

class DelivaryNoteController extends Controller
{
   public function generateDelivaryPDF($invoiceNo)
    {
        $warehouseCode = Auth::user()->ware_house;
        $userCode = Auth::user()->code;


        $invoice = DB::table('invoices')
            ->join('customers as c', 'invoices.customer_id', '=', 'c.card_code')
            ->join('sales_employees as se', 'se.id' , '=' , 'invoices.sales_emp_id' )
            ->select('invoices.*', 'c.card_name as customer_name', 'c.nif_no' , 'se.memo')
            ->where('invoices.id', $invoiceNo)
            ->first();



          if(!$invoice->generated_pdf) {

            return response()->json([
                'success' => false,
                'message' => 'Invoice Not yet Generated. Please Try After Invoice Generation..!',
            ], 500);
          }



        // Fetch the invoice items
        $items = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();

        $invoiceBatches = DB::table('invoice_batches')->where('invoice_id', $invoice->id)->get();

        $custAddress = DB::table('customer_addresses')
                            ->where('id', $invoice->address)
                            ->select('address')
                            ->first();

        $ware_house_data = DB::table('ware_houses')->where('whs_code',$warehouseCode)->first();

        $total = 0;
        $gstSummary = [];

        foreach ($items as $item) {
            $price = $item->price;
            $quantity = $item->quantity;
            $disc_percent = $item->disc_percent;
            $disc_value = $price * $disc_percent / 100;
            $price_af_disc = $price - $disc_value;
            $price_total_af_disc = $quantity * $price_af_disc;

            $gstRate = $item->gst_rate;
            $gstValue = $item->gst_value;

            $subtotal = $price_total_af_disc + ($quantity * $gstValue);
            $total += $subtotal;

            if (!isset($gstSummary[$gstRate])) {
                $gstSummary[$gstRate] = [
                    'count' => 0,
                    'sum' => 0,
                    'subTotal' => 0,
                ];
            }
            $gstSummary[$gstRate]['count'] += $quantity;
            $gstSummary[$gstRate]['subTotal'] += $price_total_af_disc;
            $gstSummary[$gstRate]['sum'] += ($quantity * $gstValue);

            $item->batches = collect($invoiceBatches)
            ->where('item_code', $item->item_no)
            ->map(function($batch) {
                return [
                    'quantity' => $batch->quantity,
                    'expiry_date' => $batch->expiry_date
                ];
            })
            ->values()
            ->all();
        }

        ksort($gstSummary);

        $itemsPerPage = 11; // Number of items per page
        $totalPages = ceil(count($items) / $itemsPerPage);
        // Generate the PDF
        $pdf = PDF::loadView('pdf.delivary-note-pdf', compact('invoice', 'items', 'warehouseCode', 'userCode', 'gstSummary','totalPages','custAddress','ware_house_data'))
        ->setPaper('a4')
        ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        // Define folder and file paths
        $folderPath = 'pdf/delivaries';
        $pdfDirectory = public_path($folderPath);
        // Create the directory if it doesn't exist
        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0755, true);
        }

        $pdfName = 'delivary_copy_' . $invoice->id . '.pdf';
        $pdfFilePath = $pdfDirectory . '/' . $pdfName;

        $pdf->render();
        $pdf->output();
        $domPdf = $pdf->getDomPDF();
        $domPdf->set_option('isHtml5ParserEnabled', true);
        $canvas = $domPdf->get_canvas();
        $font = $domPdf->getFontMetrics()->get_font('arial', 'sans-serif');
        $canvas->page_text(500, 800, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
        // Save the PDF to the specified path
        $pdf->save($pdfFilePath);

        if (!file_exists($pdfFilePath)) {
            throw new \Exception("PDF file was not created successfully at $pdfFilePath.");
        }

        if($pdfName){
            $updateDelivaryNo = DB::table('invoices')->where('id', $invoice->id)->update(['delivary_note_no' => $invoice->id]);
        }

        return url($folderPath . '/' . $pdfName);
    }
}
