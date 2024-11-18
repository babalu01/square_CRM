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
class GridstoreController extends Controller
{
    public function import(Request $request)
    {
       
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
     $spreadsheet->getAllSheets(); 
        return $spreadsheet->getActiveSheet()->toArray();
    }
    
    // Step 3: Log upload entry
    private function logGridUpload($uploadId,$company, $clientId)
    {
          // Debugging output
  
    $clientId = is_array($clientId) && count($clientId) === 0 ? null : $clientId;

    // Ensure types (if needed)
    $uploadId = (string)$uploadId; // Ensure it's a string
    $company = (string)$company; // Ensure it's a string
    $clientId = $clientId ? (string)$clientId : null; // Convert if exists
    $companyid = PoliciesCompany::firstOrCreate(['company_name' => $company]);


        return GridUploadLog::create([
            'upload_id' => $uploadId,
            'comany_name' => $companyid->id, // Adapt if needed
            'agent_id' => $clientId,
            'created_month' => date('Y-m'),
        ]);
    }
    
    // Step 4: Parse data based on company format
    private function parseSheetData($sheetData, $company)
    {
        $inputData = [];
    //    dd($sheetData);
        foreach ($sheetData as $index => $row) {
            // Detect company format and parse data accordingly
            // if ($company === "TATA" && $index >= 2) {
            //     $inputData[] = $this->parseRow($row, $company);
            // } else
            if ($company === "MAGMA" && $index >= 4) {
                $inputData[] = $this->parseMagmaRow($row);
            } elseif ($company === "SHRIRAM" && $index >= 2) {
                $inputData[] = $this->parseShriramRow($row);
            }elseif ($company === "TW_OTC" && $index >= 2) {
                $inputData[] = $this->parseTwOtcRow($row);
               
            }elseif ($company === "RELIANCE" && $index >= 4) {
                $inputData[] = $this->parseRelianceRow($row);
               
            }elseif ($company === "SBI" && $index >= 2) {
                $inputData[] = $this->parseSBIrow($row);
               
            }elseif ($company === "TATA" && $index >= 2) {
            
                $inputData[] = $this->parseTATArow($row ,$sheetData[1]);
                
               
            }elseif ($company === "ROYAL" && $index >= 3) {
            
                $inputData[] = $this->parseRoyalrow($row);
                
               
            }elseif ($company === "LIBERTY" && $index >= 3) {
            
                $inputData[] = $this->parseLibertyrow($row);  
            }
        }
    
        return $inputData;
    }
    private function parseRow($row,$company)
    {
        // Different companies’ parsing logic here
        // if ($this->isPrivateCarFormat($row)) {
        if ($company== 'SBI') {
            return $this->parseSBIrow($row);
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
// private function parseSBIrow($row)
// {
//     return [
//             'region' => $row[0],
//             'State_name' => $row[1],
//             'circle' => $row[2],
//             'vehicle_type'=>[
//             'Upto 2.5T GCV including GCV 3W' => [
//                 'Comp (Net)' => $row[3],
//                 'SATP (Net)' => $row[4],
//             ],
//             'Agricultural Tractor & Harvester (excluding Trailer)' => [
//                 'New' => $row[5],
//                 'Non New' => $row[6] ?? '',
//             ],
//             'PCV 3W (Carrying capacity 3+1) Petrol' => [
//                 'Comp (Net)' => $row[7],
//                 'SATP (Net)' => $row[8],
//             ],
//             'PCV 3W (Carrying capacity 3+1) Diesel' => [
//                 'Comp (Net)' => $row[9],
//                 'SATP (Net)' => $row[10],
//             ],
//             'PCV 3W (Carrying capacity 3+1) Other fuel' => [
//                 'Comp (Net)' => $row[11],
//                 'SATP (Net)' => $row[12],
//             ],
//             '12T to 20T (Other makes)' => [
//                 'Comp (Net)' => $row[13],
//                 'SATP (Net)' => $row[14],
//             ],
//             '12T to 20T (TATA & Ashok leyland)' => [
//                 'Comp (Net)' => $row[15],
//                 'SATP (Net)' => $row[16],
//             ],
//             '20T to 40T (Other makes)' => [
//                 'Comp (Net)' => $row[17],
//                 'SATP (Net)' => $row[18],
//             ],
//             '20T to 40T (TATA & Ashok leyland)' => [
//                 'Comp (Net)' => $row[19],
//                 'SATP (Net)' => $row[20],
//             ],
//             '>40T (Other makes)' => [
//                 'Comp (Net)' => $row[21],
//                 'SATP (Net)' => $row[22],
//             ],
//             '>40T (TATA & Ashok leyland)' => [
//                 'Comp (Net)' => $row[23],
//                 'SATP (Net)' => $row[24],
//             ],
//             'School Bus' => [
//                 'Comp (Net)' => $row[25],
//                 'SATP (Net)' => $row[26],
//             ],
//             'PCV Taxi (Carrying capacity 6+1)' => [
//                 'Comp (Net)' => $row[27],
//                 'SATP (Net)' => $row[28],
//             ],
//             'Pvt Car - Above 3 lakhs (Pvt car & 2W has common slabs)' => [
//                 'Pvt Car (PO On OD) Comp & SAOD' => $row[29],
               
//             ],
//             'Pvt Car - STP' => [
//                 'Pvt Car - STP - Petrol & Bifuel < 1500' => $row[31],
//                 'Pvt Car - STP - Diesel < 1500' => $row[32],
//                 'Pvt Car STP above 1500 (Diesel)' => $row[33],
//                     'Pvt Car STP above 1500 (Petrol & Bifuel)' => $row[33],
//                 ],
           
//             '2 Wheeler (On net for 1 year premium only (1+1))' => [
//                 'Scooter upto 150 cc (Comp & SAOD)' => $row[34] ?? null,
//                 'Bike upto 75 cc' => $row[35] ?? null,
//                 'B.75-150 Bike' => $row[36] ?? null,
//                 'C. Above 150cc' => $row[37] ?? null,
//             ],
//             ]
//     ];
// }

// SBI Grid View
private function parseSBIrow($row)
{
    return [
        'region' => $row[0],
        'State_name' => $row[1],
        'circle' => $row[2],
        'vehicle_type' => [
            'Upto 2.5T GCV including GCV 3W' => [
                [
                      // 'Fule_type'=>'Diesel',
                        //   'Age_group'=>'New',
                          'Engine_capacity'=>'2.5T GCV including GCV 3W',
                        //   'Condition_type'=>'New',
                             'Product'=>'Goods Carrying Vehicles',
                            'Premium_type'=>'Comp (Net)',
                        //   'Basis'=>'GWP',
                            'Percentage' => $this->convertPercent($row[3]),    
                        
                ],
                [
                    // 'Fule_type'=>'Diesel',
                    //  'Age_group'=>'New',
                        'Engine_capacity'=>'2.5T GCV including GCV 3W',
                    //  'Condition_type'=>'New',
                        'Product'=>'Goods Carrying Vehicles',
                        'Premium_type' => 'SATP (Net)',
                   //   'Basis'=>'GWP',
                 'Percentage' => $this->convertPercent($row[4]), 
                ],
            ],
            'Agricultural Tractor & Harvester (excluding Trailer)' => [
                [
 
                        // 'Fule_type'=>'Diesel',
                        //  'Age_group'=>'New',
                        // 'Engine_capacity'=>'2.5T GCV including GCV 3W',
                         'Condition_type'=>'New',
                            'Product'=>'Agricultural Tractor & Harvester',
                            // 'Premium_type'=>'Comp (Net)',
                        //   'Basis'=>'GWP',
                            'Percentage' => $this->convertPercent($row[5]), 

                    // 'Fule_type' => 'New',
                    // 'Percentage' => $row[5],
                ],
                [
 // 'Fule_type'=>'Diesel',
                        //  'Age_group'=>'New',
                        // 'Engine_capacity'=>'2.5T GCV including GCV 3W',
                        'Condition_type'=>'Non New',
                        'Product'=>'Agricultural Tractor & Harvester',
                        // 'Premium_type'=>'Comp (Net)',
                    //   'Basis'=>'GWP',
                        'Percentage' => $this->convertPercent($row[6]), 

                ],
            ],
            'PCV 3W (Carrying capacity 3+1) Petrol' => [
                [
                         'Fule_type'=>'Petrol',
                        //  'Age_group'=>'New',
                        'Engine_capacity'=>'Carrying capacity 3+1',
                        // 'Condition_type'=>'Non New',
                        'Product'=>'Passenger Carrying Vehicle 3W',
                        'Premium_type'=>'Comp (Net)',
                    //   'Basis'=>'GWP',
                        'Percentage' => $this->convertPercent($row[7]), 
                ],
                [

                    'Fule_type'=>'Petrol',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'Carrying capacity 3+1',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'Passenger Carrying Vehicle 3W',
                    'Premium_type' => 'SATP (Net)',

                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[8]), 

                ],
            ],
            'PCV 3W (Carrying capacity 3+1) Diesel' => [
                [
                    'Fule_type'=>'Diesel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'Carrying capacity 3+1',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'Passenger Carrying Vehicle 3W',
                    'Premium_type' => 'Comp (Net)',

                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[9]), 
                ],
                [

                    'Fule_type'=>'Diesel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'Carrying capacity 3+1',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'Passenger Carrying Vehicle 3W',
                    'Premium_type' => 'SATP (Net)',

                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[10]), 
                ],
            ],
            'PCV 3W (Carrying capacity 3+1) Other fuel' => [
                [

                    'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'Carrying capacity 3+1',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'Passenger Carrying Vehicle 3W',
                    'Premium_type' => 'Comp (Net)',

                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[11]), 
                ],
                [
                    'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'Carrying capacity 3+1',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'Passenger Carrying Vehicle 3W',
                    'Premium_type' => 'SATP (Net)',

                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[12]), 
                 
                ],
            ],
            '12T to 20T (Other makes)' => [
                [
                    // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'12T to 20T',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'Other makes',
                    'Premium_type' => 'Comp (Net)',

                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[13]), 

                ],
                [
                    // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'12T to 20T',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'Other makes',
                    'Premium_type' => 'SATP (Net)',

                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[14]), 

                ],
            ],
            '12T to 20T (TATA & Ashok leyland)' => [
                [
                     // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'12T to 20T',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'TATA & Ashok leyland',
                    'Premium_type' => 'Comp (Net)',

                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[15]),


                ],
                [
                    // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'12T to 20T',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'TATA & Ashok leyland',
                    'Premium_type' => 'SATP (Net)',
                    'Percentage' => $this->convertPercent($row[16]),
                ],
            ],
            '20T to 40T (Other makes)' => [
                [
                    // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'20T to 40T',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'Other makes',
                    'Premium_type' => 'Comp (Net)',
                    'Percentage' => $this->convertPercent($row[17]),
                ],
                [
                    // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'20T to 40T',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'Other makes',
                    'Premium_type' => 'SATP (Net)',
                    'Percentage' => $this->convertPercent($row[18]),
                ],
            ],
            '20T to 40T (TATA & Ashok leyland)' => [
                [
                    // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'20T to 40T',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'TATA & Ashok leyland',
                    'Premium_type' => 'Comp (Net)',

                //   'Basis'=>'GWP',
                    'Premium_type' => 'Comp (Net)',
                    'Percentage' =>$this->convertPercent($row[19]),
                ],
                [
                      // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'20T to 40T',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'TATA & Ashok leyland',
                    'Premium_type' => 'SATP (Net)',

                //   'Basis'=>'GWP',
                'Percentage' =>$this->convertPercent($row[20]),


                   
                ],
            ],
            '>40T (Other makes)' => [
                [

                    // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'>40T',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'Other makes',
                    'Premium_type' => 'Comp (Net)',

                //   'Basis'=>'GWP',
                'Percentage' =>$this->convertPercent($row[21]),
                ],
                [

                    // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'>40T',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'Other makes',
                    'Premium_type' => 'SATP (Net)',

                    //   'Basis'=>'GWP',
                     'Percentage' =>$this->convertPercent($row[22]),

                ],
            ],
            '>40T (TATA & Ashok leyland)' => [
                [

                        // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'>40T',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'TATA & Ashok leyland',
                    'Premium_type' => 'Comp (Net)',

                //   'Basis'=>'GWP',
                'Percentage' =>$this->convertPercent($row[23]),

                ],
                [

                        // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'>40T',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'TATA & Ashok leyland',
                    'Premium_type' => 'SATP (Net)',

                //   'Basis'=>'GWP',
                'Percentage' =>$this->convertPercent($row[24]),

                ],
            ],
            'School Bus' => [
                [
                        // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    // 'Engine_capacity'=>'>40T',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'School Bus',
                    'Premium_type' => 'COMP (Net)',

                //   'Basis'=>'GWP',
                'Percentage' =>$this->convertPercent($row[25]),
                ],
                [
                        // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    // 'Engine_capacity'=>'>40T',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'School Bus',
                    'Premium_type' => 'SATP (Net)',

                //   'Basis'=>'GWP',
                'Percentage' =>$this->convertPercent($row[26]),
                ],
            ],
            'PCV Taxi (Carrying capacity 6+1)' => [
                [

        // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'Carrying capacity 6+1',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'PCV Taxi',
                    'Premium_type' => 'COMP (Net)',

                //   'Basis'=>'GWP',
                'Percentage' =>$this->convertPercent($row[27]),
                ],
                [
                    // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'Carrying capacity 6+1',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'PCV Taxi',
                    'Premium_type' => 'SATP (Net)',

                //   'Basis'=>'GWP',
                'Percentage' =>$this->convertPercent($row[28]),
                ],
            ],
            'Pvt Car - Above 3 lakhs (Pvt car & 2W has common slabs)' => [
                [
 // 'Fule_type'=>'Other fuel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'Above 3 lakhs',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'PCV Taxi',
                    'Premium_type' => '(PO On OD) Comp & SAOD',

                //   'Basis'=>'GWP',
                'Percentage' =>$this->convertPercent($row[29]),
                ],
            ],
            'Pvt Car - STP' => [
                [
                    'Fule_type'=>'Petrol',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'Bifuel <1500',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'PVT Car',
                    'Premium_type' => 'STP',

                    //   'Basis'=>'GWP',
                    'Percentage' =>$this->convertPercent($row[30]),

                ],
                [
                    'Fule_type'=>'Diesel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'<1500',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'PVT CAR',
                    'Premium_type' => 'STP',
                    //   'Basis'=>'GWP',
                    'Percentage' =>$this->convertPercent($row[31]),

                ],
                [
                    'Fule_type'=>'Diesel',
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'above 1500',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'PVT CAR',
                    'Premium_type' => 'STP',
                    //   'Basis'=>'GWP',
                    'Percentage' =>$this->convertPercent($row[32]),

                ],
                [
                    'Fule_type'=>'Petrol & Bifuel', 
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'above 1500',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'PVT CAR',
                    'Premium_type' => 'STP',
                    //   'Basis'=>'GWP',
                    'Percentage' =>$this->convertPercent($row[33]),


                ],
            ],
            '2 Wheeler (On net for 1 year premium only (1+1))' => [
                [
                    // 'Fule_type'=>'', 
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'upto 150 cc',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'Scooter',
                    'Premium_type' => 'Comp & SAOD',
                    //   'Basis'=>'GWP',
                    'Percentage' =>$this->convertPercent($row[34]),
                ],
                [
  // 'Fule_type'=>'', 
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'upto 75 cc',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'Bike',
                    // 'Premium_type' => 'Comp & SAOD',
                    //   'Basis'=>'GWP',
                    'Percentage' =>$this->convertPercent($row[35]),
                ],
                [
 // 'Fule_type'=>'', 
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'B.75-150 cc',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'Bike',
                    // 'Premium_type' => 'Comp & SAOD',
                    //   'Basis'=>'GWP',
                    'Percentage' =>$this->convertPercent($row[36]),
                ],
                [

// 'Fule_type'=>'', 
                    //  'Age_group'=>'New',
                    'Engine_capacity'=>'Above 150 cc',
                    // 'Condition_type'=>'Non New',
                    'Product'=>'Bike',
                    // 'Premium_type' => 'Comp & SAOD',
                    //   'Basis'=>'GWP',
                    'Percentage' =>$this->convertPercent($row[37]),
                ],
            ],
        ],
    ];
}
// SBI Grid View

