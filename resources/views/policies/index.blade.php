@extends('layout')

@section('content')
@php 
$user = getAuthenticatedUser();
@endphp
<div class="container table-container">
    <h5>Policies</h5>
    <div class="row mb-3">
        <div class="col-md-12 d-flex justify-content-end">
        @if($user->can('create_policies'))
            <a href="{{ route('policies.create') }}" class="btn btn-primary mb-3">
            <i class="tf-icon tf-icon-plus"></i>
           Add Policy
            </a>&nbsp;&nbsp;
           
            <a href="{{ route('Policies.imports') }}" class="btn btn-success mb-3">
            <i class="tf-icon tf-icon-import"></i>
            Import Policy
            </a>&nbsp;&nbsp;
            @endif
            <a href="{{ route('policies.export') }}" class="btn btn-outline-danger mb-3">
            <i class="tf-icon tf-icon-import"></i>
            Export Policy
            </a>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12">
            <form id="search-form" action="{{ route('policies.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <input type="text" class="form-control search-input" name="policy_number" placeholder="Policy Number" value="{{ request('policy_number') }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <input type="text" class="form-control search-input" name="type" placeholder="Type" value="{{ request('type') }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <input type="text" class="form-control search-input" name="provider" placeholder="Provider" value="{{ request('provider') }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <input type="number" class="form-control search-input" name="premium_amount" placeholder="Premium Amount" value="{{ request('premium_amount') }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <input type="text" class="form-control search-input" name="coverage_details" placeholder="Search" value="{{ request('coverage_details') }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <input type="date" class="form-control search-input" name="start_date" placeholder="Start Date" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <input type="date" class="form-control search-input" name="end_date" placeholder="End Date" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <button type="button" id="clear-filter" class="btn btn-secondary w-100">Clear Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div id="policies-table">
        @include('policies.partials.table')
    </div>
</div>

<style>
    body {
        background-color: #f8f9fa;
    }
    .table-container {
        margin: 20px;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .table a {
        color: #6366f1;
        text-decoration: none;
    }
    .table a:hover {
        text-decoration: underline;
    }
    .pagination {
        justify-content: center;
        margin-top: 20px;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    $('.search-input').on('input', debounce(function() {
        let formData = $('#search-form').serialize();
        $.ajax({
            url: '{{ route('policies.index') }}',
            type: 'GET',
            data: formData,
            success: function(response) {
                $('#policies-table').html(response);
            }
        });
    }, 300));

    $('#clear-filter').on('click', function() {
        $('.search-input').val('');
        $('#search-form').submit();
    });
});
</script>
@endsection
