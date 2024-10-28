<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PolicyDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class PolicyDocumentController extends Controller
{
    public function showUploadForm()
    {
        return view('policies.documentupload');
    }
    public function upload(Request $request)
    {
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

            $fileNameWithoutExtension = pathinfo($originalFileName, PATHINFO_FILENAME);    
            $registrationNumber = $fileNameWithoutExtension;
}
            $notFoundFiles = array_merge($notFoundFiles, PolicyDocument::uploadDocuments([$file], $registrationNumber));
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
}
