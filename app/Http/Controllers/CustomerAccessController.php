<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\SalesOrderController;
use DB;
use Spatie\Browsershot\Browsershot;
use Mpdf\Mpdf;
use PDF;
use Picqer\Barcode\BarcodeGeneratorPNG;

class CustomerAccessController extends Controller {

    public function orderIndex(){
        return view('customer.order-creation');
    }

    public function updatePassword(Request $request)
    {
        // $request->validate([
        //     'password' => 'required|string|min:8|confirmed',
        // ]);
        $customer = Auth::guard('customer')->user();
        $customer->password = Hash::make($request->password);
        $customer->customer_login_setup = 1; // Assuming this field exists
        $customer->save();

        return response()->json(['success' => true, 'message' => 'Password updated successfully.']);
    }

    public function getCustomerItemSearch(Request $request)
    {
        $data = $request->all();
        $warehouseCode = Auth::guard('customer')->user()->store_location;
        $customer = DB::table('customers')
            ->select('product_list','list_num','factor')
            ->where('card_code', $data['card_code'])
            ->first();
        if(empty($warehouseCode)){
            throw new \Exception("This customer does not have an assigned warehouse.");

        }
        $query = DB::table('items as i')
            ->leftJoin('warehouse_codebatch_details as ws', function ($join) use ($warehouseCode) {
                $join->on('i.item_code', '=', 'ws.item_code')
                    ->where('ws.whs_code', '=', $warehouseCode);
            })
            ->select(
                'i.id',
                'i.item_code',
                'i.item_name',
                'i.others_iva',
                'i.cabinda_iva',
                DB::raw("ROUND((i.item_price) * {$customer->factor}) as item_price_factor"),
                DB::raw("ROUND(COALESCE(SUM(ws.quantity) - SUM(ws.invoice_qty) + SUM(ws.credit_qty), 0), 0) as available_quantity")
            )
            ->where(function ($query) use ($data) {
                if (!empty($data['search'])) {
                    $query->where('i.item_name', 'like', '%' . $data['search'] . '%')
                        ->orWhere('i.item_code', 'like', '%' . $data['search'] . '%');
                }
            })
            ->groupBy('i.id', 'i.item_code', 'i.item_name')  // Group by necessary fields when using aggregation
            ->orderBy('i.item_code')
            ->get();

        // Post-process the result for formatting HTML as needed.
        $results = $query->map(function($item) {
            $warehouseCode = Auth::guard('customer')->user()->store_location;

            $iva_value = 0;
            if($warehouseCode == 'AAR-CAB')
            {
              $iva_value = $item->cabinda_iva;
            }else{
              $iva_value = $item->others_iva;
            }

            $str = preg_replace("/[^0-9]/","",$iva_value);
            if($str){
            $tax = $str;
            }else{
            $tax = 0;
            }
            return [
                'id' => $item->id,
                'result' => [
                            'id' => $item->id,
                            'item_code' => $item->item_code,
                            'item_name' => $item->item_name,
                            'price' => $item->item_price_factor,
                            'quantity' => $item->available_quantity,
                            'tax'  => $tax
                        ],
                'text' => "<span style=\"color:gray; font-weight:bold; font-size:11px;\">{$item->item_code}</span> - " .
                          "<span style=\"color:black; font-weight:bold; font-size:11px;\">{$item->item_name}</span> - " .
                          "<span style=\"color:black; font-weight:bold; font-size:11px;\">kz {$item->item_price_factor}</span> - " .
                          "<span style=\"color:gray; font-weight:bold; font-size:11px;\">{$item->available_quantity}</span>"
            ];
        });
        return response()->json($results);
    }

