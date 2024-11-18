<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\CommissionRateImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\VehicleCategory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use App\Models\Region;
use App\Models\State;
use App\Models\Circle;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\CommissionRate;
use App\Models\SpecialCondition;
use App\Models\GridUploadLog;
use App\Models\Client;
use App\Models\CommissionPolicy;
use App\Models\PoliciesCompany;


class CommissionRateImportController extends Controller
{
    public function import(Request $request)
    {
        // Validate the file
        $request->validate([
            'file' => 'required|mimes:xlsx'
        ]);

        $file = $request->file('file');
        $data = Excel::toArray([], $file);
        if ($request->hasFile('file')) {
            $path = $request->file('file')->getRealPath();
            $spreadsheet = new Spreadsheet();
            $reader = new Xlsx();
            $spreadsheet = $reader->load($path);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
// dd($sheetData);
$uploadId = (string) \Str::uuid(); // Generate a UUID
$input = []; // Initialize the input array outside the loop
foreach ($sheetData as $index => $row) {
    // dd($sheetData);
    // for private car data
    $imports=0;
if($imports==0){
    if ($index >= 3) {

    $input[] = [ // Use [] to append to the array
        'region' => $row[1],
        'State_name' => $row[2],
        'circle' => $row[3],
        'vehicle_type'=>[
        'Private car- New, SAOD,Comp and Used Car(Points on OD Prem*)' => [
            'Pvt Car New 1+3' => $row[4] !== null ? (float)str_replace('%', '', $row[4]) : 0,
            'Pvt Car Petrol & CNG- 1+1 (NCB Cases)' => $row[5] !== null ? (float)str_replace('%', '', $row[5]) : 0,
            'Pvt Car Diesel & EV - 1+1 (NCB Cases)' => $row[6] !== null ? (float)str_replace('%', '', $row[6]) : 0,
            'SAOD-NCB' => $row[7] !== null ? (float)str_replace('%', '', $row[7]) : 0,
            'Pvt Car-0 NCB ( NON NCB)' => $row[8] !== null ? (float)str_replace('%', '', $row[8]) : 0,
            'Pvt Car (Used Car**)' => $row[9] !== null ? (float)str_replace('%', '', $row[9]) : 0,
        ],
        'Private car AOTP  (Points on Net)' => [
            'Pvt car AOTP- Petrol' => $row[10] !== null ? (float)str_replace('%', '', $row[10]) : 0,
            'Pvt car AOTP- Diesel' => isset($row[11]) ? (float)str_replace('%', '', $row[11]) : 0,
        ],
    ],
    ];
}
}

    // for private car data




    // if ($index >= 2) {
    //     // Collect data for each row
    //     $input[] = [ // Use [] to append to the array
    //         'region' => $row[0],
    //         'State_name' => $row[1],
    //         'circle' => $row[2],
    //         'vehicle_type'=>[
    //         'Upto 2.5T GCV including GCV 3W' => [
    //             'Comp (Net)' => $row[3],
    //             'SATP (Net)' => $row[4],
    //         ],
    //         'Agricultural Tractor & Harvester (excluding Trailer)' => [
    //             'New' => $row[5],
    //             'Non New' => $row[6] ?? '',
    //         ],
    //         'PCV 3W (Carrying capacity 3+1) Petrol' => [
    //             'Comp (Net)' => $row[7],
    //             'SATP (Net)' => $row[8],
    //         ],
    //         'PCV 3W (Carrying capacity 3+1) Diesel' => [
    //             'Comp (Net)' => $row[9],
    //             'SATP (Net)' => $row[10],
    //         ],
    //         'PCV 3W (Carrying capacity 3+1) Other fuel' => [
    //             'Comp (Net)' => $row[11],
    //             'SATP (Net)' => $row[12],
    //         ],
    //         '12T to 20T (Other makes)' => [
    //             'Comp (Net)' => $row[13],
    //             'SATP (Net)' => $row[14],
    //         ],
    //         '12T to 20T (TATA & Ashok leyland)' => [
    //             'Comp (Net)' => $row[15],
    //             'SATP (Net)' => $row[16],
    //         ],
    //         '20T to 40T (Other makes)' => [
    //             'Comp (Net)' => $row[17],
    //             'SATP (Net)' => $row[18],
    //         ],
    //         '20T to 40T (TATA & Ashok leyland)' => [
    //             'Comp (Net)' => $row[19],
    //             'SATP (Net)' => $row[20],
    //         ],
    //         '>40T (Other makes)' => [
    //             'Comp (Net)' => $row[21],
    //             'SATP (Net)' => $row[22],
    //         ],
    //         '>40T (TATA & Ashok leyland)' => [
    //             'Comp (Net)' => $row[23],
    //             'SATP (Net)' => $row[24],
    //         ],
    //         'School Bus' => [
    //             'Comp (Net)' => $row[25],
    //             'SATP (Net)' => $row[26],
    //         ],
    //         'PCV Taxi (Carrying capacity 6+1)' => [
    //             'Comp (Net)' => $row[27],
    //             'SATP (Net)' => $row[28],
    //         ],
    //         'Pvt Car - Above 3 lakhs (Pvt car & 2W has common slabs)' => [
    //             'Pvt Car (PO On OD) Comp & SAOD' => $row[29],
               
    //         ],
    //         'Pvt Car - STP' => [
    //             'Pvt Car - STP - Petrol & Bifuel < 1500' => $row[31],
    //             'Pvt Car - STP - Diesel < 1500' => $row[32],
    //             'Pvt Car STP above 1500 (Diesel)' => $row[33],
    //                 'Pvt Car STP above 1500 (Petrol & Bifuel)' => $row[33],
    //             ],
           
    //         '2 Wheeler (On net for 1 year premium only (1+1))' => [
    //             'Scooter upto 150 cc (Comp & SAOD)' => $row[34] ?? null,
    //             'Bike upto 75 cc' => $row[35] ?? null,
    //             'B.75-150 Bike' => $row[36] ?? null,
    //             'C. Above 150cc' => $row[37] ?? null,
    //         ],
    //         ]
    //     ];
    // }
}
// dd($input);
// Create a new GridUploadLog entry
$gridUploadLog = GridUploadLog::create([
    'upload_id' => $uploadId,
    'comany_name' => 'Default Company', 
    'agent_id'=>$request->client_id ?? "",
    'created_month' => date('Y-m'),
]);

// You might want to add error handling here
if (!$gridUploadLog) {
    return redirect()->back()->with('error', 'Failed to create GridUploadLog.');
}


// dd($input);

foreach($input as $data){
    $region = Region::firstOrCreate(['name' => $data['region']]);
    $state = State::firstOrCreate(
        ['name' => $data['State_name'], 'region_id' => $region->id]
    );
    $circle = Circle::firstOrCreate(['name' => $data['circle']]);
    foreach ($data['vehicle_type'] as $key => $value) {
        $vehicleCategory = VehicleCategory::firstOrCreate(['name' => $key]);
        // Store the data in the database
      foreach($value as $sectionkey =>  $sections){ 
        $sectionid = Section::firstOrCreate(['name' => $sectionkey]);
      if($sections){
        DB::table('commission_rates')->insert([
            'state_id' => $state->id,
            'vehicle_category_id' => $vehicleCategory->id,
            'value' => $sections ,
            'section_id' => $sectionid->id,
            'is_new' => isset($value['New']) ? ($value['New'] !== null ? 1 : 0) : 0,
            'circle_id' => $circle->id,
            'created_month' => date('Y-m'),
            'upload_id' => $uploadId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
      }
    }
}

// Import the file
// Excel::import(new CommissionRateImport, $request->file('file'));

return redirect()->back()->with('success', 'Commission rates imported successfully!');
    }
}
public function showCommissionRates($uploadId)
{
    // dd($uploadId);
    // Initialize colheaders
    // $uploadId=$uploadId; // This line is unnecessary and can be removed
    $colheaders = [
        'vehicle_categories' => [], // This will hold vehicle categories with their sections
    ];
   
    $commissionRates = State::with(['region', 'commissionRates.vehicleCategory', 'commissionRates.section'])
        ->whereHas('commissionRates', function($query) use ($uploadId) {
            $query->where('upload_id', $uploadId); // Filter by upload_id
        })
        ->with('commissionRates.circle') // Eager load the circle relationship
        ->get()
        ->map(function ($state) use (&$colheaders, $uploadId) { // Pass $uploadId to the closure
            // Ensure we only process states with commission rates for the given uploadId
            $rates = $state->commissionRates->where('upload_id', $uploadId);
            if ($rates->isEmpty()) {
                return null; // Skip states without commission rates for the given uploadId
            }
            $circle = $rates->first()->circle;
            return [
                'state_name' => $state->name,
                'region_name' =>$state->region ? $state->region->name : null,
                'Circle' => $circle ? $circle->name : null,
                'vehicle_categories' => $rates->groupBy('vehicle_category_id')
                    ->map(function ($rates, $vehicleCategoryId) use (&$colheaders) {
                        $vehicleCategory = $rates->first()->vehicleCategory;

                        // Initialize sections array for this vehicle category
                        $sections = [];

                        return [
                            'vehicle_category_name' => $vehicleCategory->name,
                            'sections' => $rates->map(function ($rate) use (&$sections) {
                                // Add section_name to the sections array
                                $sectionName = $rate->section ? $rate->section->name : "";

                                // Add the section to the sections array if it doesn't exist
                                if (!in_array($sectionName, $sections)) {
                                    $sections[] = $sectionName;
                                }

                                return [
                                    'section_name' => $sectionName,
                                    'value' => $rate->value,
                                    'commission_rate_id' => $rate->id // Include commission rate ID
                                ];
                            })
                        ];
                    })->values()
            ];
        })->filter(); // Remove null entries

    // Prepare the colheaders structure with vehicle categories and sections
    foreach ($commissionRates as $stateData) {
        foreach ($stateData['vehicle_categories'] as $category) {
            // Ensure that the vehicle category is added to colheaders
            if (!isset($colheaders['vehicle_categories'][$category['vehicle_category_name']])) {
                $colheaders['vehicle_categories'][$category['vehicle_category_name']] = [];
            }
            
            // Add sections for the specific vehicle category
            foreach ($category['sections'] as $section) {
                if (!in_array($section['section_name'], $colheaders['vehicle_categories'][$category['vehicle_category_name']])) {
                    $colheaders['vehicle_categories'][$category['vehicle_category_name']][] = $section['section_name'];
                }
            }
        }
    }

    return view('grid.commission_rates', compact('commissionRates', 'colheaders','uploadId'));
}

public function gridUploadLog()
{
    $user = getAuthenticatedUser();
    
    // Check if the user has the role of 'client'
    if ($user->hasRole('client')) {
        // Fetch grid upload logs where agent_id is the authenticated user's ID
        $gridUploadLog = GridUploadLog::where('agent_id', $user->id)->get();
    } else {
        // Fetch all grid upload logs for other roles
        $gridUploadLog = GridUploadLog::all();
    }

    $clients = Client::all();
    
    return view('grid.grid_upload_log', compact('gridUploadLog', 'clients'));
}

public function updateCommissionRates(request $request)
{
//   dd($request->all()); // Commented out for production
$user = getAuthenticatedUser();
if(getAuthenticatedUser()->hasRole('client') && (getAuthenticatedUser()->hasVerifiedEmail()))
 {
    session()->flash('message', 'You are not allowed to edit commission rates');
    return back();
 }



    foreach($request->commission_rates as $key => $value){
        $commissionRate = CommissionRate::find($key);
        if ($commissionRate) {
            $commissionRate->update(['value' => $value]);
        }
    }
    
    if ($request->commissionrate_ids) {
        foreach ($request->commissionrate_ids as $regionName => $states) {
            $region = Region::firstOrCreate(['name' => $regionName]);
            dd($region);
            foreach ($states as $stateName => $circles) {
                $state = State::firstOrCreate(['name' => $stateName, 'region_id' => $region->id]);
                foreach ($circles as $circleName => $vehicleTypes) {
                    $circle = Circle::firstOrCreate(['name' => $circleName]);
                    foreach ($vehicleTypes['vehicle_type'] as $vehicleTypeName => $values) {
                        $vehicleCategory = VehicleCategory::firstOrCreate(['name' => $vehicleTypeName]);
                        foreach ($values as $sectionName => $value) {
                            $section = Section::firstOrCreate(['name' => $sectionName]);
                            // dd($state);
                            if ($value) {
                                DB::table('commission_rates')->insert([
                                    'state_id' => $state->id,
                                    'vehicle_category_id' => $vehicleCategory->id,
                                    'value' => $value,
                                    'section_id' => $section->id,
                                    'is_new' => 0, // Adjust this logic as needed
                                    'circle_id' => $circle->id,
                                    'created_month' => date('Y-m'),
                                    'upload_id' => $request->upload_id,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }
    // }
    session()->flash('success', 'Commission rates updated successfully.');
    return redirect()->back()->with('success', 'Commission rates updated successfully.');
}
// delete commission rates and logs
public function deleteCommissionRates(Request $request)
{
    // dd($request->all());
    $user = getAuthenticatedUser();
    if (!$user->can('edit_grid')) {
        session()->flash('message', 'You are not allowed to delete commission rates and logs');
        return redirect()->back();
    }

    $uploadId = $request->input('upload_id');
    CommissionPolicy::where('upload_id', $uploadId)->delete();
    gridUploadLog::where('upload_id', $uploadId)->delete();

    session()->flash('success', 'Commission rates and logs deleted successfully.');
    return redirect()->back()->with('success', 'Commission rates and logs deleted successfully.');
}
// for testing purpose magma grid view
public function magmaGridView(Request $request)
{
    // $data = CommissionPolicy::all();
$user = getAuthenticatedUser();
    // Grouping the data
    // $groupedData = $data->groupBy('company_id')->map(function ($companyGroup) {
    //     return $companyGroup->groupBy('state')->map(function ($stateGroup) {
    //         return $stateGroup->groupBy('vehicle_type');
    //     });
    // });
    if(getAuthenticatedUser()->hasRole('client')){

$deductions=getAuthenticatedUser()->gridGroup()->first();
$deduction=(int)$deductions->value;
// dd($deduction);
    }else{
        $deduction=0;
    }
    $uploadalogs = GridUploadLog::get();
    $companies = PoliciesCompany::all();
    $states = State::all();

    return view('grid.magmaview',  compact('companies', 'states','deduction','uploadalogs','user'));
}
public function showmagmagrid(Request $request)
{
    $request->validate([
        'company_id' => 'nullable|exists:policies_company,id',
        'upload_id' => 'nullable|string',
        'state_id' => 'required|exists:states,id',
    ]);
// dd($request->all());
    // featch active upload id
if($request->upload_id){
    $uploadId = $request->upload_id;
}else{
    $uploadId = GridUploadLog::where('comany_name', $request->company_id)->where('activation_status', 1)->first()->upload_id;
}
    // dd($uploadId);
    // featch active upload id
    $commissionPolicies = CommissionPolicy::where('company_id', $request->company_id)->where('upload_id',$uploadId)
        ->where('state', $request->state_id)
        ->join('vehicle_categories', 'commission_policy.vehicle_type', '=', 'vehicle_categories.id') // Join with vehicle_types table
        ->join('states', 'commission_policy.state', '=', 'states.id') // Join with states table
        ->select('commission_policy.*', 'vehicle_categories.name as vehicle_type_name', 'states.name as state_name') // Select necessary fields
        ->get();
// dd ($commissionPolicies);    
    return response()->json($commissionPolicies);
}

public function updatemagmagrid(Request $request)
{
    $request->validate([
        'id' => 'required|integer',
        'amount' => 'required|numeric',
        'basis' => 'required|string',
    ]);
// dd($request->all());
    $policy = CommissionPolicy::find($request->id);
    if ($policy) {
        $policy->amount = $request->amount;
        $policy->basis = $request->basis;
        $policy->save();

        return response()->json(['success' => true, 'message' => 'Policy updated successfully.']);
    }

    return response()->json(['success' => false, 'message' => 'Policy not found.'], 404);
}

// get state by company
public function getStatesByCompany(Request $request)
{
    // dd($request->all());
    $companyId = $request->input('company_id');
    $uploadId = $request->input('upload_id');
    if($companyId){
    // Correct the table name to match the model's table name
    $states = CommissionPolicy::where('company_id', $companyId)
        ->leftJoin('states', 'states.id', '=', 'commission_policy.state') // Use 'commission_policy' instead of 'commission_policies'
        ->leftJoin('regions', 'regions.id', '=', 'states.region_id') // Join with regions table
        ->distinct() // Ensure unique states based on state_id
        ->get(['states.id', 'states.name as state_name', 'regions.name as region_name']) // Fetch both the state ID and state name, and region name
        ->unique('id'); // Ensure unique states by their ID
    }else{
        $states = CommissionPolicy::where('upload_id', $uploadId)
        ->leftJoin('states', 'states.id', '=', 'commission_policy.state') // Use 'commission_policy' instead of 'commission_policies'
        ->leftJoin('regions', 'regions.id', '=', 'states.region_id') // Join with regions table
        ->distinct() // Ensure unique states based on state_id
        ->get(['states.id', 'states.name as state_name', 'regions.name as region_name']) // Fetch both the state ID and state name, and region name
        ->unique('id'); // Ensure unique states by their ID  
    }
    return response()->json($states);
}


// for active and deactive gird status
public function updategridstatus(Request $request)
{
    // Validate the request data
    $request->validate([
        'log_id' => 'required|integer',
        'company_id' => 'required|integer',
    ]);

    // Update the activation status of the grid upload log for the given company_id
    GridUploadLog::where('comany_name', $request->company_id)
        ->where('activation_status', 1)
        ->update(['activation_status' => 0]);

    // Then update the activation status for the requested log_id
    $log = GridUploadLog::find($request->log_id);
    if ($log) {
        $log->activation_status = 1;
        $log->save();

        // Flash a success message to the session
        session()->flash('success', 'Grid status updated successfully.');
        return back();
    }

    // Flash an error message to the session
    session()->flash('error', 'Log not found.');
    return response()->json(['success' => false, 'message' => 'Log not found.'], 404);
}
}
