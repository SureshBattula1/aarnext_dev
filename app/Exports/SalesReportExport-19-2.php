<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SalesReportExport implements FromArray, WithHeadings, WithColumnFormatting
{
    protected $data;
    protected $totalAmount;
    protected $paidAmountTotal;
    protected $balanceAmountTotal;

    public function __construct($data, $totalAmount, $paidAmountTotal, $balanceAmountTotal)
    {
        $this->data = $data;
        $this->totalAmount = $totalAmount;
        $this->paidAmountTotal = $paidAmountTotal;
        $this->balanceAmountTotal = $balanceAmountTotal;
    }

    /**
     * Return data as an array for export.
     */
    public function array(): array
    {
        $rows = array_map(function ($item) {
            //echo ""; print_r($item); exit;
            $status = '';
            if ($item->status == 0) {
                $status = 'Pending';
            } elseif ($item->status == 1) {
                $status = 'Completed';
            }
            return [
                'Doc Number' =>  $item->doc_num ?? '', 
                'Customer ID' => $item->customer_id ?? '',
                'Customer Name' => $item->card_name ?? '',
                'Sales Type' => $item->sales_type ?? '',
                'Order Date' => $item->order_date ?? '',
                'Total' => number_format($item->total, 2),
                'Paid Amount' => number_format($item->paid_amount, 2),
                'Balance Amount' => number_format($item->balance_amount, 2), 
                'Status' => $status,
                'User Name' => $item->user_name ?? '',
            ];
        }, $this->data);
    
        // Add a summary row
        $rows[] = [
            'Doc Number' => '',
            'Customer ID' => '',
            'Customer Name' => '',
            'Sales Type' => '',
            'Order Date' => '',
            'Total' => 'Total: ' . number_format($this->totalAmount, 2),
            'Paid Amount' => 'Total: ' . number_format($this->paidAmountTotal, 2),
            'Balance Amount' => 'Total: ' . number_format($this->balanceAmountTotal, 2),
            'Status' => '',
            'User Name' => '',
        ];
    
        return $rows;
    }

    /**
     * Define column headings.
     */
    public function headings(): array
    {
        return [
            'Doc Number', 
            'Customer ID', 
            'Customer Name',
            'Sales Type', 
            'Order Date', 
            'Total',
            'Paid Amount',
            'Balance Amount', 
            'Status',
            'User Name'
        ];
    }

   
    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_00, // Column F for Total
            'G' => NumberFormat::FORMAT_NUMBER_00, // Column G for Paid Amount
            'H' => NumberFormat::FORMAT_NUMBER_00, // Column H for Balance Amount
        ];
    }
}
