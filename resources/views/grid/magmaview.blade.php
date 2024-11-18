@extends('layout')

@section('content')
<!-- Include Bootstrap CSS -->
{{-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> --}}

<div class="container mt-4">
    <form id="commissionPolicyForm" class="mb-4">
        @csrf
        <div class="row">
            @if ($user->can('edit_grid'))

            <div class="col-md-4">
                <div class="form-group">
                    <label for="company_id">Select Company:</label>
                    <select name="upload_id" id="company_id" required class="form-control">
                        <option value="" selected>Select Company</option>
                        @foreach($uploadalogs as $griduploadlg)
                            <option value="{{ $griduploadlg->upload_id }}">{{  $griduploadlg->policiesCompany->company_name ?? ""  }}-{{$griduploadlg->created_month ?? ""}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

@else

            <div class="col-md-4">
                <div class="form-group">
                    <label for="company_id">Select Company:</label>
                    <select name="company_id" id="company_id" required class="form-control">
                        <option value="" selected>Select Company</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif  
            <div class="col-md-4">
                <div class="form-group">
                    <label for="state_id">Select State:</label>
                    <select name="state_id" id="state_id" required class="form-control">
                        <option value="">Select a state</option>
                       
                    </select>
                </div>
            </div>
            <div class="col-md-4 mt-4">
                <button type="submit" class="btn btn-primary">
                    Show Commission Policies
                </button>
            </form>
        </div>
</div>

    <div id="commissionPolicies" class="mt-4 table-responsive"></div>
</div>
{{-- // Modal for editing policy amount --}}
<div class="modal fade" id="editPolicyModal" tabindex="-1" role="dialog" aria-labelledby="editPolicyModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPolicyModalLabel">Edit Policy Commission</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editPolicyForm">
                    @csrf
                    <input type="hidden" name="id" id="policyId">
                    <div class="form-group">
                        <label for="policyAmount">Commission:</label>
                        <input type="text" class="form-control" id="policyAmount" min="0" max="100" name="amount" required>
                    </div>
                    <div class="form-group">
                        <label for="policyBasis">Basis:</label>
                        <input type="text" class="form-control" id="policyBasis" name="basis" required>
                    </div><br>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- 
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script> --}}
<script>
    $(document).ready(function() {
        $('#commissionPolicyForm').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            $.ajax({
                url: '{{ route('commission.policy.show') }}',
                type: 'POST',
                data: $(this).serialize(),
                success: function(data) {
                    $('#commissionPolicies').empty(); // Clear previous results
                    if (data.length === 0) {
                        $('#commissionPolicies').append('<p class="text-danger">No commission policies found for the selected company and state.</p>');
                    } else {
                        let table = '<table class="table table-bordered"><thead class="thead-light"><tr>';
                       
                        table += '<th>State</th>';
                         table += '<th>Product</th>';
                        table += '<th>Vehicle Type</th>';
                        table += '<th>Fuel Type</th>'; 
                        table += '<th>Age Group</th>';
                        table += '<th>Engine Capacity</th>';
                        table += '<th>Condition Type</th>';
                        table += '<th>Premium Type</th>';
                        table += '<th>Basis</th>';
                        table += '<th>Commission</th>';
                        table += '<th>Action</th>';
                        table += '</tr></thead><tbody>';
                        data.forEach(function(policy) {
                            table += '<tr>';
                            table += '<td>' + (policy.state_name || '') + '</td>';
                             table += '<td>' + (policy.product || '') + '</td>';
                            table += '<td>' + (policy.vehicle_type_name || '') + '</td>';
                            table += '<td>' + (policy.fuel_type || '') + '</td>';
                            table += '<td>' + (policy.age_group || '') + '</td>';
                            table += '<td>' + (policy.engine_capacity || '') + '</td>';
                            table += '<td>' + (policy.condition_type || '') + '</td>';
                            table += '<td>' + (policy.premium_type || '') + '</td>';
                            table += '<td>' + (policy.basis || '') + '</td>';
                            table += '<td>' + ((Number(policy.amount) + {{$deduction}}).toFixed(2)) + '%</td>';
                            table += '<td><button class="btn btn-warning edit-policy" data-id="' + policy.id + '" data-amount="' + policy.amount + '" data-basis="' + policy.basis + '">Edit</button></td>';
                            table += '</tr>';
                        });
                        table += '</tbody></table>';
                        $('#commissionPolicies').append(table);
                    }
                },
                error: function(xhr) {
                    // Handle errors if needed
                    $('#commissionPolicies').append('<p class="text-danger">An error occurred while fetching the data.</p>');
                }
            });
        });

        // Handle edit button click
        $(document).on('click', '.edit-policy', function() {
            const policyId = $(this).data('id');
            const policyAmount = $(this).data('amount');
            const policyBasis = $(this).data('basis');

            $('#policyId').val(policyId);
            $('#policyAmount').val(policyAmount);
            $('#policyBasis').val(policyBasis);
            $('#editPolicyModal').modal('show');
        });

        // Handle edit form submission
        $('#editPolicyForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '{{ route('commission.policy.update') }}',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#editPolicyModal').modal('hide');
                    // Optionally refresh the policies or update the table
                    // location.reload(); // or update the specific row
                },
                error: function(xhr) {
                    // Handle errors if needed
                    alert('An error occurred while updating the policy.');
                }
            });
        });

        $('#company_id, #upload_id').on('change', function() {
            const selectedId = $(this).val(); // Get the value of the selected option
            const isUploadId = $(this).attr('name') === 'upload_id'; // Check if the changed element is upload_id

            $.ajax({
                url: '{{ route('get.states.by.company') }}',
                type: 'GET',
                data: { 
                    company_id: isUploadId ? null : selectedId, // Send null if upload_id is selected
                    upload_id: isUploadId ? selectedId : null // Send selectedId if upload_id is selected
                },
                success: function(states) {
                    $('#state_id').empty();
                    $('#state_id').append('<option value="">Select a state</option>');
                    states.forEach(function(state) {
                        // Check if region_name is available
                        let regionDisplay = state.region_name ? ' - ' + state.region_name : '';
                        $('#state_id').append('<option value="' + state.id + '">' + state.state_name + regionDisplay + '</option>');
                    });
                },
                error: function(xhr) {
                    // Handle errors if needed
                    alert('An error occurred while fetching states.');
                }
            });
        });
    });
</script>

<style>
    /* Add this CSS to make the table header sticky */
    .table-responsive {
        position: relative;
        overflow: auto;
        max-height: 400px; /* Adjust height as needed */
    }
    .table thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa; /* Background color for the header */
        z-index: 10; /* Ensure the header is above other content */
    }
</style>

@endsection