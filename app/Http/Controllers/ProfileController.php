<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Profile;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Rules\UniqueEmailPassword;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show()
    {
        $roles = Role::all();
        return view('users.account', ['user' => getAuthenticatedUser(), 'roles' => $roles]);
    }

    /**
     * Retrieve the authenticated user's profile.
     * 
     * This endpoint returns the profile information of the currently authenticated user. The user must be authenticated to access their profile details.
     * 
     * @group Profile Management
     * 
     * @authenticated
     *
     * @response {
     * "error": false,
     * "message": "Profile details retrieved successfully",
     * "data": {
     * "id": 7,
     * "first_name": "Madhavan",
     * "last_name": "Vaidya",
     * "role": "admin",
     * "email": "admin@gmail.com",
     * "phone": "9099882203",
     * "dob": "17-06-2024",
     * "doj": "03-10-2022",
     * "address": "Devonshire",
     * "city": "Windsor",
     * "state": "ON",
     * "country": "Canada",
     * "zip": "123654",
     * "photo": "https://test-taskify.infinitietech.com/storage/photos/yxNYBlFLALdLomrL0JzUY2USPLILL9Ocr16j4n2o.png",
     * "status": 1,
     * "created_at": "03-01-2023 10:37:20",
     * "updated_at": "13-08-2024 14:16:45",
     * "assigned": {
     * "projects": 11,
     * "tasks": 9
     * },
     * "is_admin_or_leave_editor": true,
     * "is_admin_or_has_all_data_access": true
     * }
     * }
     *
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $data = (getGuardName() == 'client') ? formatClient($user) : formatUser($user);

        $data['is_admin_or_leave_editor'] = is_admin_or_leave_editor();
        $data['is_admin_or_has_all_data_access'] = isAdminOrHasAllDataAccess();

        return formatApiResponse(
            false,
            'Profile details retrieved successfully',
            [
                'data' => $data
            ]
        );
    }

    /**
     * Update the profile details of a logged-in user.
     *
     * This endpoint allows the authenticated user to update their profile details such as name, email, address, and other relevant information.
     *
     * @authenticated
     * 
     * @group Profile Management
     * 
     * @urlParam id int required The ID of the user whose profile is being updated.
     * 
     * @bodyParam first_name string required The user's first name. Example: Madhavan
     * @bodyParam last_name string required The user's last name. Example: Vaidya
     * @bodyParam email string required The user's email address. Can only be edited if `is_admin_or_has_all_data_access` is true for the logged-in user. Example: admin@gmail.com
     * @bodyParam role integer The ID of the role for the user. If the authenticated user is an admin, the provided role will be used. If the authenticated user is not an admin, the current role of the user will be used, regardless of the input. Example: 1
     * @bodyParam phone string The user's phone number. Example: 9099882203
     * @bodyParam country_code string The country code for the phone number. Example: +91
     * @bodyParam country_iso_code string nullable The ISO code for the phone number. Example: in
     * @bodyParam dob date The user's date of birth. Example: 17-06-2024
     * @bodyParam doj date The user's date of joining. Example: 03-10-2022
     * @bodyParam address string The user's address. Example: Devonshire
     * @bodyParam city string The user's city. Example: Windsor
     * @bodyParam state string The user's state. Example: ON
     * @bodyParam country string The user's country. Example: Canada
     * @bodyParam zip string The user's zip code. Example: 123654
     * @bodyParam password string The user's new password (if changing). Example: 12345678
     * @bodyParam password_confirmation string The password confirmation (if changing password). Example: 12345678
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Profile details updated successfully.",
     *   "data": {
     *     "id": 7,
     *     "first_name": "Madhavan",
     *     "last_name": "Vaidya",
     *     "role": "admin",
     *     "email": "admin@gmail.com",
     *     "phone": "9099882203",
     *     "dob": "17-06-2024",
     *     "doj": "03-10-2022",
     *     "address": "Devonshire",
     *     "city": "Windsor",
     *     "state": "ON",
     *     "country": "Canada",
     *     "zip": "123654",
     *     "photo": "https://test-taskify.infinitietech.com/storage/photos/atEj9NKCeAJhM5VqBN69mFKHntHbZkPUl2Sa22RA.webp",
     *     "status": 1,
     *     "created_at": "03-01-2023 10:37:20",
     *     "updated_at": "13-08-2024 18:58:34",
     *     "assigned": {
     *       "projects": 11,
     *       "tasks": 21
     *     }
     *   }
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "Validation error: The email has already been taken."
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "User not found",
     *   "data": []
     * }
     * 
     * @response 500 {
     *   "error": true,
     *   "message": "Profile details couldn\'t be updated."
     * }
     *
     */


    public function update(Request $request, $id)
    {
        $isApi = request()->get('isApi', false);
        if (getAuthenticatedUser()->getRoleNames()->first() != 'admin') {
            $role = getAuthenticatedUser()->roles->pluck('id')[0];
            $request->merge(['role' => $role]);
        }
        $rules = [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'phone' => 'nullable',
            'country_code' => 'nullable',
            'country_iso_code' => 'nullable',
            'role' => 'required',
            'address' => 'nullable',
            'city' => 'nullable',
            'state' => 'nullable',
            'country' => 'nullable',
            'zip' => 'nullable',
            'password' => 'nullable|min:6',
            'password_confirmation' => 'required_with:password|same:password',
        ];

        $isUser = getGuardName() == 'web';
        if (isAdminOrHasAllDataAccess()) {
            $rules['email'] = [
                'required',
                'email',
                Rule::unique($isUser ? 'users' : 'clients', 'email')->ignore($id),
            ];
        }
        try {
            $formFields = $request->validate($rules);
            if (request()->filled('password')) {
                $uniqueEmailPasswordRule = new UniqueEmailPassword($isUser ? 'user' : 'client');
                if (!$uniqueEmailPasswordRule->passes('password', $request->input('password'))) {
                    return formatApiValidationError($isApi, ['email' => [$uniqueEmailPasswordRule->message()]]);
                }
            }
            $user = $isUser ? User::find($id) : Client::find($id);
            if (!$user) {
                return formatApiResponse(
                    true,
                    'User not found',
                    []
                );
            }
            if (isset($formFields['password']) && !empty($formFields['password'])) {
                $formFields['password'] = bcrypt($formFields['password']);
            } else {
                unset($formFields['password']);
            }
            $user->update($formFields);
            $user->syncRoles($request->input('role'));

            // Session::flash('message', 'Profile details updated successfully.');
            return formatApiResponse(
                false,
                'Profile details updated successfully.',
                [
                    'data' => $isUser ? formatUser($user) : formatClient($user),
                ]
            );
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'error' => true,
                'message' => 'Profile details couldn\'t be updated.'
            ], 500);
        }
    }

    /**
     * Update the profile picture of a logged-in user.
     *
     * This endpoint allows the authenticated user to update their profile picture.
     *
     * @authenticated
     * 
     * @group Profile Management
     * 
     * @urlParam id int required The ID of the user whose profile picture is being updated.
     * 
     * @response 200 {
     *   "error": false,
     *   "message": "Profile picture updated successfully.",
     *   "data": {
     *     "id": 7,
     *     "first_name": "Madhavan",
     *     "last_name": "Vaidya",
     *     "role": "admin",
     *     "email": "admin@gmail.com",
     *     "phone": "9099882203",
     *     "dob": "17-06-2024",
     *     "doj": "03-10-2022",
     *     "address": "Devonshire",
     *     "city": "Windsor",
     *     "state": "ON",
     *     "country": "Canada",
     *     "zip": "123654",
     *     "photo": "https://test-taskify.infinitietech.com/storage/photos/atEj9NKCeAJhM5VqBN69mFKHntHbZkPUl2Sa22RA.webp",
     *     "status": 1,
     *     "created_at": "03-01-2023 10:37:20",
     *     "updated_at": "13-08-2024 18:58:34",
     *     "assigned": {
     *       "projects": 11,
     *       "tasks": 21
     *     }
     *   }
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "No profile picture selected!"
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "User not found",
     *   "data": []
     * }
     * 
     * @response 500 {
     *   "error": true,
     *   "message": "Profile picture couldn't be updated."
     * }
     *
     */

    public function update_photo(Request $request, $id)
    {
        try {
            if ($request->hasFile('upload')) {
                $isUser = getGuardName() == 'web';
                $user =  $isUser ? User::find($id) : Client::find($id);

                if (!$user) {
                    return formatApiResponse(
                        true,
                        'User not found',
                        []
                    );
                }

                if ($user->photo != 'photos/no-image.jpg' && $user->photo !== null) {
                    Storage::disk('public')->delete($user->photo);
                }

                $formFields['photo'] = $request->file('upload')->store('photos', 'public');
                $user->update($formFields);

                return formatApiResponse(
                    false,
                    'Profile picture updated successfully.',
                    [
                        'data' => $isUser ? formatUser($user) : formatClient($user),
                    ]
                );
            } else {
                return response()->json(['error' => true, 'message' => 'No profile picture selected!']);
            }
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'error' => true,
                'message' => 'Profile picture couldn\'t be updated.'
            ], 500);
        }
    }

    /**
     * Delete account of a logged-in user.
     *
     * This endpoint allows the authenticated user to delete their account.
     *
     * @authenticated
     * 
     * @group Profile Management
     * 
     * @response 200 {
     *   "error": false,
     *   "message": "Account deleted successfully."
     *   "data": []
     * }
     * 
     * @response 404 {
     *   "error": true,
     *   "message": "User not found",
     *   "data": []
     * }
     * 
     * @response 500 {
     *   "error": true,
     *   "message": "Account couldn't be deleted."
     * }
     *
     */
    public function destroy()
    {
        try {
            $user = getAuthenticatedUser();
            if (!$user) {
                return response()->json([
                    'error' => true,
                    'message' => 'User not found',
                    'data' => []
                ], 404);
            }
            $isUser = getGuardName() == 'web';
            $modelClass = $isUser ? User::class : Client::class;
            // Call the deletion service
            $response = DeletionService::delete($modelClass, $user->id, 'Account');
            $responseData = json_decode($response->getContent(), true);
            if ($responseData['error']) {
                // Handle error response
                return response()->json($responseData);
            }
            // Delete associated todos
            $user->todos()->delete();
            return response()->json([
                'error' => false,
                'message' => 'Account deleted successfully.',
                'data' => []
            ]);
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'error' => true,
                'message' => 'Account couldn\'t be deleted.'
            ], 500);
        }
    }
}
