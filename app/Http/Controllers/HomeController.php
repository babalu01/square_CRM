<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Workspace;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\policy;

class HomeController extends Controller
{
    protected $workspace;
    protected $user;
    protected $statuses;
    public function __construct()
    {

        $this->middleware(function ($request, $next) {
            // fetch session and use it in entire class with constructor
            $this->workspace = Workspace::find(getWorkspaceId());
            $this->user = getAuthenticatedUser();
            return $next($request);
        });
        $this->statuses = Status::all();
    }
    public function index(Request $request)
    {
        $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects ?? [] : $this->user->projects ?? [];
        $tasks = isAdminOrHasAllDataAccess() ? $this->workspace->tasks ?? [] : $this->user->tasks() ?? [];
        $tasks = $tasks ? $tasks->count() : 0;
        $users = $this->workspace->users ?? [];
        $clients = $this->workspace->clients ?? [];
        $todos = $this->user->todos()
            ->orderBy('is_completed', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
        $total_todos = $this->user->todos;
        $meetings = isAdminOrHasAllDataAccess() ? $this->workspace->meetings ?? [] : $this->user->meetings ?? [];
        
        // Count active and inactive policies
        $currentDate = now()->toDateString();
        $activePolicies = policy::where('end_date', '>', $currentDate)->count();
        $inactivePolicies = policy::where('end_date', '<', $currentDate)->count();

        return view('dashboard', [
            'users' => $users,
            'clients' => $clients,
            'projects' => $projects,
            'tasks' => $tasks,
            'todos' => $todos,
            'total_todos' => $total_todos,
            'meetings' => $meetings,
            'auth_user' => $this->user,
            'activePolicies' => $activePolicies,
            'inactivePolicies' => $inactivePolicies
        ]);
    }

    public function upcoming_birthdays()
    {
        $search = request('search');
        $sort = request('sort', 'dob');
        $order = request('order', 'ASC');
        $upcoming_days = (int)request('upcoming_days', 30); // Cast to integer, default to 30 if not provided
        $user_ids = request('user_ids');

        $users = $this->workspace->users();

        // Calculate the current date
        $currentDate = today();
        $currentYear = $currentDate->format('Y');

        // Calculate the range for upcoming birthdays (e.g., 365 days from today)
        $upcomingDate = $currentDate->copy()->addDays($upcoming_days);

        $currentDateString = $currentDate->format('Y-m-d');
        $upcomingDateString = $upcomingDate->format('Y-m-d');

        $users = $users->whereRaw("DATE_ADD(DATE_FORMAT(dob, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(dob) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(dob, '%m-%d'), 1, 0) YEAR) BETWEEN ? AND ? AND DATEDIFF(DATE_ADD(DATE_FORMAT(dob, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(dob) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(dob, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) <= ?", [$currentDateString, $upcomingDateString, $upcoming_days])
            ->orderByRaw("DATEDIFF(DATE_ADD(DATE_FORMAT(dob, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(dob) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(dob, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) " . $order);
        // Search by full name (first name + last name)
        if ($search) {
            $users->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%$search%")
                    ->orWhere('last_name', 'LIKE', "%$search%")
                    ->orWhere('users.id', 'LIKE', "%$search%")
                    ->orWhere('dob', 'LIKE', "%$search%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
        }

        if (!empty($user_ids)) {
            $users->whereIn('users.id', $user_ids);
        }

        $total = $users->count();

        // $users = $users->orderBy($sort, $order)
        $users = $users->paginate(request("limit"))
            ->through(function ($user) use ($currentDate, $currentYear) {
                // Convert the 'dob' field to a DateTime object
                $birthdayDate = \Carbon\Carbon::createFromFormat('Y-m-d', $user->dob);
                $birthdayDateYear = $birthdayDate->year;
                $yearDifference = $currentYear - $birthdayDateYear;
                $ordinalSuffix = getOrdinalSuffix($yearDifference);
                // Set the year to the current year
                $birthdayDate->year = $currentDate->year;

                if ($birthdayDate->lt($currentDate)) {
                    // If the birthday has already passed this year, calculate for next year
                    $birthdayDate->year = $currentDate->year + 1;
                }

                // Calculate days left until the user's birthday
                $daysLeft = $currentDate->diffInDays($birthdayDate);

                $emoji = '';
                $label = '';

                if ($daysLeft === 0) {
                    $emoji = ' 🥳';
                    $label = '<span class="badge bg-primary mt-2">' . $yearDifference . '<sup>' . $ordinalSuffix . '</sup> ' . get_label('birthday', 'Birthday') . ' ' . get_label('today', 'Today') . '</span>';
                } elseif ($daysLeft === 1) {
                    $label = '<span class="badge bg-primary mt-2">' . $yearDifference . '<sup>' . $ordinalSuffix . '</sup> ' . get_label('birthday', 'Birthday') . ' ' . get_label('tomorrow', 'Tomorrow') . '</span>';
                } elseif ($daysLeft === 2) {
                    $label = '<span class="badge bg-primary mt-2">' . $yearDifference . '<sup>' . $ordinalSuffix . '</sup> ' . get_label('birthday', 'Birthday') . ' ' . get_label('day_after_tomorrow', 'Day After Tomorrow') . '</span>';
                }
                $dayOfWeek = $birthdayDate->format('D');
                return [
                    'id' => $user->id,
                    'member' => $user->first_name . ' ' . $user->last_name . $emoji . "<ul class='list-unstyled users-list m-0 avatar-group d-flex align-items-center'><a href='" . url("/users/profile/{$user->id}") . "' target='_blank'><li class='avatar avatar-sm pull-up' title='{$user->first_name} {$user->last_name}'><img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle'>",
                    'age' => $currentDate->diffInYears($birthdayDate),
                    'days_left' => $daysLeft,
                    'dob' => $dayOfWeek . ', ' . format_date($birthdayDate) . '<br>' . $label,
                ];
            });

        return response()->json([
            "rows" => $users->items(),
            "total" => $total,
        ]);
    }


    /**
     * List or search users with birthdays today or upcoming.
     * 
     * This endpoint retrieves a list of users with birthdays occurring today or within a specified range of days. The user must be authenticated to perform this action.
     * 
     * @authenticated
     * 
     * @group Dashboard Management
     *
     * @queryParam search string Optional. The search term to filter users by first name or last name or combination of first name and last name or User ID or date of birth. Example: John
     * @queryParam order string Optional. The sort order for the `dob` column. Acceptable values are `ASC` or `DESC`. Default is `ASC`. Example: DESC
     * @queryParam upcoming_days integer Optional. The number of days from today to consider for upcoming birthdays. Default is 30. Example: 15
     * @queryParam user_id integer Optional. The specific user ID to filter the results. Example: 123
     * @queryParam limit integer Optional. The number of results to return per page. Default is 15. Example: 10
     * @queryParam offset integer Optional. The number of results to skip before starting to collect the result set. Default is 0. Example: 5
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Upcoming birthdays retrieved successfully",
     *   "total": 1,
     *   "data": [
     *     {
     *       "id": 1,
     *       "member": "John Doe",
     *       "photo": "http://example.com/storage/photos/john_doe.jpg",
     *       "birthday_count": 30,
     *       "days_left": 10,
     *       "dob": "Tue, 2024-08-08"
     *     }
     *   ]
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "Upcoming birthdays not found.",
     *   "data": []
     * }
     */

    public function upcomingBirthdaysApi(Request $request)
    {
        $search = $request->input('search');
        $order = $request->input('order', 'ASC');
        $upcoming_days = (int)$request->input('upcoming_days', 30); // Cast to integer, default to 30 if not provided
        $user_id = $request->input('user_id');
        $limit = $request->input('limit', 15); // Default limit to 15 if not provided
        $offset = $request->input('offset', 0); // Default offset to 0 if not provided

        $users = $this->workspace->users();

        // Calculate the current date
        $currentDate = today();
        $currentYear = $currentDate->format('Y');

        // Calculate the range for upcoming birthdays (e.g., 365 days from today)
        $upcomingDate = $currentDate->copy()->addDays($upcoming_days);

        $currentDateString = $currentDate->format('Y-m-d');
        $upcomingDateString = $upcomingDate->format('Y-m-d');

        $users = $users->whereRaw("DATE_ADD(DATE_FORMAT(dob, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(dob) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(dob, '%m-%d'), 1, 0) YEAR) BETWEEN ? AND ? AND DATEDIFF(DATE_ADD(DATE_FORMAT(dob, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(dob) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(dob, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) <= ?", [$currentDateString, $upcomingDateString, $upcoming_days])
            ->orderByRaw("DATEDIFF(DATE_ADD(DATE_FORMAT(dob, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(dob) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(dob, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) " . $order);

        // Search by full name (first name + last name)
        if ($search) {
            $users->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%$search%")
                    ->orWhere('last_name', 'LIKE', "%$search%")
                    ->orWhere('users.id', 'LIKE', "%$search%")
                    ->orWhere('dob', 'LIKE', "%$search%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
        }

        if ($user_id) {
            $users->where('users.id', $user_id);
        }

        $total = $users->count();

        if ($total == 0) {
            return formatApiResponse(
                true,
                'Upcoming birthdays not found.',
                ['data' => []]
            );
        }

        $users = $users->limit($limit)->offset($offset)->get()
            ->map(function ($user) use ($currentDate, $currentYear) {
                // Convert the 'dob' field to a DateTime object
                $birthdayDate = \Carbon\Carbon::createFromFormat('Y-m-d', $user->dob);
                $birthdayDateYear = $birthdayDate->year;
                $yearDifference = $currentYear - $birthdayDateYear;
                // Set the year to the current year
                $birthdayDate->year = $currentDate->year;

                if ($birthdayDate->lt($currentDate)) {
                    // If the birthday has already passed this year, calculate for next year
                    $birthdayDate->year = $currentDate->year + 1;
                }

                // Calculate days left until the user's birthday
                $daysLeft = $currentDate->diffInDays($birthdayDate);
                $dayOfWeek = $birthdayDate->format('D');
                return [
                    'id' => $user->id,
                    'member' => $user->first_name . ' ' . $user->last_name,
                    'photo' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg'),
                    'birthday_count' => $yearDifference,
                    'days_left' => $daysLeft,
                    'dob' => $dayOfWeek . ', ' . format_date($birthdayDate),
                ];
            });

        return formatApiResponse(
            false,
            'Upcoming birthdays retrieved successfully',
            [
                'total' => $total,
                'data' => $users
            ]
        );
    }

    public function upcoming_work_anniversaries()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "doj";
        $order = (request('order')) ? request('order') : "ASC";
        $upcoming_days = (request('upcoming_days')) ? request('upcoming_days') : 30;
        $user_ids = request('user_ids');
        $users = $this->workspace->users();

        $currentDate = today();
        $currentYear = $currentDate->format('Y');

        // Calculate the range for upcoming birthdays (e.g., 365 days from today)
        $upcomingDate = $currentDate->copy()->addDays($upcoming_days);

        $currentDateString = $currentDate->format('Y-m-d');
        $upcomingDateString = $upcomingDate->format('Y-m-d');

        $users = $users->whereRaw("DATE_ADD(DATE_FORMAT(doj, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(doj) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(doj, '%m-%d'), 1, 0) YEAR) BETWEEN ? AND ? AND DATEDIFF(DATE_ADD(DATE_FORMAT(doj, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(doj) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(doj, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) <= ?", [$currentDateString, $upcomingDateString, $upcoming_days])
            ->orderByRaw("DATEDIFF(DATE_ADD(DATE_FORMAT(doj, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(doj) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(doj, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) " . $order);

        // Search by full name (first name + last name)
        if (!empty($search)) {
            $users->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%$search%")
                    ->orWhere('last_name', 'LIKE', "%$search%")
                    ->orWhere('users.id', 'LIKE', "%$search%")
                    ->orWhere('doj', 'LIKE', "%$search%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
        }
        if (!empty($user_ids)) {
            $users->whereIn('users.id', $user_ids);
        }
        $total = $users->count();

        // $users = $users->orderBy($sort, $order)
        $users = $users->paginate(request("limit"))
            ->through(function ($user) use ($currentDate, $currentYear) {
                // Convert the 'dob' field to a DateTime object
                $doj = \Carbon\Carbon::createFromFormat('Y-m-d', $user->doj);
                $dojYear = $doj->year;
                $yearDifference = $currentYear - $dojYear;
                $ordinalSuffix = getOrdinalSuffix($yearDifference);

                // Set the year to the current year
                $doj->year = $currentDate->year;

                if ($doj->lt($currentDate)) {
                    // If the birthday has already passed this year, calculate for next year
                    $doj->year = $currentDate->year + 1;
                }

                // Calculate days left until the user's birthday
                $daysLeft = $currentDate->diffInDays($doj);
                $label = '';
                $emoji = '';
                if ($daysLeft === 0) {
                    $emoji = ' 🥳';
                    $label = '<span class="badge bg-primary mt-2">' . $yearDifference . '<sup>' . $ordinalSuffix . '</sup> ' . get_label('work_anniversary', 'Work Anniversary') . ' ' . get_label('today', 'Today') . '</span>';
                } elseif ($daysLeft === 1) {
                    $label = '<span class="badge bg-primary mt-2">' . $yearDifference . '<sup>' . $ordinalSuffix . '</sup> ' . get_label('work_anniversary', 'Work Anniversary') . ' ' . get_label('tomorrow', 'Tomorrow') . '</span>';
                } elseif ($daysLeft === 2) {
                    $label = '<span class="badge bg-primary mt-2">' . $yearDifference . '<sup>' . $ordinalSuffix . '</sup> ' . get_label('work_anniversary', 'Work Anniversary') . ' ' . get_label('day_after_tomorrow', 'Day After Tomorrow') . '</span>';
                }

                $dayOfWeek = $doj->format('D');
                return [
                    'id' => $user->id,
                    'member' => $user->first_name . ' ' . $user->last_name . $emoji . "<ul class='list-unstyled users-list m-0 avatar-group d-flex align-items-center'><a href='" . url("/users/profile/{$user->id}") . "' target='_blank'><li class='avatar avatar-sm pull-up' title='{$user->first_name} {$user->last_name}'>
                    <img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle'>",
                    'wa_date' => $dayOfWeek . ', ' . format_date($doj) . '<br>' . $label,
                    'days_left' => $daysLeft,
                ];
            });

        return response()->json([
            "rows" => $users->items(),
            "total" => $total,
        ]);
    }

    /**
     * List or search users with work anniversaries today or upcoming.
     * 
     * This endpoint retrieves a list of users with work anniversaries occurring today or within a specified range of days. The user must be authenticated to perform this action.
     * 
     * @authenticated
     * 
     * @group Dashboard Management
     *
     * @queryParam search string Optional. The search term to filter users by first name or last name or combination of first name and last name or User ID or date of joining. Example: John
     * @queryParam order string Optional. The sort order for the `doj` column. Acceptable values are `ASC` or `DESC`. Default is `ASC`. Example: DESC
     * @queryParam upcoming_days integer Optional. The number of days from today to consider for upcoming work anniversaries. Default is 30. Example: 15
     * @queryParam user_id integer Optional. The specific user ID to filter the results. Example: 123
     * @queryParam limit integer Optional. The number of results to return per page. Default is 15. Example: 10
     * @queryParam offset integer Optional. The number of results to skip before starting to collect the result set. Default is 0. Example: 5
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Upcoming work anniversaries retrieved successfully",
     *   "total": 1,
     *   "data": [
     *     {
     *       "id": 1,
     *       "member": "John Doe",
     *       "photo": "http://example.com/storage/photos/john_doe.jpg",
     *       "anniversary_count": 5,
     *       "days_left": 10,
     *       "doj": "Tue, 2024-08-08"     
     *     }
     *   ]
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "Upcoming work anniversaries not found.",
     *   "data": []
     * }
     */

    public function upcomingWorkAnniversariesApi(Request $request)
    {
        $search = $request->input('search');
        $order = $request->input('order', 'ASC');
        $upcoming_days = (int)$request->input('upcoming_days', 30); // Cast to integer, default to 30 if not provided
        $user_id = $request->input('user_id');
        $limit = $request->input('limit', 15); // Default limit to 15 if not provided
        $offset = $request->input('offset', 0); // Default offset to 0 if not provided

        $users = $this->workspace->users();

        // Calculate the current date
        $currentDate = today();
        $currentYear = $currentDate->format('Y');

        // Calculate the range for upcoming work anniversaries
        $upcomingDate = $currentDate->copy()->addDays($upcoming_days);

        $currentDateString = $currentDate->format('Y-m-d');
        $upcomingDateString = $upcomingDate->format('Y-m-d');

        $users = $users->whereRaw("DATE_ADD(DATE_FORMAT(doj, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(doj) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(doj, '%m-%d'), 1, 0) YEAR) BETWEEN ? AND ? AND DATEDIFF(DATE_ADD(DATE_FORMAT(doj, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(doj) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(doj, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) <= ?", [$currentDateString, $upcomingDateString, $upcoming_days])
            ->orderByRaw("DATEDIFF(DATE_ADD(DATE_FORMAT(doj, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(doj) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(doj, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) " . $order);

        // Search by full name (first name + last name)
        if ($search) {
            $users->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%$search%")
                    ->orWhere('last_name', 'LIKE', "%$search%")
                    ->orWhere('users.id', 'LIKE', "%$search%")
                    ->orWhere('doj', 'LIKE', "%$search%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
        }

        if ($user_id) {
            $users->where('users.id', $user_id);
        }

        $total = $users->count();

        if ($total == 0) {
            return formatApiResponse(
                false,
                'Upcoming work anniversaries not found.',
                []
            );
        }

        $users = $users->limit($limit)->offset($offset)->get()
            ->map(function ($user) use ($currentDate, $currentYear) {
                // Convert the 'doj' field to a DateTime object
                $anniversaryDate = \Carbon\Carbon::createFromFormat('Y-m-d', $user->doj);
                $anniversaryDateYear = $anniversaryDate->year;
                $yearDifference = $currentYear - $anniversaryDateYear;

                // Set the year to the current year
                $anniversaryDate->year = $currentDate->year;

                if ($anniversaryDate->lt($currentDate)) {
                    // If the anniversary has already passed this year, calculate for next year
                    $anniversaryDate->year = $currentDate->year + 1;
                }

                // Calculate days left until the user's work anniversary
                $daysLeft = $currentDate->diffInDays($anniversaryDate);
                $dayOfWeek = $anniversaryDate->format('D');
                return [
                    'id' => $user->id,
                    'member' => $user->first_name . ' ' . $user->last_name,
                    'photo' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg'),
                    'anniversary_count' => $yearDifference,
                    'days_left' => $daysLeft,
                    'doj' => $dayOfWeek . ', ' . format_date($anniversaryDate)
                ];
            });

        return formatApiResponse(
            false,
            'Upcoming work anniversaries retrieved successfully',
            [
                'total' => $total,
                'data' => $users,
            ]
        );
    }


    public function members_on_leave()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "from_date";
        $order = (request('order')) ? request('order') : "ASC";
        $upcoming_days = (request('upcoming_days')) ? request('upcoming_days') : 30;
        $user_ids = request('user_ids');

        // Calculate the current date
        $currentDate = today();

        // Calculate the range for upcoming work anniversaries (e.g., 30 days from today)
        $upcomingDate = $currentDate->copy()->addDays($upcoming_days);
        // Query members on leave based on 'start_date' in the 'leave_requests' table
        $leaveUsers = DB::table('leave_requests')
            ->selectRaw('*, leave_requests.user_id as UserId')
            ->leftJoin('users', 'leave_requests.user_id', '=', 'users.id')
            ->leftJoin('leave_request_visibility', 'leave_requests.id', '=', 'leave_request_visibility.leave_request_id')
            ->where(function ($leaveUsers) use ($currentDate, $upcomingDate) {
                $leaveUsers->where('from_date', '<=', $upcomingDate)
                    ->where('to_date', '>=', $currentDate);
            })
            ->where('leave_requests.status', '=', 'approved')
            ->where('workspace_id', '=', $this->workspace->id);

        if (!is_admin_or_leave_editor()) {
            $leaveUsers->where(function ($query) {
                $query->where('leave_requests.user_id', '=', $this->user->id)
                    ->orWhere('leave_request_visibility.user_id', '=', $this->user->id)
                    ->orWhere('leave_requests.visible_to_all', '=', 1);
            });
        }

        // Search by full name (first name + last name)
        if (!empty($search)) {
            $leaveUsers->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%$search%")
                    ->orWhere('last_name', 'LIKE', "%$search%")
                    ->orWhere('users.id', 'LIKE', "%$search%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
        }
        if (!empty($user_ids)) {
            $leaveUsers->whereIn('leave_requests.user_id', $user_ids);
        }
        $total = $leaveUsers->count();
        $timezone = config('app.timezone');
        $leaveUsers = $leaveUsers->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($user) use ($currentDate, $timezone) {

                $fromDate = \Carbon\Carbon::createFromFormat('Y-m-d', $user->from_date);

                // Set the year to the current year
                $fromDate->year = $currentDate->year;

                // Calculate days left until the user's return from leave
                $daysLeft = $currentDate->diffInDays($fromDate);
                if ($fromDate->lt($currentDate)) {
                    $daysLeft = 0;
                }
                $currentDateTime = \Carbon\Carbon::now()->tz($timezone);
                $currentTime = $currentDateTime->format('H:i:s');

                $label = '';
                if ($daysLeft === 0 && $user->from_time && $user->to_time && $user->from_time <= $currentTime && $user->to_time >= $currentTime) {
                    $label = ' <span class="badge bg-info">' . get_label('on_partial_leave', 'On Partial Leave') . '</span>';
                } elseif (($daysLeft === 0 && (!$user->from_time && !$user->to_time)) ||
                    ($daysLeft === 0 && $user->from_time <= $currentTime && $user->to_time >= $currentTime)
                ) {
                    $label = ' <span class="badge bg-success">' . get_label('on_leave', 'On leave') . '</span>';
                } elseif ($daysLeft === 1) {
                    $langLabel = $user->from_time && $user->to_time ?  get_label('on_partial_leave_tomorrow', 'On partial leave from tomorrow') : get_label('on_leave_tomorrow', 'On leave from tomorrow');
                    $label = ' <span class="badge bg-primary">' . $langLabel . '</span>';
                } elseif ($daysLeft === 2) {
                    $langLabel = $user->from_time && $user->to_time ?  get_label('on_partial_leave_day_after_tomorow', 'On partial leave from day after tomorrow') : get_label('on_leave_day_after_tomorow', 'On leave from day after tomorrow');
                    $label = ' <span class="badge bg-warning">' . $langLabel . '</span>';
                }

                $fromDate = Carbon::parse($user->from_date);
                $toDate = Carbon::parse($user->to_date);
                if ($user->from_time && $user->to_time) {
                    $duration = 0;
                    // Loop through each day
                    while ($fromDate->lessThanOrEqualTo($toDate)) {
                        // Create Carbon instances for the start and end times of the leave request for the current day
                        $fromDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $user->from_time);
                        $toDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $user->to_time);

                        // Calculate the duration for the current day and add it to the total duration
                        $duration += $fromDateTime->diffInMinutes($toDateTime) / 60; // Duration in hours

                        // Move to the next day
                        $fromDate->addDay();
                    }
                } else {
                    // Calculate the inclusive duration in days
                    $duration = $fromDate->diffInDays($toDate) + 1;
                }
                $fromDateDayOfWeek = $fromDate->format('D');
                $toDateDayOfWeek = $toDate->format('D');
                return [
                    'id' => $user->UserId,
                    'member' => $user->first_name . ' ' . $user->last_name . ' ' . $label . "<ul class='list-unstyled users-list m-0 avatar-group d-flex align-items-center'><a href='" . url("/users/profile/{$user->UserId}") . "' target='_blank'><li class='avatar avatar-sm pull-up' title='{$user->first_name} {$user->last_name}'>
            <img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle'>",
                    'from_date' =>  $fromDateDayOfWeek . ', ' . ($user->from_time ? format_date($user->from_date . ' ' . $user->from_time, true, null, null, false) : format_date($user->from_date)),
                    'to_date' =>  $toDateDayOfWeek . ', ' . ($user->to_time ? format_date($user->to_date . ' ' . $user->to_time, true, null, null, false) : format_date($user->to_date)),
                    'type' => $user->from_time && $user->to_time ? '<span class="badge bg-info">' . get_label('partial', 'Partial') . '</span>' : '<span class="badge bg-primary">' . get_label('full', 'Full') . '</span>',
                    'duration' => $user->from_time && $user->to_time ? $duration . ' hour' . ($duration > 1 ? 's' : '') : $duration . ' day' . ($duration > 1 ? 's' : ''),
                    'days_left' => $daysLeft,
                ];
            });

        return response()->json([
            "rows" => $leaveUsers->items(),
            "total" => $total,
        ]);
    }


    /**
     * List members currently on leave or scheduled to be on leave.
     *
     * This endpoint retrieves a list of members who are currently on leave or scheduled to be on leave within a specified range of days. 
     * The user must be authenticated to perform this action.
     *
     * @authenticated
     *
     * @group Dashboard Management
     *
     * @queryParam search string Optional. The search term to filter users by first name or last name or combination of first name and last name or User ID or date of joining. Example: John
     * @queryParam sort string Optional. The field to sort by. Acceptable values are `from_date` and `to_date`. Default is `from_date`. Example: to_date
     * @queryParam order string Optional. The sort order. Acceptable values are `ASC` or `DESC`. Default is `ASC`. Example: DESC
     * @queryParam upcoming_days integer Optional. The number of days from today to consider for upcoming leave. Default is 30. Example: 15
     * @queryParam user_id integer Optional. The specific user ID to filter the results. Example: 123
     * @queryParam limit integer Optional. The number of results to return per page. Default is 15. Example: 10
     * @queryParam offset integer Optional. The number of results to skip before starting to collect the result set. Default is 0. Example: 5
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Members on leave retrieved successfully.",
     *   "total": 1,
     *   "data": [
     *     {
     *       "id": 1,
     *       "member": "John Doe",
     *       "photo": "http://example.com/storage/photos/john_doe.jpg",
     *       "from_date": "Mon, 2024-07-15",
     *       "to_date": "Fri, 2024-07-19",
     *       "type": "Full",
     *       "duration": "5 days",
     *       "days_left": 0
     *     }
     *   ]
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "Members on leave not found.",
     *   "data": []
     * }
     */

    public function membersOnLeaveApi(Request $request)
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "from_date";
        $order = (request('order')) ? request('order') : "ASC";
        $upcoming_days = (request('upcoming_days')) ? request('upcoming_days') : 30;
        $user_id = (request('user_id')) ? request('user_id') : "";
        $limit = (int)$request->input('limit', 15); // Cast to integer, default to 15 if not provided
        $offset = (int)$request->input('offset', 0); // Cast to integer, default to 0 if not provided

        // Calculate the current date
        $currentDate = today();

        // Calculate the range for upcoming work anniversaries (e.g., 30 days from today)
        $upcomingDate = $currentDate->copy()->addDays($upcoming_days);
        // Query members on leave based on 'start_date' in the 'leave_requests' table
        $leaveUsers = DB::table('leave_requests')
            ->selectRaw('*, leave_requests.user_id as UserId')
            ->leftJoin('users', 'leave_requests.user_id', '=', 'users.id')
            ->leftJoin('leave_request_visibility', 'leave_requests.id', '=', 'leave_request_visibility.leave_request_id')
            ->where(function ($leaveUsers) use ($currentDate, $upcomingDate) {
                $leaveUsers->where('from_date', '<=', $upcomingDate)
                    ->where('to_date', '>=', $currentDate);
            })
            ->where('leave_requests.status', '=', 'approved')
            ->where('workspace_id', '=', $this->workspace->id);

        if (!is_admin_or_leave_editor()) {
            $leaveUsers->where(function ($query) {
                $query->where('leave_requests.user_id', '=', $this->user->id)
                    ->orWhere('leave_request_visibility.user_id', '=', $this->user->id)
                    ->orWhere('leave_requests.visible_to_all', '=', 1);
            });
        }

        // Search by full name (first name + last name)
        if (!empty($search)) {
            $leaveUsers->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%$search%")
                    ->orWhere('last_name', 'LIKE', "%$search%")
                    ->orWhere('users.id', 'LIKE', "%$search%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
        }
        if (!empty($user_id)) {
            $leaveUsers->where('leave_requests.user_id', $user_id);
        }
        $total = $leaveUsers->count();
        if ($total == 0) {
            return formatApiResponse(
                true,
                'Members on leave not found',
                []
            );
        }
        $leaveUsers = $leaveUsers->orderBy($sort, $order)
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(function ($user) use ($currentDate) {

                $fromDate = \Carbon\Carbon::createFromFormat('Y-m-d', $user->from_date);

                // Set the year to the current year
                $fromDate->year = $currentDate->year;

                // Calculate days left until the user's return from leave
                $daysLeft = $currentDate->diffInDays($fromDate);
                if ($fromDate->lt($currentDate)) {
                    $daysLeft = 0;
                }

                $fromDate = Carbon::parse($user->from_date);
                $toDate = Carbon::parse($user->to_date);
                if ($user->from_time && $user->to_time) {
                    $duration = 0;
                    // Loop through each day
                    while ($fromDate->lessThanOrEqualTo($toDate)) {
                        // Create Carbon instances for the start and end times of the leave request for the current day
                        $fromDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $user->from_time);
                        $toDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $user->to_time);

                        // Calculate the duration for the current day and add it to the total duration
                        $duration += $fromDateTime->diffInMinutes($toDateTime) / 60; // Duration in hours

                        // Move to the next day
                        $fromDate->addDay();
                    }
                } else {
                    // Calculate the inclusive duration in days
                    $duration = $fromDate->diffInDays($toDate) + 1;
                }
                $fromDateDayOfWeek = $fromDate->format('D');
                $toDateDayOfWeek = $toDate->format('D');
                return [
                    'id' => $user->UserId,
                    'member' => $user->first_name . ' ' . $user->last_name,
                    'photo' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg'),
                    'from_date' =>  $fromDateDayOfWeek . ', ' . ($user->from_time ? format_date($user->from_date . ' ' . $user->from_time, true, null, null, false) : format_date($user->from_date)),
                    'to_date' =>  $toDateDayOfWeek . ', ' . ($user->to_time ? format_date($user->to_date . ' ' . $user->to_time, true, null, null, false) : format_date($user->to_date)),
                    'type' => $user->from_time && $user->to_time ? get_label('partial', 'Partial') : get_label('full', 'Full'),
                    'duration' => $user->from_time && $user->to_time ? $duration . ' hour' . ($duration > 1 ? 's' : '') : $duration . ' day' . ($duration > 1 ? 's' : ''),
                    'days_left' => $daysLeft,
                ];
            });

