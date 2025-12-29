<?php
namespace App\Http\Controllers\SapSync;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

use Exception;
class SapSyncController extends Controller
{


    public function CreateItemFromSap(Request $request)
    {

      $data = $request->all();
      Log::info('ITEM UPDATE');
     Log::info('Single Item Post From SAP: ' . print_r($data));

       try {
            if (is_array($data)) {

                 $ItemsData = [
                    'item_code' => $data['ItemCode'] ?? null,
                    'item_name' => $data['ItemName'] ?? null,
                    'frgn_name' => $data['FrgnName'] ?? null,
                    'items_grp_code' => $data['ItmsGrpCod'] ?? null,
                    'items_grp_name' => $data['ItmsGrpNam'] ?? null,
                    'frim_code' => $data['FirmCode'] ?? null,
                    'manufacturer' => $data['Manufacturer'] ?? null,
                    'code' => $data['Code'] ?? null,
                    'sale_pack' => $data['SalePack'] ?? null,
                    'cabinda_iva' => $data['Cabinda_iVA'] ?? null,
                    'others_iva' => $data['Others_iVA'] ?? null,
                    'valid_for' => $data['validFor'] ?? null,
                    'frozen_for' => $data['frozenFor'] ?? null,
                    'create_date' => $data['CreateDate'] ?? null,
                    'create_ts' => $data['CreateTS'] ?? null,
                    'update_date' => $data['UpdateDate'] ?? null,
                    'update_ts' => $data['UpdateTS'] ?? null
                ];

                    $insert_update = DB::table('items')->updateOrInsert(
                        ['item_code' => $data['ItemCode']],  // Condition to check
                        $ItemsData  // Data to insert or update
                    );
                    if($insert_update){
                        return response()->json(['success' => 1, 'message' => "Items Posted."]);

                    }

               }else {
                    Log::warning('Expected array but received non-array response', ['data' => "Data not Found"]);
                } 

        } catch (\Exception $e) {
        // Handle connection errors or other exceptions
                echo "<pre>"; print_r($e->getMessage());// exit;
         Log::error('Error during Item WEB POST request', [
                            'message' => $e->getMessage(),
                            'item_no' => $data['item_no']
                        ]);
            } 

    }


    public function UpdateItemDealPriceFromSap(Request $request)
    {
      $item_qty = $request->all();

       try {

            if(isset($item_qty['ItemCode'])){

                     foreach ($item_qty['ItemBatchDetails'] as $key => $batches) {


                        $batch_param_items = array(
                            'item_offer_qty' => $batches['OfferQty'],
                            'item_offer_price'   => $batches['OfferPrice'],
                            'updated_at'         => date('Y-m-d H:i:s')
                        );

                    $insert_update_batch = DB::table('warehouse_codebatch_details')->update(
                        ['item_code' => $item_qty['ItemCode']],
                        $batch_param_items
                    );
                                               
                }

                if($insert_update_batch){
                    return response()->json(['success' => 1, 'message' => "Item Quantities Updated..!"]);

                }else{
                    return response()->json(['success' => 0, 'message' => "Item Quantities Not Updated..!"]);
                }


            }else{
             return response()->json([
                      'status' => 'failed',
                      'message' => "Item Not Found..!",
                    ], 400);

            }

        } catch (\Exception $e) {
        // Handle connection errors or other exceptions
                echo "<pre>"; print_r($e->getMessage()); 
         Log::error('Error during Item WEB POST request', [
                            'message' => $e->getMessage(),
                            'item_no' => $item_qty['ItemCode']
                        ]);
            } 

    }

