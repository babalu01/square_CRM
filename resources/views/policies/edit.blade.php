@extends('layout')

@section('content')
<div class="container mt-3">
    <div class="card p-3">
        <h1 class="mb-4">Edit Policy</h1>
        <form action="{{ route('policies.update', $policy->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="policy_number">Policy Number</label>
                        <input type="text" class="form-control" id="policy_number" name="policy_number" value="{{ $policy->policy_number }}" required>
                    </div>
                    <div class="form-group">
                        <label for="type">Type</label>
                        <input type="text" class="form-control" id="type" name="type" value="{{ $policy->type }}" required>
                    </div>
                    <div class="form-group">
                        <label for="provider">Provider</label>
                        <input type="text" class="form-control" id="provider" name="provider" value="{{ $policy->provider }}" required>
                    </div>
                    <div class="form-group">
                        <label for="premium_amount">Premium Amount</label>
                        <input type="number" class="form-control" id="premium_amount" name="premium_amount" step="0.01" value="{{ $policy->premium_amount }}" required>
                    </div>
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $policy->start_date }}" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $policy->end_date }}" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="Active" {{ $policy->status == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ $policy->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="Pending" {{ $policy->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="company">Company</label>
                        <input type="text" class="form-control" id="company" name="company" value="{{ $policy->company }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="product">Product</label>
                        <input type="text" class="form-control" id="product" name="product" value="{{ $policy->product }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="mfg_year">Manufacturing Year</label>
                        <input type="number" class="form-control" id="mfg_year" name="mfg_year" value="{{ $policy->mfg_year }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="fuel_type">Fuel Type</label>
                        <input type="text" class="form-control" id="fuel_type" name="fuel_type" value="{{ $policy->fuel_type }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="gvw_cc">GVW/CC</label>
                        <input type="text" class="form-control" id="gvw_cc" name="gvw_cc" value="{{ $policy->gvw_cc }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="policy_holder_name">Policy Holder Name</label>
                        <input type="text" class="form-control" id="policy_holder_name" name="policy_holder_name" value="{{ $policy->policy_holder_name }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="od">OD</label>
                        <input type="number" class="form-control" id="od" name="od" step="0.01" value="{{ $policy->od }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="without_gst">Without GST</label>
                        <input type="number" class="form-control" id="without_gst" name="without_gst" step="0.01" value="{{ $policy->without_gst }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="total">Total</label>
                        <input type="number" class="form-control" id="total" name="total" step="0.01" value="{{ $policy->total }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="registration_number">Registration Number</label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number" value="{{ $policy->registration_number }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="policy_type">Policy Type</label>
                        <input type="text" class="form-control" id="policy_type" name="policy_type" value="{{ $policy->policy_type }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="agent_name">Agent Name</label>
                        <select class="form-control" id="agent_name" name="agent_name">
                            <option value="">Select an agent</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ $policy->agent_name == $agent->id ? 'selected' : '' }}>{{ $agent->first_name }} {{ $agent->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="broker_direct_code">Broker Direct Code</label>
                        <input type="text" class="form-control" id="broker_direct_code" name="broker_direct_code" value="{{ $policy->broker_direct_code }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="mode_of_payment">Mode of Payment</label>
                        <input type="text" class="form-control" id="mode_of_payment" name="mode_of_payment" value="{{ $policy->mode_of_payment }}">
                    </div>
                    <div class="form-group mb-3">
                        <label for="percentage">Percentage</label>
                        <input type="number" class="form-control" id="percentage" name="percentage" step="0.01" value="{{ $policy->percentage }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="commission">Commission</label>
                        <input type="number" class="form-control" id="commission" name="commission" step="0.01" value="{{ $policy->commission }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="tds">TDS</label>
                        <input type="number" class="form-control" id="tds" name="tds" step="0.01" value="{{ $policy->tds }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="final_commission">Final Commission</label>
                        <input type="number" class="form-control" id="final_commission" name="final_commission" step="0.01" value="{{ $policy->final_commission }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="discount_percentage">Discount Percentage</label>
                        <input type="number" class="form-control" id="discount_percentage" name="discount_percentage" step="0.01" value="{{ $policy->discount_percentage }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="discount">Discount</label>
                        <input type="number" class="form-control" id="discount" name="discount" step="0.01" value="{{ $policy->discount }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="payment">Payment</label>
                        <input type="number" class="form-control" id="payment" name="payment" step="0.01" value="{{ $policy->payment }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="cheque_no">Cheque No</label>
                        <input type="text" class="form-control" id="cheque_no" name="cheque_no" value="{{ $policy->cheque_no }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="payment_received">Payment Received</label>
                        <input type="number" class="form-control" id="payment_received" name="payment_received" step="0.01" value="{{ $policy->payment_received }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="profit">Profit</label>
                        <input type="number" class="form-control" id="profit" name="profit" step="0.01" value="{{ $policy->profit }}">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Update Policy</button>
            <a href="{{ route('policies.show', $policy->id) }}" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
</div>
@endsection
