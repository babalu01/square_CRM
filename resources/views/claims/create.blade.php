@extends('layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-6 text-center text-blue-600">Create New Claim</h1>
    <form action="{{ route('claims.store') }}" method="POST" class="bg-white shadow-lg rounded-lg p-8 mb-4">
        @csrf
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2" for="claim_number">
                Claim Number
            </label>
            <input class="form-control shadow-md appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400" id="claim_number" name="claim_number" type="text" required>
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2" for="claim_date">
                Claim Date
            </label>
            <input class="form-control shadow-md appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400" id="claim_date" name="claim_date" type="date" required>
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2" for="claim_amount">
                Claim Amount
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">₹</span>
                <input class="form-control shadow-md appearance-none border rounded-lg w-full py-3 pl-8 pr-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400" id="claim_amount" name="claim_amount" type="number" step="0.01" required>
            </div>
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2" for="policy_id">
                Policy ID
            </label>
            <input class="form-control shadow-md appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400" id="policy_id" name="policy_id" type="number" required>
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2" for="client_id">
                Client ID
            </label>
            <input class="form-control shadow-md appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400" id="client_id" name="client_id" type="number" required>
        </div>
        <div class="flex items-center justify-between">
            <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" type="submit">
                Submit
            </button>
            <a href="{{ route('claims.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-400">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
