<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\TaskUser;
use App\Models\Template;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use GuzzleHttp\Promise\TaskQueue;
use App\Notifications\VerifyEmail;
use Spatie\Permission\Models\Role;
use App\Models\UserClientPreference;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AccountCreation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Contracts\Role as ContractsRole;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use App\Rules\UniqueEmailPassword;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $workspace = Workspace::find(getWorkspaceId());
        $users = $workspace->users;
        $roles = Role::where('guard_name', 'web')->get();
        return view('users.users', ['users' => $users, 'roles' => $roles]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::where('guard_name', 'web')->get();
        return view('users.create_user', ['roles' => $roles]);
    }

    /**
     * Create a new user.
     *
     * This endpoint creates a new user with the provided details. The user must be authenticated to perform this action.
     *
     * @authenticated
     *
     * @group User Management
     *
     * @bodyParam first_name string required The first name of the user. Example: John
     * @bodyParam last_name string required The last name of the user. Example: Doe
     * @bodyParam email string required The email address of the user. Example: john.doe@example.com
     * @bodyParam password string required The password for the user. Example: password123
     * @bodyParam password_confirmation string required The password confirmation. Example: password123
     * @bodyParam address string nullable The address of the user. Example: 123 Main St
     * @bodyParam phone string nullable The phone number of the user. Example: 1234567890
     * @bodyParam country_code string nullable The country code for the phone number. Example: +91
     * @bodyParam country_iso_code string nullable The ISO code for the phone number. Example: in
     * @bodyParam city string nullable The city of the user. Example: New York
     * @bodyParam state string nullable The state of the user. Example: NY
     * @bodyParam country string nullable The country of the user. Example: USA
     * @bodyParam zip string nullable The ZIP code of the user. Example: 10001
     * @bodyParam dob string nullable The date of birth of the user in the format specified in the general settings. Example: 1990-01-01
     * @bodyParam doj string nullable The date of joining in the format specified in the general settings. Example: 2024-01-01
     * @bodyParam role integer required The ID of the role for the user. Example: 1
     * @bodyParam profile file nullable The profile photo of the user.
     * @bodyParam status boolean required 0 or 1. If Deactivated (0), the user won't be able to log in to their account. 
     * Can only specify if `is_admin_or_has_all_data_access` is true for the logged-in user, else 0 will be considered by default. Example: 1
     * @bodyParam require_ev boolean required 0 or 1. If Yes (1) is selected, the user will receive a verification link via email. 
     * Can only specify if `is_admin_or_has_all_data_access` is true for the logged-in user, else 1 will be considered by default. Example: 1
     *
     * @response 200 {
     * "error": false,
     * "message": "User created successfully.",
     * "id": 219,
     * "data": {
     *   "id": 219,
     *   "first_name": "Test",
     *   "last_name": "Test",
     *   "role": "Member",
     *   "email": "test@gmail.com",
     *   "phone": "+91 1111111111",
     *   "dob": "09-08-2024",
     *   "doj": "09-08-2024",
     *   "address": "Test",
     *   "city": "Test",
     *   "state": "Test",
     *   "country": "Test",
     *   "zip": "111-111",
     *   "photo": "https://test-taskify.infinitietech.com/storage/photos/K0OAOzWyoeD0ZXBzgsaeHZUZERbOTKRljRIYOEYU.png",
     *   "status": 1,
     *   "created_at": "09-08-2024 17:04:29",
     *   "updated_at": "09-08-2024 17:04:29",
     *   "assigned": {
     *     "projects": 0,
     *     "tasks": 0
     *   }
     * }
     * 
     * }
     * @response 422 {
     * "error": true,
     * "message": "Validation errors occurred",
     * "errors": {
     *   "first_name": [
     *     "The first name field is required."
     *   ],
     *   "last_name": [
     *     "The last name field is required."
     *   ],
     *   "email": [
     *     "The email has already been taken."
     *   ]
     * }
     *
     * }
     * @response 500 {
     *   "error": true,
     *   "message": "User couldn’t be created, please make sure email settings are operational."
     * }
     */
    public function store(Request $request, User $user)
    {
        ini_set('max_execution_time', 300);
        $isApi = request()->get('isApi', false);
        // Validate the request
        try {
            $formFields = $request->validate([
                'first_name' => ['required'],
                'last_name' => ['required'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => 'required|min:6',
                'password_confirmation' => 'required|same:password',
                'address' => 'nullable',
                'phone' => 'nullable',
                'country_code' => 'nullable',
                'country_iso_code' => 'nullable',
                'city' => 'nullable',
                'state' => 'nullable',
                'country' => 'nullable',
                'zip' => 'nullable',
                'dob' => [
                    'nullable',
                    function ($attribute, $value, $fail) {
                        $dob = request()->input('dob');
                        $errors = validate_date_format_and_order($value, $dob, startDateLabel: 'DOB', startDateKey: 'dob');

                        if (!empty($errors['dob'])) {
                            foreach ($errors['dob'] as $error) {
                                $fail($error);
                            }
                        }
                    },
                ],
                'doj' => [
                    'nullable',
                    function ($attribute, $value, $fail) {
                        $doj = request()->input('doj');
                        $errors = validate_date_format_and_order($doj, $value, endDateLabel: 'DOJ', endDateKey: 'doj');

                        if (!empty($errors['doj'])) {
                            foreach ($errors['doj'] as $error) {
                                $fail($error);
                            }
                        }
                    },
                ],
                'role' => 'required|exists:roles,id',
                'status' => 'required|boolean',
                'require_ev' => 'required|boolean'
            ], [
                'status.boolean' => 'The status field must be true or false (0 or 1).',
                'require_ev.required' => 'The email verification requirement field is required.',
                'require_ev.boolean' => 'The email verification requirement field must be true or false (0 or 1).',
            ]);
            $uniqueEmailPasswordRule = new UniqueEmailPassword('user');
            if (!$uniqueEmailPasswordRule->passes('password', $request->input('password'))) {
                return formatApiValidationError($isApi, ['email' => [$uniqueEmailPasswordRule->message()]]);
            }


            // Format dates if present
            $dob = $request->input('dob');
            $doj = $request->input('doj');
            if ($dob) {
                $formFields['dob'] = format_date($dob, false, app('php_date_format'), 'Y-m-d');
            }
            if ($doj) {
                $formFields['doj'] = format_date($doj, false, app('php_date_format'), 'Y-m-d');
            }

            // Handle password and profile photo
            $formFields['password'] = bcrypt($request->input('password'));
            $formFields['photo'] = $request->hasFile('profile') ?
                $request->file('profile')->store('photos', 'public') :
                'photos/no-image.jpg';

            // Determine email verification and status
            $require_ev = isAdminOrHasAllDataAccess() && $request->has('require_ev') && $request->input('require_ev') == 0 ? 0 : 1;
            $status = isAdminOrHasAllDataAccess() && $request->has('status') && $request->input('status') == 1 ? 1 : 0;
            $formFields['email_verified_at'] = $require_ev == 0 ? now()->tz(config('app.timezone')) : null;
            $formFields['status'] = $status;

            try {
                // Create the user
                $user = User::create($formFields);
                $user->assignRole($request->input('role'));

                // Notify the user if email verification is required
                if ($require_ev == 1) {
                    $user->notify(new VerifyEmail($user));
                }

                // Attach user to the workspace
                $workspace = Workspace::find(getWorkspaceId());
                $workspace->users()->attach($user->id);

                // Send account creation notification if email is configured
                if (isEmailConfigured()) {
                    $account_creation_template = Template::where('type', 'email')
                        ->where('name', 'account_creation')
                        ->first();
                    if (!$account_creation_template || ($account_creation_template->status !== 0)) {
                        $user->notify(new AccountCreation($user, $request->input('password')));
                    }
                }
                $data = formatUser($user);
                $data['require_ev'] = $require_ev;
                return formatApiResponse(
                    false,
                    'User created successfully.',
                    [
                        'id' => $user->id,
                        'data' => $data
                    ]
                );
            } catch (TransportExceptionInterface $e) {
                // Rollback user creation on email transport failure
                $user->delete();
                return response()->json(['error' => true, 'message' => 'User couldn\'t be created, please make sure email settings are operational.'], 500);
            } catch (Throwable $e) {
                // Rollback user creation on other errors
                $user->delete();
                return response()->json(['error' => true, 'message' => 'User couldn\'t be created, please make sure email settings are operational.'], 500);
            }
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        }
    }


    public function email_verification()
    {
        $user = getAuthenticatedUser();
        if (!$user->hasVerifiedEmail()) {
            return view('auth.verification-notice');
        } else {
            return redirect('/home');
        }
    }

    public function resend_verification_link(Request $request)
    {
        if (isEmailConfigured()) {
            try {
                $request->user()->notify(new VerifyEmail($request->user()));
                Session::flash('message', 'Verification link sent successfully.');
            } catch (TransportExceptionInterface $e) {
                Session::flash('error', 'Verification link couldn\'t be sent, please check email settings.');
            } catch (Throwable $e) {
                Session::flash('error', 'Verification link couldn\'t be sent, please check email settings.');
            }
        } else {
            Session::flash('error', 'Verification link couldn\'t be sent, please check email settings.');
        }
        return back();
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function edit_user($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::where('guard_name', 'web')->get();
        return view('users.edit_user', ['user' => $user, 'roles' => $roles]);
    }



    /**
     * Update an existing user.
     *
     * This endpoint updates the details of an existing user. The user must be authenticated to perform this action.
     *
     * @authenticated
     *
     * @group User Management
     *
     * @bodyParam id integer required The ID of the user to be updated. Example: 1
     * @bodyParam first_name string required The first name of the user. Example: John
     * @bodyParam last_name string required The last name of the user. Example: Doe
     * @bodyParam email string required The email address of the user. Example: john.doe@example.com
     * @bodyParam password string nullable The new password for the user. Can only be updated if `is_admin_or_has_all_data_access` is true for the logged-in user. Example: newpassword123
     * @bodyParam password_confirmation string required_with:password The password confirmation. Example: newpassword123
     * @bodyParam address string nullable The address of the user. Example: 123 Main St
     * @bodyParam phone string nullable The phone number of the user. Example: 1234567890
     * @bodyParam country_code string nullable The country code for the phone number. Example: +91
     * @bodyParam country_iso_code string nullable The ISO code for the phone number. Example: in
     * @bodyParam city string nullable The city of the user. Example: New York
     * @bodyParam state string nullable The state of the user. Example: NY
     * @bodyParam country string nullable The country of the user. Example: USA
     * @bodyParam zip string nullable The ZIP code of the user. Example: 10001
     * @bodyParam dob string nullable The date of birth of the user in the format specified in the general settings. Example: 1990-01-01
     * @bodyParam doj string nullable The date of joining in the format specified in the general settings. Example: 2024-01-01
     * @bodyParam role integer required The ID of the role for the user. Example: 1
     * @bodyParam profile file nullable The new profile photo of the user.
     * @bodyParam status boolean required 0 or 1. If Deactivated (0), the user won't be able to log in to their account.
     * Can only specify status if `is_admin_or_has_all_data_access` is true for the logged-in user, else the current status will be considered by default. Example: 1
     *
     * @response 200 {
     * "error": false,
     * "message": "User updated successfully.",
     * "id": 219,
     * "data": {
     *   "id": 219,
     *   "first_name": "APII",
     *   "last_name": "User",
     *   "role": "Member",
     *   "email": "test@gmail.com",
     *   "phone": "+91 1111111111",
     *   "dob": "09-08-2024",
     *   "doj": "09-08-2024",
     *   "address": "Test adr",
     *   "city": "Test cty",
     *   "state": "Test ct",
     *   "country": "test ctr",
     *   "zip": "111-111",
     *   "photo": "https://test-taskify.infinitietech.com/storage/photos/28NcF6qzmIRiOhN9zrtEu5x1iN55OBspR9o1ONMO.webp",
     *   "status": "1",
     *   "created_at": "09-08-2024 17:04:29",
     *   "updated_at": "09-08-2024 18:32:10",
     *   "assigned": {
     *     "projects": 14,
     *     "tasks": 12
     *   }
     * }

     * }
     * @response 422 {
     * "error": true,
     * "message": "Validation errors occurred",
     * "errors": {
     *   "first_name": [
     *     "The first name field is required."
     *   ],
     *   "last_name": [
     *     "The last name field is required."
     *   ],
     *   "email": [
     *     "The email has already been taken."
     *   ]
     * }
     * }
     * @response 500 {
     *   "error": true,
     *   "message": "User couldn't be updated."
     * }
     */
    public function update_user(Request $request, $id = null)
    {
        ini_set('max_execution_time', 300);
        $isApi = request()->get('isApi', false);
        if ($id) {
            $request->merge(['id' => $id]);
        }
        $id = $request->input('id');
        if ($id) {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['error' => true, 'message' => 'User not found.']);
            }

            // Determine status
            $status = isAdminOrHasAllDataAccess() && $request->has('status') ? $request->input('status') : $user->status;
            $request->merge(['status' => $status]);
        }

        // Validate the request
        try {
            $formFields = $request->validate([
                'id' => 'required|exists:users,id',
                'first_name' => ['required'],
                'last_name' => ['required'],
                'email' => ['required', 'email', Rule::unique('users')->ignore($request->input('id'))],
                'phone' => 'nullable',
                'country_code' => 'nullable',
                'country_iso_code' => 'nullable',
                'address' => 'nullable',
                'city' => 'nullable',
                'state' => 'nullable',
                'country' => 'nullable',
                'zip' => 'nullable',
                'dob' => [
                    'nullable',
                    function ($attribute, $value, $fail) {
                        $dob = request()->input('dob');
                        $errors = validate_date_format_and_order($value, $dob, startDateLabel: 'DOB', startDateKey: 'dob');

                        if (!empty($errors['dob'])) {
                            foreach ($errors['dob'] as $error) {
                                $fail($error);
                            }
                        }
                    },
                ],
                'doj' => [
                    'nullable',
                    function ($attribute, $value, $fail) {
                        $doj = request()->input('doj');
                        $errors = validate_date_format_and_order($doj, $value, endDateLabel: 'DOJ', endDateKey: 'doj');

                        if (!empty($errors['doj'])) {
                            foreach ($errors['doj'] as $error) {
                                $fail($error);
                            }
                        }
                    },
                ],
                'password' => 'nullable|min:6',
                'password_confirmation' => 'required_with:password|same:password',
                'role' => 'required|exists:roles,id',
                'status' => 'required|boolean'
            ], [
                'status.boolean' => 'The status field must be true or false (0 or 1).',
            ]);

            if (request()->filled('password')) {
                $uniqueEmailPasswordRule = new UniqueEmailPassword('user');
                if (!$uniqueEmailPasswordRule->passes('password', $request->input('password'))) {
                    return formatApiValidationError($isApi, ['email' => [$uniqueEmailPasswordRule->message()]]);
                }
            }

            // Handle profile photo upload
            if ($request->hasFile('profile')) {
                if ($user->photo != 'photos/no-image.jpg' && $user->photo !== null) {
                    Storage::disk('public')->delete($user->photo);
                }
                $formFields['photo'] = $request->file('profile')->store('photos', 'public');
            }

            // Handle password update
            if (isAdminOrHasAllDataAccess() && isset($formFields['password']) && !empty($formFields['password'])) {
                $formFields['password'] = bcrypt($formFields['password']);
            } else {
                unset($formFields['password']);
            }

            // Format dates if present
            $dob = $request->input('dob');
            $doj = $request->input('doj');
            if ($dob) {
                $formFields['dob'] = format_date($dob, false, app('php_date_format'), 'Y-m-d');
            }
            if ($doj) {
                $formFields['doj'] = format_date($doj, false, app('php_date_format'), 'Y-m-d');
            }
            $status = isAdminOrHasAllDataAccess() && $request->has('status') ? $request->input('status') : $user->status;
            $formFields['status'] = $status;
            // Update the user
            $user->update($formFields);
            $user->syncRoles($request->input('role'));
            // Return success response
            return formatApiResponse(false, 'User updated successfully.', ['id' => $user->id, 'data' => formatUser($user)]);
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'error' => true,
                'message' => 'User couldn\'t be updated.'
            ], 500);
        }
    }


    public function update_photo(Request $request, $id)
    {
        if ($request->hasFile('upload')) {
            $old = User::findOrFail($id);
            if ($old->photo != 'photos/no-image.jpg' && $old->photo !== null)
                Storage::disk('public')->delete($old->photo);
            $formFields['photo'] = $request->file('upload')->store('photos', 'public');
            User::findOrFail($id)->update($formFields);
            return back()->with('message', 'Profile picture updated successfully.');
        } else {
            return back()->with('error', 'No profile picture selected.');
        }
    }

    /**
     * Remove the specified user.
     *
     * This endpoint deletes a user based on the provided ID. The request must be authenticated to perform this action.
     *
     * @authenticated
     *
     * @group User Management
     *
     * @urlParam id int required The ID of the user to be deleted. Example: 1
     *
     * @response 200 {
     *   "error": false,
     *   "message": "User deleted successfully.",
     *   "id": "1",
     *   "title": "John Doe",     
     *   "data": []
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "User not found.",
     *   "data": []
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An internal server error occurred."
     * }
     */

    public function delete_user($id)
    {
        $user = User::find($id);
        $response = DeletionService::delete(User::class, $id, 'User');
        $responseData = json_decode($response->getContent(), true);

        if ($responseData['error']) {
            // Handle error response
            return response()->json($responseData);
        }
        UserClientPreference::where('user_id', 'u_' . $id)->delete();
        $user->todos()->delete();
        return $response;
    }

    public function delete_multiple_user(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:users,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedUsers = [];
        $deletedUserNames = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $user = User::findOrFail($id);
            if ($user) {
                $deletedUsers[] = $id;
                $deletedUserNames[] = $user->first_name . ' ' . $user->last_name;
                DeletionService::delete(User::class, $id, 'User');
                UserClientPreference::where('user_id', 'u_' . $id)->delete();
                $user->todos()->delete();
            }
        }
        return response()->json(['error' => false, 'message' => 'User(s) deleted successfully.', 'id' => $deletedUsers, 'titles' => $deletedUserNames]);
    }

    public function logout(Request $request)
    {
        if (Auth::guard('web')->check()) {
            auth('web')->logout();
        } else {
            auth('client')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('message', 'Logged out successfully.');
    }

    public function login()
    {
        return view('auth.login');
    }

    /**
     * Log in an existing user.
     * 
     * This endpoint allows a user to log in by providing their email and password. Upon successful authentication, a token is returned for accessing protected resources.
     *
     * @group User Authentication
     *
     * @bodyParam email string required The email of the user. Example: john.doe@example.com
     * @bodyParam password string required The password for the user. Example: password123
     * @bodyParam fcm_token string nullable The optional FCM token for push notifications. Example: cXJ1AqT6B...
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Logged in successfully.",
     *   "token": "15|ANl9HwfqiiUxdOmNWba5qKhzfk3h1fyi8ZUoYbH8de8d3534",
     *   "data": {
     *     "user_id": 7,
     *     "workspace_id": 6,
     *     "my_locale": "en",
     *     "locale": "en"
     *   }
     * }
     *
     * @response 401 {
     *   "error": true,
     *   "message": "Unauthorized"
     * }
     *
     * @response 422 {
     *   "error": true,
     *   "message": "Validation errors occurred",
     *   "errors": {
     *     "email": ["The email field is required."],
     *     "password": ["The password field is required."]
     *   }
     * }
     *
     */

    public function authenticate(Request $request)
    {
        $isApi = request()->get('isApi', false);
        try {
            $formFields = $request->validate([
                'email' => ['required', 'email'],
                'password' => 'required'
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        }

        $logged_in = false;

        // First attempt to login as user
        if (auth('web')->attempt($formFields)) {
            $user = auth('web')->user();

            // Now check if user has active status
            if ($user->hasRole('admin') || $user->status == 1) {
                $logged_in = true;
            } else {
                auth('web')->logout();
                return response()->json(['error' => true, 'message' => get_label('status_not_active', 'Your account is currently inactive. Please contact the admin for assistance.')]);
            }
        }

        // If user login fails, attempt client login
        if (!$logged_in && auth('client')->attempt($formFields)) {
            $client = auth('client')->user();

            // Now check if client has active status
            if ($client->internal_purpose == 0 && $client->status == 1) {
                $logged_in = true;
            } else {
                auth('client')->logout();
                return response()->json(['error' => true, 'message' => get_label('status_not_active', 'Your account is currently inactive or for internal purposes only. Please contact the admin for assistance.')]);
            }
        }

        // Check if neither user nor client account exists
        $userExists = User::where('email', $formFields['email'])->exists();
        $clientExists = Client::where('email', $formFields['email'])->exists();

        if (!$logged_in && !$userExists && !$clientExists) {
            return response()->json(['error' => true, 'message' => get_label('account_not_found', 'Account not found!')]);
        }

        // Handle failed login
        if (!$logged_in) {
            return response()->json(['error' => true, 'message' => get_label('login_failed', 'Login failed! Please check your credentials.')]);
        }

        // Successful login actions
        $user = auth('web')->check() ? auth('web')->user() : auth('client')->user();
        $guard = auth('web')->check() ? 'web' : 'client';
        if ($request->filled('fcm_token')) {
            $user->fcm_token = $request->input('fcm_token'); // Update the FCM token
            $user->save();
        }

        $workspace_id = $user->default_workspace_id ?? (isset($user->workspaces[0]['id']) && !empty($user->workspaces[0]['id']) ? $user->workspaces[0]['id'] : 0);
        $my_locale = $locale = isset($user->lang) && !empty($user->lang) ? $user->lang : 'en';
        $data = ['user_id' => $user->id, 'workspace_id' => $workspace_id, 'email' => $formFields['email'], 'password' => $formFields['password'], 'my_locale' => $my_locale, 'locale' => $locale];

        if (!$isApi) {
            session()->put($data);
            $request->session()->regenerate();
            Session::flash('message', 'Logged in successfully.');
            return response()->json(['error' => false]);
        } else {
            $role = $user->roles->first(); // Get the first (and only) role
            $role_name = $role ? $role->name : null; // Role name
            $role_id = $role ? $role->id : null; // Role ID
            $data['role'] = $role_name;
            $data['role_id'] = $role_id;
            $data['guard'] = $guard;
            $data['is_admin_or_has_all_data_access'] = isAdminOrHasAllDataAccess();
            $data['is_leave_editor'] = $guard == 'web' && \App\Models\LeaveEditor::where('user_id', $user->id)->exists() ? true : false;
            $token = $user->createToken('authToken')->plainTextToken;
            return formatApiResponse(
                false,
                'Logged in successfully.',
                [
                    'token' => $token,
                    'data' => $data
                ]
            );
        }
    }



    public function show($id)
    {
        $user = User::findOrFail($id);
        $workspace = Workspace::find(getWorkspaceId());
        $projects = isAdminOrHasAllDataAccess('user', $id) ? $workspace->projects : $user->projects;
        $tasks = isAdminOrHasAllDataAccess() ? $workspace->tasks->count() : $user->tasks->count();
        $users = $workspace->users;
        $clients = $workspace->clients;

        return view('users.user_profile', ['user' => $user, 'projects' => $projects, 'tasks' => $tasks, 'users' => $users, 'clients' => $clients, 'auth_user' => getAuthenticatedUser()]);
    }

    public function list()
    {
        $workspace = Workspace::find(getWorkspaceId());
        $search = request('search');
        $sort = request('sort') ?: 'id';
        $order = request('order') ?: 'DESC';
        $type = request('type');
        $typeId = request('typeId');
        $statuses = request('statuses', []);
        $role_ids = request('role_ids', []);

        if ($type && $typeId) {
            if ($type == 'project') {
                $project = Project::find($typeId);
                $users = $project->users();
            } elseif ($type == 'task') {
                $task = Task::find($typeId);
                $users = $task->users();
            } else {
                $users = $workspace->users();
            }
        } else {
            $users = $workspace->users();
        }

        // Ensure the search query does not introduce duplicates
        $users = $users->when($search, function ($query) use ($search) {
            return $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('users.id', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        });

        if (!empty($statuses)) {
            $users->whereIn('status', $statuses);
        }

        if (!empty($role_ids)) {
            $users = $users->whereHas('roles', function ($query) use ($role_ids) {
                $query->whereIn('roles.id', $role_ids);
            });
        }

        $totalusers = $users->count();

        $canEdit = checkPermission('edit_users');
        $canDelete = checkPermission('delete_users');
        $canManageProjects = checkPermission('manage_projects');
        $canManageTasks = checkPermission('manage_tasks');

        // Use distinct to avoid duplicates if any join condition or query causes duplicates
        $users = $users->select('users.*')
            ->distinct()
            ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->orderByRaw("CASE WHEN roles.name = 'admin' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN roles.name = 'admin' THEN users.id END ASC")
            ->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(
                function ($user) use ($workspace, $canEdit, $canDelete, $canManageProjects, $canManageTasks) {
                    $actions = '';
                    if ($canEdit) {
                        $actions .= '<a href="' . url("/users/edit/{$user->id}") . '" title="' . get_label('update', 'Update') . '">' .
                            '<i class="bx bx-edit mx-1"></i>' .
                            '</a>';
                    }

                    // if ($canDelete) {
                    //     $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $user->id . '" data-type="users">' .
                    //         '<i class="bx bx-trash text-danger mx-1"></i>' .
                    //         '</button>';
                    // }

                    $actions = $actions ?: '-';

                    $projectsBadge = '<span class="badge rounded-pill bg-primary">' . (isAdminOrHasAllDataAccess('user', $user->id) ? count($workspace->projects) : count($user->projects)) . '</span>';
                    if ($canManageProjects) {
                        $projectsBadge = '<a href="javascript:void(0);" class="viewAssigned" data-type="projects" data-id="' . 'user_' . $user->id . '" data-user="' . $user->first_name . ' ' . $user->last_name . '">' .
                            $projectsBadge . '</a>';
                    }

                    $tasksBadge = '<span class="badge rounded-pill bg-primary">' . (isAdminOrHasAllDataAccess('user', $user->id) ? count($workspace->tasks) : count($user->tasks)) . '</span>';
                    if ($canManageTasks) {
                        $tasksBadge = '<a href="javascript:void(0);" class="viewAssigned" data-type="tasks" data-id="' . 'user_' . $user->id . '" data-user="' . $user->first_name . ' ' . $user->last_name . '">' .
                            $tasksBadge . '</a>';
                    }

                    $photoHtml = "<div class='avatar avatar-md pull-up' title='" . $user->first_name . " " . $user->last_name . "'>
                                    <a href='" . url("/users/profile/{$user->id}") . "'>
                                        <img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle'>
                                    </a>
                                </div>";


                    $statusBadge = $user->status === 1
                        ? '<span class="badge bg-success">' . get_label('active', 'Active') . '</span>'
                        : '<span class="badge bg-danger">' . get_label('deactive', 'Deactive') . '</span>';

                    $formattedHtml = '<div class="d-flex mt-2">' .
                        $photoHtml .
                        '<div class="mx-2">' .
                        '<h6 class="mb-1">' .
                        $user->first_name . ' ' . $user->last_name .
                        ' ' . $statusBadge .
                        '</h6>' .
                        '<p class="text-muted">' . $user->email . '</p>' .
                        '</div>' .
                        '</div>';

                    $phone = !empty($user->country_code) ? $user->country_code . ' ' . $user->phone : $user->phone;

                    return [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'role' => "<span class='badge bg-label-" . (isset(config('taskhub.role_labels')[$user->getRoleNames()->first()]) ? config('taskhub.role_labels')[$user->getRoleNames()->first()] : config('taskhub.role_labels')['default']) . " me-1'>" . $user->getRoleNames()->first() . "</span>",
                        'email' => $user->email,
                        'phone' => $phone,
                        'dob' => $user->dob ? format_date($user->dob) : '-',
                        'doj' => $user->doj ? format_date($user->doj) : '-',
                        'profile' => $formattedHtml,
                        'status' => $user->status,
                        'created_at' => format_date($user->created_at, true),
                        'updated_at' => format_date($user->updated_at, true),
                        'assigned' => '<div class="d-flex justify-content-start align-items-center">' .
                            '<div class="text-center mx-4">' .
                            $projectsBadge .
                            '<div>' . get_label('projects', 'Projects') . '</div>' .
                            '</div>' .
                            '<div class="text-center">' .
                            $tasksBadge .
                            '<div>' . get_label('tasks', 'Tasks') . '</div>' .
                            '</div>' .
                            '</div>',
                        'actions' => $actions
                    ];
                }
            );

        return response()->json([
            "rows" => $users->items(),
            "total" => $totalusers,
        ]);
    }


    /**
     * List or search users.
     * 
     * This endpoint retrieves a list of users based on various filters. The user must be authenticated to perform this action. The request allows filtering by status, search term, role, type, type_id, and other parameters.
     * 
     * @authenticated
     * 
     * @group User Management
     *
     * @urlParam id int optional The ID of the user to retrieve. Example: 1
     * 
     * @queryParam search string optional The search term to filter users by id, first name, last name, phone, or email. Example: John
     * @queryParam sort string optional The field to sort by. Defaults to "id". Sortable fields include: id, phone, dob, doj, created_at, and updated_at. Example: id
     * @queryParam order string optional The sort order, either "ASC" or "DESC". Defaults to "DESC". Example: ASC
     * @queryParam status int optional The status ID to filter users by, either 0 or 1. Example: 1
     * @queryParam role_ids array optional The role IDs to filter users by. Example: [1, 2]
     * @queryParam type string optional The type of filter to apply, either "project" or "task". Example: project
     * @queryParam type_id int optional The ID associated with the type filter. Example: 3
     * @queryParam limit int optional The number of users per page for pagination. Example: 10
     * @queryParam offset int optional The offset for pagination, indicating the starting point of results. Example: 0
     * 
     * @response 200 {
     *  "error": false,
     *  "message": "Users retrieved successfully",
     *  "total": 1,
     *  "data": [
     *    {
     *   "id": 219,
     *   "first_name": "Test",
     *   "last_name": "Test",
     *   "role": "Member",
     *   "email": "test@gmail.com",
     *   "phone": "+91 1111111111",
     *   "dob": "09-08-2024",
     *   "doj": "09-08-2024",
     *   "address": "Test",
     *   "city": "Test",
     *   "state": "Test",
     *   "country": "Test",
     *   "zip": "111-111",
     *   "photo": "https://test-taskify.infinitietech.com/storage/photos/K0OAOzWyoeD0ZXBzgsaeHZUZERbOTKRljRIYOEYU.png",
     *   "status": 1,
     *   "created_at": "09-08-2024 17:04:29",
     *   "updated_at": "09-08-2024 17:04:29",
     *   "assigned": {
     *     "projects": 0,
     *     "tasks": 0
     *   }
     *    }
     *  ]
     * }
     * 
     * @response 200 {
     *   "error": true,
     *   "message": "User not found",
     *   "total": 0,
     *   "data": []     
     * }
     * 
     * @response 200 {
     *   "error": true,
     *   "message": "Users not found",
     *   "total": 0,
     *   "data": []
     * }
     * 
     * @response 200 {
     *   "error": true,
     *   "message": "Project not found",
     *   "total": 0,
     *   "data": []
     * }
     * 
     * @response 200 {
     *   "error": true,
     *   "message": "Task not found",
     *   "total": 0,
     *   "data": []
     * }
     */

    public function apiList(Request $request, $id = '')
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $status = $request->input('status', '');
        $role_ids = $request->input('role_ids', []);
        $type = $request->input('type', '');
        $type_id = $request->input('type_id', '');
        $limit = $request->input('limit', 10); // default limit
        $offset = $request->input('offset', 0); // default offset

        if ($id) {
            $user = User::find($id);
            if (!$user) {
                return formatApiResponse(
                    false,
                    'User not found',
                    [
                        'total' => 0,
                        'data' => []
                    ]
                );
            } else {
                return formatApiResponse(false, 'User retrieved successfully', [
                    'total' => 1,
                    'data' => [formatUser($user)],
                ]);
            }
        } else {
            $workspace = Workspace::find(getWorkspaceId());

            if ($type && $type_id) {
                if ($type == 'project') {
                    $project = Project::find($type_id);
                    if ($project) {
                        $usersQuery = $project->users();
                    } else {
                        return formatApiResponse(
                            true,
                            'Project not found',
                            [
                                'total' => 0,
                                'data' => []
                            ]
                        );
                    }
                } elseif ($type == 'task') {
                    $task = Task::find($type_id);
                    if ($task) {
                        $usersQuery = $task->users();
                    } else {
                        return formatApiResponse(
                            true,
                            'Task not found',
                            [
                                'total' => 0,
                                'data' => []
                            ]
                        );
                    }
                } else {
                    $usersQuery = $workspace->users();
                }
            } else {
                $usersQuery = $workspace->users();
            }

            $usersQuery->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('users.id', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            });

            if ($status != '') {
                $usersQuery->where('status', $status);
            }

            if (!empty($role_ids)) {
                $usersQuery->whereHas('roles', function ($query) use ($role_ids) {
                    $query->whereIn('roles.id', $role_ids);
                });
            }

            $total = $usersQuery->count(); // get total count before applying offset and limit

            $users = $usersQuery->orderBy($sort, $order)
                ->skip($offset)
                ->take($limit)
                ->get();

            if ($users->isEmpty()) {
                return formatApiResponse(
                    false,
                    'Users not found',
                    [
                        'total' => 0,
                        'data' => []
                    ]
                );
            }

            $data = $users->map(function ($user) {
                return formatUser($user);
            });

            return formatApiResponse(false, 'Users retrieved successfully', [
                'total' => $total,
                'data' => $data,
            ]);
        }
    }

    /**
     * Update FCM Token.
     * 
     * This endpoint allows an authenticated user or client to update their FCM token for push notifications.
     *
     * @bodyParam fcm_token string required The new FCM token for push notifications. Example: dXkJz7KYZ9o:APA91bGfLa_qwAeD...
     *
     * @response 200 {
     *   "error": false,
     *   "message": "FCM token updated successfully."
     * }
     */

    public function updateFcmToken(Request $request)
    {
        try {
            $formFields = $request->validate([
                'fcm_token' => 'required|string',
            ]);

            $authUser = getAuthenticatedUser();
            $isUser = getGuardName() == 'web';
            $user = $isUser ? User::find($authUser->id) : Client::find($authUser->id);

            if (!$user) {
                return formatApiResponse(
                    true,
                    'User not found',
                    []
                );
            }

            $user->fcm_token = $formFields['fcm_token'];
            $user->save();

            return formatApiResponse(
                false,
                'FCM token updated successfully.',
                [
                    'data' => $isUser ? formatUser($user) : formatClient($user),
                ]
            );
        } catch (ValidationException $e) {
            return formatApiValidationError(true, $e->errors());
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }
}
