<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PolicyDocument;
use App\Models\PolicyDetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class PolicyDocumentController extends Controller
{
    public function showUploadForm()
    {
        return view('policies.documentupload');
    }
    public function upload(Request $request ,$upload_id=null)
    {
        // dd($request->all());
        // Validate the incoming request
        $request->validate([
            'files.*' => 'required|file|max:2048', // 2MB Max
        ]);

        $files = $request->file('files');
        $notFoundFiles = [];
        foreach ($files as $file) {
            // Extract registration number from the filename
            $originalFileName = $file->getClientOriginalName();
            // Remove the file extension
if($request->policy_id){
    $registrationNumber=$request->policy_id;
}else{
// read pdf file
// $parser = new \Smalot\PdfParser\Parser();
// $pdf = $parser->parseFile($file->getPathname()); // Use the uploaded file

// $text = $pdf->getText();
// preg_match('/Name\s*[:\xA0]*([\w\s]+(?:\s[\w\s]+)*)/u', $text, $nameMatches);
// $name = isset($nameMatches[1]) ? $nameMatches[1] : 'Name not found';

// // Extract Registration Number (assuming a format like 'MH 12 AB 1234')
// preg_match('/\b[A-Z]{2}\s?\d{2}\s?[A-Z]{1,2}\s?\d{1,4}\b/', $text, $regNumMatches);
// $regNumber = isset($regNumMatches[0]) ? $regNumMatches[0] : 'Registration number not found';

// // Output the extracted data
// dd(['Name' => $name, 'Registration Number' => $regNumber]);
// read pdf file
            $fileNameWithoutExtension = pathinfo($originalFileName, PATHINFO_FILENAME);    
            $registrationNumber = $fileNameWithoutExtension;
}
            $notFoundFiles = array_merge($notFoundFiles, PolicyDocument::uploadDocuments([$file], $registrationNumber ,$upload_id));
        }

        // Check if any files were not associated with a policy
        if (!empty($notFoundFiles)) {
            return redirect()->back()->with('error', 'The following files could not be associated with any policy: ' . implode(', ', $notFoundFiles));
        }

        return redirect()->back()->with('success', 'Documents uploaded successfully.');
    }

// delete policy document
public function destroy($id)
{
    // Find the document by ID
    $document = PolicyDocument::find($id);

    if ($document) {
        // Delete the document file from storage
        \Storage::delete($document->file_path);
        // Delete the document record from the database
        $document->delete();

        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false, 'message' => 'Document not found'], 404);
}

// Agent Documents
public function agentdocuments(){
    $user = getAuthenticatedUser();
    $policydocuments = PolicyDocument::where('agent_id', '!=', null)->where('agent_id', '=', $user->client_id)->get();
    return view('agent.documents',compact('policydocuments'));
}
// Agent Documents
// for pending documents
public function pendingdocuments(){
    $notfoundpolicies = PolicyDetails::whereDoesntHave('policydocuments')
        ->whereNull('partner_code')
        ->get();

    $policydocuments = PolicyDocument::where('notfound', '=', 1)->get();
    return view('policies.pendingpoliciesdocument',compact('policydocuments','notfoundpolicies'));
}
public function storePendingDocuments(Request $request, $id){
    try {
        // Validate the incoming request
        $request->validate([
            'registration_number' => 'required|exists:policy_details,Vehicle_No', // Ensure registration_number exists in policies table
        ]);

        $policydocument = PolicyDocument::find($id);
        if ($policydocument) {
            $policydocument->notfound = 0;
            $policydocument->vehicle_no = $request->registration_number;
            $policydocument->policy_details_id = PolicyDetails::where('Vehicle_No', $request->registration_number)->first()->id;
            $policydocument->save();

            // Return JSON response for successful upload
            return response()->json(['success' => true, 'message' => 'Document uploaded successfully.']);
        }

        // Return JSON response if document not found
        return response()->json(['success' => false, 'message' => 'Document not found.'], 404);
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Return validation errors
        return response()->json(['success' => false, 'errors' => $e->validator->errors()], 422);
    }
}



}