private function parseTATArow($row, $state)
{
    dd($row, $state);
    // Check if required fields are present in the row (Region, State, etc.)
    $region = $row[3] ?? null; // Region starts from index 3
    $state_name = $row[4] ?? null; // State Name starts from index 4
    $circle = $row[5] ?? null; // Circle starts from index 5

    // Prepare the data array
    $data = [
        'region' => $region,
        'State_name' => $state_name,
        'circle' => $circle,
        'vehicle_type' => []
    ];

    // If vehicle data (age group, percentage etc.) exists in the row, add it
    // We'll start by defining vehicle data for "Upto 2.5T GCV including GCV 3W"
    $vehicle_data = [
        'Upto 2.5T GCV including GCV 3W' => [
            [
                'Engine_capacity' => '2.5T GCV including GCV 3W',
                'Product' => 'Goods Carrying Vehicles',
                'Premium_type' => 'Comp (Net)',
                'Percentage' => $this->convertPercent($row[6] ?? '0'),
            ],
            [
                'Engine_capacity' => '2.5T GCV including GCV 3W',
                'Product' => 'Goods Carrying Vehicles',
                'Premium_type' => 'SATP (Net)',
                'Percentage' => $this->convertPercent($row[7] ?? '0'),
            ],
        ]
    ];

    // Add vehicle data to the main data array
    $data['vehicle_type'] = $vehicle_data;
dd($data);
    return $data;
}

