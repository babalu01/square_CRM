<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Policy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_id',
        'vehicle_no',
        'file_path',
        'agent_id',
        'notfound',
        'policy_details_id',
        'upload_id',
    ];

    public function policyDetails(): BelongsTo
    {
        return $this->belongsTo(PolicyDetails::class, 'policy_id', 'id');
    }

    public static function uploadDocuments($files, $registrationNumber,$upload_id=null)
    {
        // Find the policy by registration number
        $policy = PolicyDetails::where('Vehicle_No', $registrationNumber)->first();
// dd($policy);
        $Agent = client::where('client_id', $registrationNumber)->first();
        $notFoundFiles = [];

        if ($policy) {
            foreach ($files as $file) {
                // Accept all file types
                $validator = Validator::make(['file' => $file], [
                    'file' => 'required|file|max:2048', // 2MB Max
                ]);

                if ($validator->fails()) {
                    // Handle validation failure (optional)
                    continue;
                }

                // Create a unique filename using the registration number and the original file extension
                $fileExtension = $file->getClientOriginalExtension();
                $fileName = $registrationNumber . '_' . time() . '.' . $fileExtension; // e.g., 12345_1633036800.jpg

                // Store the file with the new name
                $filePath = $file->storeAs('policy_documents', $fileName, 'public');

                // Create a new PolicyDocument entry
                self::create([
                    'policy_details_id' => $policy->id,
                    'vehicle_no' => $registrationNumber, // Store the new filename as vehicle_no
                    'file_path' => $filePath,
                    'upload_id'=>$upload_id,
                ]);
            }
        } elseif($Agent){
            foreach ($files as $file) {
                // Accept all file types
                $validator = Validator::make(['file' => $file], [
                    'file' => 'required|file|max:2048', // 2MB Max
                ]);

                if ($validator->fails()) {
                    // Handle validation failure (optional)
                    continue;
                }

                // Create a unique filename using the registration number and the original file extension
                $fileExtension = $file->getClientOriginalExtension();
                $fileName = $registrationNumber . '_' . time() . '.' . $fileExtension; // e.g., 12345_1633036800.jpg

                // Store the file with the new name
                $filePath = $file->storeAs('policy_documents', $fileName, 'public');

                // Create a new PolicyDocument entry
                self::create([
                    'agent_id' => $registrationNumber,
                    // 'vehicle_no' => $registrationNumber, // Store the new filename as vehicle_no
                    'file_path' => $filePath,
                ]);
            }




        } else{
            // If policy not found, log the filenames
            foreach ($files as $file) {
                // $notFoundFiles[] = $file->getClientOriginalName();
                // not found documents
                $validator = Validator::make(['file' => $file], [
                    'file' => 'required|file|max:2048', // 2MB Max
                ]);

                if ($validator->fails()) {
                    // Handle validation failure (optional)
                    continue;
                }

                // Create a unique filename using the registration number and the original file extension
                $fileExtension = $file->getClientOriginalExtension();
                $fileName = $registrationNumber . '_' . time() . '.' . $fileExtension; // e.g., 12345_1633036800.jpg

                // Store the file with the new name
                $filePath = $file->storeAs('policy_documents', $fileName, 'public');

                // Create a new PolicyDocument entry
                self::create([
                    'notfound' => 1,
                    // 'vehicle_no' => $registrationNumber, // Store the new filename as vehicle_no
                    'file_path' => $filePath,
                    'upload_id'=>$upload_id,

                ]);




                // not found documents
            }
        }
        
        return $notFoundFiles; // Return the list of files that could not be associated
    }
}
