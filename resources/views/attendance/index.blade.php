@extends('layout')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Attendance Records</h2>

    <!-- Success and Error Messages -->
    @if(session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Attendance Check-In/Check-Out Buttons -->
    @if($user->can('create_attendance'))
        <div class="row mb-4">
            <div class="col-md-6 mb-2">
                <form action="{{ route('attendance.check-in') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">Check In</button>
                </form>
            </div>
            <div class="col-md-6 mb-2">
                <form action="{{ route('attendance.check-out') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100">Check Out</button>
                </form>
            </div>
        </div>
    @endif

    <!-- Attendance Edit Filters (Employee & Month) -->
    @if($user->can('edit_attendance'))
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <select name="employee_id" class="form-control">
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <select name="month" class="form-control">
                        <option value="">Select Month</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <button type="submit" class="btn btn-info w-100">Filter</button>
                </div>
            </div>
        </form>
    @endif

    <!-- Attendance Table -->
    <table class="table table-bordered">
        <thead class="thead-light">
            <tr>
                <th>User</th>
                <th>Date</th>
                <th>Status</th>
                <th>Check-in Time</th>
                <th>Check-out Time</th>
                @if($user->can('edit_attendance'))
                <th>Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user->first_name }} {{ $attendance->user->last_name }}</td>
                    <td>{{ $attendance->check_in_time ? $attendance->check_in_time->format('d M Y') : 'N/A' }}</td>
                    <td>{{ ucfirst($attendance->status) }}</td>
                    <td>{{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : 'N/A' }}</td>
                    <td>{{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : 'N/A' }}</td>
                    @if($user->can('edit_attendance'))
                    <td>
                        @if($attendance->status != 'absent')
                            <a href="{{ route('attendance.mark-status', [$attendance->id, 'absent']) }}" class="btn btn-warning btn-sm">Mark Absent</a>
                        @endif
                    </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