//   TATA Grid View
// private function parseTATArow($row)
// {
//     return [
//         'region' => $row[0],
//         'State_name' => $row[1],
//         'circle' => $row[2],
//         'vehicle_type' => [
//             'Upto 2.5T GCV including GCV 3W' => [
//                 [
//                       // 'Fule_type'=>'Diesel',
//                         //   'Age_group'=>'New',
//                           'Engine_capacity'=>'2.5T GCV including GCV 3W',
//                         //   'Condition_type'=>'New',
//                              'Product'=>'Goods Carrying Vehicles',
//                             'Premium_type'=>'Comp (Net)',
//                         //   'Basis'=>'GWP',
//                             'Percentage' => $this->convertPercent($row[3]),    
                        
//                 ],
//                 [
//                     // 'Fule_type'=>'Diesel',
//                     //  'Age_group'=>'New',
//                         'Engine_capacity'=>'2.5T GCV including GCV 3W',
//                     //  'Condition_type'=>'New',
//                         'Product'=>'Goods Carrying Vehicles',
//                         'Premium_type' => 'SATP (Net)',
//                    //   'Basis'=>'GWP',
//                  'Percentage' => $this->convertPercent($row[4]), 
//                 ],
//             ],
            
//         ],
//     ];
// }

// TATA Grid View

// ROYAL Grid View
private function parseRoyalrow($row)
{
    return [
        // 'region' => $row[0],
        'State_name' => $row[0],
        'circle' => $row[1],
        'vehicle_type' => [
            'Goods Carrying Vehicle ' => [
                [
                      // 'Fule_type'=>'Diesel',
                          'Age_group'=>'0-4',
                          'Engine_capacity'=>'0-2 T',
                        //   'Condition_type'=>'New',
                             'Product'=>'Goods Carrying Vehicles',
                            'Premium_type'=>'STP',
                        //   'Basis'=>'GWP',
                            'Percentage' => $this->convertPercent($row[2]),    
                        
                ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'5-10',
                        'Engine_capacity'=>'0-2 T',
                      //   'Condition_type'=>'New',
                           'Product'=>'Goods Carrying Vehicles',
                          'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[3]),    
                      
              ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>10',
                        'Engine_capacity'=>'0-2 T',
                      //   'Condition_type'=>'New',
                           'Product'=>'Goods Carrying Vehicles',
                          'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[3]),    
                      
              ],
            //   same fields
            [
                // 'Fule_type'=>'Diesel',
                    'Age_group'=>'0-4',
                    'Engine_capacity'=>'2 to 2.5',
                  //   'Condition_type'=>'New',
                       'Product'=>'Goods Carrying Vehicles',
                      'Premium_type'=>'STP',
                  //   'Basis'=>'GWP',
                      'Percentage' => $this->convertPercent($row[2]),    
                  
          ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'5-10',
                  'Engine_capacity'=>'2 to 2.5',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[3]),    
                
        ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'>10',
                  'Engine_capacity'=>'2 to 2.5',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[3]),    
                
        ],
            //   same fields

             //   same fields
             [
                // 'Fule_type'=>'Diesel',
                    'Age_group'=>'0-4',
                    'Engine_capacity'=>'2.5 to 3',
                  //   'Condition_type'=>'New',
                       'Product'=>'Goods Carrying Vehicles',
                      'Premium_type'=>'STP',
                  //   'Basis'=>'GWP',
                      'Percentage' => $this->convertPercent($row[2]),    
                  
          ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'5-10',
                  'Engine_capacity'=>'2.5 to 3',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[3]),    
                
        ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'>10',
                  'Engine_capacity'=>'2.5 to 3',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[3]),    
                
        ],
            //   same fields
             //   same fields
             [
                // 'Fule_type'=>'Diesel',
                    'Age_group'=>'0-4',
                    'Engine_capacity'=>'3 to 7',
                  //   'Condition_type'=>'New',
                       'Product'=>'Goods Carrying Vehicles',
                      'Premium_type'=>'STP',
                  //   'Basis'=>'GWP',
                      'Percentage' => $this->convertPercent($row[2]),    
                  
          ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'5-10',
                  'Engine_capacity'=>'3 to 7',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[3]),    
                
        ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'>10',
                  'Engine_capacity'=>'3 to 7',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[3]),    
                
        ],
            //   same fields

             //   same fields
             [
                // 'Fule_type'=>'Diesel',
                    'Age_group'=>'0-4',
                    'Engine_capacity'=>'7 to 12',
                  //   'Condition_type'=>'New',
                       'Product'=>'Goods Carrying Vehicles',
                      'Premium_type'=>'STP',
                  //   'Basis'=>'GWP',
                      'Percentage' => $this->convertPercent($row[2]),    
                  
          ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'5-10',
                  'Engine_capacity'=>'7 to 12',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[3]),    
                
        ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'>10',
                  'Engine_capacity'=>'7 to 12',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[3]),    
                
        ],
            //   same fields

             //   same fields
             [
                // 'Fule_type'=>'Diesel',
                    'Age_group'=>'0-4',
                    'Engine_capacity'=>'12 to 25',
                  //   'Condition_type'=>'New',
                       'Product'=>'Goods Carrying Vehicles',
                      'Premium_type'=>'STP',
                  //   'Basis'=>'GWP',
                      'Percentage' => $this->convertPercent($row[2]),    
                  
          ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'5-10',
                  'Engine_capacity'=>'12 to 25',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[3]),    
                
        ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'>10',
                  'Engine_capacity'=>'12 to 25',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[3]),    
                
        ],
            //   same fields

            //   same fields
            [
                // 'Fule_type'=>'Diesel',
                    'Age_group'=>'0-4',
                    'Engine_capacity'=>'25 to 40',
                  //   'Condition_type'=>'New',
                       'Product'=>'Goods Carrying Vehicles',
                      'Premium_type'=>'STP',
                  //   'Basis'=>'GWP',
                      'Percentage' => $this->convertPercent($row[2]),    
                  
          ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'5-10',
                  'Engine_capacity'=>'25 to 40',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[3]),    
                
        ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'>10',
                  'Engine_capacity'=>'Above 40',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[3]),    
                
        ],
        [
            // 'Fule_type'=>'Diesel',
                'Age_group'=>'0-4',
                'Engine_capacity'=>'Above 40',
              //   'Condition_type'=>'New',
                   'Product'=>'Goods Carrying Vehicles',
                  'Premium_type'=>'STP',
              //   'Basis'=>'GWP',
                  'Percentage' => $this->convertPercent($row[2]),    
              
      ],
      [
          // 'Fule_type'=>'Diesel',
              'Age_group'=>'5-10',
              'Engine_capacity'=>'Above 40',
            //   'Condition_type'=>'New',
                 'Product'=>'Goods Carrying Vehicles',
                'Premium_type'=>'STP',
            //   'Basis'=>'GWP',
                'Percentage' => $this->convertPercent($row[3]),    
            
    ],
            ],
        ],
        ];
   
    return $data;
}
// ROYAL Grid View

