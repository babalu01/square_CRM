<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PolicyExport implements FromCollection, WithHeadings
{
    protected $policies;

    public function __construct($policies)
    {
        $this->policies = collect($policies);
    }

    public function collection()
    {
        return $this->policies->map(function ($policy, $index) {
            return [
                'SR.NO' => $index + 1,
                'DATE' => isset($policy['created_at']) ? date('Y-m-d', strtotime($policy['created_at'])) : '',
                'POLICY NUMBER' => $policy['policy_number'] ?? '',
                'COMPANY' => $policy['provider'] ?? '',
                'PRODUCT' => $policy['type'] ?? '',
                'MFG YEAR' => $policy['mfg_year'] ?? '',
                'FUEL TYPE' => $policy['fuel_type'] ?? '',
                'GVW/CC' => $policy['gvw_cc'] ?? '',
                'POLICY HOLDER NAME' => $policy['policy_holder_name'] ?? '',
                'OD' => $policy['od'] ?? '',
                'WITHOUT GST' => $policy['premium_amount'] ?? 0 - ($policy['gst'] ?? 0),
                'TOTAL' => $policy['premium_amount'] ?? 0,
                'FROM' => $policy['start_date'] ?? '',
                'TO' => $policy['end_date'] ?? '',
                'REGISTRATION NUMBER' => $policy['registration_number'] ?? '',
                'POLICY TYPE' => $policy['policy_type'] ?? '',
                'AGENT NAME' => $policy['agent_name'] ?? '',
                'BROKER/DIRECT CODE' => $policy['broker_code'] ?? '',
                'MODE OF PAYMENT' => $policy['payment_mode'] ?? '',
                'ID' => $policy['id'] ?? '',
                'PERCENTAGE' => $policy['commission_percentage'] ?? '',
                'COMMISSION' => $policy['commission_amount'] ?? '',
                'TDS' => $policy['tds'] ?? '',
                'FINAL COMMISSION' => $policy['final_commission'] ?? '',
                'DISC %' => $policy['discount_percentage'] ?? '',
                'DISCOUNT' => $policy['discount_amount'] ?? '',
                'PAYMENT' => $policy['payment_amount'] ?? '',
                'CHEQUE NO' => $policy['cheque_no'] ?? '',
                'PAYMENT RECEIVED/NOT' => $policy['payment_received'] ?? '',
                'PROFIT' => $policy['profit'] ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'SR.NO', 'DATE', 'POLICY NUMBER', 'COMPANY', 'PRODUCT', 'MFG YEAR', 'FUEL TYPE', 'GVW/CC',
            'POLICY HOLDER NAME', 'OD', 'WITHOUT GST', 'TOTAL', 'FROM', 'TO', 'REGISTRATION NUMBER',
            'POLICY TYPE', 'AGENT NAME', 'BROKER/DIRECT CODE', 'MODE OF PAYMENT', 'ID', 'PERCENTAGE',
            'COMMISSION', 'TDS', 'FINAL COMMISSION', 'DISC %', 'DISCOUNT', 'PAYMENT', 'CHEQUE NO',
            'PAYMENT RECEIVED/NOT', 'PROFIT'
        ];
    }
}
