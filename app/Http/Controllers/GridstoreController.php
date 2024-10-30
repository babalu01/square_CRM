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

class GridstoreController extends Controller
{
    public function import(Request $request)
    {
        // dd($request->all());
        // Step 1: Validate the file
        $this->validateImportFile($request);
    
        // Step 2: Load data from Excel
        $sheetData = $this->loadExcelData($request->file('file'));
    
        // Step 3: Generate unique upload ID and log import
        $uploadId = (string) \Str::uuid();
        $company = $request->company;

        // Convert client_id array to a comma-separated string
        if(is_array($request->client_id)) {
            $clientIds = implode(',', $request->client_id);
        }else{
            $clientIds = [];
        }
        $this->logGridUpload($uploadId, $company, $clientIds);
    
        // Step 4: Parse and structure data
        $inputData = $this->parseSheetData($sheetData,$company);
    
        // Step 5: Insert data into tables
        $this->saveParsedData($inputData, $uploadId,$company);
    
        return redirect()->back()->with('success', 'Commission rates imported successfully!');
    }
    
    // Step 1: File validation
    private function validateImportFile($request)
    {
        $request->validate(['file' => 'required|mimes:xlsx']);
    }
    
    // Step 2: Load Excel data
    private function loadExcelData($file)
    {
        $path = $file->getRealPath();
        $spreadsheet = (new Xlsx())->load($path);
        return $spreadsheet->getActiveSheet()->toArray();
    }
    
    // Step 3: Log upload entry
    private function logGridUpload($uploadId,$company, $clientId)
    {
        // dd($company);
        return GridUploadLog::create([
            'upload_id' => $uploadId,
            'comany_name' => $company, // Adapt if needed
            'agent_id' => $clientId,
            'created_month' => date('Y-m'),
        ]);
    }
    
    // Step 4: Parse data based on company format
    private function parseSheetData($sheetData, $company)
    {
        $inputData = [];
        // dd($sheetData);
        foreach ($sheetData as $index => $row) {
            // Detect company format and parse data accordingly
            if ($company === "TATA" && $index >= 2) {
                $inputData[] = $this->parseRow($row, $company);
            } elseif ($company === "MAGMA" && $index >= 4) {
                $inputData[] = $this->parseMagmaRow($row);
            } elseif ($company === "SHRIRAM" && $index >= 2) {
                $inputData[] = $this->parseShriramRow($row);
            }elseif ($company === "TW_OTC" && $index >= 2) {
                $inputData[] = $this->parseTwOtcRow($row);
               
            }
        }
        return $inputData;
    }
    private function parseRow($row,$company)
    {
        // Different companies’ parsing logic here
        // if ($this->isPrivateCarFormat($row)) {
        if ($company== 'TATA') {
            return $this->parseTataRow($row);
        }elseif($company == 'MAGMA'){
            return $this->parseMagmaRow($row);
        }     
        elseif ($this->isTataFormat($row)) {
            return $this->parsePrivateCarRow($row);
        }
        // Add more company formats as needed
        return [];
    }
    