// Liberty Grid View
private function parseLibertyrow($row)
{
    return [
        // 'region' => $row[0],
        'State_name' => $row[0],
        // 'circle' => $row[1],
        'vehicle_type' => [
            'Goods Carrying Vehicle ' => [
                [
                      // 'Fule_type'=>'Diesel',
                          'Age_group'=>'>1 - 5 Years',
                          'Engine_capacity'=>'0 - 2.5T',
                        //   'Condition_type'=>'New',
                             'Product'=>'Goods Carrying Vehicles',
                            // 'Premium_type'=>'STP',
                        //   'Basis'=>'GWP',
                            'Percentage' => $this->convertPercent($row[1]),    
                        
                ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>5 - 10 Years',
                        'Engine_capacity'=>'0-2 T',
                      //   'Condition_type'=>'New',
                           'Product'=>'Goods Carrying Vehicles',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[2]),    
                      
              ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>10',
                        'Engine_capacity'=>'>10 Years',
                      //   'Condition_type'=>'New',
                           'Product'=>'Goods Carrying Vehicles',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[3]),    
                      
              ],
            //   same fields
            [
                // 'Fule_type'=>'Diesel',
                    'Age_group'=>'>1 - 5 Years',
                    'Engine_capacity'=>'2.5 - 3.5T',
                  //   'Condition_type'=>'New',
                       'Product'=>'Goods Carrying Vehicles',
                    //   'Premium_type'=>'STP',
                  //   'Basis'=>'GWP',
                      'Percentage' => $this->convertPercent($row[4]),    
                  
          ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'>5 - 10 Years',
                  'Engine_capacity'=>'2.5 - 3.5T',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    // 'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[5]),    
                
        ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'>10 Years',
                  'Engine_capacity'=>'2.5 - 3.5T',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    // 'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[6]),    
                
        ],
            //   same fields

             //   same fields
             [
                // 'Fule_type'=>'Diesel',
                    'Age_group'=>'>1 - 5 Years',
                    'Engine_capacity'=>'3.5 - 7.5T',
                  //   'Condition_type'=>'New',
                       'Product'=>'Goods Carrying Vehicles',
                    //   'Premium_type'=>'STP',
                  //   'Basis'=>'GWP',
                      'Percentage' => $this->convertPercent($row[7]),    
                  
          ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'>5 - 10 Years',
                  'Engine_capacity'=>'3.5 - 7.5T',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    // 'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[8]),    
                
        ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'>10 Years',
                  'Engine_capacity'=>'3.5 - 7.5T',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    // 'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[9]),    
                
        ],
            //   same fields
             //   same fields
             [
                // 'Fule_type'=>'Diesel',
                    'Age_group'=>'>1 - 5 Years',
                    'Engine_capacity'=>'12 - 20T',
                  //   'Condition_type'=>'New',
                       'Product'=>'Goods Carrying Vehicles',
                    //   'Premium_type'=>'STP',
                  //   'Basis'=>'GWP',
                      'Percentage' => $this->convertPercent($row[10]),    
                  
          ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'>5 - 10 Years',
                  'Engine_capacity'=>'12 - 20T',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    // 'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[11]),    
                
        ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'>10 Years',
                  'Engine_capacity'=>'12 - 20T',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    // 'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[12]),    
                
        ],
            //   same fields

             //   same fields
             [
                // 'Fule_type'=>'Diesel',
                    'Age_group'=>'>1 - 5 Years',
                    'Engine_capacity'=>'20 - 40T',
                  //   'Condition_type'=>'New',
                       'Product'=>'Goods Carrying Vehicles',
                    //   'Premium_type'=>'STP',
                  //   'Basis'=>'GWP',
                      'Percentage' => $this->convertPercent($row[13]),    
                  
          ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'>5 - 10 Years',
                  'Engine_capacity'=>'20 - 40T',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    // 'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[14]),    
                
        ],
          [
              // 'Fule_type'=>'Diesel',
                  'Age_group'=>'>10 Years',
                  'Engine_capacity'=>'20 - 40T',
                //   'Condition_type'=>'New',
                     'Product'=>'Goods Carrying Vehicles',
                    // 'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[15]),    
                
        ],
            //   same fields

             //   same fields
             
            ],
            'Passenger Carrying Vehicle 3W' => [
                [
                      // 'Fule_type'=>'Diesel',
                          'Age_group'=>'>1 - 5 Years',
                          'Engine_capacity'=>'0 - 2.5T',
                        //   'Condition_type'=>'New',
                             'Product'=>'Passenger Carrying Vehicle 3W',
                            // 'Premium_type'=>'STP',
                        //   'Basis'=>'GWP',
                            'Percentage' => $this->convertPercent($row[16]),    
                        
                ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>5 - 10 Years',
                        'Engine_capacity'=>'0-2 T',
                      //   'Condition_type'=>'New',
                           'Product'=>'Passenger Carrying Vehicle 3W',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[17]),    
                      
              ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>10',
                        'Engine_capacity'=>'>10 Years',
                      //   'Condition_type'=>'New',
                           'Product'=>'Passenger Carrying Vehicle 3W',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[18]),    
                      
              ],
            //   same fields
           
    ],
            'MISD Tractors' => [
                [
                      // 'Fule_type'=>'Diesel',
                          'Age_group'=>'New',
                        //   'Engine_capacity'=>'0 - 2.5T',
                        //   'Condition_type'=>'New',
                             'Product'=>'MISD Tractors',
                            // 'Premium_type'=>'STP',
                        //   'Basis'=>'GWP',
                            'Percentage' => $this->convertPercent($row[19]),    
                        
                ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>1 - 5 Years',
                        // 'Engine_capacity'=>'0-2 T',
                      //   'Condition_type'=>'New',
                           'Product'=>'MISD Tractors',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[20]),    
                      
              ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>5 - 10 Years',
                        // 'Engine_capacity'=>'>10 Years',
                      //   'Condition_type'=>'New',
                           'Product'=>'MISD Tractors',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[21]),    
                      
              ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>10 Years',
                        // 'Engine_capacity'=>'>10 Years',
                      //   'Condition_type'=>'New',
                           'Product'=>'MISD Tractors',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[22]),    
                      
              ],
            //   same fields
           
    ],
            '0 - 2.5T SATP' => [
                [
                      // 'Fule_type'=>'Diesel',
                          'Age_group'=>'>1 - 5 Years',
                          'Engine_capacity'=>'0 - 2.5T',
                        //   'Condition_type'=>'New',
                            //  'Product'=>'MISD Tractors',
                            // 'Premium_type'=>'STP',
                        //   'Basis'=>'GWP',
                            'Percentage' => $this->convertPercent($row[23]),    
                        
                ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>5 - 10 Years',
                        'Engine_capacity'=>'0 - 2.5T',
                      //   'Condition_type'=>'New',
                        //    'Product'=>'MISD Tractors',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[24]),    
                      
              ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>10 Years',
                        'Engine_capacity'=>'0 - 2.5T',
                      //   'Condition_type'=>'New',
                        //    'Product'=>'MISD Tractors',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[25]),    
                      
              ],
            //   same fields
           
    ],
            '2.5 - 3.5T SATP' => [
                [
                      // 'Fule_type'=>'Diesel',
                          'Age_group'=>'>1 - 5 Years',
                          'Engine_capacity'=>'2.5 - 3.5T',
                        //   'Condition_type'=>'New',
                            //  'Product'=>'MISD Tractors',
                            // 'Premium_type'=>'STP',
                        //   'Basis'=>'GWP',
                            'Percentage' => $this->convertPercent($row[26]),    
                        
                ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>5 - 10 Years',
                        'Engine_capacity'=>'2.5 - 3.5T',
                      //   'Condition_type'=>'New',
                        //    'Product'=>'MISD Tractors',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[27]),    
                      
              ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>10 Years',
                        'Engine_capacity'=>'2.5 - 3.5T',
                      //   'Condition_type'=>'New',
                        //    'Product'=>'MISD Tractors',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[28]),    
                      
              ],
            //   same fields
           
    ],
            '3.5 - 7.5T SATP' => [
                [
                      // 'Fule_type'=>'Diesel',
                          'Age_group'=>'>1 - 5 Years',
                          'Engine_capacity'=>'3.5 - 7.5T',
                        //   'Condition_type'=>'New',
                            //  'Product'=>'MISD Tractors',
                            // 'Premium_type'=>'STP',
                        //   'Basis'=>'GWP',
                            'Percentage' => $this->convertPercent($row[29]),    
                        
                ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>5 - 10 Years',
                        'Engine_capacity'=>'3.5 - 7.5T',
                      //   'Condition_type'=>'New',
                        //    'Product'=>'MISD Tractors',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[30]),    
                      
              ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>10 Years',
                        'Engine_capacity'=>'3.5 - 7.5T',
                      //   'Condition_type'=>'New',
                        //    'Product'=>'MISD Tractors',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[31]),    
                      
              ],
            //   same fields
           
    ],
            '12 - 20T SATP' => [
                [
                      // 'Fule_type'=>'Diesel',
                          'Age_group'=>'>1 - 5 Years',
                          'Engine_capacity'=>'12 - 20T',
                        //   'Condition_type'=>'New',
                            //  'Product'=>'MISD Tractors',
                            // 'Premium_type'=>'STP',
                        //   'Basis'=>'GWP',
                            'Percentage' => $this->convertPercent($row[32]),    
                        
                ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>5 - 10 Years',
                        'Engine_capacity'=>'12 - 20T',
                      //   'Condition_type'=>'New',
                        //    'Product'=>'MISD Tractors',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[33]),    
                      
              ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>10 Years',
                        'Engine_capacity'=>'12 - 20T',
                      //   'Condition_type'=>'New',
                        //    'Product'=>'MISD Tractors',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[34]),    
                      
              ],
            //   same fields
           
    ],
            '20 - 40T SATP' => [
                [
                      // 'Fule_type'=>'Diesel',
                          'Age_group'=>'>1 - 5 Years',
                          'Engine_capacity'=>'20 - 40T',
                        //   'Condition_type'=>'New',
                            //  'Product'=>'MISD Tractors',
                            // 'Premium_type'=>'STP',
                        //   'Basis'=>'GWP',
                            'Percentage' => $this->convertPercent($row[35]),    
                        
                ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>5 - 10 Years',
                        'Engine_capacity'=>'20 - 40T',
                      //   'Condition_type'=>'New',
                        //    'Product'=>'MISD Tractors',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[36]),    
                      
              ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>10 Years',
                        'Engine_capacity'=>'20 - 40T',
                      //   'Condition_type'=>'New',
                        //    'Product'=>'MISD Tractors',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[37]),    
                      
              ],
            //   same fields
           
    ],
            'Passenger Carrying Vehicle - 3 W SATP' => [
                [
                      // 'Fule_type'=>'Diesel',
                          'Age_group'=>'>1 - 5 Years',
                        //   'Engine_capacity'=>'20 - 40T',
                        //   'Condition_type'=>'New',
                             'Product'=>'Passenger Carrying Vehicle - 3 Wheeler',
                            // 'Premium_type'=>'STP',
                        //   'Basis'=>'GWP',
                            'Percentage' => $this->convertPercent($row[38]),    
                        
                ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>5 - 10 Years',
                        // 'Engine_capacity'=>'20 - 40T',
                      //   'Condition_type'=>'New',
                           'Product'=>'Passenger Carrying Vehicle - 3 Wheeler',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[39]),    
                      
              ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>10 Years',
                        // 'Engine_capacity'=>'20 - 40T',
                      //   'Condition_type'=>'New',
                           'Product'=>'Passenger Carrying Vehicle - 3 Wheeler',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[40]),    
                      
              ],
            //   same fields
           
    ],
            'MISD Tractors SATP' => [
                [
                      // 'Fule_type'=>'Diesel',
                          'Age_group'=>'>1 - 5 Years',
                        //   'Engine_capacity'=>'20 - 40T',
                        //   'Condition_type'=>'New',
                             'Product'=>'MISD Tractors SATP',
                            // 'Premium_type'=>'STP',
                        //   'Basis'=>'GWP',
                            'Percentage' => $this->convertPercent($row[41]),    
                        
                ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>5 - 10 Years',
                        // 'Engine_capacity'=>'20 - 40T',
                      //   'Condition_type'=>'New',
                           'Product'=>'MISD Tractors SATP',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[42]),    
                      
              ],
                [
                    // 'Fule_type'=>'Diesel',
                        'Age_group'=>'>10 Years',
                        // 'Engine_capacity'=>'20 - 40T',
                      //   'Condition_type'=>'New',
                           'Product'=>'MISD Tractors SATP',
                        //   'Premium_type'=>'STP',
                      //   'Basis'=>'GWP',
                          'Percentage' => $this->convertPercent($row[43]),    
                      
              ],
            //   same fields
           
    ],
        ],
        ];
   
    return $data;
}