   public function storeCustomerOrder(Request $request)
    {
        try {
            // echo "<pre>"; print_r($request->all()); exit;
            DB::beginTransaction();
            $customer = Auth::guard('customer')->user();
            $warehouseCode = $customer->store_location;
            $custSubGrp = $customer->sub_grp ?? 'GENERAL';
            $custGrpCode = $customer->grp_code;

            if(empty($warehouseCode)){
                throw new \Exception("This customer does not have an assigned warehouse.");
            }

            $lastCode = DB::table('ware_houses')->select('str_so_hosp_series','str_so_gen_series')
                ->where('whs_code', $warehouseCode)
                ->first();

            if($custSubGrp == 'HOSPITAL')
            {
                $last_series =  $lastCode->str_so_hosp_series;
            } else{
                $last_series =  $lastCode->str_so_gen_series;
            }

            $numericPart = substr($last_series, strrpos($last_series, '/') + 1);
            $newNumericPart = str_pad((int)$numericPart + 1, 5, '0', STR_PAD_LEFT);
            $stringPart = strtok($last_series, '/');
            $unique_number = $stringPart.'/'.$newNumericPart;
            if($custSubGrp == 'HOSPITAL')
            {
                DB::table('ware_houses')
                    ->where('whs_code',$warehouseCode)
                    ->update(['str_so_hosp_series'=>$unique_number]);
            } else{
                DB::table('ware_houses')
                    ->where('whs_code',$warehouseCode)
                    ->update(['str_so_gen_series'=>$unique_number]);
            }

            $invoiceNo = $unique_number;

            $systemName = gethostname();
            $systemIP = $this->getLaptopIp();
            $orderId = DB::table('orders')->insertGetId([
                'customer_id' => $customer->card_code,
                'ref_id' => 1,
                'sales_ref_no' => $request->ref_no,
                'user_id' => $customer->card_code, 
                'sales_emp_id'=> 1,
                'address' => $request->cust_address_id,
                'sales_type' => $request->sales_type,
                'remarks' => $request->remarks,
                'order_date' => date('Y-m-d'),
                'discount_status' => 0,
                'sales_invoice_no' => $invoiceNo,
                'system_name' => $systemName,
                'system_ip' => $systemIP,
                'ware_house_code' =>$warehouseCode,
                'customer_sub_grp' => $custSubGrp,
                'is_zip_customer' => 'No',
                'is_sales_approved' => 0,
                'approval_type' => 'customer_order'
            ]);

            $total = 0;
            $hasDiscount = false;
            foreach ($request->row_item_no as $index => $itemCode) {
                if (empty($itemCode)) {
                    continue;
                }

                $itemInfo = DB::table('items')->select('id','item_code','item_name')->where('id', $itemCode)->first();

                if (!$itemInfo) {
                    throw new \Exception('Item not found for code: ' . $itemCode);
                }

                $price = $request->row_item_price[$index];
                $quantity = $request->row_item_qty[$index];
                $disc_percent = $request->row_item_disc_percent[$index];
                $disc_value = $price * $disc_percent / 100;
                $price_af_disc = $price - $disc_value;
                $price_total_af_disc = $quantity * $price_af_disc;
                $gstRate =  $request->row_item_gst_rate[$index];
                $gstValue =  $request->row_item_gst_rate_value[$index];
                $subtotal = $price_total_af_disc + ($quantity * $gstValue);
                $total += $subtotal;

                $baseLine = $index;

                if ($disc_value > 0) {
                    $hasDiscount = true;
                }

                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'item_id' => $itemInfo->id,
                    'item_no' => $itemInfo->item_code,
                    'item_desc' => $itemInfo->item_name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'disc_value' => $disc_value,
                    'disc_percent' => $disc_percent,
                    'price_af_disc' => $price_af_disc,
                    'price_total_af_disc' => $price_total_af_disc,
                    'gst_rate' => $gstRate ?? 0,
                    'gst_value' => $gstValue ?? 0,
                    'subtotal' => $subtotal,
                    'base_line' => $baseLine,
                ]);
            }

            DB::table('orders')->where('id', $orderId)->update([
                'total' => $total,
                'discount_status' => $hasDiscount ? 1 : 0,
            ]);

            $salesOrderPdfUrl = $this->generateSalesInvoicePDF($orderId);

            DB::commit();
            $response = [
                'success' => 1,
                'message' => 'Customer Order created successfully.',
                'sales_order_url' => $salesOrderPdfUrl,
            ];
            return response()->json($response, 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => 0,
                'message' => 'Error creating Order: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getLaptopIp() {
        $output = array();
        exec('ipconfig /all', $output);

        foreach ($output as $line) {
            if (strpos($line, 'IPv4 Address') !== false) {
                $parts = explode(':', $line);
                return trim(end($parts));
            }
        }
        return '';
    }


    function generateSalesInvoicePDF($orderId)
    {
        $warehouseCode = Auth::guard('customer')->user()->store_location;

        $order = DB::table('orders')
                        ->join('customers as c','orders.customer_id', '=', 'c.card_code')
                        ->join('sales_employees as se', 'se.id' , '=' , 'orders.sales_emp_id')
                        ->select('orders.*','c.card_name as customer_name','c.nif_no', 'se.memo')
                        ->where('orders.id', $orderId)->first();

        $items = DB::table('order_items')->where('order_id', $orderId)->get();
        $custAddress = null;
        if (!empty($order->address)) {
            $custAddress = DB::table('customer_addresses')
                ->where('id', $order->address)
                ->select('address')
                ->first();
        }

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

            // GST Calculation
            $gstRate = $item->gst_rate;
            $gstValue = $item->gst_value;

            $subtotal = $price_total_af_disc + ($quantity * $gstValue);
            $total += $subtotal;

            // Add to GST summary
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
        }

        ksort($gstSummary);

        if (!$order) {
            throw new \Exception("Order with invoice number $invoiceNo not found.");
        }

        $barcodeGenerator = new BarcodeGeneratorPNG();
        $barcodeData = 'Name'.$order->customer_name . '| Total' . $order->total . '| Order Date' . $order->order_date;

        $barcode = $barcodeGenerator->getBarcode($barcodeData, $barcodeGenerator::TYPE_CODE_128);

        $barcodeBase64 = 'data:image/png;base64,' . base64_encode($barcode);

