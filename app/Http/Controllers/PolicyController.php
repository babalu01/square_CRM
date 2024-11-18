<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Policy;
use App\Models\PolicyDetails;
use App\Models\client;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Exports\PolicyExport;
use App\Exports\InvalidPolicyExport;
use Carbon\Carbon;
use App\Models\VehicleCategory;
use App\Models\PolicyUploadLog;

class PolicyController extends Controller
{
    public function index(Request $request)
    {
        $query = PolicyDetails::query();

        // Role-based filtering
        if (getAuthenticatedUser()->hasRole('client')) {
            $query->where('Partner_Code', getAuthenticatedUser()->id);
        }

        // Search functionality
        if ($request->filled('policy_number')) {
            $query->where('Policy_No', 'LIKE', '%' . $request->policy_number . '%');
        }
        if ($request->filled('type')) {
            $query->where('Business_Type', 'LIKE', '%' . $request->type . '%');
        }
        if ($request->filled('provider')) {
            $query->where('Insurer_Name', 'LIKE', '%' . $request->provider . '%');
        }
        if ($request->filled('premium_amount')) {
            $query->where('TP_Premium', $request->premium_amount);
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
            $query->where('PolicyStartDateTP', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('PolicyEndDateTP', '<=', $request->end_date);
        }

        $policies = $query->orderBy('created_at', 'desc')->paginate(50);

        if ($request->ajax()) {
            return view('policies.partials.table', compact('policies'))->render();
        }

        return view('policies.index', compact('policies'));
    }
    public function create()
    {
        $agents = Client::all();
        return view('policies.create', compact('agents'));
    }

    public function store(Request $request)
    {
        // Validate incoming request data
        dd($request->all());
        $validatedData = $request->validate([
            'CustomerName' => 'required',
            'Partner_Name' => 'required',
            'Partner_Code' => 'required',
            'Insurer_Name' => 'required',
            'Business_Type' => 'required',
            'LOB' => 'required',
            'Product' => 'required',
            'Sub_Product' => 'nullable',
            'Segment' => 'nullable',
            'Plan_Type' => 'required',
            'Class_Name' => 'nullable',
            'Sub_Class' => 'nullable',
            'Vehicle_No' => 'required',
            'Policy_No' => 'required|unique:policy_details',
            'Policy_Issue_Date' => 'required|date',
            'PolicyStartDateTP' => 'required|date',
            'PolicyEndDateTP' => 'required|date|after:PolicyStartDateTP',
            'NCB' => 'nullable|numeric',
            'IDV' => 'required|numeric',
            'Payment_Mode' => 'required',
            'Payment_Towards' => 'required',
            'Payment_Cheque_Ref_No' => 'nullable',
            'GrossPrem' => 'required|numeric',
            'NetPrem' => 'required|numeric',
            'OD_Premium' => 'required|numeric',
            'TP_Premium' => 'required|numeric',
            'LPA_Partner_Payout_OD%' => 'nullable|numeric',
            'LPA_Partner_Payout_OD_Amount' => 'nullable|numeric',
            'LPA_Partner_Payout_Net%' => 'nullable|numeric',
            'LPA_Partner_Payout_Net_Amount' => 'nullable|numeric',
            'LPA_Partner_Total_Amount' => 'nullable|numeric',
            'REMARK' => 'nullable',
            'STATUS' => 'required',
            'is_verified' => 'nullable|boolean',
            'upload_id' => 'nullable|integer',
        ]);

        // Create a new policy record
        $policy = PolicyDetails::create($validatedData);

        // Prepare to export the policy to an Excel file
        $fileName = 'policies.xlsx';
        $filePath = 'public/' . $fileName;

        // Check if the file exists, if so, load the existing policies
        if (Storage::exists($filePath)) {
            $existingPolicies = Excel::toCollection(null, $filePath)->first();
            $policies = $existingPolicies->push($policy);
        } else {
            $policies = collect([$policy]);
        }

        // Store the policies collection to Excel
        Excel::store(new PolicyExport($policies), $fileName, 'public');

        // Redirect to the newly created policy's detail page with success message
        return redirect()->route('policies.show', $policy->id)
            ->with('success', 'Policy created successfully and exported to Excel.');
    }

    public function show(PolicyDetails $policy)
    {
        $currentDate = now();
        $startDate = Carbon::parse($policy->PolicyStartDateTP);
        $endDate = Carbon::parse($policy->PolicyEndDateTP);

        $remainingInterval = $currentDate->diff($endDate);
        $remainingTime = '';

        if ($remainingInterval->y > 0) {
            $remainingTime .= $remainingInterval->y . ' year' . ($remainingInterval->y > 1 ? 's' : '') . ' ';
        }
        if ($remainingInterval->m > 0) {
            $remainingTime .= $remainingInterval->m . ' month' . ($remainingInterval->m > 1 ? 's' : '') . ' ';
        }
        if ($remainingInterval->d > 0) {
            $remainingTime .= $remainingInterval->d . ' day' . ($remainingInterval->d > 1 ? 's' : '');
        }

        $remainingTime = trim($remainingTime);

        $totalDays = $startDate->diffInDays($endDate);

        return view('policies.show', compact('policy', 'remainingTime', 'totalDays'));
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

        $policy->update($validatedData);

        // Update the Excel file
        // $fileName = 'policies.xlsx';
        // $filePath = 'public/' . $fileName;

        // if (Storage::exists($filePath)) {
        //     $existingPolicies = Excel::toCollection(null, $filePath)->first();
        //     $updatedPolicies = $existingPolicies->map(function ($item) use ($policy) {
        //         if ($item['id'] == $policy->id) {
        //             return $policy;
        //         }
        //         return $item;
        //     });
        // } else {
        //     $updatedPolicies = collect([$policy]);
        // }

        // Excel::store(new PolicyExport($updatedPolicies), $fileName, 'public');

        return redirect()->route('policies.show', $policy->id)
            ->with('success', 'Policy updated successfully and Excel file updated.');
    }
    public function importpolicy(){
        return view('policies.importpolicy');
    }

    // Import Clients
//     public function import(Request $request)
//     {
//         $request->validate([
//             'file' => 'required|mimes:xlsx,csv,xls',
//         ]);


        
//         // dd($request->all());



//         $file = $request->file('file');
//         $data = Excel::toArray([], $file);
// dd($data);
//         $validPolicies = [];
//         $invalidPolicies = [];

//         if (!empty($data[0])) {
//             // Skip the header row
//             $rows = array_slice($data[0], 1);

//             foreach ($rows as $index => $row) {
//                 $validator = Validator::make([
//                     'policy_number' => $row[2],
//                     'type' => $row[5],
//                     'provider' => $row[3],
//                     'premium_amount' => $row[12],
//                     'start_date' => $this->excelDateToPhpDate($row[13]),
//                     'end_date' => $this->excelDateToPhpDate($row[14]),
//                     // Add more fields as needed
//                 ], [
//                     'policy_number' => 'required|unique:policies',
//                     'type' => 'required',
//                     'provider' => 'required',
//                     'premium_amount' => 'required|numeric',
//                     'start_date' => 'required|date',
//                     'end_date' => 'required|date|after:start_date',
//                     // Add more validation rules as needed
//                 ]);

//                 if ($validator->fails()) {
//                     $invalidPolicies[] = [
//                         'row' => $row,
//                         'errors' => $validator->errors()->all()
//                     ];
//                 } else {
//                     $validPolicies[] = [
//                         'policy_number' => $row[2],
//                         'type' => $row[5],
//                         'provider' => $row[3],
//                         'premium_amount' => $row[12],
//                         'start_date' => $this->excelDateToPhpDate($row[13]),
//                         'end_date' => $this->excelDateToPhpDate($row[14]),
//                         'status' => 'Active',
//                         'company' => $row[3],
//                         'product' => $row[4],
//                         'mfg_year' => $row[6],
//                         'fuel_type' => $row[7],
//                         'gvw_cc' => $row[8],
//                         'policy_holder_name' => $row[9],
//                         'od' => $row[10],
//                         'without_gst' => $row[11],
//                         'total' => $row[12],
//                         'registration_number' => $row[15],
//                         'policy_type' => $row[16],
//                         'agent_name' =>$this->getAgentId($row[17]),
//                         'broker_direct_code' => $row[18],
//                         'mode_of_payment' => $row[19],
//                         'percentage' => $row[21],
//                         'commission' => $row[22],
//                         'tds' => $row[23],
//                         'final_commission' => $row[24],
//                         'discount_percentage' => $row[25],
//                         'discount' => $row[26],
//                         'payment' => $row[27],
//                         'cheque_no' => $row[28],
//                         'payment_received' => $row[29] === 'RECEIVED' ? $row[12] : 0,
//                         'profit' => $row[30],
//                     ];
//                 }
//             }

//             // Load existing policies or create a new collection
//             $fileName = 'policies.xlsx';
//             $filePath = 'public/' . $fileName;
            
//             if (Storage::exists($filePath)) {
//                 $existingPolicies = Excel::toCollection(null, $filePath)->first();
//             } else {
//                 $existingPolicies = collect();
//             }

//             // Store valid policies
//             foreach ($validPolicies as $policyData) {
//                 $policy = Policy::create($policyData);
//                 $existingPolicies->push($policy);
//             }

//             // Export all policies to Excel
//             Excel::store(new PolicyExport($existingPolicies), $fileName, 'public');

//             // Store invalid policies in an Excel file
//             if (!empty($invalidPolicies)) {
//                 $invalidPoliciesFolder = 'invalid_policies';
//                 $invalidFileName = now()->format('Y-m-d_H-i-s') . '.xlsx';
//                 $invalidFilePath = $invalidPoliciesFolder . '/' . $invalidFileName;
                
//                 // Create the folder if it doesn't exist
//                 if (!Storage::exists('public/' . $invalidPoliciesFolder)) {
//                     Storage::makeDirectory('public/' . $invalidPoliciesFolder);
//                 }

//                 Excel::store(new InvalidPolicyExport($invalidPolicies), $invalidFilePath, 'public');
//             }

//             $message = 'Policies imported successfully. ';
//             $message .= count($validPolicies) . ' valid policies imported. ';
//             $message .= count($invalidPolicies) . ' invalid policies found.';

//             if (!empty($invalidPolicies)) {
//                 $message .= ' Invalid policies stored in Excel file. ';
//                 $message .= '<a href="' . route('policies.invalid') . '">View Invalid Policies</a>';
//             }

// // upload pdf files
// $uploadpolicypdf = new PolicyDocumentController();
// $uploadpolicypdf->upload($request);
// // upload pdf files
// // dd('test');







//             return redirect()->back()->with('success', $message);
//         }

//         return redirect()->back()->with('error', 'No data found in the imported file.');
//     }

   // Import Clients
   public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,csv,xls',
    ]);
    $upload_id = rand(100000, 999999); // Generate a random number as upload ID
    $file = $request->file('file');

    $data = Excel::toArray([], $file);
    $uploadLog = new PolicyUploadLog();
    $uploadLog->upload_id = $upload_id; // Create a unique upload ID
    $uploadLog->user_id = auth()->id(); // Get the authenticated user's ID
  
    $uploadLog->save(); // Save the log entry

    $validPolicies = [];
    $invalidPolicies = [];

    if (!empty($data[0])) {
        // Skip the header row
        $rows = array_slice($data[0], 1);

        foreach ($rows as $index => $row) {
            $validator = Validator::make([
                'policy_number' => $row[13], // Policy_No
                'vehicle_no' => $row[12], // Vehicle_No
            ], [
                'policy_number' => 'required|unique:policy_details,Policy_No', // Policy_No
                'vehicle_no' => 'required|unique:policy_details,Vehicle_No', // Vehicle_No
            ]);

            if ($validator->fails()) {
                $invalidPolicies[] = [
                    'row' => $row,
                    'errors' => $validator->errors()->all()
                ];
            } else {
                $validPolicies[] = [
                    'CustomerName' => $row[0],
                    'Partner_Name' => $row[1],
                    'Partner_Code' =>$this->getAgentId($row[2]), // Assuming Partner_Code is present in the dataset
                    'Insurer_Name' => $row[3],
                    'Business_Type' => $row[4],
                    'LOB' => $row[5],
                    'Product' => $row[6],
                    'Sub_Product' => $row[7],
                    'Segment' => $row[8],
                    'Plan_Type' => $row[9],
                    'Class_Name' => $row[10], // If needed
                    'Sub_Class' => $row[11], // If needed
                    'Vehicle_No' => $row[12],
                    'Policy_No' => $row[13],
                    'Policy_Issue_Date' => $this->excelDateToPhpDate($row[14]),
                    'PolicyStartDateTP' => $this->excelDateToPhpDate($row[15]),
                    'PolicyEndDateTP' => $this->excelDateToPhpDate($row[16]),
                    'NCB' => $row[17],
                    'IDV' => $row[18],
                    'Payment_Mode' => $row[19],
                    'Payment_Towards' => $row[20],
                    'Payment_Cheque_Ref_No' => $row[21],
                    'GrossPrem' => $row[22],
                    'NetPrem' => $row[23],
                    'OD_Premium' => $row[24],
                    'TP_Premium' => $row[25],
                    'LPA_Partner_Payout_OD%' => $row[26],
                    'LPA_Partner_Payout_OD_Amount' => $row[27],
                    'LPA_Partner_Payout_Net%' => $row[28],
                    'LPA_Partner_Payout_Net_Amount' => $row[29],
                    'LPA_Partner_Total_Amount' => $row[30],
                    'REMARK' => $row[31],
                    'STATUS' => 'Active', // You can set the default status
                    'is_verified' => 0, // Assuming 0 for unverified
                    'upload_id' => $upload_id, // Add the upload ID to the policy details
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Store valid policies to database
        foreach ($validPolicies as $policyData) {
            PolicyDetails::create($policyData);
        }

        // Export all valid policies
        $fileName = 'policies.xlsx';
        $filePath = 'public/' . $fileName;

        Excel::store(new PolicyExport($validPolicies), $filePath, 'public');

        // Store invalid policies to Excel file
        if (!empty($invalidPolicies)) {
            $invalidPoliciesFolder = 'invalid_policies';
            $invalidFileName = now()->format('Y-m-d_H-i-s') . '.xlsx';
            $invalidFilePath = $invalidPoliciesFolder . '/' . $invalidFileName;

            // Create the folder if it doesn't exist
            if (!Storage::exists('public/' . $invalidPoliciesFolder)) {
                Storage::makeDirectory('public/' . $invalidPoliciesFolder);
            }

            Excel::store(new InvalidPolicyExport($invalidPolicies), $invalidFilePath, 'public');
        }

        $message = 'Policies imported successfully. ';
        $message .= count($validPolicies) . ' valid policies imported. ';
        $message .= count($invalidPolicies) . ' invalid policies found.';

        if (!empty($invalidPolicies)) {
            $message .= ' Invalid policies stored in Excel file. ';
            $message .= '<a href="' . route('policies.invalid') . '">View Invalid Policies</a>';
        }

        // Upload PDF files (if needed)
        $uploadPolicyPdf = new PolicyDocumentController();
        $uploadPolicyPdf->upload($request,$upload_id);
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
    private function getAgentId($agentid)
    {
       
        $agent = Client::where('client_id', $agentid)->first();
     
        return $agent ? $agent->id : null;
    }
// invalid policies Page

public function showInvalidPolicies()
{
    $directory = 'invalid_policies';
    $files = Storage::files('public/' . $directory);
    
    $invalidPolicies = collect($files)->map(function ($file) use ($directory) {
        return [
            'name' => basename($file),
            'path' => Storage::url($file),
            'date' => Storage::lastModified($file),
        ];
    })->sortByDesc('date');

    return view('policies.invalid', compact('invalidPolicies'));
}

// featch products by company
public function getProductsByCompany(Request $request)
{
    // dd($request->all());
    $companyName = $request->query('company');
    $products = VehicleCategory::where('company_name', $companyName)->get(['id', 'name']); // Adjust fields as necessary
    return response()->json($products);
}





}