    // Step 5: Save parsed data to DB
    private function saveParsedData($inputData, $uploadId,$companyName)
    {
        // dd($inputData);
        foreach ($inputData as $data) {
            $this->saveCommissionRateData($data, $uploadId,$companyName);
        }
    }

    


// Private car data parsing
private function parsePrivateCarRow($row)
{
    // Add private car-specific parsing logic here
    return [
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

// TATA company data parsing
private function parseTataRow($row)
{
    return [
            'region' => $row[0],
            'State_name' => $row[1],
            'circle' => $row[2],
            'vehicle_type'=>[
            'Upto 2.5T GCV including GCV 3W' => [
                'Comp (Net)' => $row[3],
                'SATP (Net)' => $row[4],
            ],
            'Agricultural Tractor & Harvester (excluding Trailer)' => [
                'New' => $row[5],
                'Non New' => $row[6] ?? '',
            ],
            'PCV 3W (Carrying capacity 3+1) Petrol' => [
                'Comp (Net)' => $row[7],
                'SATP (Net)' => $row[8],
            ],
            'PCV 3W (Carrying capacity 3+1) Diesel' => [
                'Comp (Net)' => $row[9],
                'SATP (Net)' => $row[10],
            ],
            'PCV 3W (Carrying capacity 3+1) Other fuel' => [
                'Comp (Net)' => $row[11],
                'SATP (Net)' => $row[12],
            ],
            '12T to 20T (Other makes)' => [
                'Comp (Net)' => $row[13],
                'SATP (Net)' => $row[14],
            ],
            '12T to 20T (TATA & Ashok leyland)' => [
                'Comp (Net)' => $row[15],
                'SATP (Net)' => $row[16],
            ],
            '20T to 40T (Other makes)' => [
                'Comp (Net)' => $row[17],
                'SATP (Net)' => $row[18],
            ],
            '20T to 40T (TATA & Ashok leyland)' => [
                'Comp (Net)' => $row[19],
                'SATP (Net)' => $row[20],
            ],
            '>40T (Other makes)' => [
                'Comp (Net)' => $row[21],
                'SATP (Net)' => $row[22],
            ],
            '>40T (TATA & Ashok leyland)' => [
                'Comp (Net)' => $row[23],
                'SATP (Net)' => $row[24],
            ],
            'School Bus' => [
                'Comp (Net)' => $row[25],
                'SATP (Net)' => $row[26],
            ],
            'PCV Taxi (Carrying capacity 6+1)' => [
                'Comp (Net)' => $row[27],
                'SATP (Net)' => $row[28],
            ],
            'Pvt Car - Above 3 lakhs (Pvt car & 2W has common slabs)' => [
                'Pvt Car (PO On OD) Comp & SAOD' => $row[29],
               
            ],
            'Pvt Car - STP' => [
                'Pvt Car - STP - Petrol & Bifuel < 1500' => $row[31],
                'Pvt Car - STP - Diesel < 1500' => $row[32],
                'Pvt Car STP above 1500 (Diesel)' => $row[33],
                    'Pvt Car STP above 1500 (Petrol & Bifuel)' => $row[33],
                ],
           
            '2 Wheeler (On net for 1 year premium only (1+1))' => [
                'Scooter upto 150 cc (Comp & SAOD)' => $row[34] ?? null,
                'Bike upto 75 cc' => $row[35] ?? null,
                'B.75-150 Bike' => $row[36] ?? null,
                'C. Above 150cc' => $row[37] ?? null,
            ],
            ]
    ];
}

private function parseMagmaRow($row){
    // dd($row);
    return [
        // 'region' => $row[0],
        'State_name' => $row[0],
        'circle' => $row[1],
        'vehicle_type'=>[
        'Pvt Car New Diesel' => [
            'Comp(OD)' => $this->convertPercent($row[2]),
        ],
        'Pvt Car New Petrol' => [
            'Comp(OD)' => $this->convertPercent($row[3]),
        ],
        'Pvt Car Old Diesel & NCB' => [
            'Comp(OD)' => $this->convertPercent($row[4]),
        ],
        'Pvt Car Old Diesel & Zero NCB' => [
            'Comp(OD)' => $this->convertPercent($row[5]),
        ],
        'Pvt Car Old Petrol & NCB' => [
            'Comp(OD)' => $this->convertPercent($row[6]),
        ],
        'Pvt Car Old Petrol & Zero NCB' => [
            'Comp(OD)' => $this->convertPercent($row[7]),
        ],
        'Pvt Car STP < 1000 cc Diesel' => [
            'STP(GWP)' => $this->convertPercent($row[8]),
        ],
        'Pvt Car STP < 1000 cc Petrol' => [
            'STP(GWP)' => $this->convertPercent($row[9]),
        ],
        'Pvt Car STP 1000 cc-1500 cc Diesel' => [
            'STP(GWP)' => $this->convertPercent($row[10]),
        ],
        'Pvt Car STP 1000 cc-1500 cc Petrol' => [
            'STP(GWP)' => $this->convertPercent($row[11]),
        ],
        'Pvt Car STP >1500 cc Diesel' => [
            'STP(GWP)' => $this->convertPercent($row[12]),
        ],
        'Pvt Car STP >1500 cc Petrol' => [
            'STP(GWP)' => $this->convertPercent($row[13]),
        ],
        'PCV 3W - New' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[14]),
        ],
        'PCV 3W - Old' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[15]),
        ],
        'PCV 3W - Electric' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[16]),
        ],
        'PCV School Bus' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[17]),
        ],
        'PCV Other Bus' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[18]),
        ],
        'PCV Taxi' => [
            'Comp(OD) & STP(GWP)' => $this->convertPercent($row[19]),
        ],
        'GCV < 2.5T - Age < 5' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[20]),
        ],
        'GCV < 2.5T - Age >= 5' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[21]),
        ],
        'GCV 2.5-3.5T - Age < 5' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[22]),
        ],
        'GCV 2.5-3.5T - Age >= 5' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[23]),
        ],
        'GCV 3.5-7.5T - Age < 5' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[24]),
        ],
        'GCV 3.5-7.5T - Age >= 5' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[25]),
        ],
        'GCV 7.5 - 12T - Age < 5' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[26]),
        ],
        'GCV 7.5 - 12T - Age >= 5' => [
            'Comp(GWP)' => $this->convertPercent($row[27]),
        ],
        'GCV 7.5 - 12T - Age >= 5' => [
            'STP(GWP)' => $this->convertPercent($row[28]),
        ],
        'GCV 12 - 20T - Age < 5' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[29]),
        ],
        'GCV 12 - 20T - Age >= 5' => [
            'Comp(GWP)' => $this->convertPercent($row[30]),
        ],
        'GCV 12 - 20T - Age >= 5' => [
            'STP(GWP)' => $this->convertPercent($row[31]),
        ],
        'GCV 20 - 40T - Age < 5' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[32]),
        ],
        'GCV 20 - 40T - Age >= 5' => [
            'Comp(GWP)' => $this->convertPercent($row[33]),
        ],
        'GCV 20 - 40T - Age >= 5' => [
            'STP(GWP)' => $this->convertPercent($row[34]),
        ],
        'GCV > 40T - Age < 5' => [
            'Comp(GWP)' => $this->convertPercent($row[35]),
        ],
        'GCV > 40T - Age >= 5' => [
            'Comp(GWP)' => $this->convertPercent($row[36]),
        ],
        'GCV > 40T - Age < 5' => [
            'STP(GWP)' => $this->convertPercent($row[37]),
        ],
        'GCV > 40T - Age >= 5' => [
            'STP(GWP)' => $this->convertPercent($row[38]),
        ],
        'Tractor - New' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[39]),
        ],
        'Tractor - Old' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[40]),
        ],
        'Misc D' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[41]),
        ],
        'Garbage Van' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[42]),
        ],
        '2W Old - <150 cc' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[43]),
        ],
        '2W Old - 150 - 350 cc' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[44]),
        ],
        '2W Old - > 350 cc' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[45]),
        ],
        '2W Old - Scooter' => [
            'Comp(GWP)' => $this->convertPercent($row[46]),
        ],
        '2W Old - Scooter' => [
            'STP(GWP)' => $this->convertPercent($row[47]),
        ],
        '2W New - <150 cc' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[48]),
        ],
        '2W New - 150 - 350 cc' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[49]),
        ],
        '2W New - > 350 cc' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[50]),
        ],
        '2W New - Scooter' => [
            'Comp & STP(GWP)' => $this->convertPercent($row[51]),
        ],
      
    ],
];
}


