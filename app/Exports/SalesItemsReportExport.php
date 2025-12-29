<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SalesItemsReportExport implements FromArray, WithHeadings, WithColumnFormatting
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = array_map(function ($item) {
            $status = '';
            if ($item->status == 0) {
                $status = 'Pending';
            } elseif ($item->status == 1) {
                $status = 'Completed';
            }
            //echo"suresh"; print_r($item); exit;

            return [
               'Item Code' =>  $item->item_no ?? '',               // Item Code
               'Item Description' => $item->item_desc ?? '',        // Item Description
               'Quantity' => $item->quantity ?? 0,                 // Quantity
               'Unit Price' => $item->price ?? 0.0,             // Unit Price
               'Discount Percent' => $item->disc_percent ?? 0,       // Discount Percent
               'Tax Percent' =>  $item->gst_rate ?? 0,            // Tax Percent
               'Item Total' => $item->item_total ?? 0,             // Item Total
               'Document Number' => $item->doc_number ?? '',         // Document Number
               'Order Date' => $item->order_date ?? '',              // Order Date
               'Customer Name' => $item->customer_name ?? '',           // Customer Name
               'Sub Group' => $item->sub_grp ?? '',               // Sub Group
               'Status' => $status,                              // Status
               'User Name' => $item->user_name ?? '',               // User Name
            ];
        }, $this->data);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Item Code',
            'Item Description',
            'Quantity',
            'Unit Price',
            'Discount Percent',
            'Tax Percent',
            'Item Total',
            'Document Number',
            'Order Date',
            'Customer Name',
            'Sub Group',
            'Status',
            'User Name'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER, // Quantity
            //'D' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Unit Price
            //'E' => NumberFormat::FORMAT_PERCENTAGE_00, // Discount Percent
            //'F' => NumberFormat::FORMAT_PERCENTAGE_00, // Tax Percent
            //'G' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Item Total
        ];
    }
}