// Liberty Grid View
private function parseMagmaRow($row){
  
    return [
        // 'region' => $row[0],
        'State_name' => $row[0],
        'circle' => $row[1],
        'vehicle_type'=>[
        'Pvt Car New Diesel' => [
            'Fule_type'=>'Diesel',
            'Age_group'=>'New',
            'Product'=>'Private Car',
            // 'Engine_capacity'=>'3.5-7.5T',
            'Condition_type'=>'New',
            'Premium_type'=>'Comp',
            'Basis'=>'OD',
            'Percentage'=>$this->convertPercent($row[2]),
        ],
        'Pvt Car New Petrol' => [
            'Fule_type'=>'Petrol',
            'Age_group'=>'New',
            'Product'=>'Private Car',
            // 'Engine_capacity'=>'3.5-7.5T',
            'Condition_type'=>'New',
            'Premium_type'=>'Comp',
            'Basis'=>'OD',
            'Percentage' => $this->convertPercent($row[3]),
        ],
        'Pvt Car Old Diesel & NCB' => [
            'Fule_type'=>'Diesel & NCB',
            'Age_group'=>'Old',
            'Product'=>'Private Car',
            // 'Engine_capacity'=>'3.5-7.5T',
            'Condition_type'=>'Old',
            'Premium_type'=>'Comp',
            'Basis'=>'OD',
            'Percentage' => $this->convertPercent($row[4]),
        ],
        'Pvt Car Old Diesel & Zero NCB' => [
            'Fule_type'=>'Diesel & Zero NCB',
            'Age_group'=>'Old',
            'Product'=>'Private Car',
            // 'Engine_capacity'=>'3.5-7.5T',
            'Condition_type'=>'Old',
            'Premium_type'=>'Comp',
            'Basis'=>'OD',
            'Percentage' => $this->convertPercent($row[5]),
        ],
        'Pvt Car Old Petrol & NCB' => [
            'Fule_type'=>'Petrol & NCB',
            'Age_group'=>'Old',
            'Product'=>'Private Car',
            // 'Engine_capacity'=>'3.5-7.5T',
            'Condition_type'=>'Old',
            'Premium_type'=>'Comp',
            'Basis'=>'OD',
            'Percentage' => $this->convertPercent($row[6]),
        ],
        'Pvt Car Old Petrol & Zero NCB' => [
            'Fule_type'=>'Petrol & Zero NCB',
            'Age_group'=>'Old',
            'Product'=>'Private Car',
            // 'Engine_capacity'=>'3.5-7.5T',
            'Condition_type'=>'Old',
            'Premium_type'=>'Comp',
            'Basis'=>'OD',
            'Percentage' => $this->convertPercent($row[7]),
        ],
        'Pvt Car STP < 1000 cc Diesel' => [
            'Fule_type'=>'Diesel',
            // 'Age_group'=>'Old',
            'Product'=>'Private Car',
            'Engine_capacity'=>'< 1000 cc',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[8]),
        ],
        'Pvt Car STP < 1000 cc Petrol' => [
            'Fule_type'=>'Petrol',
            'Product'=>'Private Car',
            // 'Age_group'=>'Old',
            'Engine_capacity'=>'< 1000 cc',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[9]),
        ],
        'Pvt Car STP 1000 cc-1500 cc Diesel' => [
            'Fule_type'=>'Diesel',
            'Product'=>'Private Car',
            // 'Age_group'=>'Old',
            'Engine_capacity'=>'1000-1500 cc',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[10]),
        ],
        'Pvt Car STP 1000 cc-1500 cc Petrol' => [
            'Fule_type'=>'Petrol',
            'Product'=>'Private Car',
            // 'Age_group'=>'Old',
            'Engine_capacity'=>'1000-1500 cc',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[11]),
        ],
        'Pvt Car STP >1500 cc Diesel' => [
            'Fule_type'=>'Diesel',
            'Product'=>'Private Car',
            // 'Age_group'=>'Old',
            'Engine_capacity'=>'>1500 cc',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[12]),
        ],
        'Pvt Car STP >1500 cc Petrol' => [
            'Fule_type'=>'Petrol',
            'Product'=>'Private Car',
            // 'Age_group'=>'Old',
            'Engine_capacity'=>'>1500 cc',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[13]),
        ],
        'PCV 3W - New' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'New',
            'Product'=>'PCV',
            // 'Engine_capacity'=>'3.5-7.5T',
            'Condition_type'=>'New',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage'=>$this->convertPercent($row[14]),
        ],
        'PCV 3W - Old' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'Old',
            'Product'=>'PCV',
            // 'Engine_capacity'=>'3.5-7.5T',
            'Condition_type'=>'Old',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[15]),
        ],
        'PCV 3W - Electric' => [
            // 'Fule_type'=>'Diesel',
            'Product'=>'PCV',
            'Age_group'=>'Old',
            // 'Engine_capacity'=>'3.5-7.5T',
            'Condition_type'=>'Old',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[16]),
        ],
        'PCV School Bus' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'Old',
            'Product'=>'PCV',
            // 'Engine_capacity'=>'3.5-7.5T',
            'Condition_type'=>'Old',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[17]),
        ],
        'PCV Other Bus' => [
            // 'Fule_type'=>'Diesel',
            // 'Age_group'=>'Old',
            // 'Engine_capacity'=>'3.5-7.5T',
            // 'Condition_type'=>'Old',
            'Product'=>'PCV',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[18]),
        ],
        'PCV Taxi' => [
            // 'Fule_type'=>'Diesel',
            // 'Age_group'=>'Old',
            // 'Engine_capacity'=>'3.5-7.5T',
            // 'Condition_type'=>'Old',
            'Product'=>'PCV Taxi',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[19]),
        ],
        'GCV < 2.5T - Age < 5' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'<5',
            'Product'=>'GCV',
            'Engine_capacity'=>'< 2.5T',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[20]),
        ],
        'GCV < 2.5T - Age >= 5' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'>=5',
            'Engine_capacity'=>'< 2.5T',
            'Product'=>'GCV',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[21]),
        ],
        'GCV 2.5-3.5T - Age < 5' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'<5',
            'Engine_capacity'=>'2.5-3.5T',
            'Product'=>'GCV',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[22]),
        ],
        'GCV 2.5-3.5T - Age >= 5' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'>=5',
            'Product'=>'GCV',
            'Engine_capacity'=>'2.5-3.5T',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[23]),
            
        ],
        'GCV 3.5-7.5T - Age < 5' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'<5',
            'Engine_capacity'=>'3.5-7.5T',
            'Product'=>'GCV',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[24]),
        ],
        'GCV 3.5-7.5T - Age >= 5' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'>=5',
            'Engine_capacity'=>'3.5-7.5T',
            // 'Condition_type'=>'Old',
            'Product'=>'GCV',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[25]),
        ],
        'GCV 7.5 - 12T - Age < 5' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'<5',
            'Engine_capacity'=>'7.5-12T',
            'Product'=>'GCV',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[26]),
        ],
        'GCV 7.5 - 12T - Age >= 5' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'>=5',
            'Product'=>'GCV',
            'Engine_capacity'=>'7.5-12T',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'Comp',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[27]),
        ],
        'GCV 7.5 - 12T - Age >= 5' => [
            // 'Fule_type'=>'Diesel',
            'Product'=>'GCV',
            'Age_group'=>'>=5',
            'Engine_capacity'=>'7.5-12T',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[28]),
        ],
        'GCV 12 - 20T - Age < 5' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'<5',
            'Product'=>'GCV',
            'Engine_capacity'=>'12-20T',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[29]),
        ],
        'GCV 12 - 20T - Age >= 5' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'>=5',
            'Engine_capacity'=>'12-20T',
            'Product'=>'GCV',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'Comp',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[30]),
        ],
        'GCV 12 - 20T - Age >= 5' => [
            // 'Fule_type'=>'Diesel',
            'Product'=>'GCV',
            'Age_group'=>'>=5',
            'Engine_capacity'=>'12-20T',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[31]),
        ],
        'GCV 20 - 40T - Age < 5' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'<5',
            'Product'=>'GCV',
            'Engine_capacity'=>'20-40T',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[32]),
        ],
        'GCV 20 - 40T - Age >= 5' => [
                // 'Fule_type'=>'Diesel',
                'Product'=>'GCV',
            'Age_group'=>'>=5',
            'Engine_capacity'=>'20-40T',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'Comp',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[33]),
        ],
        'GCV 20 - 40T - Age >= 5' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'>=5',
            'Product'=>'GCV',
            'Engine_capacity'=>'20-40T',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'STP',
            'Basis'=>'GWP', 
            'Percentage' => $this->convertPercent($row[34]),
        ],
        'GCV > 40T - Age < 5' => [
                // 'Fule_type'=>'Diesel',
            'Age_group'=>'<5',
            'Engine_capacity'=>'>40T',
            'Product'=>'GCV',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'Comp',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[35]),
        ],
        'GCV > 40T - Age >= 5' => [
                // 'Fule_type'=>'Diesel',
                'Product'=>'GCV',
            'Age_group'=>'>=5',
            'Engine_capacity'=>'>40T',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'Comp',
            'Basis'=>'GWP', 
            'Percentage' => $this->convertPercent($row[36]),
        ],
        'GCV > 40T - Age < 5' => [
                // 'Fule_type'=>'Diesel',
            'Age_group'=>'<5',
            'Product'=>'GCV',
            'Engine_capacity'=>'>40T',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[37]),
        ],
        'GCV > 40T - Age >= 5' => [
                // 'Fule_type'=>'Diesel',
            'Age_group'=>'>=5',
            'Product'=>'GCV',
            'Engine_capacity'=>'>40T',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'STP',
            'Basis'=>'GWP', 
            'Percentage' => $this->convertPercent($row[38]),
        ],
        'Tractor - New' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'New',
            'Product'=>'Tractor',
            // 'Engine_capacity'=>'3.5-7.5T',
            'Condition_type'=>'New',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[39]),
        ],
        'Tractor - Old' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'Old',
            'Product'=>'Tractor',
            // 'Engine_capacity'=>'3.5-7.5T',
            'Condition_type'=>'Old',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[40]),
        ],
        'Misc D' => [
            // 'Fule_type'=>'Diesel',
            // 'Age_group'=>'Old',
            // 'Engine_capacity'=>'3.5-7.5T',
            // 'Condition_type'=>'Old',
            'Product'=>'Misc',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[41]),
        ],
        'Garbage Van' => [
            // 'Fule_type'=>'Diesel',
            // 'Age_group'=>'Old',
            // 'Engine_capacity'=>'3.5-7.5T',
            // 'Condition_type'=>'Old',
            'Product'=>'Garbage Van',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[42]),
        ],
        '2W Old - <150 cc' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'Old',
            'Engine_capacity'=>'<150 cc',
            // 'Condition_type'=>'Old',
            'Product'=>'2W',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[43]),
        ],
        '2W Old - 150 - 350 cc' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'Old',
            'Engine_capacity'=>'150-350 cc',
            'Product'=>'2W',
            'Condition_type'=>'Old',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[44]),
        ],
        '2W Old - > 350 cc' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'Old',
            'Product'=>'2W',
            'Engine_capacity'=>'>350 cc',
            // 'Condition_type'=>'Old',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP', 
            'Percentage' => $this->convertPercent($row[45]),
        ],
        '2W Old - Scooter' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'Old',
            // 'Engine_capacity'=>'Scooter',
            'Product'=>'2W Scooter',
            'Condition_type'=>'Old',
            'Premium_type'=>'Comp',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[46]),
        ],
        '2W Old - Scooter' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'Old',
            // 'Engine_capacity'=>'Scooter',
            'Product'=>'2W Scooter',
            'Condition_type'=>'Old',
            'Premium_type'=>'STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[47]),
        ],
        '2W New - <150 cc' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'New',
            'Engine_capacity'=>'<150 cc',
            'Product'=>'2W',
            'Condition_type'=>'New',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[48]),
        ],
        '2W New - 150 - 350 cc' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'New',
            'Engine_capacity'=>'150-350 cc',
            'Condition_type'=>'New',
            'Product'=>'2W',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[49]),
        ],
        '2W New - > 350 cc' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'New',
            'Engine_capacity'=>'>350 cc',
            'Condition_type'=>'New',
            'Product'=>'2W',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[50]),
        ],
        '2W New - Scooter' => [
            // 'Fule_type'=>'Diesel',
            'Age_group'=>'New',
            'Product'=>'2W Scooter',
            // 'Engine_capacity'=>'Scooter',
            'Condition_type'=>'New',
            'Premium_type'=>'Comp & STP',
            'Basis'=>'GWP',
            'Percentage' => $this->convertPercent($row[51]),
        ],
      
    ],
];
}


