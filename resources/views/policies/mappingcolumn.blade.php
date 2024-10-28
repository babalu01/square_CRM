@extends('layout')

@section('content')
@php 
$user = getAuthenticatedUser();
@endphp
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Map Policy Columns</h1>
    <form action="{{ route('test.route') }}" method="POST">
        @csrf
        <div class="flex flex-col space-y-6">
            @foreach($policyColumns as $index => $column)
                <div class="flex items-center">
                    <!-- Left side: Policy Column -->
                    <div class="w-1/2 pr-4">
                        <h2 class="text-lg font-semibold mb-2">Policy Column: {{ $column }}</h2>
                    </div>
                    <!-- Right side: System Columns Selection -->
                    <div class="w-1/2 pl-4">
                        <h2 class="text-lg font-semibold mb-2">Select System Columns</h2>
                        <select name="system_columns[{{ $column }}][]" multiple class="form-select block w-full mt-1">
                            @foreach($systemColumns as $systemColumn)
                                <option value="{{ $systemColumn }}">{{ $systemColumn }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Submit</button>
        </div>
    </form>
</div>
@endsection
