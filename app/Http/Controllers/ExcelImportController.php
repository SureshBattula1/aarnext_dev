<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class ExcelImportController extends Controller
{

    function importItems()
    {
        $filePath = public_path('imports/FORTUNET_ITEMS.xlsx');

        if (file_exists($filePath)) {

            $sheets = Excel::toArray([], $filePath);
            $sheet1Data = $sheets[6];

            // echo "<pre>";
            // print_r($sheet1Data);
            // exit;

            if (count($sheet1Data) > 1) {

                $first = true;
                $i = 0;
                foreach ($sheet1Data as $row) {
                    echo $i;

                    if ($i < 1) {  // Skip First Row
                        $first = false;
                        $i++;
                        continue;
                    }

                    // if ($i == 10)
                    //     break;
                    // echo 'works';

                    $item_no = trim($row[0]);
                    $item_desc = trim($row[1]);
                    $uom_code = trim($row[9]);
                    $gst_rate = trim($row[21]);
                    $category = trim($row[26]);
                    $subcategory = trim($row[27]);
                    if (!empty($item_no))
                    {

                        $res = DB::table('items')->where('item_no', 'LIKE', $item_no)->first();

                        if (!$res) {
                            $inserted = DB::table('items')
                                ->insert([
                                    'item_no' => $item_no,
                                    'item_desc' => $item_desc,
                                    'uom_code' => $uom_code,
                                    'gst_rate' => $gst_rate,
                                    'category' => $category,
                                    'subcategory' => $subcategory,
                                ]);

                            if ($inserted)
                                echo "Item is added $item_no - "  . ' <br />';
                            else
                            echo "Failed to add Item $item_no - "  . ' <br />';

                        } else {
                            echo "Item is already Available " . $item_no . '<br />';
                        }
                    } else {
                        echo "EMPTY Items  $item_no - ". '<br />';
                    }
                    $i++;
                }
            }

        } else {
            echo 'File Not found on path ' . $filePath;
        }
    }

    function updateItemPrice()
    {
        $filePath = public_path('imports/Item Prices _ FortuneArrt Group.xlsx');

        if (file_exists($filePath)) {

            $sheets = Excel::toArray([], $filePath);
            $sheet1Data = $sheets[0];

            // echo "<pre>";
            // print_r($sheet1Data);
            // exit;

            if (count($sheet1Data) > 1) {

                $first = true;
                $i = 0;
                foreach ($sheet1Data as $row) {
                    echo $i;

                    if ($i < 2) {  // Skip First Row
                        $first = false;
                        $i++;
                        continue;
                    }


                    // if ($i == 10) break;

                    $item_no = trim($row[0]);
                    $item_price = trim($row[3]);

                    if (!empty($item_no))
                    {
                        $res = DB::table('items')->where('item_no', 'LIKE', $item_no)->first();

                        if ($res) {
                            $updated = DB::table('items')
                                ->update(['price' => $item_price]);

                            if ($updated)
                                echo "Item is updated $item_no - "  . ' <br />';
                            else
                            echo "Failed to add Item $item_no - "  . ' <br />';

                        } else {
                            echo "Item is not Available " . $item_no . '<br />';
                        }
                    } else {
                        echo "EMPTY Items  $item_no - ". '<br />';
                    }
                    $i++;
                }
            }

        } else {
            echo 'File Not found on path ' . $filePath;
        }
    }

    function updateCustomerCategory()
    {
        $filePath = public_path('imports/Fortuneart_categories 13.8.24 (1).xlsx');

        if (file_exists($filePath)) {

            $sheets = Excel::toArray([], $filePath);
            $sheet1Data = $sheets[0];

            // echo "<pre>";
            // print_r($sheet1Data);
            // exit;

            if (count($sheet1Data) > 1) {

                $first = true;
                $i = 0;
                foreach ($sheet1Data as $row) {

                    if ($i < 2) {  // Skip First Row
                        $first = false;
                        $i++;
                        continue;
                    }
                    // echo 'works';

                    $old_category = trim($row[0]);
                    $new_category = trim($row[2]);
                    $new_subcategory = trim($row[3]);
                    if (!empty($old_category) && !empty($new_category)) {

                        $res = DB::table('sap_customers')->where('u_csubcat', 'LIKE', $old_category)->first();

                        if ($res) {
                            $updated = DB::table('sap_customers')
                                ->where('u_csubcat', 'LIKE', $old_category)
                                ->update([
                                    'new_u_csubcat' => strtoupper($new_category),
                                    'new_u_csubcat2' => strtoupper($new_subcategory)
                                ]);

                            if ($updated)
                                echo "Category updated $old_category - " . $new_category .  " -  $new_subcategory" . ' <br />';
                            else
                                echo  "Already updated CommonNam updated  $old_category - " . $new_category .  " -  $new_subcategory" . ' <br />';

                        } else {
                            echo "NoCategory" . $old_category . '<br />';
                        }
                    } else {
                        echo "EMPTY Category  $old_category - " . $new_category . '<br />';
                    }
                    $i++;
                }
            }

        } else {
            echo 'File Not found on path ' . $filePath;
        }
    }


    function updateCustomerSubcategory()
    {
        $filePath = public_path('imports/Fortuneart_Subcategories -13.08.24 (1).xlsx');

        if (file_exists($filePath)) {

            $sheets = Excel::toArray([], $filePath);
            $sheet1Data = $sheets[0];

            // echo "<pre>";
            // print_r($sheet1Data);
            // exit;

            if (count($sheet1Data) > 1) {

                $first = true;
                $i = 0;
                foreach ($sheet1Data as $row) {

                    if ($i < 2) {  // Skip First Row
                        $first = false;
                        $i++;
                        continue;
                    }
                    // echo 'works';

                    $old_subcategory = trim($row[0]);
                    $new_category = trim($row[2]);
                    $new_subcategory = trim($row[3]);
                    if (!empty($old_subcategory) && !empty($new_category)) {

                        $res = DB::table('sap_customers')->where('u_csubcat2', 'LIKE', $old_subcategory)->first();

                        if ($res) {
                            $updated = DB::table('sap_customers')
                                ->where('u_csubcat2', 'LIKE', $old_subcategory)
                                ->update([
                                    'new_u_csubcat' => strtoupper($new_category),
                                    'new_u_csubcat2' => strtoupper($new_subcategory)
                                ]);

                            if ($updated)
                                echo "SubCategory updated $old_subcategory - " . $new_category .  " -  $new_subcategory" . ' <br />';
                            else
                                echo  "Already updated CommonNam updated  $old_subcategory - " . $new_category .  " -  $new_subcategory" . ' <br />';

                        } else {
                            echo "NoCategory" . $old_subcategory . '<br />';
                        }
                    } else {
                        echo "EMPTY Category  $old_subcategory - " . $new_subcategory . '<br />';
                    }
                    $i++;
                }
            }

        } else {
            echo 'File Not found on path ' . $filePath;
        }
    }

    function updateInactiveCustomersdbel2023()
    {
        $db_name = 'EL2023';
        $filePath = public_path('imports/Inactive Customer Report (12-08-21).xlsx');

        if (file_exists($filePath)) {

            $sheets = Excel::toArray([], $filePath);
            $sheet1Data = $sheets[0];

            // echo "<pre>";
            // print_r($sheet1Data);
            // exit;

            if (count($sheet1Data) > 1) {

                $first = true;
                $i = 0;
                foreach ($sheet1Data as $row) {

                    if ($i < 1) {  // Skip First Row
                        $first = false;
                        $i++;
                        continue;
                    }

                    $card_code = trim($row[0]);

                    if (!empty($card_code) && !empty($db_name))
                    {
                        $res = DB::table('sap_customers')
                                ->where('card_code', 'LIKE', $card_code)
                                ->where('db_name', 'LIKE', $db_name)
                                ->where('is_active_customer', 1)
                                ->first();

                        if ($res) {
                                // echo $card_code. " - $db_name - $i <br >";
                            $update = DB::table('sap_customers')
                                        ->where('id', $res->id)
                                        ->update([
                                            'is_active_customer' => 0
                                        ]);

                            if ($update)
                                echo "Customer Inactive $card_code - " . $db_name . '<br />';
                            else
                                echo  "Failed to Update $card_code - " . $db_name . '<br />';

                        } else {
                            echo "" . $card_code . '<br />';
                        }
                    } else {
                        echo "Empty CardCode or DBName  $card_code - " . $db_name . '<br />';
                    }
                    $i++;

                }
            }

        } else {
            echo 'File Not found on path ' . $filePath;
        }
    }

    function updateInactiveCustomersfaled2023()
    {
        $db_name = 'FALED2023';
        $filePath = public_path('imports/Inactive Customer Report (12-08-21).xlsx');

        if (file_exists($filePath)) {

            $sheets = Excel::toArray([], $filePath);
            $sheet1Data = $sheets[1];

            // echo "<pre>";
            // print_r($sheet1Data);
            // exit;

            if (count($sheet1Data) > 1) {

                $first = true;
                $i = 0;
                foreach ($sheet1Data as $row) {

                    if ($i < 1) {  // Skip First Row
                        $first = false;
                        $i++;
                        continue;
                    }

                    $card_code = trim($row[0]);

                    if (!empty($card_code) && !empty($db_name))
                    {
                        $res = DB::table('sap_customers')
                                ->where('card_code', 'LIKE', $card_code)
                                ->where('db_name', 'LIKE', $db_name)
                                ->where('is_active_customer', 1)
                                ->first();

                        if ($res) {
                                // echo $card_code. " - $db_name - $i <br >";
                            $update = DB::table('sap_customers')
                                        ->where('id', $res->id)
                                        ->update([
                                            'is_active_customer' => 0
                                        ]);

                            if ($update)
                                echo "Customer Inactive $card_code - " . $db_name . '<br />';
                            else
                                echo  "Failed to Update $card_code - " . $db_name . '<br />';

                        } else {
                            echo "" . $card_code . '<br />';
                        }
                    } else {
                        echo "Empty CardCode or DBName  $card_code - " . $db_name . '<br />';
                    }
                    $i++;

                }
            }

        } else {
            echo 'File Not found on path ' . $filePath;
        }
    }

    function updateInactiveCustomersfawcpltd2023()
    {
        $db_name = 'FAWCPLTD2023';
        $filePath = public_path('imports/Inactive Customer Report (12-08-21).xlsx');

        if (file_exists($filePath)) {

            $sheets = Excel::toArray([], $filePath);
            $sheet1Data = $sheets[2];

            // echo "<pre>";
            // print_r($sheet1Data);
            // exit;

            if (count($sheet1Data) > 1) {

                $first = true;
                $i = 0;
                foreach ($sheet1Data as $row) {

                    if ($i < 1) {  // Skip First Row
                        $first = false;
                        $i++;
                        continue;
                    }

                    $card_code = trim($row[0]);

                    if (!empty($card_code) && !empty($db_name))
                    {
                        $res = DB::table('sap_customers')
                                ->where('card_code', 'LIKE', $card_code)
                                ->where('db_name', 'LIKE', $db_name)
                                ->where('is_active_customer', 1)
                                ->first();

                        if ($res) {
                                // echo $card_code. " - $db_name - $i <br >";
                            $update = DB::table('sap_customers')
                                        ->where('id', $res->id)
                                        ->update([
                                            'is_active_customer' => 0
                                        ]);

                            if ($update)
                                echo "Customer Inactive $card_code - " . $db_name . '<br />';
                            else
                                echo  "Failed to Update $card_code - " . $db_name . '<br />';

                        } else {
                            echo "" . $card_code . '<br />';
                        }
                    } else {
                        echo "Empty CardCode or DBName  $card_code - " . $db_name . '<br />';
                    }
                    $i++;

                }
            }

        } else {
            echo 'File Not found on path ' . $filePath;
        }
    }

    function updateInactiveCustomersvpl2023()
    {
        $db_name = 'VPL2023';
        $filePath = public_path('imports/Inactive Customer Report (12-08-21).xlsx');

        if (file_exists($filePath)) {

            $sheets = Excel::toArray([], $filePath);
            $sheet1Data = $sheets[3];

            // echo "<pre>";
            // print_r($sheet1Data);
            // exit;

            if (count($sheet1Data) > 1) {

                $first = true;
                $i = 0;
                foreach ($sheet1Data as $row) {

                    if ($i < 1) {  // Skip First Row
                        $first = false;
                        $i++;
                        continue;
                    }

                    $card_code = trim($row[0]);

                    if (!empty($card_code) && !empty($db_name))
                    {
                        $res = DB::table('sap_customers')
                                ->where('card_code', 'LIKE', $card_code)
                                ->where('db_name', 'LIKE', $db_name)
                                ->where('is_active_customer', 1)
                                ->first();

                        if ($res) {
                                // echo $card_code. " - $db_name - $i <br >";
                            $update = DB::table('sap_customers')
                                        ->where('id', $res->id)
                                        ->update([
                                            'is_active_customer' => 0
                                        ]);

                            if ($update)
                                echo "Customer Inactive $card_code - " . $db_name . '<br />';
                            else
                                echo  "Failed to Update $card_code - " . $db_name . '<br />';

                        } else {
                            echo "" . $card_code . '<br />';
                        }
                    } else {
                        echo "Empty CardCode or DBName  $card_code - " . $db_name . '<br />';
                    }
                    $i++;

                }
            }

        } else {
            echo 'File Not found on path ' . $filePath;
        }
    }

    function createSalesEmployees()
    {
        $filePath = public_path('imports/Fortuneart_saleemployees_email.xls');

        if (file_exists($filePath)) {

            $sheets = Excel::toArray([], $filePath);
            $sheet1Data = $sheets[0];

            // echo "<pre>";
            // print_r($sheet1Data);
            // exit;

            if (count($sheet1Data) > 1) {

                $first = true;
                $i = 0;
                foreach ($sheet1Data as $row) {

                    if ($i < 2) {  // Skip First Row
                        $first = false;
                        $i++;
                        continue;
                    }
                    // echo 'works';

                    $name_sales_emp_name = trim($row[2]);
                    $email_sales_emp_name = trim($row[4]);
                    if (!empty($name_sales_emp_name) && !empty($email_sales_emp_name))
                    {

                        $res = DB::table('users')->where('email', 'LIKE', $email_sales_emp_name)->first();

                        if (!$res) {
                            $insert = DB::table('users')
                                        ->insert([
                                            'name' => $name_sales_emp_name,
                                            'email' => $email_sales_emp_name,
                                            'password' => Hash::make('Fort@2024'),
                                            'status' => 1,
                                            'role' => 3
                                        ]);

                            if ($insert)
                                echo "SaleEmp Created $name_sales_emp_name - " . $email_sales_emp_name . '<br />';
                            else
                                echo  "Failed to Create $name_sales_emp_name - " . $email_sales_emp_name . '<br />';

                        } else {
                            echo "SaleEmp already Exists " . $email_sales_emp_name . '<br />';
                        }
                    } else {
                        echo "EMPTY SalesEmp Email or Name  $name_sales_emp_name - " . $email_sales_emp_name . '<br />';
                    }
                    $i++;

                }
            }

        } else {
            echo 'File Not found on path ' . $filePath;
        }
    }

    function updateManagerSalesEmployees()
    {
        $filePath = public_path('imports/Fortuneart_saleemployees_email.xls');

        if (file_exists($filePath)) {

            $sheets = Excel::toArray([], $filePath);
            $sheet1Data = $sheets[0];

            // echo "<pre>";
            // print_r($sheet1Data);
            // exit;

            if (count($sheet1Data) > 1) {

                $first = true;
                $i = 0;
                foreach ($sheet1Data as $row) {

                    if ($i < 2) {  // Skip First Row
                        $first = false;
                        $i++;
                        continue;
                    }
                    // echo 'works';

                    $emp_email_sales_emp_name = trim($row[4]);
                    $manager_email_sales_emp_name = trim($row[5]);
                    if (!empty($emp_email_sales_emp_name) && !empty($manager_email_sales_emp_name))
                    {
                        $res = DB::table('users')->where('email', 'LIKE', $emp_email_sales_emp_name)->first();
                        if ($res) {
                            $manager = DB::table('users')->where('email', 'LIKE', $manager_email_sales_emp_name)->first();

                            if ($manager) {
                                    $update = DB::table('users')
                                    ->where('id', $res->id)
                                    ->update([
                                        'manager_id' => $manager->id,
                                    ]);

                                    if ($update)
                                        echo "SaleEmp Created $emp_email_sales_emp_name - " . $manager_email_sales_emp_name . '<br />';
                                    else
                                        echo  "Failed to Create $emp_email_sales_emp_name - " . $manager_email_sales_emp_name . '<br />';

                            }

                        } else {
                            echo "No SaleEmp " . $emp_email_sales_emp_name . '<br />';
                        }
                    } else {
                        echo "EMPTY SalesEmp Email or Name  $emp_email_sales_emp_name - " . $manager_email_sales_emp_name . '<br />';
                    }
                    $i++;

                }
            }

        } else {
            echo 'File Not found on path ' . $filePath;
        }
    }


    function updateDuplicatePANCustomers()
    {

        $filePath = public_path('imports/Fortuneart Duplicate Customers.xlsx');

        if (file_exists($filePath)) {

            $sheets = Excel::toArray([], $filePath);
            $sheet1Data = $sheets[0];

            // echo "<pre>";
            // print_r($sheet1Data);
            // exit;

            if (count($sheet1Data) > 1) {

                $first = true;
                $i = 0;
                foreach ($sheet1Data as $row) {

                    if ($i < 2) {  // Skip First Row
                        $first = false;
                        $i++;
                        continue;
                    }
                    echo 'works';

                    $gst_pan = trim($row[2]);
                    $common_name = trim($row[4]);
                    if (!empty($gst_pan) && !empty($common_name)) {
                        $res = DB::table('sap_customers')->where('gst_pan', '=', $gst_pan)->first();

                        if ($res) {
                            $updated = DB::table('sap_customers')
                                ->where('gst_pan', '=', $gst_pan)
                                ->update([
                                    'u_trpbpnm' => $common_name
                                ]);

                            if ($updated)
                                echo "CommonNam updated $gst_pan - " . $common_name . '<br />';
                            else
                                echo  "Alrady updated CommonNam updated $gst_pan - " . $common_name . '<br />';
                        } else {
                            echo "NoPAN " . $gst_pan . '<br />';
                        }
                    } else {
                        echo "EMPTY PAN or Name  $gst_pan - " . $common_name . '<br />';
                    }
                }
            }
        } else {
            echo 'File Not found on path ' . $filePath;
        }
    }

    function updateSalesEmployeesCustomers()
    {


        $filePath = public_path('imports/Fortuneart_saleemployees.xls');

        if (file_exists($filePath)) {

            $sheets = Excel::toArray([], $filePath);
            $sheet1Data = $sheets[0];

            // echo "<pre>";
            // print_r($sheet1Data);
            // exit;

            if (count($sheet1Data) > 1) {

                $first = true;
                $i = 0;
                foreach ($sheet1Data as $row) {

                    if ($i < 2) {  // Skip First Row
                        $first = false;
                        $i++;
                        continue;
                    }
                    // echo 'works';

                    $old_sales_emp_name = trim($row[0]);
                    $new_sales_emp_name = trim($row[2]);
                    if (!empty($old_sales_emp_name) && !empty($new_sales_emp_name)) {

                        $res = DB::table('sap_customers')->where('sales_emp_name', 'LIKE', $old_sales_emp_name)->first();

                        if ($res) {
                            $updated = DB::table('sap_customers')
                                ->where('sales_emp_name', 'LIKE', $old_sales_emp_name)
                                ->update([
                                    'sales_emp_name' => $new_sales_emp_name
                                ]);

                            if ($updated)
                                echo "SaleEmp updated $old_sales_emp_name - " . $new_sales_emp_name . '<br />';
                            else
                                echo  "Alrady updated CommonNam updated $old_sales_emp_name - " . $new_sales_emp_name . '<br />';

                        } else {
                            echo "NoSaleEmp " . $old_sales_emp_name . '<br />';
                        }
                    } else {
                        echo "EMPTY SalesEmp or Name  $old_sales_emp_name - " . $new_sales_emp_name . '<br />';
                    }
                    $i++;

                }
            }

        } else {
            echo 'File Not found on path ' . $filePath;
        }
    }

    function importCategories()
    {
        $filePath = public_path('imports/FA Catergory Sub-Category.xlsx');

        if (file_exists($filePath)) {

            $sheets = Excel::toArray([], $filePath);
            $sheet1Data = $sheets[2];

            // echo "<pre>";
            // print_r($sheet1Data);
            // exit;

            if (count($sheet1Data) > 1) {

                $first = true;
                foreach ($sheet1Data as $row) {

                    if ($first) {  // Skip First Row
                        $first = false;
                        continue;
                    }

                    $category = trim($row[2]);
                    if (!empty($category)) {
                        $row = DB::table('categories')->where('name', 'LIKE', $category)->first();

                        if (!$row) {
                            DB::table('categories')->insert([
                                'name' => $category
                            ]);
                            echo "Category Inserted " . $category . '<br />';
                        } else {
                            echo "Already Exists" . $category . '<br />';
                        }
                    }
                    // print_r($row); exit;
                }
            }
        } else {
            echo 'File Not found on path ' . $filePath;
        }
    }
}