        $itemsPerPage = 11; // Number of items per page
        $totalPages = ceil(count($items) / $itemsPerPage);
        // Load the invoice view
        $pdf = PDF::loadView('pdf.sales-order-pdf', compact('order', 'items','warehouseCode' , 'gstSummary','totalPages','custAddress','ware_house_data', 'barcodeBase64'))
        ->setPaper('a4')
        ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        // Define the directory and file name
        $folderPath = 'pdf/sales';
        $pdfDirectory = public_path($folderPath);

        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0755, true);
        }

        $pdfName = 'sales_copy_' . $order->id . '.pdf';
        $pdfFilePath = $pdfDirectory . '/' . $pdfName;

        $pdf->render();
        $pdf->output();
        $domPdf = $pdf->getDomPDF();
        $domPdf->set_option('isHtml5ParserEnabled', true);
        $canvas = $domPdf->get_canvas();
        $font = $domPdf->getFontMetrics()->get_font('arial', 'sans-serif');
        $canvas->page_text(500, 800, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));

        $pdf->save($pdfFilePath);

        if (!file_exists($pdfFilePath)) {
            throw new \Exception("PDF file was not created successfully at $pdfFilePath.");
        }

        // Return the URL to the PDF file
        return url($folderPath . "/" . $pdfName);
    }

    public function customerOrderReports(){
        return view('customer.order-reports');
    }

    public function customerInvoiceReports(){
        return view('customer.invoice-reports');
    }

    public function customerOrderReportsJson(Request $request)
    {
        $data = $request->all();

        if (!isset($data['draw'])) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        try {
            $customer = Auth::guard('customer')->user();
            $warehouseCode = $customer->store_location;

            $query = DB::table('orders as q')
                ->where('q.ware_house_code', $warehouseCode)
                // ->where('q.user_id', $customer->card_code)
                ->select([
                    'q.id',
                    'q.sales_invoice_no as doc_num',
                    'q.sales_type',
                    'q.customer_id',
                    'q.order_date',
                    'q.is_sales_approved as status',
                    'q.total',
                ])
                ->orderBy('q.id', 'DESC');
            if (!empty($request->sales_type)) {
                $query->where('q.sales_type', $request->sales_type);
            }

            if ($request->status_search !== null) {
                $query->where('q.status', $request->status_search);
            }

            if (!empty($request->start_date) && !empty($request->end_date)) {
                try {
                    $query->whereBetween('q.order_date', [$request->start_date, $request->end_date]);
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Invalid date format'], 400);
                }
            }

            if (!empty($data['search'])) {
                $searchTerm = '%' . $data['search'] . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('q.sales_invoice_no', 'LIKE', $searchTerm);
                });
            }

            $totalRecords = $query->count();
            $totalSum = $query->sum('q.total');

            if ($data['length'] != -1) {
                $query->skip($data['start'])->take($data['length']);
            }

            $orders = $query->get();

        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'total_amount' => 0,
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
        }

        return response()->json([
            'draw' => intval($data['draw']),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $orders,
            'total_amount' => $totalSum,
        ]);
    }

    public function customerInvoiceReportsJson(Request $request)
    {
        $data = $request->all();

        if (!isset($data['draw'])) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        try {
            $customer = Auth::guard('customer')->user();
            $warehouseCode = $customer->store_location;

            $query = DB::table('invoices as q')
                ->join('customers as c', 'c.card_code', '=', 'q.customer_id')
                ->where('q.store_code', $warehouseCode)
                ->where('q.customer_id', $customer->card_code)
                ->select([
                    'q.id',
                    'q.invoice_no as doc_num',
                    'q.sales_type',
                    'q.customer_id',
                    'q.order_date',
                    'q.status',
                    'q.total',
                    'q.paid_amount',

                ])
                ->orderBy('q.id', 'DESC');

            if (!empty($request->sales_type)) {
                $query->where('q.sales_type', $request->sales_type);
            }

            if ($request->status_search !== null) {
                $query->where('q.status', $request->status_search);
            }

            if (!empty($request->start_date) && !empty($request->end_date)) {
                try {
                    $query->whereBetween('q.order_date', [$request->start_date, $request->end_date]);
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Invalid date format'], 400);
                }
            }

            if (!empty($data['search'])) {
                $searchTerm = '%' . $data['search'] . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('q.invoice_no', 'LIKE', $searchTerm);
                });
            }

            $totalRecords = $query->count();

            if ($data['length'] != -1) {
                $query->skip($data['start'])->take($data['length']);
            }

            $invoices = $query->get();

        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
        }

        return response()->json([
            'draw' => intval($data['draw']),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $invoices,
        ]);
    }

    public function customerAddresses($cardCode, Request $request)
    {
        $search = $request->input('search', '');

        $addresses = DB::table('customer_addresses')

            ->where('customer_addresses.card_code', $cardCode)
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('customer_addresses.street', 'LIKE', "%$search%")
                    ->orWhere('customer_addresses.city', 'LIKE', "%$search%")
                    ->orWhere('customer_addresses.state', 'LIKE', "%$search%")
                    ->orWhere('customer_addresses.country', 'LIKE', "%$search%")
                    ->orWhere('customer_addresses.zip_code', 'LIKE', "%$search%");
                });
            })
            ->groupBy('customer_addresses.card_code')
            ->get();

        return response()->json([
            'addresses' => $addresses
        ]);
    }
}