// shriram company grid
private function parseShriramRow($row)
{
    // dd($row);
    // Add private car-specific parsing logic here
    return [
        // 'region' => $row[1],
        'State_name' => $row[0],
        'circle' => $row[3],
        'vehicle_type'=>[
        $row[1] => [
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

// shriram company grid
// parse row for TW_OTC

private function parseTwOtcRow($row){
    // dd($row);
    $data = [
        'rto_category' => $row[0],
        'State_name' => $row[1],
        'vehicle_type_CV' => [
            'GCV 3W' => $this->convertpercent($row[2]),
            'GCV 3W Electric' => $this->convertpercent($row[3]),
            'SCV <2450 GVW New' => $this->convertpercent($row[4]),
            'SCV <2450 GVW Old' => $this->convertpercent($row[5]),
            'SCV >= 2450 GVW New' => $this->convertpercent($row[6]),
            'SCV >= 2450 GVW Old' => $this->convertpercent($row[7]),
            'LCV 3.5-7.5T' => $this->convertpercent($row[8]),
            'LCV 7.5-12T' => $this->convertpercent($row[9]),
            'MHCV 12-20T Tanker' => $this->convertpercent($row[10]),
            'MHCV 12-20T Tipper' => $this->convertpercent($row[11]),
            'MHCV 12-20T Truck' => $this->convertpercent($row[12]),
            'MHCV 20-40T Tanker' => $this->convertpercent($row[13]),
            'MHCV 20-40T Tipper' => $this->convertpercent($row[14]),
            'MHCV 20-40T Truck' => $this->convertpercent($row[15]),
            'MHCV 20-40T Trailer' => $this->convertpercent($row[16]),
            'MHCV >40T' => $this->convertpercent($row[17]),
            'MIsc D CE (Excluding CRANES)' => $this->convertpercent($row[18]),
            'Tractor New' => $this->convertpercent($row[19]),
            'Tractor Old' => $this->convertpercent($row[20]),
            'PCV 3W Petrol/CNG' => $this->convertpercent($row[21]),
            'PCV 3W Others (Diesel)' => $this->convertpercent($row[22]),
            'PCV 3W Electric' => $this->convertpercent($row[23]),
            'School Bus <18' => $this->convertpercent($row[24]),
            'School Bus 18-36' => $this->convertpercent($row[25]),
            'School Bus >36' => $this->convertpercent($row[26]),
            'Staff Bus >18' => $this->convertpercent($row[27]),
            'PCVTAXI_ELECTRIC' => $this->convertpercent($row[28]),
            'PCVTAXI<=1000CC' => $this->convertpercent($row[29]),
            'PCVTAXI>1000CC' => $this->convertpercent($row[30]),
            'PCV(2W)' => $this->convertpercent($row[31]),
        ],
        'vehicle_type_MHCV' => [
            'LCV 3.5-7.5T' => $this->convertpercent($row[33]),
            'LCV 7.5-12T' => $this->convertpercent($row[34]),
            'MHCV 12-20T Tanker' => $this->convertpercent($row[35]),
            'MHCV 12-20T Tipper' => $this->convertpercent($row[36]),
            'MHCV 12-20T Truck' => $this->convertpercent($row[37]),
            'MHCV 20-40T Tanker' => $this->convertpercent($row[38]),
            'MHCV 20-40T Tipper' => $this->convertpercent($row[39]),
            'MHCV 20-40T Truck' => $this->convertpercent($row[40]),
            'MHCV 20-40T Trailer' => $this->convertpercent($row[41]),
            'MHCV >40T' => $this->convertpercent($row[42]),
            'MIsc D CE (Excluding CRANES)' => $this->convertpercent($row[43]),
        ],
    ];
    return $data;
}
// Parse row for TW_OTC











// Utility to convert percent fields
private function convertPercent($value)
{
    return $value !== null ? (float)str_replace('%', '', $value) : null;
}

// Save Commission Rates to DB
private function saveCommissionRateData($data, $uploadId,$companyName)
{
    // Insert or update region, state, circle, and commission rates as per parsed data
   
    // dd($data);
if($companyName == 'TW_OTC'){
    $state = State::firstOrCreate(['name' => $data['State_name']]);
  
    foreach ($data['vehicle_type_CV'] as $vehicleType => $categories) {
      
        $vehicleCategory = VehicleCategory::firstOrCreate(['name' => $vehicleType ,'company_name'=>$companyName]);
        // dd($vehicleCategory);
      if($categories != null){
            // $section = Section::firstOrCreate(['name' => $sectionKey]);
            DB::table('commission_rates')->insert([
                'state_id' => $state->id,
                'vehicle_category_id' => $vehicleCategory->id,
                // 'section_id' => $section->id,
                'value' => $categories,
                // 'circle_id' => $circle->id,
                'created_month' => date('Y-m'),
                'is_new'=>0,
                'upload_id' => $uploadId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
       
    }
    
    foreach ($data['vehicle_type_MHCV'] as $vehicleType => $categories) {
      
        $vehicleCategory = VehicleCategory::firstOrCreate(['name' => $vehicleType ,'company_name'=>$companyName]);
        // dd($vehicleCategory);
      
            // $section = Section::firstOrCreate(['name' => $sectionKey]);
            if($categories != null){
            DB::table('commission_rates')->insert([
                'state_id' => $state->id,
                'vehicle_category_id' => $vehicleCategory->id,
                // 'section_id' => $section->id,
                'value' => $categories,
                // 'circle_id' => $circle->id,
                'created_month' => date('Y-m'),
                'is_new'=>0,
                'upload_id' => $uploadId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }


}else{



if($data['State_name']){
    if(isset($data['region'])){
    $region = Region::firstOrCreate(['name' => $data['region']]);
    $state = State::firstOrCreate(['name' => $data['State_name'], 'region_id' => $region->id]);
    }else{
        $state = State::firstOrCreate(['name' => $data['State_name']]);
    }
    $circle = Circle::firstOrCreate(['name' => $data['circle']]);
// dd($data);
    foreach ($data['vehicle_type'] as $vehicleType => $categories) {
        $vehicleCategory = VehicleCategory::firstOrCreate(['name' => $vehicleType ,'company_name'=>$companyName]);
        // dd($vehicleCategory);
        foreach ($categories as $sectionKey => $rate) {
           if($rate){
            $section = Section::firstOrCreate(['name' => $sectionKey]);
            DB::table('commission_rates')->insert([
                'state_id' => $state->id,
                'vehicle_category_id' => $vehicleCategory->id,
                'section_id' => $section->id,
                'value' => $rate,
                'circle_id' => $circle->id,
                'created_month' => date('Y-m'),
                'is_new'=>0,
                'upload_id' => $uploadId,
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
