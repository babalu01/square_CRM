@extends('layout')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>

<div class="bg-gray-100 min-h-screen py-8">
   
        <div class="p-6">
            <div class=" mx-auto p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 border border-gray-300 p-4 rounded-lg">
                    <div>
                        <p class="text-gray-600">ID:</p>
                        <p class="text-gray-600">{{ $policy->id }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Policy Number:</p>
                        <p class="text-gray-600">{{ $policy->policy_number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Type:</p>
                        <p class="text-gray-600">{{ $policy->type }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Provider:</p>
                        <p class="text-gray-600">{{ $policy->provider }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Premium Amount:</p>
                        <p class="text-gray-600">{{ number_format($policy->premium_amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Start Date:</p>
                        <p class="text-gray-600">{{ $policy->start_date }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">End Date:</p>
                        <p class="text-gray-600">{{ $policy->end_date }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Status:</p>
                        <p class="text-gray-600">{{ $policy->status }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Created At:</p>
                        <p class="text-gray-600">{{ $policy->created_at }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Updated At:</p>
                        <p class="text-gray-600">{{ $policy->updated_at }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Company:</p>
                        <p class="text-gray-600">{{ $policy->company }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Product:</p>
                        <p class="text-gray-600">{{ $policy->product }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Manufacturing Year:</p>
                        <p class="text-gray-600">{{ $policy->mfg_year }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Fuel Type:</p>
                        <p class="text-gray-600">{{ $policy->fuel_type }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">GVW/CC:</p>
                        <p class="text-gray-600">{{ $policy->gvw_cc }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Policy Holder Name:</p>
                        <p class="text-gray-600">{{ $policy->policy_holder_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">OD:</p>
                        <p class="text-gray-600">{{ number_format($policy->od, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Without GST:</p>
                        <p class="text-gray-600">{{ number_format($policy->without_gst, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Total:</p>
                        <p class="text-gray-600">{{ number_format($policy->total, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Registration Number:</p>
                        <p class="text-gray-600">{{ $policy->registration_number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Policy Type:</p>
                        <p class="text-gray-600">{{ $policy->policy_type }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Agent Name:</p>
                        <p class="text-gray-600">{{ $policy->agent_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Broker/Direct Code:</p>
                        <p class="text-gray-600">{{ $policy->broker_direct_code }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Mode of Payment:</p>
                        <p class="text-gray-600">{{ $policy->mode_of_payment }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Percentage:</p>
                        <p class="text-gray-600">{{ number_format($policy->percentage, 2) }}%</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Commission:</p>
                        <p class="text-gray-600">{{ number_format($policy->commission, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">TDS:</p>
                        <p class="text-gray-600">{{ number_format($policy->tds, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Final Commission:</p>
                        <p class="text-gray-600">{{ number_format($policy->final_commission, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Discount Percentage:</p>
                        <p class="text-gray-600">{{ number_format($policy->discount_percentage, 2) }}%</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Discount:</p>
                        <p class="text-gray-600">{{ number_format($policy->discount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Payment:</p>
                        <p class="text-gray-600">{{ number_format($policy->payment, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Cheque No:</p>
                        <p class="text-gray-600">{{ $policy->cheque_no }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Payment Received:</p>
                        <p class="text-gray-600">{{ number_format($policy->payment_received, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Profit:</p>
                        <p class="text-gray-600">{{ number_format($policy->profit, 2) }}</p>
                    </div>
                    <div>
                    <a href="{{ route('policies.edit', $policy->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                Update Policy
            </a>
                    </div>
                    <div>
                    <a href="{{ route('policies.index') }}" class="ml-4 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                Back to Policies
            </a>
                    </div>
                </div>
            </div>
            
            
        </div>
        
    <!-- </div> -->
</div>
@endsection
