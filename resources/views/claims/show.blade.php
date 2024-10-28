@extends('layout')

@section('content')
<div class="container">
    <h1>Claim Details</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Claim Number: {{ $claim->claim_number }}</h5>
            <p class="card-text"><strong>Claim Date:</strong> {{ $claim->claim_date }}</p>
            <p class="card-text"><strong>Claim Amount:</strong> ${{ number_format($claim->claim_amount, 2) }}</p>
            <p class="card-text"><strong>Policy ID:</strong> {{ $claim->policy_id }}</p>
            <p class="card-text"><strong>Client ID:</strong> {{ $claim->client_id }}</p>
        </div>
    </div>
    <div class="mt-3">
        <a href="{{ route('claims.edit', $claim->id) }}" class="btn btn-warning">Edit</a>
        <form action="{{ route('claims.destroy', $claim->id) }}" method="POST" style="display: inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this claim?')">Delete</button>
        </form>
        <a href="{{ route('claims.index') }}" class="btn btn-secondary">Back to Claims</a>
    </div>
</div>
@endsection
