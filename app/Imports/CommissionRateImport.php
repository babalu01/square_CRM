<?php
namespace App\Imports;

use App\Models\Region;
use App\Models\State;
use App\Models\Circle;
use App\Models\VehicleCategory;
use App\Models\CommissionRate;
use App\Models\SpecialCondition;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CommissionRateImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
      
      
        // Adjust keys to match the column headers
        $regionName = $row['all_broker_above_5_lakhs_for_cv_and_above_3_lac_for_pvt_tw'] ?? null;
        $stateName = $row[1] ?? null; // Assuming the second element is the state name
        $circleName = $row[2] ?? null; // Assuming the third element is the circle name
        $compNet = $this->parseNumeric($row['upto_25t_gcv_including_gcv_3w'] ?? null);
        $satpNet = $this->parseNumeric($row[4] ?? null); // Assuming the fifth element is SATP (Net)
        $isNew = !empty($row[6]) ? true : false; // Assuming the seventh element indicates if it's new

        // Check if required fields are present
        if (is_null($regionName) || is_null($stateName) || is_null($circleName) || is_null($compNet) || is_null($satpNet)) {
            // Handle the missing data case (e.g., log an error, skip the row, etc.)
            return null; // Skip this row
        }

        // Fetch or create the region
        $region = Region::firstOrCreate(['name' => $regionName]);

        // Fetch or create the state under the region
        $state = State::firstOrCreate(
            ['name' => $stateName, 'region_id' => $region->id]
        );
        $circle = Circle::firstOrCreate(['name' => $circleName]);

        // Fetch or create vehicle categories based on the relevant keys
        $vehicleCategories = [];
        foreach ($row as $key => $value) {
            // Adjusted regex to match keys that are likely to be vehicle categories
            if (preg_match('/^(.*_comp_net|.*_satp|.*_carrying_capacity.*|.*_harvester.*)$/', $key)) { // Match keys that are vehicle categories
                $vehicleCategories[] = VehicleCategory::firstOrCreate(['name' => $key]);
            }
        }
        // dd($vehicleCategories); // Debugging line to check vehicle categories

        // Save the commission rate for each vehicle category
        foreach ($vehicleCategories as $vehicleCategory) {
            // Determine the correct keys for comp_net and satp_net based on the vehicle category
            $compNetKey = $this->getCompNetKey($vehicleCategory->name);
            $satpNetKey = $this->getSatpNetKey($vehicleCategory->name);

            CommissionRate::create([
                'state_id' => $state->id,
                'vehicle_category_id' => $vehicleCategory->id,
                'comp_net' => $this->parseNumeric($row[$compNetKey] ?? null), // Ensure correct value is used
                'satp_net' => $this->parseNumeric($row[$satpNetKey] ?? null), // Ensure correct value is used
                'is_new' => $isNew,
            ]);
        }

        // ... existing code ...
    }

    private function parseNumeric($value)
    {
        // Remove any non-numeric characters and convert to float
        return is_numeric($value) ? (float)$value : null;
    }

    private function getCompNetKey($vehicleCategoryName)
    {
        // Logic to determine the correct comp_net key based on the vehicle category name
        // Example: return 'some_key_based_on_' . $vehicleCategoryName;
    }

    private function getSatpNetKey($vehicleCategoryName)
    {
        // Logic to determine the correct satp_net key based on the vehicle category name
        // Example: return 'some_key_based_on_' . $vehicleCategoryName;
    }
}