        return formatApiResponse(
            false,
            'Members on leave retrieved successfully',
            [
                'total' => $total,
                'data' => $leaveUsers,
            ]
        );
    }

    public function upcoming_birthdays_calendar()
    {
        $users = $this->workspace->users()->get();
        $currentDate = today();

        $events = [];

        foreach ($users as $user) {
            if (!empty($user->dob)) {
                // Format the start date in the required format for FullCalendar
                $birthdayDate = \Carbon\Carbon::createFromFormat('Y-m-d', $user->dob);

                // Set the year to the current year
                $birthdayDate->year = $currentDate->year;

                if ($birthdayDate->lt($currentDate)) {
                    // If the birthday has already passed this year, calculate for next year
                    $birthdayDate->year = $currentDate->year + 1;
                }
                $startDate = $birthdayDate->format('Y-m-d');

                // Prepare the event data
                $event = [
                    'userId' => $user->id,
                    'title' => $user->first_name . ' ' . $user->last_name . '\'s Birthday',
                    'start' => $startDate,
                    'backgroundColor' => '#007bff',
                    'borderColor' => '#007bff',
                    'textColor' => '#ffffff',
                ];

                // Add the event to the events array
                $events[] = $event;
            }
        }
        return response()->json($events);
    }

    public function upcoming_work_anniversaries_calendar()
    {
        $users = $this->workspace->users()->get();

        // Calculate the current date
        $currentDate = today();

        $events = [];

        foreach ($users as $user) {
            if (!empty($user->doj)) {
                // Format the start date in the required format for FullCalendar
                $WADate = \Carbon\Carbon::createFromFormat('Y-m-d', $user->doj);

                // Set the year to the current year
                $WADate->year = $currentDate->year;

                if ($WADate->lt($currentDate)) {
                    // If the birthday has already passed this year, calculate for next year
                    $WADate->year = $currentDate->year + 1;
                }
                $startDate = $WADate->format('Y-m-d');

                // Prepare the event data
                $event = [
                    'userId' => $user->id,
                    'title' => $user->first_name . ' ' . $user->last_name . '\'s Work Anniversary',
                    'start' => $startDate,
                    'backgroundColor' => '#007bff',
                    'borderColor' => '#007bff',
                    'textColor' => '#ffffff',
                ];

                // Add the event to the events array
                $events[] = $event;
            }
        }

        return response()->json($events);
    }

    public function members_on_leave_calendar()
    {
        $currentDate = today();
        $leaveRequests = DB::table('leave_requests')
            ->selectRaw('*, leave_requests.user_id as UserId')
            ->leftJoin('users', 'leave_requests.user_id', '=', 'users.id')
            ->leftJoin('leave_request_visibility', 'leave_requests.id', '=', 'leave_request_visibility.leave_request_id')
            ->where('to_date', '>=', $currentDate)
            ->where('leave_requests.status', '=', 'approved')
            ->where('workspace_id', '=', $this->workspace->id);


        // Add condition to restrict results based on user roles
        if (!is_admin_or_leave_editor()) {
            $leaveRequests->where(function ($query) {
                $query->where('leave_requests.user_id', '=', $this->user->id)
                    ->orWhere('leave_request_visibility.user_id', '=', $this->user->id);
            });
        }

        $time_format = get_php_date_time_format(true);
        $time_format = str_replace(':s', '', $time_format);
        // Get leave requests and format for calendar
        $events = $leaveRequests->get()->map(function ($leave) use ($time_format) {
            $title = $leave->first_name . ' ' . $leave->last_name;
            if ($leave->from_time && $leave->to_time) {
                // If both start and end times are present, format them according to the desired format
                $formattedStartTime = \Carbon\Carbon::createFromFormat('H:i:s', $leave->from_time)->format($time_format);
                $formattedEndTime = \Carbon\Carbon::createFromFormat('H:i:s', $leave->to_time)->format($time_format);
                $title .= ' - ' . $formattedStartTime . ' to ' . $formattedEndTime;
                $backgroundColor = '#02C5EE';
            } else {
                $backgroundColor = '#007bff';
            }
            return [
                'userId' => $leave->UserId,
                'title' => $title,
                'start' => $leave->from_date,
                'end' => $leave->to_date,
                'startTime' => $leave->from_time,
                'endTime' => $leave->to_time,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $backgroundColor,
                'textColor' => '#ffffff'
            ];
        });

        return response()->json($events);
    }


    /**
     * Get Statistics
     * 
     * This endpoint retrieves workspace-specific statistics related to projects, tasks, users, clients, todos, and meetings. The user must be authenticated and have the necessary permissions to manage (if applicable) each respective module.
     * 
     * @group Dashboard Management
     * 
     * @authenticated
     * 
     * @response {
     *   "error": false,
     *   "message": "Statistics retrieved successfully",
     *   "data": {
     *     "total_projects": 8,
     *     "total_tasks": 8,
     *     "total_users": 8,
     *     "total_clients": 8,
     *     "total_meetings": 8,
     *     "total_todos": 0,
     *     "completed_todos": 0,
     *     "pending_todos": 0,
     *     "status_wise_projects": [
     *       {
     *         "id": 1,
     *         "title": "In Progress",
     *         "color": "primary",
     *         "total_projects": 4
     *       },
     *       {
     *         "id": 2,
     *         "title": "Completed",
     *         "color": "success",
     *         "total_projects": 4
     *       }
     *     ],
     *     "status_wise_tasks": [
     *       {
     *         "id": 1,
     *         "title": "In Progress",
     *         "color": "primary",
     *         "total_tasks": 4
     *       },
     *       {
     *         "id": 2,
     *         "title": "Completed",
     *         "color": "success",
     *         "total_tasks": 4
     *       }
     *     ]
     *   }
     * }
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred while retrieving statistics: Internal server error message"
     * }
     */
    public function getStatistics()
    {
        try {
            // Initialize variables
            $statusCountsProjects = [];
            $statusCountsTasks = [];
            $total_projects_count = 0;
            $total_tasks_count = 0;
            $total_users_count = 0;
            $total_clients_count = 0;
            $total_todos_count = 0;
            $total_completed_todos_count = 0;
            $total_pending_todos_count = 0;
            $total_meetings_count = 0;

            // Fetch the total counts based on permissions
            if ($this->user->can('manage_projects')) {
                $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects ?? [] : $this->user->projects ?? [];
                $total_projects_count = $projects->count();
            }

            if ($this->user->can('manage_tasks')) {
                $tasks = isAdminOrHasAllDataAccess() ? $this->workspace->tasks ?? [] : $this->user->tasks() ?? [];
                $total_tasks_count = $tasks->count();
            }

            if ($this->user->can('manage_users')) {
                $users = $this->workspace->users ?? [];
                $total_users_count = count($users);
            }

            if ($this->user->can('manage_clients')) {
                $clients = $this->workspace->clients ?? [];
                $total_clients_count = count($clients);
            }

            $todos = $this->user->todos;
            $total_todos_count = $todos->count();
            $total_completed_todos_count = $todos->where('is_completed', true)->count();
            $total_pending_todos_count = $todos->where('is_completed', false)->count();

            if ($this->user->can('manage_meetings')) {
                $meetings = isAdminOrHasAllDataAccess() ? $this->workspace->meetings ?? [] : $this->user->meetings ?? [];
                $total_meetings_count = $meetings->count();
            }

            // Calculate status-wise counts for projects if user can manage projects
            if ($this->user->can('manage_projects')) {
                foreach ($this->statuses as $status) {
                    $projectCount = isAdminOrHasAllDataAccess() ? count($status->projects) : $this->user->status_projects($status->id)->count();
                    $statusCountsProjects[] = [
                        'id' => $status->id,
                        'title' => $status->title,
                        'color' => $status->color,
                        'total_projects' => $projectCount
                    ];
                }
                // Sort status-wise projects by count in descending order
                usort($statusCountsProjects, function ($a, $b) {
                    return $b['total_projects'] <=> $a['total_projects'];
                });
            }

            // Calculate status-wise counts for tasks if user can manage tasks
            if ($this->user->can('manage_tasks')) {
                foreach ($this->statuses as $status) {
                    $taskCount = isAdminOrHasAllDataAccess() ? count($status->tasks) : $this->user->status_tasks($status->id)->count();
                    $statusCountsTasks[] = [
                        'id' => $status->id,
                        'title' => $status->title,
                        'color' => $status->color,
                        'total_tasks' => $taskCount
                    ];
                }
                // Sort status-wise tasks by count in descending order
                usort($statusCountsTasks, function ($a, $b) {
                    return $b['total_tasks'] <=> $a['total_tasks'];
                });
            }

            // Return response
            return formatApiResponse(
                false,
                'Statistics retrieved successfully.',
                [
                    'data' => [
                        'total_projects' => $total_projects_count,
                        'total_tasks' => $total_tasks_count,
                        'total_users' => $total_users_count,
                        'total_clients' => $total_clients_count,
                        'total_meetings' => $total_meetings_count,
                        'total_todos' => $total_todos_count,
                        'completed_todos' => $total_completed_todos_count,
                        'pending_todos' => $total_pending_todos_count,
                        'status_wise_projects' => $statusCountsProjects,
                        'status_wise_tasks' => $statusCountsTasks,
                    ]
                ]
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while retrieving statistics: ' . $e->getMessage(),
            ], 500);
        }
    }
}
