<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Policy Number</th>
                <th>Policy Holder Name</th>
                <th>Registration Number</th>
                <th>Agent Name</th>
                <th>Type</th>
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
                    <td><a href="{{ route('policies.show', $policy->id) }}">{{ $policy->policy_number }}</a></td>
                    <td>{{ $policy->policy_holder_name }}</td>
                    <td>{{ $policy->registration_number }}</td>
                    <td>{{ $policy->client->first_name ?? "N/A" }} {{ $policy->client->last_name ?? "" }}</td>
                    <td>{{ $policy->type }}</td>
                    <td>{{ $policy->provider }}</td>
                    <td>₹{{ number_format($policy->premium_amount, 2) }}</td>
                    <td>{{ $policy->status }}</td>
                    <td>{{ $policy->start_date }}</td>
                    <td>{{ $policy->end_date }}</td>
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
