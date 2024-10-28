<?php

namespace App\Exports;

use App\Models\Policy;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Policy::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Policy Number',
            'Type',
            'Provider',
            'Premium Amount',
            'Start Date',
            'End Date',
            'Status',
            'Created At',
            'Updated At',
            'Company',
            'Product',
            'Manufacturing Year',
            'Fuel Type',
            'GVW/CC',
            'Policy Holder Name',
            'OD',
            'Without GST',
            'Total',
            'Registration Number',
            'Policy Type',
            'Agent Name',
            'Broker Direct Code',
            'Mode of Payment',
            'Percentage',
            'Commission',
            'TDS',
            'Final Commission',
            'Discount Percentage',
            'Discount',
            'Payment',
            'Cheque No',
            'Payment Received',
            'Profit'
        ];
    }
}
