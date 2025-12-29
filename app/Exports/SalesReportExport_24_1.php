<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SalesReportExport implements FromArray, WithHeadings, WithColumnFormatting
{
    protected $data;
    protected $totalAmount;

    public function __construct($data, $totalAmount)
    {
        $this->data = $data;
        $this->totalAmount = $totalAmount;
    }

    /**
     * Return data as an array for export.
     */
    public function array(): array
    {
        // Map the data to the required format
        $rows = array_map(function ($item) {
            //return [
            //    'ID' => $item['id'] ?? '',
            //    'Day Series' => $item['day_series'] ?? '', 
            //    'Customer ID' => $item['customer_id'],
            //    'Sales Type' => $item['sales_type'],
            //    'Order Date' => $item['order_date'],
            //    'Total' => number_format($item['total'], 2), 
            //    'Status' => $item['status'],
            //];
            return [
                'ID' => $item->id ?? '', 
                'Day Series' => $item->day_series ?? '', 
                'Customer ID' => $item->customer_id,
                'Sales Type' => $item->sales_type,
                'Order Date' => $item->order_date,
                'Total' => number_format($item->total, 2), 
                'Status' => $item->status,
            ];
        }, $this->data);
    
        $rows[] = [
            'ID' => '',
            'Day Series' => '',
            'Customer ID' => '',
            'Sales Type' => '',
            'Order Date' => '',
            'Total' => 'Total: ' . number_format($this->totalAmount, 2),
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
            'ID',
            'Day Series',
            'Customer ID',
            'Sales Type',
            'Order Date',
            'Total',
            'Status',
        ];
    }

    /**
     * Format the "Total" column as a number.
     */
    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_00, // Column F for Total
        ];
    }
}