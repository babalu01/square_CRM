<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvalidPolicyExport implements FromCollection, WithHeadings
{
    protected $invalidPolicies;

    public function __construct($invalidPolicies)
    {
        $this->invalidPolicies = $invalidPolicies;
    }

    public function collection()
    {
        return collect($this->invalidPolicies)->map(function ($item) {
            return [
                $item['row'][2] ?? '', // Policy Number
                $item['row'][5] ?? '', // Type
                $item['row'][3] ?? '', // Provider
                $item['row'][12] ?? '', // Premium Amount
                $item['row'][13] ?? '', // Start Date
                $item['row'][14] ?? '', // End Date
                implode(', ', $item['errors']) // Errors
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Policy Number',
            'Type',
            'Provider',
            'Premium Amount',
            'Start Date',
            'End Date',
            'Errors'
        ];
    }
}
