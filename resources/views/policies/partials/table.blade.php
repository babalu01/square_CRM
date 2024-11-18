<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Policy Number</th>
                <th>Policy Holder Name</th>
                <th>Registration Number</th>
                <th>Agent Name</th>
                <th>Product</th>
                <th>Provider</th>
                <th>Premium Amount</th>
                <th>Status</th>
                <th>Start Date</th>
                <th>End Date</th>   
                @if($user->can('edit_policies'))
                <th>Action</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($policies as $policy)
                <tr>
                    <td>{{ $policy->id }}</td>
                    <td><a href="{{ route('policies.show', $policy->id) }}">{{ $policy->Policy_No }}</a></td>
                    <td>{{ $policy->CustomerName }}</td>
                    <td>{{ $policy->Vehicle_No }}</td>
                    <td>{{ $policy->Partner_Name ?? "N/A" }}</td>
                    <td>{{ $policy->Product }}</td>
                    <td>{{ $policy->Insurer_Name }}</td>
                    <td>₹{{ number_format($policy->NetPrem, 2) }}</td>
                    <td>{{ $policy->STATUS }}</td>
                    <td>{{ $policy->PolicyStartDateTP }}</td>
                    <td>{{ $policy->PolicyEndDateTP }}</td>
                @if($user->can('edit_policies'))
                    <td>
                   <a href="{{ route('policies.edit', $policy->id) }}"> <i class="bx bx-edit mx-1"></i></a>
                    </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{ $policies->appends(request()->query())->links('pagination::bootstrap-4') }}
