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
        return collect($this->invalidPolicies);
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
