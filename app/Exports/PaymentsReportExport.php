<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PaymentsReportExport implements FromArray, WithHeadings, WithColumnFormatting
{
    protected $data;
    protected $totalAmount;
    protected $cashAmount;
    protected $bankAmount;
    protected $tpaAmount;

    public function __construct($data, $totalAmount, $cashAmount, $bankAmount, $tpaAmount)
    {
        $this->data = $data;
        $this->totalAmount = $totalAmount;
        $this->cashAmount = $cashAmount;
        $this->bankAmount = $bankAmount;
        $this->tpaAmount = $tpaAmount;
    }

    public function array(): array
    {
        $rows = array_map(function ($item) {
            // Format the data correctly for Cash, Bank, and TPA
            $cash = $item->cash ?? 0;
            $bank = ($item->bank_tpa == 'bank') ? $item->online : 0;  // Bank gets online if bank_tpa is 'bank'
            $tpa = ($item->bank_tpa == 'tpa') ? $item->online : 0;      // TPA gets online if bank_tpa is 'tpa'

            return [
                'Customer Code' => $item->customer_code ?? '',
                'Customer Name' => $item->card_name ?? '',
                'Payment Method' => $item->payment_method ?? '',
                'Payment Type' => $item->payment_type ?? '',
                'Total Amount' => number_format($item->total_amount, 2),
                //'Excess Amount' => number_format($item->excess_amount, 2),
                'Cash' => number_format($cash, 2),
                'Bank' => number_format($bank, 2),
                'TPA' => number_format($tpa, 2),
                'Created At' => $item->created_at ?? '',
                'Sub Group' => $item->sub_grp ?? '',
                'User Name' => $item->user_name ?? '',
                'Warehouse' => $item->ware_house ?? '',
            ];
        }, $this->data);

        // Add a summary row with total amounts
        $rows[] = [
            'Customer Code' => '',
            'Customer Name' => '',
            'Payment Method' => '',
            'Payment Type' => '',
            'Total Amount' => round($this->totalAmount, 2),
            'Cash' => round($this->cashAmount, 2),
            'Bank' => round($this->bankAmount, 2),
            'TPA' => round($this->tpaAmount, 2),
            'Created At' => '',
            'Sub Group' => '',
            'User Name' => '',
            'Warehouse' => '',
        ];

        return $rows;
    }

    /**
     * Define column headings.
     */
    public function headings(): array
    {
        return [
            'Customer Code', 'Customer Name', 'Payment Method', 'Payment Type', 
            'Total Amount',  'Cash', 'Bank', 'TPA', 
            'Created At', 'Sub Group', 'User Name', 'Warehouse'
        ];
    }

    public function columnFormats(): array
    {
        return [
            
        ];
    }
}