// shriram company grid
private function parseShriramRow($row)
{
  
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

// relience company grid
private function parseRelianceRow($row){
    return [
        // 'region' => $row[0],
        'State_name' => $row[2],
        'circle' => $row[3],
        'vehicle_type'=>[
            'PCV 3W (Non diesel)' => [
                ['Fule_type' => 'Non diesel',
                 'Premium_type' => 'Comp',
                //   'Basis' => 'OD', 
                  'Percentage' => $this->convertPercent($row[4])],
                ['Fule_type' => 'Non diesel', 
                'Premium_type' => 'STP', 
                // 'Basis' => 'OD', 
                'Percentage' => $this->convertPercent($row[5])]
            ],
            'School Bus (Comp & STP)' => [
                [
                    'Age_group' => '0-10 Years',
                    'Engine_capacity' => '<18 Str',
                    'Premium_type' => 'COMP & STP',
                    // 'Basis' => 'OD',
                    'Percentage' => $this->convertPercent($row[6]),
                ],
                [
                    'Age_group' => '>10 Years',
                    'Engine_capacity' => '<18 Str',
                    'Premium_type' => 'COMP & STP',
                    'Percentage' => $this->convertPercent($row[7]),
                ],
                [
                    'Age_group' => '0-10 Years',
                    'Engine_capacity' => '>18 Str',
                    'Condition_type' => 'New',
                    'Premium_type' => 'COMP & STP',
                    // 'Basis' => 'OD',
                    'Percentage' => $this->convertPercent($row[8]),
                ],
                [
                    'Age_group' => '>10 Years',
                    'Engine_capacity' => '>18 Str',
                    'Condition_type' => 'New',
                    'Premium_type' => 'COMP & STP',
                    // 'Basis' => 'OD',
                    'Percentage' => $this->convertPercent($row[9]),
                ],
            ],
            'PCV TAXI (Excluded "BYD" Make)' => [
            [

            // 'Fule_type'=>'Diesel',
            //   'Age_group'=>'New',
            //   'Engine_capacity'=>'150-350 cc',
            //   'Condition_type'=>'New',
                 'Product'=>'PCV TAXI',
                'Premium_type'=>'NND',
            //   'Basis'=>'GWP',
                'Percentage' => $this->convertPercent($row[10]),    
            ],
            [
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                //   'Engine_capacity'=>'150-350 cc',
                //   'Condition_type'=>'New',
                     'Product'=>'PCV TAXI',
                     'Premium_type'=>'ND',
                //   'Basis'=>'GWP',
                     'Percentage' => $this->convertPercent($row[11]),   
            ],
            [

                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                //   'Engine_capacity'=>'150-350 cc',
                //   'Condition_type'=>'New',
                'Product'=>'PCV TAXI',
                'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                'Percentage' => $this->convertPercent($row[12]),   

            ],
            ],
            'PCV TAXI  KAALI PEELI ' => [
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                //   'Engine_capacity'=>'7-11 Str',
                //   'Condition_type'=>'New',
                     'Product'=>'PCV TAXI',
                    'Premium_type'=>'Comp/STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[13]),    
                ],
            ],
            'PCV Taxi 7-11 STR' => [
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                  'Engine_capacity'=>'7-11 Str',
                //   'Condition_type'=>'New',
                     'Product'=>'PCV TAXI',
                    'Premium_type'=>'Comp',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[14]),    
                ],
                [
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                  'Engine_capacity'=>'7-11 Str',
                //   'Condition_type'=>'New',
                     'Product'=>'PCV TAXI',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[14]),    
                ],
            ],

            // gcv <2k
            'GCV < 2 K' => [
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                  'Engine_capacity'=>'<2K',
                //   'Condition_type'=>'New',
                     'Product'=>'TATA / Maruti',
                    'Premium_type'=>'Comp',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[15]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                  'Engine_capacity'=>'<2K',
                //   'Condition_type'=>'New',
                     'Product'=>'TATA / Maruti',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[16]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                  'Engine_capacity'=>'<2K',
                //   'Condition_type'=>'New',
                     'Product'=>'Other Make	',
                    'Premium_type'=>'COMP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[17]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                  'Engine_capacity'=>'<2K',
                //   'Condition_type'=>'New',
                     'Product'=>'Other Make	',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[18]),    
                ],
            ],
            // gcv <2k
            'GCV  2K - 2.5K' => [
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                  'Engine_capacity'=>'<2K',
                //   'Condition_type'=>'New',
                     'Product'=>'TATA / Maruti',
                    'Premium_type'=>'Comp',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[19]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                  'Engine_capacity'=>'<2K',
                //   'Condition_type'=>'New',
                     'Product'=>'TATA / Maruti',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[20]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                  'Engine_capacity'=>'<2K',
                //   'Condition_type'=>'New',
                     'Product'=>'Other Make	',
                    'Premium_type'=>'COMP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[21]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                  'Engine_capacity'=>'<2K',
                //   'Condition_type'=>'New',
                     'Product'=>'Other Make	',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[22]),    
                ],
            ],
            // 2.5K - 3.5K
            'GCV 2.5K-3.5K' => [
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                  'Engine_capacity'=>'<2K',
                //   'Condition_type'=>'New',
                     'Product'=>'All Make/All Ages',
                    'Premium_type'=>'Comp',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[23]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                  'Engine_capacity'=>'<2K',
                //   'Condition_type'=>'New',
                     'Product'=>'All Make/All Ages',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[24]),    
                ],
                
            ],
            'GCV 3.5K-7.5K' => [
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                  'Engine_capacity'=>'3.5K-7.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'All Make/All Ages',
                    'Premium_type'=>'Comp/STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[25]),    
                ],
            ],
            'GCV 7.5K-12K' => [
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                  'Engine_capacity'=>'7.5K-12K',
                //   'Condition_type'=>'New',
                     'Product'=>'All Make/All Ages	',
                    'Premium_type'=>'Comp',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[26]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'New',
                  'Engine_capacity'=>'7.5K-12K',
                //   'Condition_type'=>'New',
                     'Product'=>'All Make/All Ages',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[27]),    
                ],
                
            ],


            'GCV 12K-20K ( COMP TATA/AL/Eicher Makes & STP All Makes)' => [
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Upto 5years',
                  'Engine_capacity'=>'12K-20K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes	',
                    'Premium_type'=>'Comp',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[28]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Upto 5years',
                  'Engine_capacity'=>'12K-20K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes	',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[29]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Above 5 Years',
                  'Engine_capacity'=>'12K-20K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes',
                    'Premium_type'=>'COMP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[30]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Above 5 Years',
                  'Engine_capacity'=>'12K-20K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[31]),    
                ],
                
            ],


            'GCV 20K-40K ( COMP TATA/AL/Eicher Makes & STP All Makes)			' => [
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Upto 5years',
                  'Engine_capacity'=>'20K-40K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes	',
                    'Premium_type'=>'Comp',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[32]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Upto 5years',
                  'Engine_capacity'=>'20K-40K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes	',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[33]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Above 5 Years',
                  'Engine_capacity'=>'20K-40K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes',
                    'Premium_type'=>'COMP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[34]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Above 5 Years',
                  'Engine_capacity'=>'20K-40K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[35]),    
                ],
                
            ],

            'GCV >40K-45.5K ( COMP TATA/AL/Eicher Makes & STP All Makes)' => [
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Upto 5years',
                  'Engine_capacity'=>'>40K-45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes	',
                    'Premium_type'=>'Comp',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[36]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Upto 5years',
                  'Engine_capacity'=>'>40K-45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes	',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[37]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Above 5 Years',
                  'Engine_capacity'=>'>40K-45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes',
                    'Premium_type'=>'COMP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[38]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Above 5 Years',
                  'Engine_capacity'=>'>40K-45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[39]),    
                ],
                
            ],
            
            'GCV >45.5K ( COMP TATA/AL/Eicher Makes & STP All Makes)' => [
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Upto 5years',
                  'Engine_capacity'=>'>45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes	',
                    'Premium_type'=>'Comp',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[36]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Upto 5years',
                  'Engine_capacity'=>'>45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes	',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[37]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Above 5 Years',
                  'Engine_capacity'=>'>45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes',
                    'Premium_type'=>'COMP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[38]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Above 5 Years',
                  'Engine_capacity'=>'>45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[39]),    
                ],
                
            ],
            'GCV 3W (Non Electric)' => [
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'Upto 5years',
                //   'Engine_capacity'=>'>45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'Non Electric All Makes',
                    'Premium_type'=>'Comp',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[40]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'Upto 5years',
                //   'Engine_capacity'=>'>45.5K',
                // //   'Condition_type'=>'New',
                     'Product'=>'Non Electric All Makes',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[41]),    
                ],
            ],
            'GCV 3W (Electric)' => [
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'Upto 5years',
                //   'Engine_capacity'=>'>45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'Electric All Makes	',
                    'Premium_type'=>'Comp',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[42]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'Upto 5years',
                //   'Engine_capacity'=>'>45.5K',
                // //   'Condition_type'=>'New',
                     'Product'=>'Electric All Makes	',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[43]),    
                ],
            ],
            'Flat Bed' => [
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'Upto 5years',
                //   'Engine_capacity'=>'>45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'Flat Bed',
                    'Premium_type'=>'Comp/STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[43]),    
                ],
           
            ],
            'Car Carrier' => [
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'Upto 5years',
                //   'Engine_capacity'=>'>45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'Car Carrier',
                    'Premium_type'=>'Comp/STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[44]),    
                ],
           
            ],
            'MISD CPM ' => [
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'Upto 5years',
                //   'Engine_capacity'=>'>45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'MISD CPM (Only JCB, L&T and Caterpillar)',
                    'Premium_type'=>'Comp',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[45]),    
                ],
           
            ],
            // tracktore
            'Agricultural Tractor W/o Trailer)' => [
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'Upto 5years',
                //   'Engine_capacity'=>'>45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'Tractor - (Agricultural without Trailer)',
                    'Premium_type'=>'Comp(Fresh)',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[46]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'<5 Years',
                //   'Engine_capacity'=>'>45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'Agricultural Tractor W/o Trailer)',
                    'Premium_type'=>'Comp(Non fresh)',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[47]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'>5 Years',
                //   'Engine_capacity'=>'>45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'Agricultural Tractor W/o Trailer)',
                    'Premium_type'=>'Comp(Non fresh)',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[48]),    
                ],
                [
    
                // 'Fule_type'=>'Diesel',
                  'Age_group'=>'Above 5 Years',
                  'Engine_capacity'=>'>45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'COMP TATA/AL/Eicher Makes & STP All Makes',
                    'Premium_type'=>'STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[49]),    
                ],
                
            ],
            'Employee Pick up' => [
                [
    
                // 'Fule_type'=>'Diesel',
                //   'Age_group'=>'Upto 5years',
                //   'Engine_capacity'=>'>45.5K',
                //   'Condition_type'=>'New',
                     'Product'=>'Employee Pick up',
                     'Premium_type'=>'Comp/STP',
                //   'Basis'=>'GWP',
                    'Percentage' => $this->convertPercent($row[45]),    
                ],
           
            ],


        ],
    ];
}


