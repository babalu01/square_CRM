<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Claim;
use App\Models\CommunicationLog;


class ClaimController extends Controller
{
    public function index()
    {
        $claims = Claim::all();
        // dd('claim');
        return view('claims.index', compact('claims'));
    }

    public function create()
    {
        return view('claims.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'policy_number' => 'required',
            'claim_status' => 'required|in:Filed,In Review,Settled,Denied',
            'date_filed' => 'required|date',
            'settlement_amount' => 'nullable|numeric',
            'documentation' => 'nullable|file',
            'communication_log' => 'nullable|string',
        ]);

        $claim = new Claim;
        $claim->policy_number = $request->policy_number;
        $claim->claim_status = $request->claim_status;
        $claim->date_filed = $request->date_filed;
        $claim->settlement_amount = $request->settlement_amount;
        
        if ($request->hasFile('documentation')) {
            $path = $request->file('documentation')->store('claim_documents');
            $claim->documentation = $path;
        }
        
        $claim->communication_log = $request->communication_log;
        $claim->save();

        return redirect()->route('claims.index');
    }

    public function show($id)
    {
        $claim = Claim::find($id);
        return view('claims.show', compact('claim'));
    }

    public function edit($id)
    {
        $claim = Claim::find($id);
        return view('claims.edit', compact('claim'));
    }

    public function update(Request $request, $id)
    {
        $claim = Claim::find($id);
        $this->validate($request, [
            'claim_number' => 'required',
            'claim_date' => 'required',
            'claim_amount' => 'required',
            'policy_id' => 'required',
            'client_id' => 'required',
        ]);

        $claim->claim_number = $request->claim_number;
        $claim->claim_date = $request->claim_date;
        $claim->claim_amount = $request->claim_amount;
        $claim->policy_id = $request->policy_id;
        $claim->client_id = $request->client_id;
        $claim->save();

        return redirect()->route('claims.index');
    }                   
}
