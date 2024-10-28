@extends('layout')

@section('content')
<div class="container">
    <h1>Claims</h1>
    <a href="{{ route('claims.create') }}" class="btn btn-primary mb-3">Create New Claim</a>

    @if(count($claims) > 0)
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Claim Number</th>
                    <th>Claim Date</th>
                    <th>Claim Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($claims as $claim)
                    <tr>
                        <td>{{ $claim->claim_number }}</td>
                        <td>{{ $claim->claim_date }}</td>
                        <td>{{ $claim->claim_amount }}</td>
                        <td>
                            <a href="{{ route('claims.show', $claim->id) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('claims.edit', $claim->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('claims.destroy', $claim->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this claim?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No claims found.</p>
    @endif
</div>
@endsection
