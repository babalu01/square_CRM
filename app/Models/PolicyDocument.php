<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Policy;

class PolicyDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_id',
        'vehicle_no',
        'file_path',
        'agent_id',
    ];

    public static function uploadDocuments($files, $registrationNumber)
    {
        // Find the policy by registration number
        $policy = Policy::where('registration_number', $registrationNumber)->first();

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
                    'policy_id' => $policy->id,
                    'vehicle_no' => $registrationNumber, // Store the new filename as vehicle_no
                    'file_path' => $filePath,
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
                $notFoundFiles[] = $file->getClientOriginalName();
            }
        }

        return $notFoundFiles; // Return the list of files that could not be associated
    }
}
