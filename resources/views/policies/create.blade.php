@extends('layout')

@section('content')
<div class="container">
    <div class="card p-4">
    <h1>Create Policy</h1>
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    <form action="{{ route('policies.store') }}" method="POST" class="needs-validation" novalidate>
        @csrf
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="customer_name" class="form-label">Customer Name</label>
                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
            </div>
            <div class="col-md-6">

                            <label for="agent_name" class="form-label">Agent Name</label>
                            <select class="form-select" id="agent_name" name="agent_name">
                                <option value="">Select an agent</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->first_name }} {{ $agent->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="partner_code" class="form-label">Partner Code</label>
                <input type="text" class="form-control" id="partner_code" name="partner_code" required>
            </div>
            <div class="col-md-6">
                <label for="insurer_name" class="form-label">Insurer Name</label>
                <select class="form-select" id="insurer_name" name="insurer_name" required>
                    <option value="">Select an insurer</option>
                    <option value="Future Generali India Insurance Company">Future Generali India Insurance Company</option>
                    <option value="Tata AIG General Insurance Co. Ltd.">Tata AIG General Insurance Co. Ltd.</option>
                    <option value="Cholamandalam MS General Insurance Co. Ltd.">Cholamandalam MS General Insurance Co. Ltd.</option>
                    <option value="Liberty General Insurance Ltd.">Liberty General Insurance Ltd.</option>
                    <option value="Bajaj Allianz General Insurance Co. Ltd.">Bajaj Allianz General Insurance Co. Ltd.</option>
                    <option value="Go Digit General Insurance Ltd.">Go Digit General Insurance Ltd.</option>
                    <option value="Shriram General Insurance Co. Ltd.">Shriram General Insurance Co. Ltd.</option>
                    <option value="Royal Sundaram General Insurance Co. Ltd.">Royal Sundaram General Insurance Co. Ltd.</option>
                    <option value="Universal Sompo General Insurance Co. Ltd.">Universal Sompo General Insurance Co. Ltd.</option>
                    <option value="Magma HDI General Insurance Co. Ltd.">Magma HDI General Insurance Co. Ltd.</option>
                    <option value="ICICI Lombard General Insurance Co. Ltd.">ICICI Lombard General Insurance Co. Ltd.</option>
                    <option value="SBI General Insurance Co. Ltd.">SBI General Insurance Co. Ltd.</option>
                    <option value="United India Insurance Co. Ltd.">United India Insurance Co. Ltd.</option>
                    <option value="Iffco Tokio General Insurance Co. Ltd.">Iffco Tokio General Insurance Co. Ltd.</option>
                    <option value="HDFC Ergo General Insurance Co. Ltd.">HDFC Ergo General Insurance Co. Ltd.</option>
                    <option value="National Insurance Co. Ltd.">National Insurance Co. Ltd.</option>
                    <option value="Reliance General Insurance Co. Ltd.">Reliance General Insurance Co. Ltd.</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="business_type" class="form-label">Business Type</label>
                <input type="text" class="form-control" id="business_type" name="business_type" required>
            </div>
            <div class="col-md-6">
                <label for="lob" class="form-label">LOB</label>
                <input type="text" class="form-control" id="lob" name="lob" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="product" class="form-label">Product</label>
                <input type="text" class="form-control" id="product" name="product" required>
            </div>
            <div class="col-md-6">
                <label for="sub_product" class="form-label">Sub Product</label>
                <input type="text" class="form-control" id="sub_product" name="sub_product">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="segment" class="form-label">Segment</label>
                <input type="text" class="form-control" id="segment" name="segment">
            </div>
            <div class="col-md-6">
                <label for="plan_type" class="form-label">Plan Type</label>
                <input type="text" class="form-control" id="plan_type" name="plan_type" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="class_name" class="form-label">Class Name</label>
                <input type="text" class="form-control" id="class_name" name="class_name">
            </div>
            <div class="col-md-6">
                <label for="sub_class" class="form-label">Sub Class</label>
                <input type="text" class="form-control" id="sub_class" name="sub_class">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="vehicle_no" class="form-label">Vehicle No</label>
                <input type="text" class="form-control" id="vehicle_no" name="vehicle_no" required>
            </div>
            <div class="col-md-6">
                <label for="policy_no" class="form-label">Policy No</label>
                <input type="text" class="form-control" id="policy_no" name="policy_no" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="policy_issue_date" class="form-label">Policy Issue Date</label>
                <input type="date" class="form-control" id="policy_issue_date" name="policy_issue_date" required>
            </div>
            <div class="col-md-6">
                <label for="policy_start_date" class="form-label">Policy Start Date</label>
                <input type="date" class="form-control" id="policy_start_date" name="policy_start_date" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="policy_end_date" class="form-label">Policy End Date</label>
                <input type="date" class="form-control" id="policy_end_date" name="policy_end_date" required>
            </div>
            <div class="col-md-6">
                <label for="ncb" class="form-label">NCB</label>
                <input type="number" class="form-control" id="ncb" name="ncb" value="0">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="idv" class="form-label">IDV</label>
                <input type="number" class="form-control" id="idv" name="idv" required>
            </div>
            <div class="col-md-6">
                <label for="payment_mode" class="form-label">Payment Mode</label>
                <input type="text" class="form-control" id="payment_mode" name="payment_mode" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="payment_towards" class="form-label">Payment Towards</label>
                <input type="text" class="form-control" id="payment_towards" name="payment_towards" required>
            </div>
            <div class="col-md-6">
                <label for="payment_cheque_ref_no" class="form-label">Payment Cheque Ref No</label>
                <input type="text" class="form-control" id="payment_cheque_ref_no" name="payment_cheque_ref_no">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="gross_prem" class="form-label">Gross Premium</label>
                <input type="number" class="form-control" id="gross_prem" name="gross_prem" required>
            </div>
            <div class="col-md-6">
                <label for="net_prem" class="form-label">Net Premium</label>
                <input type="number" class="form-control" id="net_prem" name="net_prem" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="od_premium" class="form-label">OD Premium</label>
                <input type="number" class="form-control" id="od_premium" name="od_premium" required>
            </div>
            <div class="col-md-6">
                <label for="tp_premium" class="form-label">TP Premium</label>
                <input type="number" class="form-control" id="tp_premium" name="tp_premium" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="lpa_partner_payout_od" class="form-label">LPA Partner Payout OD%</label>
                <input type="number" class="form-control" id="lpa_partner_payout_od" name="lpa_partner_payout_od" value="0">
            </div>
            <div class="col-md-6">
                <label for="lpa_partner_payout_od_amount" class="form-label">LPA Partner Payout OD Amount</label>
                <input type="number" class="form-control" id="lpa_partner_payout_od_amount" name="lpa_partner_payout_od_amount" value="0">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="lpa_partner_payout_net" class="form-label">LPA Partner Payout Net%</label>
                <input type="number" class="form-control" id="lpa_partner_payout_net" name="lpa_partner_payout_net" value="0">
            </div>
            <div class="col-md-6">
                <label for="lpa_partner_payout_net_amount" class="form-label">LPA Partner Payout Net Amount</label>
                <input type="number" class="form-control" id="lpa_partner_payout_net_amount" name="lpa_partner_payout_net_amount" value="0">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="lpa_partner_total_amount" class="form-label">LPA Partner Total Amount</label>
                <input type="number" class="form-control" id="lpa_partner_total_amount" name="lpa_partner_total_amount" value="0">
            </div>
            <div class="col-md-6">
                <label for="remark" class="form-label">Remark</label>
                <textarea class="form-control" id="remark" name="remark"></textarea>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="total_policy_amount" class="form-label">Total Policy Amount</label>
                <input type="number" class="form-control" id="total_policy_amount" name="total_policy_amount" readonly>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
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

    function calculatePolicyAmount() {
        var grossPrem = parseFloat(document.getElementById('gross_prem').value) || 0;
        var netPrem = parseFloat(document.getElementById('net_prem').value) || 0;
        var odPremium = parseFloat(document.getElementById('od_premium').value) || 0;
        var tpPremium = parseFloat(document.getElementById('tp_premium').value) || 0;

        var totalAmount = grossPrem + netPrem + odPremium + tpPremium;
        document.getElementById('total_policy_amount').value = totalAmount;
    }

    // Add event listeners to relevant fields
    document.getElementById('gross_prem').addEventListener('input', calculatePolicyAmount);
    document.getElementById('net_prem').addEventListener('input', calculatePolicyAmount);
    document.getElementById('od_premium').addEventListener('input', calculatePolicyAmount);
    document.getElementById('tp_premium').addEventListener('input', calculatePolicyAmount);
</script>
@endsection
