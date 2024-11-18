<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // Display all attendance records (Admin view)
    public function index(Request $request)
{
    $user = getAuthenticatedUser();
    $attendances = Attendance::with('user');

    // If the user can edit attendance
    if ($user->can('edit_attendance')) {
        // Filter by month if the month parameter is passed
        if ($request->has('month') && $request->input('month') != '') {
            $month = $request->input('month');
            $attendances = $attendances->whereMonth('created_at', $month);
        }

        // Filter by employee_id if the employee_id parameter is passed
        if ($request->has('employee_id') && $request->input('employee_id') != '') {
            $employeeId = $request->input('employee_id');
            $attendances = $attendances->where('user_id', $employeeId);
        }
    } else {
        // If the user cannot edit attendance, show only the user's own attendance
        $attendances = $attendances->where('user_id', $user->id);
    }
   // Get final results after applying filters
    $attendances = $attendances->get();
    
    // Get all employees for the filter dropdown
    $employees = User::all();
   // Return the view with the attendance data
    return view('attendance.index', compact('attendances', 'user', 'employees'));
}


    // Store attendance check-in
    public function storeCheckIn(Request $request)
    {
        $user = auth()->user();  // Assuming the user is authenticated
        
        // Check if the user has already checked in
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if ($existingAttendance) {
            return redirect()->back()->with('error', 'You have already checked in today.');
        }

        // Create a new check-in record
        Attendance::create([
            'user_id' => $user->id,
            'status' => 'present',
            'check_in_time' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Checked in successfully.');
    }

    // Store attendance check-out
    public function storeCheckOut(Request $request)
    {
        $user = auth()->user();

        // Find today's attendance
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', 'You have not checked in today.');
        }

        // Update the check-out time
        $attendance->update([
            'check_out_time' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Checked out successfully.');
    }

    // Mark attendance status (for Admin) - Manual update
    public function markStatus($attendanceId, $status)
    {
        $attendance = Attendance::findOrFail($attendanceId);
        $attendance->update(['status' => $status]);

        return redirect()->route('attendance.index')->with('success', 'Attendance status updated.');
    }

    public function getWorkingDays($userId)
    {
        $workingDays = Attendance::getWorkingDays($userId);
        return response()->json(['working_days' => $workingDays]);
    }
}