private function parseTwOtcRow($row){
  
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
   
//    dd($data);
if($companyName == 'TW_OTC'){
    $state = State::firstOrCreate(['name' => $data['State_name']]);
  
    foreach ($data['vehicle_type_CV'] as $vehicleType => $categories) {
      
        $vehicleCategory = VehicleCategory::firstOrCreate(['name' => $vehicleType ,'company_name'=>$companyName]);
       
      if($categories != null){
            $section = Section::firstOrCreate(['name' => 'CV']);
            DB::table('commission_rates')->insert([
                'state_id' => $state->id,
                'vehicle_category_id' => $vehicleCategory->id,
                'section_id' => $section->id,
                'value' => $categories,
                'rto_category' =>  $data['rto_category'],
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
       
            $section = Section::firstOrCreate(['name' => 'MHCV']);
            if($categories != null){
            DB::table('commission_rates')->insert([
                'state_id' => $state->id,
                'vehicle_category_id' => $vehicleCategory->id,
                'section_id' => $section->id,
                'value' => $categories,
                'rto_category' =>  $data['rto_category'],
                'created_month' => date('Y-m'),
                'is_new'=>0,
                'upload_id' => $uploadId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }


}elseif($companyName == 'MAGMA'){

if($data['State_name']){

    if(isset($data['circle'])){
        $region = Region::firstOrCreate(['name' => $data['circle']]);
        $state = State::firstOrCreate(['name' => $data['State_name'], 'region_id' => $region->id]);
        }else{
            $state = State::firstOrCreate(['name' => $data['State_name']]);
        }
        $company = PoliciesCompany::firstOrCreate(['company_name' => $companyName]);

        // $circle = Circle::firstOrCreate(['name' => $data['circle']]);
   
        foreach ($data['vehicle_type'] as $vehicleType => $categories) {
            $vehicleCategory = VehicleCategory::firstOrCreate(['name' => $vehicleType ,'company_name'=>$companyName]);
            if(isset($categories['Percentage']) && $categories['Percentage'] != 0){
                $commissionpolicy = new CommissionPolicy([ // Fixed class name capitalization
                    'state' => $state->id,
                    'vehicle_type' => $vehicleCategory->id,
                    'fuel_type' => $categories['Fule_type'] ?? null, // Store null if key not available
                    'int_cluster' =>$data['circle'] ?? null, // Store null if key not available
                    'age_group' => $categories['Age_group'] ?? null, // Store null if key not available
                    'engine_capacity' => $categories['Engine_capacity'] ?? null, // Store null if key not available
                    'condition_type' => $categories['Condition_type'] ?? null, // Store null if key not available
                    'premium_type' => $categories['Premium_type'] ?? null, // Store null if key not available
                    'basis' => $categories['Basis'] ?? null, // Store null if key not available
                    'amount' => $categories['Percentage'] ?? null, // Store null if key not available
                    'company_id' => $company->id ?? null,
                    'product' => $categories['Product'] ?? null, // Store null if key not available
                    'upload_id' => $uploadId,

                ]);
                $commissionpolicy->save();
            }
        }
    }   

}elseif($companyName == 'RELIANCE' || $companyName == 'SBI' || $companyName == 'ROYAL' || $companyName == 'LIBERTY'){
// reliance grid save

if($data['State_name']){

    if(isset($data['circle'])){
        $region = Region::firstOrCreate(['name' => $data['circle']]);
        $state = State::firstOrCreate(['name' => $data['State_name'], 'region_id' => $region->id]);
        }else{
            $state = State::firstOrCreate(['name' => $data['State_name']]);
        }
        $company = PoliciesCompany::firstOrCreate(['company_name' => $companyName]);

        // $circle = Circle::firstOrCreate(['name' => $data['circle']]);
   
        foreach ($data['vehicle_type'] as $vehicleType => $categories) {
            $vehicleCategory = VehicleCategory::firstOrCreate(['name' => $vehicleType ,'company_name'=>$companyName]);
            // dd($categories);
            foreach ($categories as $value) 
          
            if(isset($value['Percentage']) && $value['Percentage'] != 0){
                $commissionpolicy = new CommissionPolicy([ // Fixed class name capitalization
                    'state' => $state->id,
                    'vehicle_type' => $vehicleCategory->id,
                    'fuel_type' => $value['Fule_type'] ?? null, // Store null if key not available
                    'int_cluster' =>$data['circle'] ?? null, // Store null if key not available
                    'age_group' => $value['Age_group'] ?? null, // Store null if key not available
                    'engine_capacity' => $value['Engine_capacity'] ?? null, // Store null if key not available
                    'condition_type' => $value['Condition_type'] ?? null, // Store null if key not available
                    'premium_type' => $value['Premium_type'] ?? null, // Store null if key not available
                    'basis' => $value['Basis'] ?? null, // Store null if key not available
                    'amount' => $value['Percentage'] ?? null, // Store null if key not available
                    'company_id' => $company->id ?? null,
                    'product' => $value['Product'] ?? null, // Store null if key not available
                    'upload_id' => $uploadId,
                ]);
                $commissionpolicy->save();
            }
        }
        }
    

// reliance grid save
}else{
if($data['State_name']){
    if(isset($data['region'])){
    $region = Region::firstOrCreate(['name' => $data['region']]);
    $state = State::firstOrCreate(['name' => $data['State_name'], 'region_id' => $region->id]);
    }else{
        $state = State::firstOrCreate(['name' => $data['State_name']]);
    }
    $circle = Circle::firstOrCreate(['name' => $data['circle']]);

    foreach ($data['vehicle_type'] as $vehicleType => $categories) {
        $vehicleCategory = VehicleCategory::firstOrCreate(['name' => $vehicleType ,'company_name'=>$companyName]);
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
