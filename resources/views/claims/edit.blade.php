@extends('layout')

@section('content')
<div class="container">
    <h1>Edit Claim</h1>
    <form action="{{ route('claims.update', $claim->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="claim_number">Claim Number</label>
            <input type="text" class="form-control" id="claim_number" name="claim_number" value="{{ $claim->claim_number }}" required>
        </div>
        <div class="form-group">
            <label for="claim_date">Claim Date</label>
            <input type="date" class="form-control" id="claim_date" name="claim_date" value="{{ $claim->claim_date }}" required>
        </div>
        <div class="form-group">
            <label for="claim_amount">Claim Amount</label>
            <input type="number" step="0.01" class="form-control" id="claim_amount" name="claim_amount" value="{{ $claim->claim_amount }}" required>
        </div>
        <div class="form-group">
            <label for="policy_id">Policy ID</label>
            <input type="number" class="form-control" id="policy_id" name="policy_id" value="{{ $claim->policy_id }}" required>
        </div>
        <div class="form-group">
            <label for="client_id">Client ID</label>
            <input type="number" class="form-control" id="client_id" name="client_id" value="{{ $claim->client_id }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('claims.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
