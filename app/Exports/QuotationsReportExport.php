<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class QuotationsReportExport implements FromArray, WithHeadings, WithColumnFormatting
{
    protected $data;
    // Constructor to accept data
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Return data as an array for export.
     */
    public function array(): array
    {
        $rows = array_map(function ($item) {
            // Map each item to a row for the export
            $status = '';
            if ($item->status == 0) {
                $status = 'Pending';
            } elseif ($item->status == 1) {
                $status = 'Completed';
            }

            return [
                'Doc Number' => $item->doc_num ?? '', 
                'Customer ID' => $item->customer_code ?? '',
                'Customer Name' => $item->customer_name ?? '',
                'Sales Type' => $item->sales_type ?? '',
                'Order Date' => $item->date ?? '',
                'Total' => number_format($item->total, 2),
                'User Name' => $item->user_name ?? '',
                'Sub Group' => $item->sub_grp ?? '',
                'Sales Employee Name' => $item->sales_emp_name ?? '',
                'Status' => $status,
            ];
        }, $this->data);

        // Add a summary row at the end
        $totalSum = array_sum(array_column($this->data, 'total'));
        $rows[] = [
            'Doc Number' => 'Total',
            'Customer ID' => '',
            'Customer Name' => '',
            'Sales Type' => '',
            'Order Date' => '',
            'Total' => number_format($totalSum, 2),
            'User Name' => '',
            'Sub Group' => '',
            'Sales Employee Name' => '',
            'Status' => '',
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
            'User Name',
            'Sub Group',
            'Sales Employee Name',
            'Status',
        ];
    }

    /**
     * Define column formats.
     */
    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_00, 
        ];
    }
}
