@extends('layout')

@section('content')
<div class="container mt-3">
    <div class="card">
        <div class="card-body">
            <h1 class="card-title mb-4">Create New Policy</h1>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('policies.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="policy_number" class="form-label">Policy Number</label>
                            <input type="text" class="form-control" id="policy_number" name="policy_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="" name="end_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="company" class="form-label">Type</label>
                            <input type="text" class="form-control" id="type" name="type">
                        </div>
                        <div class="mb-3">
                            <label for="Provider" class="form-label">Provider</label>
                            <input type="text" class="form-control" id="type" name="provider">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="company" class="form-label">Company</label>
                            <select class="form-select" id="company" name="company" required onchange="fetchProducts()">
                                <option value="">Select a company</option>
                                <option value="SBI">SBI</option>
                                <option value="SHRIRAM">SHRIRAM</option>
                                <option value="TATA">TATA</option>
                                <option value="MAGMA">MAGMA</option>
                                <option value="ICICI">ICICI</option>
                                <option value="DIGIT">DIGIT</option>
                                <option value="ROYAL">ROYAL</option>
                                <option value="RAHEJA QBE">RAHEJA QBE</option>
                                <option value="LIBERTY">LIBERTY</option>
                                <option value="BAJAJ">BAJAJ</option>
                                <option value="CHOLA">CHOLA</option>
                                <option value="HDFC ERGO">HDFC ERGO</option>
                                <option value="UNITED INDIA">UNITED INDIA</option>
                                <option value="RELIANCE">RELIANCE</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="product" class="form-label">Product</label>
                            <select class="form-select" id="product" name="product">
                                <option value="">Select a product</option>
                                <!-- Options will be populated here based on selected company -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="mfg_year" class="form-label">Manufacturing Year</label>
                            <input type="number" class="form-control" id="mfg_year" name="mfg_year">
                        </div>
                        <div class="mb-3">
                            <label for="fuel_type" class="form-label">Fuel Type</label>
                            <input type="text" class="form-control" id="fuel_type" name="fuel_type">
                        </div>
                        <div class="mb-3">
                            <label for="gvw_cc" class="form-label">GVW/CC</label>
                            <input type="text" class="form-control" id="gvw_cc" name="gvw_cc">
                        </div>
                        <div class="mb-3">
                            <label for="policy_holder_name" class="form-label">Policy Holder Name</label>
                            <input type="text" class="form-control" id="policy_holder_name" name="policy_holder_name">
                        </div>
                        <div class="mb-3">
                            <label for="od" class="form-label">OD</label>
                            <input type="number" class="form-control" id="od" name="od" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="without_gst" class="form-label">Without GST</label>
                            <input type="number" class="form-control" id="without_gst" name="without_gst" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="total" class="form-label">Total</label>
                            <input type="number" class="form-control" id="total" name="total" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="registration_number" class="form-label">Registration Number</label>
                            <input type="text" class="form-control" id="registration_number" name="registration_number">
                        </div>
                        <div class="mb-3">
                            <label for="policy_type" class="form-label">Policy Type</label>
                            <input type="text" class="form-control" id="policy_type" name="policy_type">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="agent_name" class="form-label">Agent Name</label>
                            <select class="form-select" id="agent_name" name="agent_name">
                                <option value="">Select an agent</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->first_name }} {{ $agent->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="broker_direct_code" class="form-label">Broker Direct Code</label>
                            <input type="text" class="form-control" id="broker_direct_code" name="broker_direct_code">
                        </div>
                        <div class="mb-3">
                            <label for="mode_of_payment" class="form-label">Mode of Payment</label>
                            <input type="text" class="form-control" id="mode_of_payment" name="mode_of_payment">
                        </div>
                        <div class="mb-3">
                            <label for="percentage" class="form-label">Percentage</label>
                            <input type="number" class="form-control" id="percentage" name="percentage" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="commission" class="form-label">Commission</label>
                            <input type="number" class="form-control" id="commission" name="commission" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="tds" class="form-label">TDS</label>
                            <input type="number" class="form-control" id="tds" name="tds" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="final_commission" class="form-label">Final Commission</label>
                            <input type="number" class="form-control" id="final_commission" name="final_commission" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="discount_percentage" class="form-label">Discount Percentage</label>
                            <input type="number" class="form-control" id="discount_percentage" name="discount_percentage" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="discount" class="form-label">Discount</label>
                            <input type="number" class="form-control" id="discount" name="discount" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="payment" class="form-label">Payment</label>
                            <input type="number" class="form-control" id="payment" name="payment" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="cheque_no" class="form-label">Cheque No</label>
                            <input type="text" class="form-control" id="cheque_no" name="cheque_no">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="payment_received" class="form-label">Payment Received</label>
                            <input type="number" class="form-control" id="payment_received" name="payment_received" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="profit" class="form-label">Profit</label>
                            <input type="number" class="form-control" id="profit" name="profit" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Create Policy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function fetchProducts() {
    var companyId = document.getElementById('company').value;
    var productSelect = document.getElementById('product');
    
    // Clear previous options
    productSelect.innerHTML = '<option value="">Select a product</option>';
    
    if (companyId) {
        fetch(`{{ route('policies.getProductsByCompany') }}?company=${companyId}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(product => {
                    var option = document.createElement('option');
                    option.value = product.id;
                    option.textContent = product.name; // Adjust based on your product structure
                    productSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching products:', error));
    }
}
</script>
@endsection
