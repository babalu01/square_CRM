@extends('layout')

 <style>
        .table-header {
            background-color: #D9B2D9;
            text-align: center;
            font-weight: bold;
        }
        .table-subheader {
            background-color: #FFD700;
            text-align: center;
            font-weight: bold;
        }
        .table-subheader-blue {
            background-color: #ADD8E6;
            text-align: center;
            font-weight: bold;
        }
        .table-subheader-orange {
            background-color: #FFA07A;
            text-align: center;
            font-weight: bold;
        }
        .table-cell {
            text-align: center;
            vertical-align: middle;
        }
        .table-cell-green {
            background-color: #00FF00;
        }
        .table-cell-red {
            background-color: #FF0000;
        }
    </style>

@section('content')
@php
    $user = getAuthenticatedUser();
@endphp
<div class="container mt-4">
@if ($user->can('edit_grid'))
<form action="{{route('update.commission.rates')}}" method="POST" onsubmit="return removeEmptyFields()">
    @csrf
    @endif
    <input type="hidden" name="upload_id" value="{{$uploadId}}">
    <div class="container-fluid table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th colspan="3" class="table-subheader">All Broker - Above 5 Lakhs for CV and Above 3 Lac for PVT & TW</th>
                    @foreach ($colheaders['vehicle_categories'] as $category => $subcategories)
                        <th colspan="{{ count($subcategories) }}" class="table-subheader">{{ $category }}</th>
                    @endforeach
                </tr>
                <tr>
                    <th class="table-subheader">Region</th>
                    <th class="table-subheader">State Name</th>
                    <th class="table-subheader">Circle</th>
                    @foreach ($colheaders['vehicle_categories'] as $category => $subcategories)
                        @foreach ($subcategories as $subcategory)
                            <th class="table-subheader">{{ $subcategory }}</th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($commissionRates as $state)
                    <tr>
                        <td>{{ $state['region_name'] }}</td>
                        <td>{{ $state['state_name'] }}</td>
                        <td>{{ $state['Circle'] ?? '' }}</td>
                        @foreach ($colheaders['vehicle_categories'] as $category => $subcategories)
                            @foreach ($subcategories as $subcategory)
                                @php
                                    $valueFound = false;
                                @endphp
                                @foreach ($state['vehicle_categories'] as $vehicle_category)
                                    @if ($vehicle_category['vehicle_category_name'] == $category)
                                        @foreach ($vehicle_category['sections'] as $section)
                                            @if ($section['section_name'] == $subcategory)
                                                <!-- <input type="hidden" name="commissionrate_ids[{{$state['state_name']}}][{{$category}}][{{$subcategory}}]" value="{{$section['commission_rate_id']}}"> -->
                                                <td class="bg-success p-0 m-0 w-50">
                                                    <input type="number" max="100" step="0.01" name="commission_rates[{{$section['commission_rate_id']}}]" value="{{ $section['value'] ?? '' }}" class="bg-success border-gray-300 w-100 p-2 " @if (!$user->can('edit_grid')) @disabled(true) @readonly(true) @endif  />
                                                </td>
                                                @php
                                                    $valueFound = true;
                                                @endphp
                                                @break
                                            @endif
                                        @endforeach
                                    @endif
                                    @if ($valueFound) @break @endif
                                @endforeach
                                @if (!$valueFound)
                                    <!-- <td class="bg-danger"></td> -->
                                    <td class="bg-danger p-0 m-0 w-50">
                                        <input type="number" max="100" step="0.01"
                                               name="commissionrate_ids[{{$state['region_name']}}][{{$state['state_name']}}][{{$state['Circle']}}][vehicle_type][{{$category}}][{{$subcategory}}]" 
                                               value="" 
                                               class="bg-danger text-center text-dark border-gray-300 w-100 p-2 "  @if (!$user->can('edit_grid')) @disabled(true) @readonly(true) @endif />
                                    </td>
                                @endif
                            @endforeach
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@if ($user->can('edit_grid'))

    <button type="submit" class="btn btn-primary">
        Update Commission Rates
    </button>
</form>
@endif
</div>
<script>
function removeEmptyFields() {
    const inputs = document.querySelectorAll('input[type="number"]');
    inputs.forEach(input => {
        if (input.value.trim() === '') {
            input.name = ''; // Remove the name attribute for empty fields
        }
    });
    return true; // Allow form submission
}
</script>
@endsection