    public function UpdateItemBatchesFromSap(Request $request)
    {

     //echo "<pre>"; print_r($request->all()); exit;
      $item_qty = $request->all();

       //Log::info('Single Item Post From SAP: ' . print_r($item_qty));
       try {
           // $item_found  = DB::table('warehouse_stocks')->where('item_code',$item_qty['ItemCode'])->exists();

            if(isset($item_qty['ItemCode'])){

                    // $warehouse_params = array(
                    //     'item_code' => $item_qty['ItemCode'],
                    //     'item_name' => $item_qty['ItemName'],
                    //     'warehouse_code' => $item_qty['WhsCode'],
                    //     'warehouse_name' => $item_qty['WhsName'],
                    //     'on_hand' => $item_qty['Quantity'],
                    //     'sap_updated_at' => date('Y-m-d H:i:s')
                    // ); 
                    // $insert_update = DB::table('warehouse_stocks')->updateOrInsert(
                    //     ['item_code' => $item_qty['ItemCode'],'warehouse_code' =>$item_qty['WhsCode']],  // Condition to check
                    //     $warehouse_params  // Data to insert or update
                    // );

                     foreach ($item_qty['ItemBatchDetails'] as $key => $batches) {

                       // / echo "AGN";

                        //echo "<pre>"; print_r($batches); exit;


                        $batch_param_items = array(
                            'item_code' => $item_qty['ItemCode'],
                            'whs_code'  => $item_qty['WhsCode'],
                            'batch_num' => $batches['BatchNum'],
                            'in_date'   => $batches['InDate'],
                            'prd_date'  => $batches['PrdDate'],
                            'quantity'  => $batches['Quantity'],
                            'exp_date'  => $batches['ExpDate'],
                            'status'    => $batches['Status']  
                        );
                        if(isset($batches['CreditQty']))
                        {
                          $batch_param_items['credit_qty'] =  $batches['CreditQty'];
                        }

                    $insert_update_batch = DB::table('warehouse_codebatch_details')->update(
                        ['item_code' => $item_qty['ItemCode'],'whs_code' =>$item_qty['WhsCode'],'batch_num' =>$batches['BatchNum']],
                        $batch_param_items
                    );
                                               
                }

                if($insert_update_batch){
                    return response()->json(['success' => 1, 'message' => "Item Quantities Updated..!"]);

                }else{
                    return response()->json(['success' => 0, 'message' => "Item Quantities Not Updated..!"]);
                }


            }else{
             return response()->json([
                      'status' => 'failed',
                      'message' => "Item Not Found..!",
                    ], 400);

            }

        } catch (\Exception $e) {
        // Handle connection errors or other exceptions
                echo "<pre>"; print_r($e->getMessage()); 
         Log::error('Error during Item WEB POST request', [
                            'message' => $e->getMessage(),
                            'item_no' => $item_qty['ItemCode']
                        ]);
            } 

    }
    public function UpdateItemBatchQtyFromSap(Request $request)
    {
        //echo "<pre>"; print_r($request->all()); exit;
        $item_qty = $request->all();
        Log::info('Single Item Post From SAPPP: ' . print_r($item_qty, true)); // Log the request data
        try {
            if (isset($item_qty['ItemCode']) && isset($item_qty['ItemBatchDetails'])) {

                DB::beginTransaction(); // Start a transaction

                $quantity = 0;
                foreach ($item_qty['ItemBatchDetails'] as $key => $batches) {

                    $existingitembatch = DB::table('warehouse_codebatch_details')
                        ->select('item_code', 'whs_code', 'batch_num', 'quantity')
                        ->where('item_code', $item_qty['ItemCode'])
                        ->where('whs_code', $item_qty['WhsCode'])
                        ->where('batch_num', $batches['BatchNum'])
                        ->first();

                    if ($existingitembatch) {
                        // Existing batch, update the quantity
                        //$quantity = $existingitembatch->quantity;
                       // $update_qty = $quantity + $batches['Quantity']; // Ensure correct calculation

                        $update_params = [
                            'quantity' => $batches['Quantity'],
                            'updated_at' => now()
                        ];

                        $insert_update_batch = DB::table('warehouse_codebatch_details')
                            ->where('item_code', $item_qty['ItemCode'])
                            ->where('whs_code', $item_qty['WhsCode'])
                            ->where('batch_num', $batches['BatchNum'])
                            ->update($update_params);

                    } else {
                        // New batch, insert new record
                        $batch_param_items = [
                            'item_code' => $item_qty['ItemCode'],
                            'whs_code'  => $item_qty['WhsCode'],
                            'batch_num' => $batches['BatchNum'],
                            'in_date'   => $batches['InDate'],
                            'prd_date'  => $batches['PrdDate'],
                            'quantity'  => $batches['Quantity'],
                            'exp_date'  => $batches['ExpDate'],
                            'status'    => $batches['Status'],
                            'created_at' => now(),
                            'updated_at' => now()
                        ];

                        $insert_update_batch = DB::table('warehouse_codebatch_details')->insert($batch_param_items);
                    }

                    if (!$insert_update_batch) {
                        DB::rollBack(); // Roll back if any insert or update fails
                        return response()->json(['success' => 0, 'message' => "Item Quantities Not Updated..!"], 500);
                    }
                }

                DB::commit(); // Commit the transaction

                return response()->json(['success' => 1, 'message' => "Item Quantities Updated..!"]);

            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => "Item Not Found or Batch Details Missing..!"
                ], 400);
            }

        } catch (\Exception $e) {
            // Rollback in case of exception
            DB::rollBack();

            // Handle connection errors or other exceptions
            Log::error('Error during Item WEB POST request', [
                'message' => $e->getMessage(),
                'item_no' => $item_qty['ItemCode'] ?? 'N/A'
            ]);

            return response()->json(['success' => 0, 'message' => "An error occurred while updating the item"], 500);
        }
    }



    public function UpdateItemPrice(Request $request)
    {


      $data = $request->all();
     //echo "<pre>"; print_r($request);
       try {
            if(isset($data['ItemCode'])){
                    $pricesesData = [
                        'item_code'        => $data['ItemCode'] ?? null,
                        'item_price'       => $data['Price'] ?? null,
                    ];

                $insert_update = DB::table('items')->updateOrInsert(

                        ['item_code' => $data['ItemCode']],  // Condition to check
                        $pricesesData  // Data to insert or update
                    );
                    if($insert_update){
                        return response()->json(['success' => 1, 'message' => "Item Prices Updated."]);

                    }

               }else {
                     return response()->json([
                              'status' => 'failed',
                              'message' => "Item Not Found..!",
                            ], 400);              
                    Log::warning('Bad Data', ['data' => $data]);
                } 

        } catch (\Exception $e) {
        // Handle connection errors or other exceptions
                echo "<pre>"; print_r($e->getMessage());// exit;
         Log::error('Error during Item WEB POST request', [
                            'message' => $e->getMessage(),
                            'item_no' => $data['ItemCode']
                        ]);
            } 

    }
    public function UpdateDocumentStatus(Request $request)
    {

      $data = $request->all();
     //echo "<pre>"; print_r($request);
       try {
        $document_found  = DB::table('invoices')->where('sap_created_id',$data['DocEntry'])->exists();

            if($document_found){
                    $pricesesData = [
                        'status'     => $data['Status'] ?? null,
                        'sap_payment_status_update' => date('Y-m-d H:i:s')
                        
                    ];

                $insert_update = DB::table('invoices')
                                    ->where('sap_created_id',$data['DocEntry'])
                                    ->update($pricesesData);
                    if($insert_update){
                        return response()->json(['success' => 1, 'message' => "Document Status Updated."]);

                    }

               }else {
                     return response()->json([
                              'status' => 'failed',
                              'message' => "Document Not Found..!",
                            ], 400);              
                    Log::warning('Bad Data', ['data' => $data]);
                } 

        } catch (\Exception $e) {
        // Handle connection errors or other exceptions
                echo "<pre>"; print_r($e->getMessage());// exit;
         Log::error('Error during Item WEB POST request', [
                            'message' => $e->getMessage()
                        ]);
            } 

    }

    public function UpdateSingleCustomerFromSap(Request $request)
    {
        // Get customer data from the request
        $customer = $request->all();
        // /echo "<pre>"; print_r($customer); exit;

        try {
            // Prepare parameters to insert or update
            $params = array(
                'series'           => $customer['series'],
                'card_code'        => $customer['card_code'],
                'card_name'        => $customer['card_name'],
                'card_fname'       => $customer['card_fname'],
                'alias_name'       => $customer['alias_name'],
                'card_typ_nm'      => $customer['card_typ_nm'],
                'currency'         => $customer['currency'],
                'grp_code'         => $customer['grp_code'],
                'grp_name'         => $customer['grp_name'],
                'phone1'           => $customer['phone1'],
                'phone2'           => $customer['phone2'],
                'cellular'         => $customer['cellular'],
                'factor'           => $customer['factor'],
                'email'            => $customer['email'],
                'grp_num'          => $customer['grp_num'],
                'credit_line'      => $customer['credit_line'],
                'list_num'         => $customer['list_num'],
                'product_list'     => $customer['product_list'],
                'pymnt_grp'        => $customer['pymnt_grp'],
                'deb_pay_acct'     => $customer['deb_pay_acct'],
                'deb_pay_name'     => $customer['deb_pay_name'],
                'valid_for'        => $customer['valid_for'],
                'frozen_for'       => $customer['frozen_for'],
                'sub_grp'          => $customer['sub_grp'],
                'nif_no'           => $customer['nif_no'],
                'store_location'   => $customer['store_location'],
                'sales_commission' => $customer['sales_commission'],
                'hike_percent'     => $customer['hike_percent'],
                'create_ts'        => $customer['create_ts'],
                'default_discount' => $customer['CustomerDiscount'],
                'update_date'      => $customer['update_date'],
                'update_ts'        => $customer['update_ts']
            );

            // Get sales employee ID from the users table based on sales_emp_code
            $sales_emp_id = DB::table('sales_employees')
                                ->select('id','code','name', 'email')
                                ->where('code', $customer['sale_emp_code'])
                                ->first();

            // Check if sales employee exists, otherwise assign default value

            $params['sal_emp_name'] = $sales_emp_id->name ? $sales_emp_id->name : '-1';
            $params['slp_code'] = $sales_emp_id->code ? $sales_emp_id->code : '-1';


        // Step 1: Check if record exists
        $existingCustomer = DB::table('customers')->where('card_code', $customer['card_code'])->first();

        if ($existingCustomer) {
            // Step 2: Update the record if it exists
            DB::table('customers')->where('card_code', $customer['card_code'])->update($params);
            $status = 'updated';
            $id = $existingCustomer->id; // Get the existing customer ID


            foreach ($customer['Address'] as $key => $address) {
                //$address_params = $arrayName = array('' => , );

                $address_update_params = array(
                                'address_type' => $address['address_type'],
                                'sap_address_id' => $address['sap_address_id'],
                                'address' => $customer['card_name'],
                                'card_code' => $address['card_code'],
                                'building' => $address['building'],
                                'block'=> $address['block'],
                                'zip_code' => $address['zip_code'],
                                'city' => $address['city'],
                                'country' => $address['country'],
                                'street' => $address['street'],
                                'landmark' => $address['landmark'],
                                'country_code' => $address['country_code'],
                                'state' => $address['state'],
                                'state_code' => $address['state_code'],
                                'gst_type'   => $address['gst_type'],
                                'gst_regn_no'   => $address['gst_regn_no'],
                                'sap_created_date' => now(),
                                'CreateTS' => $address['CreateTS'], 
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            );
                $update_address = DB::table('customer_addresses')
                                        ->where('card_code', $customer['card_code'])
                                        ->where('address_type', $address['address_type'])
                                        ->update($address_update_params);
                
            }

            return response()->json(['success' => 1, 'message' => "Customer Updated."], 200);
            

        } else {
            $params['created_at'] = now();
            // Step 2: Insert a new record if it does not exist
            $inserted_id = DB::table('customers')->insertGetId($params); // Get the inserted ID
            $status = 'inserted';


            foreach ($customer['Address'] as $key => $address) {
                //$address_params = $arrayName = array('' => , );

                $address_params = array(
                                'address_type' => $address['address_type'],
                                'address' => $address['address'],
                                'card_code' => $address['card_code'],
                                'building' => $address['building'],
                                'block'=> $address['block'],
                                'zip_code' => $address['zip_code'],
                                'city' => $address['city'],
                                'country' => $address['country'],
                                'street' => $address['street'],
                                'landmark' => $address['landmark'],
                                'country_code' => $address['country_code'],
                                'state' => $address['state'],
                                'state_code' => $address['state_code'],
                                'gst_type'   => $address['gst_type'],
                                'gst_regn_no'   => $address['gst_regn_no'],
                                'sap_created_date' => now(),
                                'CreateTS' => $address['CreateTS'], 
                                'created_at' => date('Y-m-d H:i:s')
                            );
                $insert_address = DB::table('customer_addresses')->Insert($address_params);
                
            }

        }

            // Check if insert or update was successful
            if ($status) {
                return response()->json(['success' => 1, 'message' => "Customer Posted."], 200);
            } else {
                return response()->json(['success' => 0, 'message' => "Failed to post customer."], 500);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database-related exceptions
            Log::error('Database error during Customer WEB POST request', [
                'message' => $e->getMessage(),
                'card_code' => $customer['card_code']
            ]);
            return response()->json(['success' => 0, 'message' => 'Database error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            // Handle general exceptions
            Log::error('Error during Customer WEB POST request', [
                'message' => $e->getMessage(),
                'card_code' => $customer['card_code']
            ]);
            return response()->json(['success' => 0, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }



    // public function UpdateCustomesrFromSap(Request $request){

    //   //  $customers = 



    // }

    public function customerStatus(Request $request)
    {
        try {
            $requestData = $request->all();

            if (empty($requestData['CardCode']) || !is_array($requestData['CardCode'])) {
                return response()->json(['error' => 'CardCode data must be a non-empty array.'], 400);
            }

            foreach ($requestData['CardCode'] as $data) {
                if (empty($data['CardCode']) || !isset($data['Status']) || empty($data['UpdatedBy'])) {
                    return response()->json(['error' => 'Each item must have CardCode, Status, and UpdatedBy fields.'], 422);
                }

                $updateParams = [
                    'status' => $data['Status'],
                    'status_updated_at' => now(),
                    'status_updated_user_id' => $data['UpdatedBy'],
                ];

                DB::table('customers')
                    ->where('card_code', $data['CardCode'])
                    ->update($updateParams);
            }

            return response()->json(['message' => 'Customer statuses updated successfully.'], 200);

        } catch (\Exception $e) {
            Log::error('Customer status update failed', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function RFTStatusUpdate(Request $request)
    {
        try {
            $requestData = $request->all();
            $rft_number_check = DB::table('request_for_tender')
                                        ->select('rft_number')
                                        ->where('rft_number',$requestData['RFTNumber'])
                                        ->first();
            if($rft_number_check->rft_number)
            {

                $update_status = DB::table('request_for_tender')
                                        ->where('rft_number',$requestData['RFTNumber'])

                                        ->update([
                                            'cancelled_status' => $requestData['CanceledStatus']
                                        ]);

                return response()->json(['message' => 'RFT statuses updated successfully.'], 200);

            }else{

                return response()->json(['error' => 'RFT Not Found.'], 400);

            }


        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
    public function CustomerDiscount(Request $request)
    {
        try {
            $requestData = $request->all();
            $customer_check = DB::table('customers')
                                        ->select('card_code')
                                        ->where('card_code',$requestData['CardCode'])
                                        ->first();
            if($customer_check->card_code)
            {

                $update_status = DB::table('customers')
                                        ->where('card_code',$requestData['CardCode'])
                                        ->update([
                                                    'default_discount' => $requestData['CustomerDiscount']
                                                ]);

                return response()->json(['message' => 'Customer statuses updated successfully.'], 200);

            }else{

                return response()->json(['error' => 'Customer Not Found.'], 400);

            }


        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }     









}
