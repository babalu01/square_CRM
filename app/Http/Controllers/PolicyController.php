<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Policy;
use App\Models\client;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Exports\PolicyExport;
use App\Exports\InvalidPolicyExport;
class PolicyController extends Controller
{
    public function index(Request $request)
    {
        $query = Policy::query();

        // Role-based filtering
        if (getAuthenticatedUser()->hasRole('client')) {
            $query->where('agent_name', getAuthenticatedUser()->id);
        }

        // Search functionality
        if ($request->filled('policy_number')) {
            $query->where('policy_number', 'LIKE', '%' . $request->policy_number . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', 'LIKE', '%' . $request->type . '%');
        }
        if ($request->filled('provider')) {
            $query->where('provider', 'LIKE', '%' . $request->provider . '%');
        }
        if ($request->filled('premium_amount')) {
            $query->where('premium_amount', $request->premium_amount);
        }
        if ($request->filled('coverage_details')) {
            $query->where(function($q) use ($request) {
                $columns = ['policy_number', 'type', 'provider', 'policy_holder_name', 'registration_number'];
                foreach ($columns as $column) {
                    $q->orWhere($column, 'LIKE', '%' . $request->coverage_details . '%');
                }
            });
        }
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        $policies = $query->orderBy('created_at', 'desc')->paginate(50);

        if ($request->ajax()) {
            return view('policies.partials.table', compact('policies'))->render();
        }

        return view('policies.index', compact('policies'));
    }
    public function create()
    {
        $agents=Client::all();
        return view('policies.create',compact('agents'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'policy_number' => 'required|unique:policies',
            'type' => 'required',
            'provider' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required',
            'company' => 'nullable',
            'product' => 'nullable',
            'mfg_year' => 'nullable|integer',
            'fuel_type' => 'nullable',
            'gvw_cc' => 'nullable',
            'policy_holder_name' => 'nullable',
            'od' => 'nullable|numeric',
            'without_gst' => 'nullable|numeric',
            'total' => 'required|numeric',
            'registration_number' => 'nullable',
            'policy_type' => 'nullable',
            'agent_name' => 'nullable',
            'broker_direct_code' => 'nullable',
            'mode_of_payment' => 'nullable',
            'percentage' => 'nullable|numeric',
            'commission' => 'nullable|numeric',
            'tds' => 'nullable|numeric',
            'final_commission' => 'nullable|numeric',
            'discount_percentage' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'payment' => 'nullable|numeric',
            'cheque_no' => 'nullable',
            'payment_received' => 'nullable|numeric',
            'profit' => 'nullable|numeric',
        ]);

        $validatedData['premium_amount'] = $request->total;

        $policy = Policy::create($validatedData);

        // Export the new policy to Excel
        $fileName = 'policies.xlsx';
        $filePath = 'public/' . $fileName;

        if (Storage::exists($filePath)) {
            $existingPolicies = Excel::toCollection(null, $filePath)->first();
            $policies = $existingPolicies->push($policy);
        } else {
            $policies = collect([$policy]);
        }

        Excel::store(new PolicyExport($policies), $fileName, 'public');

        return redirect()->route('policies.show', $policy->id)
            ->with('success', 'Policy created successfully and exported to Excel.');
    }

    public function show(Policy $policy)
    {
        return view('policies.show', compact('policy'));
    }

    public function edit(Policy $policy)
    {
        $agents=Client::all();

        return view('policies.edit', compact('policy','agents'));
    }

    public function update(Request $request, Policy $policy)
    {
        $validatedData = $request->validate([
            'policy_number' => 'required|unique:policies,policy_number,' . $policy->id,
            'type' => 'required',
            'provider' => 'required',
            'premium_amount' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required',
        ]);

        $policy->update($validatedData);

        return redirect()->route('policies.show', $policy->id)->with('success', 'Policy updated successfully.');
    }
    public function importpolicy(){
        return view('policies.importpolicy');
    }

    // Import Clients
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls',
        ]);

        $file = $request->file('file');
        $data = Excel::toArray([], $file);

        $validPolicies = [];
        $invalidPolicies = [];

        if (!empty($data[0])) {
            // Skip the header row
            $rows = array_slice($data[0], 1);

            foreach ($rows as $index => $row) {
                $validator = Validator::make([
                    'policy_number' => $row[2],
                    'type' => $row[5],
                    'provider' => $row[3],
                    'premium_amount' => $row[12],
                    'start_date' => $this->excelDateToPhpDate($row[13]),
                    'end_date' => $this->excelDateToPhpDate($row[14]),
                    // Add more fields as needed
                ], [
                    'policy_number' => 'required|unique:policies',
                    'type' => 'required',
                    'provider' => 'required',
                    'premium_amount' => 'required|numeric',
                    'start_date' => 'required|date',
                    'end_date' => 'required|date|after:start_date',
                    // Add more validation rules as needed
                ]);

                if ($validator->fails()) {
                    $invalidPolicies[] = [
                        'row' => $row,
                        'errors' => $validator->errors()->all()
                    ];
                } else {
                    $validPolicies[] = [
                        'policy_number' => $row[2],
                        'type' => $row[5],
                        'provider' => $row[3],
                        'premium_amount' => $row[12],
                        'start_date' => $this->excelDateToPhpDate($row[13]),
                        'end_date' => $this->excelDateToPhpDate($row[14]),
                        'status' => 'Active',
                        'company' => $row[3],
                        'product' => $row[4],
                        'mfg_year' => $row[6],
                        'fuel_type' => $row[7],
                        'gvw_cc' => $row[8],
                        'policy_holder_name' => $row[9],
                        'od' => $row[10],
                        'without_gst' => $row[11],
                        'total' => $row[12],
                        'registration_number' => $row[15],
                        'policy_type' => $row[16],
                        'agent_name' =>$this->getAgentId($row[17]),
                        'broker_direct_code' => $row[18],
                        'mode_of_payment' => $row[19],
                        'percentage' => $row[21],
                        'commission' => $row[22],
                        'tds' => $row[23],
                        'final_commission' => $row[24],
                        'discount_percentage' => $row[25],
                        'discount' => $row[26],
                        'payment' => $row[27],
                        'cheque_no' => $row[28],
                        'payment_received' => $row[29] === 'RECEIVED' ? $row[12] : 0,
                        'profit' => $row[30],
                    ];
                }
            }

            // Load existing policies or create a new collection
            $fileName = 'policies.xlsx';
            $filePath = 'public/' . $fileName;
            
            if (Storage::exists($filePath)) {
                $existingPolicies = Excel::toCollection(null, $filePath)->first();
            } else {
                $existingPolicies = collect();
            }

            // Store valid policies
            foreach ($validPolicies as $policyData) {
                $policy = Policy::create($policyData);
                $existingPolicies->push($policy);
            }

            // Export all policies to Excel
            Excel::store(new PolicyExport($existingPolicies), $fileName, 'public');

            // Store invalid policies in a JSON file
            if (!empty($invalidPolicies)) {
                $invalidFileName = 'invalid_policies_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
                Excel::store(new InvalidPolicyExport($invalidPolicies), $invalidFileName, 'public');
            }

            $message = 'Policies imported successfully. ';
            $message .= count($validPolicies) . ' valid policies imported. ';
            $message .= count($invalidPolicies) . ' invalid policies found.';

            if (!empty($invalidPolicies)) {
                $message .= ' Invalid policies stored in Excel file.';
            }

            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->with('error', 'No data found in the imported file.');
    }

    private function excelDateToPhpDate($excelDate)
    {
        if (is_numeric($excelDate)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelDate)->format('Y-m-d');
        } else {
            // Attempt to parse the date string
            $dateTime = \DateTime::createFromFormat('Y-m-d', $excelDate);
            if ($dateTime !== false) {
                return $dateTime->format('Y-m-d');
            }
            // If parsing fails, return null or a default date
            return null; // or return a default date like '1970-01-01'
        }
    }
    private function getAgentId($clientName)
    {
        // Split the client name into first name and last name
        $nameParts = explode(' ', $clientName);
        
        if (count($nameParts) < 2) {
            return null; // Return null if there aren't at least two parts
        }
    
        $firstName = $nameParts[0];
        $lastName = $nameParts[1];
    
        // Query the client table for a match
        $agent = Client::where('first_name', 'LIKE', '%' . $firstName . '%')
                       ->where('last_name', 'LIKE', '%' . $lastName . '%')
                       ->first();
        
        return $agent ? $agent->id : null;  
    }
    




}
