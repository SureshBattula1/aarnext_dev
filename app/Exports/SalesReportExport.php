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
    // Initialize total variables outside the loop
    $totalCash = 0;
    $totalCashCredit = 0;
    $totalCredit = 0;
    $totalHospital = 0;

    // Process data and accumulate totals
    $rows = array_map(function ($item) use (&$totalCash, &$totalCashCredit, &$totalCredit, &$totalHospital) {
        // Determine status
        $status = ($item->status == 0) ? 'Pending' : (($item->status == 1) ? 'Completed' : '');

        // Accumulate totals
        switch ($item->sales_type) {
            case 'cash':
                $totalCash += $item->total;
                break;
            case 'cash/credit':
                $totalCashCredit += $item->total;
                break;
            case 'credit':
                $totalCredit += $item->total;
                break;
            case 'hospital':
                $totalHospital += $item->total;
                break;
        }

        return [
            'Doc Number'    => $item->doc_num ?? '',
            'Customer ID'   => $item->customer_id ?? '',
            'Customer Name' => $item->card_name ?? '',
            'Sales Type'    => $item->sales_type ?? '',
            'Order Date'    => $item->order_date ?? '',
            'Total'         => number_format($item->total, 2),
            'Paid Amount'   => number_format($item->paid_amount, 2),
            'Balance Amount'=> number_format($item->balance_amount, 2),
            'Status'        => $status,
            'User Name'     => $item->user_name ?? '',
            'Cash'          => ($item->sales_type == 'cash') ? number_format($item->total, 2) : '',
            'Cash/Credit'   => ($item->sales_type == 'cash/credit') ? number_format($item->total, 2) : '',
            'Credit'        => ($item->sales_type == 'credit') ? number_format($item->total, 2) : '',
            'Hospital'      => ($item->sales_type == 'hospital') ? number_format($item->total, 2) : '',
            'Memo'          => $item->memo ?? '',
        ];
    }, $this->data);

    // Add a summary row
    $rows[] = [
        'Doc Number'    => '',
        'Customer ID'   => '',
        'Customer Name' => '',
        'Sales Type'    => '',
        'Order Date'    => '',
        'Total'         => number_format($this->totalAmount, 2),
        'Paid Amount'   => number_format($this->paidAmountTotal, 2),
        'Balance Amount'=> number_format($this->balanceAmountTotal, 2),
        'Status'        => '',
        'User Name'     => '',
        'Cash'          => number_format($totalCash, 2),
        'Cash/Credit'   => number_format($totalCashCredit, 2),
        'Credit'        => number_format($totalCredit, 2),
        'Hospital'      => number_format($totalHospital, 2),
        'Memo'          => '',
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
            'User Name',
            'Cash',
            'Cash/Credit',
            'Credit',
            'Hospital',
            'Memo',

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
