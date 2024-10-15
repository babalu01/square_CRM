<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Status;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Workspace;
use App\Models\Milestone;
use App\Models\UserClientPreference;
use App\Models\Tag;
use App\Models\ProjectUser;
use Illuminate\Http\Request;
use App\Models\ProjectClient;
use App\Services\DeletionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Chatify\Facades\ChatifyMessenger as Chatify;
use Exception;
use Illuminate\Validation\ValidationException;

class ProjectsController extends Controller
{
    protected $workspace;
    protected $user;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // fetch session and use it in entire class with constructor
            $this->workspace = Workspace::find(getWorkspaceId());
            $this->user = getAuthenticatedUser();
            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $type = null)
    {
        // Get multiple statuses from the request
        $statuses = $request->input('statuses', []); // Default to empty array if not provided
        $selectedTags = $request->input('tags', []);

        $where = [];
        $is_favorite = 0;

        if ($type === 'favorite') {
            $where['is_favorite'] = 1;
            $is_favorite = 1;
        }

        $sort = $request->input('sort', 'id');
        $order = 'desc';

        switch ($sort) {
            case 'newest':
                $sort = 'created_at';
                $order = 'desc';
                break;
            case 'oldest':
                $sort = 'created_at';
                $order = 'asc';
                break;
            case 'recently-updated':
                $sort = 'updated_at';
                $order = 'desc';
                break;
            case 'earliest-updated':
                $sort = 'updated_at';
                $order = 'asc';
                break;
            default:
                $sort = 'id';
                $order = 'desc';
                break;
        }

        $projectsQuery = isAdminOrHasAllDataAccess() ? $this->workspace->projects() : $this->user->projects();

        if (!empty($statuses)) {
            $projectsQuery->whereIn('status_id', $statuses); // Apply multiple status filter
        }

        if (!empty($selectedTags)) {
            $projectsQuery->whereHas('tags', function ($q) use ($selectedTags) {
                $q->whereIn('tags.id', $selectedTags);
            });
        }

        $projects = $projectsQuery->orderBy($sort, $order)->paginate(6);

        return view('projects.grid_view', [
            'projects' => $projects,
            'auth_user' => $this->user,
            'selectedTags' => $selectedTags,
            'is_favorite' => $is_favorite
        ]);
    }


    public function list_view(Request $request, $type = null)
    {
        $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects : $this->user->projects;

        $is_favorites = 0;
        if ($type === 'favorite') {
            $is_favorites = 1;
        }
        return view('projects.projects', ['projects' => $projects, 'is_favorites' => $is_favorites]);
    }

    /**
     * Create a new project.
     * 
     * This endpoint creates a new project with the provided details. The user must be authenticated to perform this action. The request validates various fields, including title, status, priority, dates, and task accessibility.
     * 
     * @authenticated
     * 
     * @group Project Management
     *
     * @bodyParam title string required The title of the project. Example: New Website Launch
     * @bodyParam status_id int required The ID of the project's status. Example: 1
     * @bodyParam priority_id int optional The ID of the project's priority. Example: 2
     * @bodyParam start_date string|null optional The start date of the project in the format specified in the general settings. Example: 2024-08-01
     * @bodyParam end_date string|null optional The end date of the project in the format specified in the general settings. Example: 2024-08-31
     * @bodyParam budget string|null optional Only digits, commas as thousand separators, and a single decimal point are allowed. digits can optionally be grouped in thousands with commas, where each group of digits must be exactly three digits long (e.g., 1,000 is correct; 10,0000 is not). Example: 5000.00
     * @bodyParam task_accessibility string required Indicates who can access the task. Must be either 'project_users' or 'assigned_users'. Example: project_users
     * @bodyParam description string|null optional A description of the project. Example: A project to launch a new company website.
     * @bodyParam note string|null optional Additional notes for the project. Example: Ensure all team members are informed.
     * @bodyParam user_id array|null optional Array of user IDs to be associated with the project. Example: [1, 2, 3]
     * @bodyParam client_id array|null optional Array of client IDs to be associated with the project. Example: [5, 6]
     * @bodyParam tag_ids array|null optional Array of tag IDs to be associated with the project. Example: [10, 11]
     *
     * @response 200 {
     * "error": false,
     * "message": "Project created successfully.",
     * "id": 438,
     * "data": {
     *   "id": 438,
     *   "title": "Res Test",
     *   "status": "Default",
     *   "priority": "dsfdsf",
     *   "users": [
     *     {
     *       "id": 7,
     *       "first_name": "Madhavan",
     *       "last_name": "Vaidya",
     *       "photo": "https://test-taskify.infinitietech.com/storage/photos/yxNYBlFLALdLomrL0JzUY2USPLILL9Ocr16j4n2o.png"
     *     },
     *     {
     *       "id": 185,
     *       "first_name": "Admin",
     *       "last_name": "Test",
     *       "photo": "https://test-taskify.infinitietech.com/storage/photos/no-image.jpg"
     *     }
     *   ],
     *   "clients": [
     *     {
     *       "id": 103,
     *       "first_name": "Test",
     *       "last_name": "Test",
     *       "photo": "https://test-taskify.infinitietech.com/storage/photos/no-image.jpg"
     *     }
     *   ],
     *   "tags": [
     *     {
     *       "id": 45,
     *       "title": "Tag from update project"
     *     }
     *   ],
     *   "start_date": null,
     *   "end_date": null,
     *   "budget": "1000",
     *   "task_accessibility": "assigned_users",
     *   "description": null,
     *   "note": null,
     *   "favorite": 0,
     *   "created_at": "07-08-2024 14:38:51",
     *   "updated_at": "07-08-2024 14:38:51"
     * }
     * }
     *
     * @response 422 {
     *   "error": true,
     *   "message": "Validation errors occurred",
     *   "errors": {
     *     "title": [
     *       "The title field is required."
     *     ],
     *     "status_id": [
     *       "The status_id field is required."
     *     ],
     *     "start_date": [
     *       "The start date must be before or equal to the end date."
     *     ],
     *     "budget": [
     *       "The budget format is invalid."
     *     ],
     *     "task_accessibility": [
     *       "The task accessibility must be either project_users or assigned_users."
     *     ]
     *   }
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "You are not authorized to set this status."
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred while creating the project."
     * }
     */
    public function store(Request $request)
    {
        $isApi = request()->get('isApi', false);
        // Define validation rules
        $rules = [
            'title' => 'required',
            'status_id' => 'required|exists:statuses,id',
            'priority_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value != 0 && !\DB::table('priorities')->where('id', $value)->exists()) {
                        $fail('The selected ' . $attribute . ' is invalid.');
                    }
                },
            ],
            'start_date' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $endDate = request()->input('end_date');
                    $errors = validate_date_format_and_order($value, $endDate);

                    if (!empty($errors['start_date'])) {
                        foreach ($errors['start_date'] as $error) {
                            $fail($error);
                        }
                    }
                },
            ],
            'end_date' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $startDate = request()->input('start_date');
                    $errors = validate_date_format_and_order($startDate, $value);

                    if (!empty($errors['end_date'])) {
                        foreach ($errors['end_date'] as $error) {
                            $fail($error);
                        }
                    }
                },
            ],
            'budget' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $error = validate_currency_format($value, 'budget');
                    if ($error) {
                        $fail($error);
                    }
                }
            ],
            'task_accessibility' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value !== 'project_users' && $value !== 'assigned_users') {
                        $fail('The task accessibility must be either project_users or assigned_users.');
                    }
                }
            ],
            'description' => 'nullable|string',
            'note' => 'nullable|string',
            'user_id' => 'nullable|array',
            'user_id.*' => 'integer|exists:users,id', // Validate that each user_id exists in the users table

            'client_id' => 'nullable|array',
            'client_id.*' => 'integer|exists:clients,id', // Validate that each client_id exists in the clients table

            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id', // Validate that each tag_id exists in the tags table
        ];


        // Custom validation messages
        $messages = [
            'status_id.required' => 'The status field is required.'
        ];

        // Validate the request
        try {
            $formFields = $request->validate($rules, $messages);
            $status = Status::findOrFail($request->input('status_id'));
            if (canSetStatus($status)) {
                $start_date = $request->input('start_date');
                $end_date = $request->input('end_date');
                if ($start_date) {
                    $formFields['start_date'] = format_date($start_date, false, app('php_date_format'), 'Y-m-d');
                }
                if ($end_date) {
                    $formFields['end_date'] = format_date($end_date, false, app('php_date_format'), 'Y-m-d');
                }
                $formFields['budget'] = str_replace(',', '', $request->input('budget'));
                $formFields['workspace_id'] = getWorkspaceId();
                $formFields['created_by'] = $this->user->id;

                unset($formFields['user_id']);
                unset($formFields['client_id']);
                unset($formFields['tag_ids']);
                $new_project = Project::create($formFields);

                $userIds = $request->input('user_id') ?? [];
                $clientIds = $request->input('client_id') ?? [];
                $tagIds = $request->input('tag_ids') ?? [];
                // Set creator as a participant automatically
                if (getGuardName() == 'client' && !in_array($this->user->id, $clientIds)) {
                    array_splice($clientIds, 0, 0, $this->user->id);
                } else if (getGuardName() == 'web' && !in_array($this->user->id, $userIds)) {
                    array_splice($userIds, 0, 0, $this->user->id);
                }

                $project_id = $new_project->id;
                $project = Project::find($project_id);
                $project->users()->attach($userIds);
                $project->clients()->attach($clientIds);
                $project->tags()->attach($tagIds);

                $notification_data = [
                    'type' => 'project',
                    'type_id' => $project_id,
                    'type_title' => $project->title,
                    'access_url' => 'projects/information/' . $project_id,
                    'action' => 'assigned'
                ];
                $recipients = array_merge(
                    array_map(function ($userId) {
                        return 'u_' . $userId;
                    }, $userIds),
                    array_map(function ($clientId) {
                        return 'c_' . $clientId;
                    }, $clientIds)
                );
                processNotifications($notification_data, $recipients);

                return formatApiResponse(
                    false,
                    'Project created successfully.',
                    [
                        'id' => $new_project->id,
                        'data' => formatProject($project)
                    ]
                );
            } else {
                return response()->json(['error' => true, 'message' => 'You are not authorized to set this status.']);
            }
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while creating the project.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $project = Project::findOrFail($id);
        $projectTags = $project->tags;
        $types = getControllerNames();
        return view('projects.project_information', ['project' => $project, 'projectTags' => $projectTags, 'types' => $types, 'auth_user' => $this->user]);
    }

    public function get($projectId)
    {
        $project = Project::findOrFail($projectId);
        $project->budget = format_currency($project->budget, false, false);
        $users = $project->users()->get();
        $clients = $project->clients()->get();
        $tags = $project->tags()->get();

        $workspace_users = $this->workspace->users;
        $workspace_clients = $this->workspace->clients;

        return response()->json(['error' => false, 'project' => $project, 'users' => $users, 'clients' => $clients, 'workspace_users' => $workspace_users, 'workspace_clients' => $workspace_clients, 'tags' => $tags]);
    }

    /**
     * Update an existing project.
     * 
     * This endpoint updates an existing project with the provided details. The user must be authenticated to perform this action. The request validates various fields, including title, status, priority, dates, and task accessibility.
     * 
     * @authenticated
     * 
     * @group Project Management
     *
     * @bodyParam id int required The ID of the project to update. Example: 1
     * @bodyParam title string required The title of the project. Example: Updated Project Title
     * @bodyParam status_id int required The ID of the project's status. Example: 2
     * @bodyParam priority_id int optional The ID of the project's priority. Example: 3
     * @bodyParam budget string|null optional Only digits, commas as thousand separators, and a single decimal point are allowed. digits can optionally be grouped in thousands with commas, where each group of digits must be exactly three digits long (e.g., 1,000 is correct; 10,0000 is not). Example: 5000.00
     * @bodyParam task_accessibility string required Indicates who can access the task. Must be either 'project_users' or 'assigned_users'. Example: assigned_users
     * @bodyParam start_date string|null optional The start date of the project in the format specified in the general settings. Example: 2024-08-01
     * @bodyParam end_date string|null optional The end date of the project in the format specified in the general settings. Example: 2024-08-31
     * @bodyParam description string|null optional A description of the project. Example: Updated project description.
     * @bodyParam note string|null optional Additional notes for the project. Example: Updated note for the project.
     * @bodyParam user_id array|null optional Array of user IDs to be associated with the project. Example: [2, 3]
     * @bodyParam client_id array|null optional Array of client IDs to be associated with the project. Example: [5, 6]
     * @bodyParam tag_ids array|null optional Array of tag IDs to be associated with the project. Example: [10, 11]
     *
     * @response 200 {
     * "error": false,
     * "message": "Project updated successfully.",
     * "id": 438,
     * "data": {
     *   "id": 438,
     *   "title": "Res Test",
     *   "status": "Default",
     *   "priority": "dsfdsf",
     *   "users": [
     *     {
     *       "id": 7,
     *       "first_name": "Madhavan",
     *       "last_name": "Vaidya",
     *       "photo": "https://test-taskify.infinitietech.com/storage/photos/yxNYBlFLALdLomrL0JzUY2USPLILL9Ocr16j4n2o.png"
     *     },
     *     {
     *       "id": 185,
     *       "first_name": "Admin",
     *       "last_name": "Test",
     *       "photo": "https://test-taskify.infinitietech.com/storage/photos/no-image.jpg"
     *     }
     *   ],
     *   "clients": [
     *     {
     *       "id": 103,
     *       "first_name": "Test",
     *       "last_name": "Test",
     *       "photo": "https://test-taskify.infinitietech.com/storage/photos/no-image.jpg"
     *     }
     *   ],
     *   "tags": [
     *     {
     *       "id": 45,
     *       "title": "Tag from update project"
     *     }
     *   ],
     *   "start_date": null,
     *   "end_date": null,
     *   "budget": "1000",
     *   "task_accessibility": "assigned_users",
     *   "description": null,
     *   "note": null,
     *   "favorite": 0,
     *   "created_at": "07-08-2024 14:38:51",
     *   "updated_at": "07-08-2024 14:38:51"
     * }
     * }
     *
     * @response 422 {
     *   "error": true,
     *   "message": "Validation errors occurred",
     *   "errors": {
     *     "id": [
     *       "The project ID is required.",
     *       "The project ID does not exist in our records."
     *     ],
     *     "status_id": [
     *       "The status field is required."
     *     ],
     *     "budget": [
     *       "The budget format is invalid."
     *     ],
     *     "task_accessibility": [
     *       "The task accessibility must be either project_users or assigned_users."
     *     ],
     *     "start_date": [
     *       "The start date must be before or equal to the end date."
     *     ]
     *   }
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "You are not authorized to set this status."
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred while updating the project."
     * }
     */


    public function update(Request $request)
    {
        $isApi = request()->get('isApi', false);
        $rules = [
            'id' => 'required|exists:projects,id',
            'title' => 'required',
            'status_id' => 'required',
            'priority_id' => 'nullable',
            'budget' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $error = validate_currency_format($value, 'budget');
                    if ($error) {
                        $fail($error);
                    }
                }
            ],
            'task_accessibility' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value !== 'project_users' && $value !== 'assigned_users') {
                        $fail('The task accessibility must be either project_users or assigned_users.');
                    }
                }
            ],
            'start_date' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $endDate = request()->input('end_date');
                    $errors = validate_date_format_and_order($value, $endDate);

                    if (!empty($errors['start_date'])) {
                        foreach ($errors['start_date'] as $error) {
                            $fail($error);
                        }
                    }
                },
            ],
            'end_date' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $startDate = request()->input('start_date');
                    $errors = validate_date_format_and_order($startDate, $value);

                    if (!empty($errors['end_date'])) {
                        foreach ($errors['end_date'] as $error) {
                            $fail($error);
                        }
                    }
                },
            ],
            'user_id' => 'nullable|array',
            'user_id.*' => 'exists:users,id', // Validate that each user_id exists in the users table

            'client_id' => 'nullable|array',
            'client_id.*' => 'exists:clients,id', // Validate that each client_id exists in the clients table

            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id', // Validate that each tag_id exists in the tags table
        ];


        $messages = [
            'status_id.required' => 'The status field is required.'
        ];

        // Validate the request
        try {
            $request->validate($rules, $messages);



            $id = $request->input('id');
            $project = Project::findOrFail($id);
            $currentStatusId = $project->status_id;

            // Check if the status has changed
            if ($currentStatusId != $request->input('status_id')) {
                $status = Status::findOrFail($request->input('status_id'));
                if (!canSetStatus($status)) {
                    return response()->json(['error' => true, 'message' => 'You are not authorized to set this status.']);
                }
            }
            $formFieldsToUpdate = [
                'title' => $request->input('title'),
                'status_id' => $request->input('status_id'),
                'priority_id' => $request->input('priority_id'),
                'budget' => str_replace(',', '', $request->input('budget')),
                'task_accessibility' => $request->input('task_accessibility'),
                'description' => $request->input('description'),
                'note' => $request->input('note'),
            ];

            // Handle start_date
            if ($request->filled('start_date')) {
                $formFieldsToUpdate['start_date'] = format_date($request->input('start_date'), false, app('php_date_format'), 'Y-m-d');
            } else {
                $formFieldsToUpdate['start_date'] = null;
            }

            // Handle end_date
            if ($request->filled('end_date')) {
                $formFieldsToUpdate['end_date'] = format_date($request->input('end_date'), false, app('php_date_format'), 'Y-m-d');
            } else {
                $formFieldsToUpdate['end_date'] = null;
            }

            $userIds = $request->input('user_id') ?? [];
            $clientIds = $request->input('client_id') ?? [];
            $tagIds = $request->input('tag_ids') ?? [];

            // Set creator as a participant automatically
            if (getGuardName() == 'client' && !in_array($this->user->id, $clientIds)) {
                array_splice($clientIds, 0, 0, $this->user->id);
            } else if (getGuardName() == 'web' && !in_array($this->user->id, $userIds)) {
                array_splice($userIds, 0, 0, $this->user->id);
            }

            // Get current list of users and clients associated with the project
            $existingUserIds = $project->users->pluck('id')->toArray();
            $existingClientIds = $project->clients->pluck('id')->toArray();

            // Update project and its relationships
            $project->update($formFieldsToUpdate);
            $project->users()->sync($userIds);
            $project->clients()->sync($clientIds);
            $project->tags()->sync($tagIds);

            // Exclude old users and clients from receiving notification
            $userIds = array_diff($userIds, $existingUserIds);
            $clientIds = array_diff($clientIds, $existingClientIds);

            // Prepare notification data
            $notificationData = [
                'type' => 'project',
                'type_id' => $project->id,
                'type_title' => $project->title,
                'access_url' => 'projects/information/' . $project->id,
                'action' => 'assigned'
            ];

            // Determine recipients
            $recipients = array_merge(
                array_map(function ($userId) {
                    return 'u_' . $userId;
                }, $userIds),
                array_map(function ($clientId) {
                    return 'c_' . $clientId;
                }, $clientIds)
            );

            // Process notifications
            processNotifications($notificationData, $recipients);

            if ($currentStatusId != $request->input('status_id')) {
                $currentStatus = Status::findOrFail($currentStatusId);
                $newStatus = Status::findOrFail($request->input('status_id'));

                $notification_data = [
                    'type' => 'project_status_updation',
                    'type_id' => $project->id,
                    'type_title' => $project->title,
                    'updater_first_name' => $this->user->first_name,
                    'updater_last_name' => $this->user->last_name,
                    'old_status' => $currentStatus->title,
                    'new_status' => $newStatus->title,
                    'access_url' => 'projects/information/' . $project->id,
                    'action' => 'status_updated'
                ];

                $currentRecipients = array_merge(
                    array_map(function ($userId) {
                        return 'u_' . $userId;
                    }, $existingUserIds),
                    array_map(function ($clientId) {
                        return 'c_' . $clientId;
                    }, $existingClientIds)
                );
                processNotifications($notification_data, $currentRecipients);
            }
            $project = $project->fresh();
            return formatApiResponse(
                false,
                'Project updated successfully.',
                [
                    'id' => $project->id,
                    'data' => formatProject($project)
                ]
            );
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while updating the project.'
            ], 500);
        }
    }

    /**
     * Remove the specified project.
     *
     * This endpoint deletes a project based on the provided ID. The user must be authenticated to perform this action.
     *
     * @authenticated
     *
     * @group Project Management
     *
     * @urlParam id int required The ID of the project to be deleted. Example: 1
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Project deleted successfully.",
     *   "id": 1,
     *   "title": "Project Title",
     *   "data": []
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "Project not found.",
     *   "data": []
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred while deleting the project."
     * }
     */

    public function destroy($id)
    {
        $response = DeletionService::delete(Project::class, $id, 'Project');
        return $response;
    }

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:projects,id' // Ensure each ID in 'ids' is an integer and exists in the 'projects' table
        ]);

        $ids = $validatedData['ids'];
        $deletedProjects = [];
        $deletedProjectTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $project = Project::find($id);
            if ($project) {
                $deletedProjectTitles[] = $project->title;
                DeletionService::delete(Project::class, $id, 'Project');
                $deletedProjects[] = $id;
            }
        }

        return response()->json(['error' => false, 'message' => 'Project(s) deleted successfully.', 'id' => $deletedProjects, 'titles' => $deletedProjectTitles]);
    }



    public function list(Request $request, $id = '', $type = '')
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $status_ids = request('status_ids', []);
        $priority_ids = request('priority_ids', []);
        $user_ids = request('user_ids', []);
        $client_ids = request('client_ids', []);
        $tag_ids = $request->input('tag_ids', []);
        $start_date_from = (request('project_start_date_from')) ? request('project_start_date_from') : "";
        $start_date_to = (request('project_start_date_to')) ? request('project_start_date_to') : "";
        $end_date_from = (request('project_end_date_from')) ? request('project_end_date_from') : "";
        $end_date_to = (request('project_end_date_to')) ? request('project_end_date_to') : "";
        $is_favorites = (request('is_favorites')) ? request('is_favorites') : "";
        $where = [];

        if ($is_favorites) {
            $where['is_favorite'] = 1;
        }
        if ($id) {
            $id = explode('_', $id);
            $belongs_to = $id[0];
            $belongs_to_id = $id[1];
            $userOrClient = $belongs_to == 'user' ? User::find($belongs_to_id) : Client::find($belongs_to_id);
            $projects = isAdminOrHasAllDataAccess($belongs_to, $belongs_to_id) ? $this->workspace->projects() : $userOrClient->projects();
        } else {
            $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects() : $this->user->projects();
        }
        if (!empty($user_ids)) {
            $projects = $projects->whereHas('users', function ($query) use ($user_ids) {
                $query->whereIn('users.id', $user_ids);
            });
        }

        if (!empty($client_ids)) {
            $projects = $projects->whereHas('clients', function ($query) use ($client_ids) {
                $query->whereIn('clients.id', $client_ids);
            });
        }
        if (!empty($status_ids)) {
            $projects->whereIn('status_id', $status_ids);
        }
        if (!empty($priority_ids)) {
            $projects->whereIn('priority_id', $priority_ids);
        }
        if (!empty($tag_ids)) {
            $projects->whereHas('tags', function ($query) use ($tag_ids) {
                $query->whereIn('tags.id', $tag_ids);
            });
        }
        if ($start_date_from && $start_date_to) {
            $projects->whereBetween('start_date', [$start_date_from, $start_date_to]);
        }
        if ($end_date_from && $end_date_to) {
            $projects->whereBetween('end_date', [$end_date_from, $end_date_to]);
        }
        $projects->when($search, function ($query) use ($search) {
            $query->where('title', 'like', '%' . $search . '%')
                ->orWhere('id', 'like', '%' . $search . '%');
        });
        $projects->where($where);
        $totalprojects = $projects->count();
        $canCreate = checkPermission('create_projects');
        $canEdit = checkPermission('edit_projects');
        $canDelete = checkPermission('delete_projects');
        $statuses = Status::all();
        $priorities = Priority::all();
        $isHome = $request->query('from_home') == '1';
        $webGuard = Auth::guard('web')->check();
        $projects = $projects->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(
                function ($project) use ($statuses, $priorities, $canEdit, $canDelete, $canCreate, $isHome, $webGuard) {
                    $statusOptions = '';
                    foreach ($statuses as $status) {
                        // Determine if the option should be disabled
                        $disabled = canSetStatus($status)  ? '' : 'disabled';

                        // Render the option with appropriate attributes
                        $selected = $project->status_id == $status->id ? 'selected' : '';
                        $statusOptions .= "<option value='{$status->id}' class='badge bg-label-$status->color' $selected $disabled>$status->title</option>";
                    }

                    $priorityOptions = "";
                    foreach ($priorities as $priority) {
                        $selected = $project->priority_id == $priority->id ? 'selected' : '';
                        $priorityOptions .= "<option value='{$priority->id}' class='badge bg-label-$priority->color' $selected>$priority->title</option>";
                    }



                    $actions = '';

                    if ($canEdit) {
                        $actions .= '<a href="javascript:void(0);" class="edit-project" data-id="' . $project->id . '" title="' . get_label('update', 'Update') . '">' .
                            '<i class="bx bx-edit mx-1"></i>' .
                            '</a>';
                    }

                    if ($canDelete) {
                        $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $project->id . '" data-type="projects" data-table="projects_table" data-reload="' . ($isHome ? 'true' : '') . '">' .
                            '<i class="bx bx-trash text-danger mx-1"></i>' .
                            '</button>';
                    }

                    if ($canCreate) {
                        $actions .= '<a href="javascript:void(0);" class="duplicate" data-id="' . $project->id . '" data-title="' . $project->title . '" data-type="projects" data-table="projects_table" data-reload="' . ($isHome ? 'true' : '') . '" title="' . get_label('duplicate', 'Duplicate') . '">' .
                            '<i class="bx bx-copy text-warning mx-2"></i>' .
                            '</a>';
                    }

                    $actions .= '<a href="javascript:void(0);" class="quick-view" data-id="' . $project->id . '" data-type="project" title="' . get_label('quick_view', 'Quick View') . '">' .
                        '<i class="bx bx-info-circle mx-3"></i>' .
                        '</a>';


                    $actions = $actions ?: '-';

                    $userHtml = '';
                    if (!empty($project->users) && count($project->users) > 0) {
                        $userHtml .= '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
                        foreach ($project->users as $user) {
                            $userHtml .= "<li class='avatar avatar-sm pull-up'><a href='" . url("/users/profile/{$user->id}") . "' target='_blank' title='{$user->first_name} {$user->last_name}'><img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                        }
                        if ($canEdit) {
                            $userHtml .= '<li title=' . get_label('update', 'Update') . '><a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients" data-id="' . $project->id . '"><span class="bx bx-edit"></span></a></li>';
                        }
                        $userHtml .= '</ul>';
                    } else {
                        $userHtml = '<span class="badge bg-primary">' . get_label('not_assigned', 'Not Assigned') . '</span>';
                        if ($canEdit) {
                            $userHtml .= '<a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients" data-id="' . $project->id . '">' .
                                '<span class="bx bx-edit"></span>' .
                                '</a>';
                        }
                    }

                    $clientHtml = '';
                    if (!empty($project->clients) && count($project->clients) > 0) {
                        $clientHtml .= '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
                        foreach ($project->clients as $client) {
                            $clientHtml .= "<li class='avatar avatar-sm pull-up'><a href='" . url("/clients/profile/{$client->id}") . "' target='_blank' title='{$client->first_name} {$client->last_name}'><img src='" . ($client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                        }
                        if ($canEdit) {
                            $clientHtml .= '<li title=' . get_label('update', 'Update') . '><a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients" data-id="' . $project->id . '"><span class="bx bx-edit"></span></a></li>';
                        }
                        $clientHtml .= '</ul>';
                    } else {
                        $clientHtml = '<span class="badge bg-primary">' . get_label('not_assigned', 'Not Assigned') . '</span>';
                        if ($canEdit) {
                            $clientHtml .= '<a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients" data-id="' . $project->id . '">' .
                                '<span class="bx bx-edit"></span>' .
                                '</a>';
                        }
                    }

                    $tagHtml = '';
                    foreach ($project->tags as $tag) {
                        $tagHtml .= "<span class='badge bg-label-{$tag->color}'>{$tag->title}</span> ";
                    }

                    return [
                        'id' => $project->id,
                        'title' => "<a href='" . url("/projects/information/{$project->id}") . "' target='_blank'><strong>{$project->title}</strong></a> 
                        <a href='javascript:void(0);' class='mx-2'>
                            <i class='bx " . ($project->is_favorite ? 'bxs' : 'bx') . "-star favorite-icon text-warning' data-favorite='{$project->is_favorite}' data-id='{$project->id}' title='" . ($project->is_favorite ? get_label('remove_favorite', 'Click to remove from favorite') : get_label('add_favorite', 'Click to mark as favorite')) . "'></i>
                        </a>" . ($webGuard ?
                            "<a href='" . url('/chat?type=project&id=' . $project->id) . "' target='_blank'>
                                <i class='bx bx-message-rounded-dots text-danger' data-bs-toggle='tooltip' data-bs-placement='right' title='" . get_label('discussion', 'Discussion') . "'></i>
                            </a>"
                            : ""),
                        'users' => $userHtml,
                        'clients' => $clientHtml,
                        'start_date' => format_date($project->start_date),
                        'end_date' => format_date($project->end_date),
                        'budget' => !empty($project->budget) && $project->budget !== null ? format_currency($project->budget) : '-',
                        'status_id' => "<div class='d-flex align-items-center'>
                            <select class='form-select form-select-sm select-bg-label-{$project->status->color} fixed-width-select' id='statusSelect' data-id='{$project->id}' data-original-status-id='{$project->status->id}' data-original-color-class='select-bg-label-{$project->status->color}'" . ($isHome ? ' data-reload="true"' : '') . ">
                                {$statusOptions}
                            </select>
                            " . ($project->note ? 
                                "<i class='bx bx-notepad ms-2 text-primary' title='{$project->note}'></i>"
                                : "") . "
                        </div>",
                        'priority_id' => "<select class='form-select form-select-sm select-bg-label-" . ($project->priority ? $project->priority->color : 'secondary') . "' id='prioritySelect' data-id='{$project->id}' data-original-priority-id='" . ($project->priority ? $project->priority->id : '') . "' data-original-color-class='select-bg-label-" . ($project->priority ? $project->priority->color : 'secondary') . "'>{$priorityOptions}</select>",
                        'task_accessibility' => get_label($project->task_accessibility, ucwords(str_replace("_", " ", $project->task_accessibility))),
                        'tags' => $tagHtml ?: ' - ',
                        'created_at' => format_date($project->created_at, true),
                        'updated_at' => format_date($project->updated_at, true),
                        'actions' => $actions
                    ];
                }
            );

        return response()->json([
            "rows" => $projects->items(),
            "total" => $totalprojects,
        ]);
    }


    /**
     * List or search projects.
     * 
     * This endpoint retrieves a list of projects based on various filters. The user must be authenticated to perform this action. The request allows filtering by status, user, client, date ranges, and other parameters.
     * 
     * @authenticated
     * 
     * @group Project Management
     *
     * @urlParam id int optional The ID of the project to retrieve. Example: 1
     * 
     * @queryParam search string optional The search term to filter projects by title or id. Example: Project
     * @queryParam sort string optional The field to sort by. Defaults to "id". Sortable fields include: id, title, status, priority, start_date, end_date, budget, created_at, and updated_at. Example: title
     * @queryParam order string optional The sort order, either "ASC" or "DESC". Defaults to "DESC". Example: ASC
     * @queryParam status int optional The status ID to filter projects by. Example: 2
     * @queryParam user_id int optional The user ID to filter projects by. Example: 1
     * @queryParam client_id int optional The client ID to filter projects by. Example: 5
     * @queryParam project_start_date_from string optional The start date range's start in YYYY-MM-DD format. Example: 2024-01-01
     * @queryParam project_start_date_to string optional The start date range's end in YYYY-MM-DD format. Example: 2024-12-31
     * @queryParam project_end_date_from string optional The end date range's start in YYYY-MM-DD format. Example: 2024-01-01
     * @queryParam project_end_date_to string optional The end date range's end in YYYY-MM-DD format. Example: 2024-12-31
     * @queryParam is_favorites boolean optional Filter projects marked as favorites. Example: true
     * @queryParam limit int optional The number of projects per page for pagination. Example: 10
     * @queryParam offset int optional The offset for pagination, indicating the starting point of results. Example: 0
     * 
     * @response 200 {
     *   "error": false,
     *   "message": "Projects retrieved successfully",
     *   "total": 1,
     *   "data": [
     *     {
     *       "id": 351,
     *       "title": "rwer",
     *       "status": "Rel test",
     *       "priority": "Default",
     *       "users": [
     *         {
     *           "id": 7,
     *           "first_name": "Madhavan",
     *           "last_name": "Vaidya",
     *           "photo": "https://test-taskify.infinitietech.com/storage/photos/yxNYBlFLALdLomrL0JzUY2USPLILL9Ocr16j4n2o.png"
     *         },
     *         {
     *           "id": 183,
     *           "first_name": "Girish",
     *           "last_name": "Thacker",
     *           "photo": "https://test-taskify.infinitietech.com/storage/photos/no-image.jpg"
     *         }
     *       ],
     *       "clients": [],
     *       "tags": [],
     *       "start_date": "14-06-2024",
     *       "end_date": "14-06-2024",
     *       "budget": "",
     *       "created_at": "14-06-2024 17:50:09",
     *       "updated_at": "17-06-2024 19:08:16"
     *     }
     *   ]
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
     *   "message": "Projects not found",
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
        $user_id = $request->input('user_id', '');
        $client_id = $request->input('client_id', '');
        $start_date_from = $request->input('project_start_date_from', '');
        $start_date_to = $request->input('project_start_date_to', '');
        $end_date_from = $request->input('project_end_date_from', '');
        $end_date_to = $request->input('project_end_date_to', '');
        $is_favorites = $request->input('is_favorites', '');
        $limit = $request->input('limit', 10); // default limit
        $offset = $request->input('offset', 0); // default offset

        $where = [];
        if ($status != '') {
            $where['status_id'] = $status;
        }

        if ($is_favorites) {
            $where['is_favorite'] = 1;
        }

        if ($id) {
            $project = Project::find($id);
            if (!$project) {
                return formatApiResponse(
                    false,
                    'Project not found',
                    [
                        'total' => 0,
                        'data' => []
                    ]
                );
            } else {
                return formatApiResponse(
                    false,
                    'Project retrieved successfully',
                    [
                        'total' => 1,
                        'data' => [formatProject($project)]
                    ]
                );
            }
        } else {
            $projectsQuery = isAdminOrHasAllDataAccess() ? $this->workspace->projects() : $this->user->projects();
            if ($user_id) {
                $user = User::find($user_id);
                if (!$user) {
                    return formatApiResponse(
                        false,
                        'User not found',
                        [
                            'total' => 0,
                            'data' => []
                        ]
                    );
                }
                $projectsQuery = $user->projects();
            }
            if ($client_id) {
                $client = Client::find($client_id);
                if (!$client) {
                    return formatApiResponse(
                        false,
                        'Client not found',
                        [
                            'total' => 0,
                            'data' => []
                        ]
                    );
                }
                $projectsQuery = $client->projects();
            }
            if ($start_date_from && $start_date_to) {
                $projectsQuery->whereBetween('start_date', [$start_date_from, $start_date_to]);
            }
            if ($end_date_from && $end_date_to) {
                $projectsQuery->whereBetween('end_date', [$end_date_from, $end_date_to]);
            }
            $projectsQuery->when($search, function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
            $projectsQuery->where($where);
            $total = $projectsQuery->count(); // get total count before applying offset and limit

            $projects = $projectsQuery->orderBy($sort, $order)
                ->skip($offset)
                ->take($limit)
                ->get();

            if ($projects->isEmpty()) {
                return formatApiResponse(
                    false,
                    'Projects not found',
                    [
                        'total' => 0,
                        'data' => []
                    ]
                );
            }
            $data = $projects->map(function ($project) {
                return formatProject($project);
            });

            return formatApiResponse(
                false,
                'Projects retrieved successfully',
                [
                    'total' => $total,
                    'data' => $data
                ]
            );
        }
    }

    /**
     * Update the favorite status of a project.
     * 
     * This endpoint updates whether a project is marked as a favorite or not. The user must be authenticated to perform this action.
     * 
     * @authenticated
     * 
     * @group Project Management
     *
     * @urlParam id int required The ID of the project to update.
     * @bodyParam is_favorite int required Indicates whether the project is a favorite. Use 1 for true and 0 for false.
     *
     * @response 200 {
     * "error": false,
     * "message": "Project favorite status updated successfully",
     * "data": {
     * "id": 438,
     * "title": "Res Test",
     * "status": "Default",
     * "priority": "dsfdsf",
     * "users": [
     * {
     * "id": 7,
     * "first_name": "Madhavan",
     * "last_name": "Vaidya",
     * "photo": "https://test-taskify.infinitietech.com/storage/photos/yxNYBlFLALdLomrL0JzUY2USPLILL9Ocr16j4n2o.png"
     * }
     * ],
     * "clients": [
     * {
     * "id": 103,
     * "first_name": "Test",
     * "last_name": "Test",
     * "photo": "https://test-taskify.infinitietech.com/storage/photos/no-image.jpg"
     * }
     * ],
     * "tags": [
     * {
     * "id": 45,
     * "title": "Tag from update project"
     * }
     * ],
     * "start_date": null,
     * "end_date": null,
     * "budget": "1000.00",
     * "task_accessibility": "assigned_users",
     * "description": null,
     * "note": null,
     * "favorite": 1,
     * "created_at": "07-08-2024 14:38:51",
     * "updated_at": "12-08-2024 13:36:10"
     * }
     * }
     *
     * @response 422 {
     *   "error": true,
     *   "message": "Validation errors occurred",
     *   "errors": {
     *     "is_favorite": [
     *       "The is favorite field must be either 0 or 1."
     *     ]
     *   }
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "Project not found",
     *   "data": []
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred while updating the favorite status."
     * }
     */
    public function update_favorite(Request $request, $id)
    {
        $isApi = request()->get('isApi', false);
        try {
            // Validate the request data
            $request->validate([
                'is_favorite' => 'required|integer|in:0,1',
            ]);

            $project = Project::find($id);

            if (!$project) {
                return formatApiResponse(
                    true,
                    'Project not found',
                    []
                );
            }

            $isFavorite = $request->input('is_favorite');

            // Update the project's favorite status
            $project->is_favorite = $isFavorite;
            $project->save();
            return formatApiResponse(
                false,
                'Project favorite status updated successfully',
                ['data' => formatProject($project)]
            );
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while updating the project favorite status.'
            ], 500);
        }
    }

    public function duplicate($id)
    {
        // Define the related tables for this meeting
        $relatedTables = ['users', 'clients', 'tasks', 'tags']; // Include related tables as needed

        // Use the general duplicateRecord function
        $title = (request()->has('title') && !empty(trim(request()->title))) ? request()->title : '';
        $duplicate = duplicateRecord(Project::class, $id, $relatedTables, $title);

        if (!$duplicate) {
            return response()->json(['error' => true, 'message' => 'Project duplication failed.']);
        }

        if (request()->has('reload') && request()->input('reload') === 'true') {
            Session::flash('message', 'Project duplicated successfully.');
        }
        return response()->json(['error' => false, 'message' => 'Project duplicated successfully.', 'id' => $id]);
    }

    public function upload_media(Request $request)
    {
        $maxFileSizeBytes = config('media-library.max_file_size');
        $maxFileSizeKb = $maxFileSizeBytes / 1024;

        // Round to an integer (Laravel validation rules expect integer values)
        $maxFileSizeKb = (int)$maxFileSizeKb;
        try {
            $validatedData = $request->validate([
                'id' => 'integer|exists:projects,id',
                'media_files.*' => 'file|max:' . $maxFileSizeKb
            ]);

            $mediaIds = [];

            if ($request->hasFile('media_files')) {
                $project = Project::find($validatedData['id']);
                $mediaFiles = $request->file('media_files');

                foreach ($mediaFiles as $mediaFile) {
                    $mediaItem = $project->addMedia($mediaFile)
                        ->sanitizingFileName(function ($fileName) use ($project) {
                            // Replace special characters and spaces with hyphens
                            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));

                            // Generate a unique identifier based on timestamp and random component
                            $uniqueId = time() . '_' . mt_rand(1000, 9999);

                            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                            return "{$baseName}-{$uniqueId}.{$extension}";
                        })
                        ->toMediaCollection('project-media');

                    $mediaIds[] = $mediaItem->id;
                }


                return response()->json(['error' => false, 'message' => 'File(s) uploaded successfully.', 'id' => $mediaIds, 'type' => 'media', 'parent_type' => 'project', 'parent_id' => $project->id]);
            } else {
                return response()->json(['error' => true, 'message' => 'No file(s) chosen.']);
            }
        } catch (Exception $e) {
            // Handle the exception as needed
            return response()->json(['error' => true, 'message' => 'An error occurred during file upload: ' . $e->getMessage()]);
        }
    }





    public function get_media($id)
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $project = Project::findOrFail($id);
        $media = $project->getMedia('project-media');

        if ($search) {
            $media = $media->filter(function ($mediaItem) use ($search) {
                return (
                    // Check if ID contains the search query
                    stripos($mediaItem->id, $search) !== false ||
                    // Check if file name contains the search query
                    stripos($mediaItem->file_name, $search) !== false ||
                    // Check if date created contains the search query
                    stripos($mediaItem->created_at->format('Y-m-d'), $search) !== false
                );
            });
        }
        $canDelete = checkPermission('delete_media');
        $formattedMedia = $media->map(function ($mediaItem) use ($canDelete) {
            // Check if the disk is public
            $isPublicDisk = $mediaItem->disk == 'public' ? 1 : 0;

            // Generate file URL based on disk visibility
            $fileUrl = $isPublicDisk
                ? asset('storage/project-media/' . $mediaItem->file_name)
                : $mediaItem->getFullUrl();

            $fileExtension = pathinfo($fileUrl, PATHINFO_EXTENSION);

            // Check if file extension corresponds to an image type
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            $isImage = in_array(strtolower($fileExtension), $imageExtensions);

            if ($isImage) {
                $html = '<a href="' . $fileUrl . '" data-lightbox="project-media">';
                $html .= '<img src="' . $fileUrl . '" alt="' . $mediaItem->file_name . '" width="50">';
                $html .= '</a>';
            } else {
                $html = '<a href="' . $fileUrl . '" title=' . get_label('download', 'Download') . '>' . $mediaItem->file_name . '</a>';
            }

            $actions = '';

            $actions .= '<a href="' . $fileUrl . '" title="' . get_label('download', 'Download') . '" download>' .
                '<i class="bx bx-download bx-sm"></i>' .
                '</a>';

            if ($canDelete) {
                $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $mediaItem->id . '" data-type="project-media" data-table="project_media_table">' .
                    '<i class="bx bx-trash text-danger"></i>' .
                    '</button>';
            }

            $actions = $actions ?: '-';

            return [
                'id' => $mediaItem->id,
                'file' => $html,
                'file_name' => $mediaItem->file_name,
                'file_size' => formatSize($mediaItem->size),
                'created_at' => format_date($mediaItem->created_at, true),
                'updated_at' => format_date($mediaItem->updated_at, true),
                'actions' => $actions,
            ];
        });


        if ($order == 'asc') {
            $formattedMedia = $formattedMedia->sortBy($sort);
        } else {
            $formattedMedia = $formattedMedia->sortByDesc($sort);
        }

        return response()->json([
            'rows' => $formattedMedia->values()->toArray(),
            'total' => $formattedMedia->count(),
        ]);
    }

    public function delete_media($mediaId)
    {
        $mediaItem = Media::find($mediaId);

        if (!$mediaItem) {
            // Handle case where media item is not found
            return response()->json(['error' => true, 'message' => 'File not found.']);
        }

        // Delete media item from the database and disk
        $mediaItem->delete();

        return response()->json(['error' => false, 'message' => 'File deleted successfully.', 'id' => $mediaId, 'title' => $mediaItem->file_name, 'parent_id' => $mediaItem->model_id,  'type' => 'media', 'parent_type' => 'project']);
    }

    public function delete_multiple_media(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:media,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        $parentIds = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $media = Media::find($id);
            if ($media) {
                $deletedIds[] = $id;
                $deletedTitles[] = $media->file_name;
                $parentIds[] = $media->model_id;
                $media->delete();
            }
        }

        return response()->json(['error' => false, 'message' => 'Files(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles, 'parent_id' => $parentIds, 'type' => 'media', 'parent_type' => 'project']);
    }

    public function store_milestone(Request $request)
    {
        $formFields = $request->validate([
            'project_id' => ['required'],
            'title' => ['required'],
            'status' => ['required'],
            'start_date' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $endDate = request()->input('end_date');
                    $errors = validate_date_format_and_order($value, $endDate);

                    if (!empty($errors['start_date'])) {
                        foreach ($errors['start_date'] as $error) {
                            $fail($error);
                        }
                    }
                },
            ],
            'end_date' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $startDate = request()->input('start_date');
                    $errors = validate_date_format_and_order($startDate, $value);

                    if (!empty($errors['end_date'])) {
                        foreach ($errors['end_date'] as $error) {
                            $fail($error);
                        }
                    }
                },
            ],
            'cost' => [
                'required',
                function ($attribute, $value, $fail) {
                    $error = validate_currency_format($value, 'cost');
                    if ($error) {
                        $fail($error);
                    }
                }
            ],
            'description' => ['nullable'],
        ]);

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        if ($start_date) {
            $formFields['start_date'] = format_date($start_date, false, app('php_date_format'), 'Y-m-d');
        }
        if ($end_date) {
            $formFields['end_date'] = format_date($end_date, false, app('php_date_format'), 'Y-m-d');
        }
        $formFields['cost'] = str_replace(',', '', $request->input('cost'));
        $formFields['workspace_id'] = $this->workspace->id;
        $formFields['created_by'] = isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id;


        $milestone = Milestone::create($formFields);

        return response()->json(['error' => false, 'message' => 'Milestone created successfully.', 'id' => $milestone->id, 'type' => 'milestone', 'parent_type' => 'project', 'parent_id' => $milestone->project_id]);
    }

    public function get_milestones($id)
    {
        $project = Project::findOrFail($id);
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $statuses = request('statuses');
        $start_date_from = (request('start_date_from')) ? request('start_date_from') : "";
        $start_date_to = (request('start_date_to')) ? request('start_date_to') : "";
        $end_date_from = (request('end_date_from')) ? request('end_date_from') : "";
        $end_date_to = (request('end_date_to')) ? request('end_date_to') : "";
        $milestones =  $project->milestones();
        if ($search) {
            $milestones = $milestones->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%')
                    ->orWhere('cost', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        if ($start_date_from && $start_date_to) {
            $milestones = $milestones->whereBetween('start_date', [$start_date_from, $start_date_to]);
        }
        if ($end_date_from && $end_date_to) {
            $milestones  = $milestones->whereBetween('to_date', [$end_date_from, $end_date_to]);
        }
        if ($statuses) {
            $milestones  = $milestones->whereIn('status', $statuses);
        }
        $total = $milestones->count();

        $canEdit = checkPermission('edit_milestones');
        $canDelete = checkPermission('delete_milestones');

        $milestones = $milestones->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($milestone) use ($canEdit, $canDelete) {
                if (strpos($milestone->created_by, 'u_') === 0) {
                    // The ID corresponds to a user
                    $creator = User::find(substr($milestone->created_by, 2)); // Remove the 'u_' prefix
                } elseif (strpos($milestone->created_by, 'c_') === 0) {
                    // The ID corresponds to a client
                    $creator = Client::find(substr($milestone->created_by, 2)); // Remove the 'c_' prefix                    
                }
                if ($creator !== null) {
                    $creator = $creator->first_name . ' ' . $creator->last_name;
                } else {
                    $creator = '-';
                }

                $statusBadge = '';

                if ($milestone->status == 'incomplete') {
                    $statusBadge = '<span class="badge bg-danger">' . get_label('incomplete', 'Incomplete') . '</span>';
                } elseif ($milestone->status == 'complete') {
                    $statusBadge = '<span class="badge bg-success">' . get_label('complete', 'Complete') . '</span>';
                }
                $progress = '<div class="demo-vertical-spacing">
                <div class="progress">
                  <div class="progress-bar" role="progressbar" style="width: ' . $milestone->progress . '%" aria-valuenow="' . $milestone->progress . '" aria-valuemin="0" aria-valuemax="100">
                    
                  </div>
                </div>
              </div> <h6 class="mt-2">' . $milestone->progress . '%</h6>';


                $actions = '';

                if ($canEdit) {
                    $actions .= '<a href="javascript:void(0);" class="edit-milestone" data-bs-toggle="modal" data-bs-target="#edit_milestone_modal" data-id="' . $milestone->id . '" title="' . get_label('update', 'Update') . '">' .
                        '<i class="bx bx-edit mx-1"></i>' .
                        '</a>';
                }

                if ($canDelete) {
                    $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $milestone->id . '" data-type="milestone" data-table="project_milestones_table">' .
                        '<i class="bx bx-trash text-danger mx-1"></i>' .
                        '</button>';
                }
                $actions = $actions ?: '-';

                return [
                    'id' => $milestone->id,
                    'title' => $milestone->title,
                    'status' => $statusBadge,
                    'progress' => $progress,
                    'cost' => format_currency($milestone->cost),
                    'start_date' => format_date($milestone->start_date),
                    'end_date' => format_date($milestone->end_date),
                    'created_by' => $creator,
                    'description' => $milestone->description,
                    'created_at' => format_date($milestone->created_at, true),
                    'updated_at' => format_date($milestone->updated_at, true),
                    'actions' => $actions
                ];
            });



        return response()->json([
            "rows" => $milestones->items(),
            "total" => $total,
        ]);
    }

    public function get_milestone($id)
    {
        $ms = Milestone::findOrFail($id);
        $ms->cost = format_currency($ms->cost, false, false);
        return response()->json(['ms' => $ms]);
    }

    public function update_milestone(Request $request)
    {
        $request->validate([
            'title' => ['required'],
            'status' => ['required'],
            'start_date' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $endDate = request()->input('end_date');
                    $errors = validate_date_format_and_order($value, $endDate);

                    if (!empty($errors['start_date'])) {
                        foreach ($errors['start_date'] as $error) {
                            $fail($error);
                        }
                    }
                },
            ],
            'end_date' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $startDate = request()->input('start_date');
                    $errors = validate_date_format_and_order($startDate, $value);

                    if (!empty($errors['end_date'])) {
                        foreach ($errors['end_date'] as $error) {
                            $fail($error);
                        }
                    }
                },
            ],
            'cost' => [
                'required',
                function ($attribute, $value, $fail) {
                    $error = validate_currency_format($value, 'cost');
                    if ($error) {
                        $fail($error);
                    }
                }
            ],
            'progress' => ['required'],
            'description' => ['nullable'],
        ]);

        $formFieldsToUpdate = [
            'title' => $request->input('title'),
            'status' => $request->input('status'),
            'cost' => str_replace(',', '', $request->input('cost')),
            'progress' => $request->input('progress'),
            'description' => $request->input('description')
        ];


        // Handle start_date
        if ($request->filled('start_date')) {
            $formFieldsToUpdate['start_date'] = format_date($request->input('start_date'), false, app('php_date_format'), 'Y-m-d');
        } else {
            $formFieldsToUpdate['start_date'] = null;
        }

        // Handle end_date
        if ($request->filled('end_date')) {
            $formFieldsToUpdate['end_date'] = format_date($request->input('end_date'), false, app('php_date_format'), 'Y-m-d');
        } else {
            $formFieldsToUpdate['end_date'] = null;
        }

        $ms = Milestone::findOrFail($request->id);

        if ($ms->update($formFieldsToUpdate)) {
            return response()->json(['error' => false, 'message' => 'Milestone updated successfully.', 'id' => $ms->id, 'type' => 'milestone', 'parent_type' => 'project', 'parent_id' => $ms->project_id]);
        } else {
            return response()->json(['error' => true, 'message' => 'Milestone couldn\'t updated.']);
        }
    }
    public function delete_milestone($id)
    {
        $ms = Milestone::findOrFail($id);
        DeletionService::delete(Milestone::class, $id, 'Milestone');
        return response()->json(['error' => false, 'message' => 'Milestone deleted successfully.', 'id' => $id, 'title' => $ms->title, 'type' => 'milestone', 'parent_type' => 'project', 'parent_id' => $ms->project_id]);
    }
    public function delete_multiple_milestone(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:milestones,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        $parentIds = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $ms = Milestone::findOrFail($id);
            $deletedIds[] = $id;
            $deletedTitles[] = $ms->title;
            $parentIds[] = $ms->project_id;
            DeletionService::delete(Milestone::class, $id, 'Milestone');
        }

        return response()->json(['error' => false, 'message' => 'Milestone(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles, 'type' => 'milestone', 'parent_type' => 'project', 'parent_id' => $parentIds]);
    }

    /**
     * Update the status of a project.
     * 
     * This endpoint updates the status of a specified project. The user must be authenticated and have permission to set the new status. A notification will be sent to all users and clients associated with the project.
     * 
     * @authenticated
     * 
     * @group Project Management
     *
     * @urlParam id int required The ID of the project whose status is to be updated.
     * @bodyParam statusId int required The ID of the new status to set for the project.
     * @bodyParam note string optional An optional note to attach to the project update.
     *
     * @response 200 {
     * "error": false,
     * "message": "Status updated successfully.",
     * "id": "438",
     * "type": "project",
     * "activity_message": "Madhavan Vaidya updated project status from Default to vbnvbnvbn",
     * "data": {
     * "id": 438,
     * "title": "Res Test",
     * "status": "vbnvbnvbn",
     * "priority": "dsfdsf",
     * "users": [
     * {
     * "id": 7,
     * "first_name": "Madhavan",
     * "last_name": "Vaidya",
     * "photo": "https://test-taskify.infinitietech.com/storage/photos/yxNYBlFLALdLomrL0JzUY2USPLILL9Ocr16j4n2o.png"
     * }
     * ],
     * "clients": [
     * {
     * "id": 103,
     * "first_name": "Test",
     * "last_name": "Test",
     * "photo": "https://test-taskify.infinitietech.com/storage/photos/no-image.jpg"
     * }
     * ],
     * "tags": [
     * {
     * "id": 45,
     * "title": "Tag from update project"
     * }
     * ],
     * "start_date": null,
     * "end_date": null,
     * "budget": "1000.00",
     * "task_accessibility": "assigned_users",
     * "description": null,
     * "note": null,
     * "favorite": 1,
     * "created_at": "07-08-2024 14:38:51",
     * "updated_at": "12-08-2024 13:49:33"
     * }
     * }
     *
     * @response 422 {
     *   "error": true,
     *   "message": "Validation errors occurred",
     *   "errors": {
     *     "id": [
     *       "The selected id is invalid."
     *     ],
     *     "statusId": [
     *       "The selected status id is invalid."
     *     ]
     *   }
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "You are not authorized to set this status."
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "Status couldn't be updated."
     * }
     */

    public function update_status(Request $request, $id = null)
    {
        $isApi = request()->get('isApi', false);
        if ($id) {
            $request->merge(['id' => $id]);
        }

        $rules = [
            'id' => 'required|exists:projects,id',
            'statusId' => 'required|exists:statuses,id'
        ];

        try {
            $request->validate($rules);

            $id = $request->id;
            $statusId = $request->statusId;

            $status = Status::findOrFail($statusId);
            if (canSetStatus($status)) {
                $project = Project::findOrFail($id);
                if ($project->status->id != $statusId) {
                    $currentStatus = $project->status->title;
                    $project->status_id = $statusId;
                    $project->note = $request->note;
                    if ($project->save()) {
                        // Reload the project to get updated status information
                        $project = $project->fresh();
                        $newStatus = $project->status->title;

                        $notification_data = [
                            'type' => 'project_status_updation',
                            'type_id' => $id,
                            'type_title' => $project->title,
                            'updater_first_name' => $this->user->first_name,
                            'updater_last_name' => $this->user->last_name,
                            'old_status' => $currentStatus,
                            'new_status' => $newStatus,
                            'access_url' => 'projects/information/' . $id,
                            'action' => 'status_updated'
                        ];
                        $userIds = $project->users->pluck('id')->toArray();
                        $clientIds = $project->clients->pluck('id')->toArray();
                        $recipients = array_merge(
                            array_map(function ($userId) {
                                return 'u_' . $userId;
                            }, $userIds),
                            array_map(function ($clientId) {
                                return 'c_' . $clientId;
                            }, $clientIds)
                        );
                        processNotifications($notification_data, $recipients);
                        return formatApiResponse(
                            false,
                            'Status updated successfully.',
                            [
                                'id' => $id,
                                'type' => 'project',
                                'activity_message' => trim($this->user->first_name) . ' ' . trim($this->user->last_name) . ' updated project status from ' . trim($currentStatus) . ' to ' . trim($newStatus),
                                'data' => formatProject($project)
                            ]
                        );
                    } else {
                        return response()->json(['error' => true, 'message' => 'Status couldn\'t be updated.']);
                    }
                } else {
                    return response()->json(['error' => true, 'message' => 'No status change detected.']);
                }
            } else {
                return response()->json(['error' => true, 'message' => 'You are not authorized to set this status.']);
            }
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'error' => true,
                'message' => 'Status couldn\'t be updated.'
            ], 500);
        }
    }


    /**
     * Update the priority of a project.
     * 
     * This endpoint updates the priority of a specified project. The user must be authenticated and have permission to set the new priority.
     * 
     * @authenticated
     * 
     * @group Project Management
     *
     * @urlParam id int required The ID of the project whose priority is to be updated.
     * @bodyParam priorityId int required The ID of the new priority to set for the project.
     *
     * @response 200 {
     * "error": false,
     * "message": "Priority updated successfully.",
     * "id": "438",
     * "type": "project",
     * "activity_message": "Madhavan Vaidya updated project priority from Low to Medium",
     * "data": {
     * "id": 438,
     * "title": "Res Test",
     * "status": "Test From Pro",
     * "priority": "Medium",
     * "users": [
     * {
     * "id": 7,
     * "first_name": "Madhavan",
     * "last_name": "Vaidya",
     * "photo": "https://test-taskify.infinitietech.com/storage/photos/yxNYBlFLALdLomrL0JzUY2USPLILL9Ocr16j4n2o.png"
     * }
     * ],
     * "clients": [
     * {
     * "id": 103,
     * "first_name": "Test",
     * "last_name": "Test",
     * "photo": "https://test-taskify.infinitietech.com/storage/photos/no-image.jpg"
     * }
     * ],
     * "tags": [
     * {
     * "id": 45,
     * "title": "Tag from update project"
     * }
     * ],
     * "start_date": null,
     * "end_date": null,
     * "budget": "1000.00",
     * "task_accessibility": "assigned_users",
     * "description": null,
     * "note": null,
     * "favorite": 1,
     * "created_at": "07-08-2024 14:38:51",
     * "updated_at": "12-08-2024 13:58:55"
     * }
     * }
     *
     * @response 422 {
     *   "error": true,
     *   "message": "Validation errors occurred",
     *   "errors": {
     *     "id": [
     *       "The selected id is invalid."
     *     ],
     *     "priorityId": [
     *       "The selected priority id is invalid."
     *     ]
     *   }
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "Priority couldn't be updated."
     * }
     */

    public function update_priority(Request $request, $id = null)
    {
        $isApi = request()->get('isApi', false);
        if ($id) {
            $request->merge(['id' => $id]);
        }

        $rules = [
            'id' => 'required|exists:projects,id',
            'priorityId' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value != 0 && !\DB::table('priorities')->where('id', $value)->exists()) {
                        $fail('The selected ' . $attribute . ' is invalid.');
                    }
                },
            ],
        ];

        try {
            $request->validate($rules);

            $id = $request->id;
            $priorityId = $request->priorityId;

            $project = Project::findOrFail($id);
            if ($project->priority_id != $priorityId) {
                $currentPriority = $project->priority ? $project->priority->title : 'Default';
                $project->priority_id = $priorityId;
                if ($project->save()) {
                    // Reload the project to get updated priority information
                    $project = $project->fresh();
                    $newPriority = $project->priority ? $project->priority->title : 'Default';
                    $message = trim($this->user->first_name) . ' ' . trim($this->user->last_name) . ' updated project priority from ' . trim($currentPriority) . ' to ' . trim($newPriority);
                    return formatApiResponse(
                        false,
                        'Priority updated successfully.',
                        [
                            'id' => $id,
                            'type' => 'project',
                            'activity_message' => $message,
                            'data' => formatProject($project)
                        ]
                    );
                } else {
                    return response()->json(['error' => true, 'message' => 'Priority couldn\'t be updated.']);
                }
            } else {
                return response()->json(['error' => true, 'message' => 'No priority change detected.']);
            }
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'error' => true,
                'message' => 'Priority couldn\'t be updated.'
            ], 500);
        }
    }


    public function saveViewPreference(Request $request)
    {
        $view = $request->input('view');
        $prefix = isClient() ? 'c_' : 'u_';
        if (UserClientPreference::updateOrCreate(
            ['user_id' => $prefix . $this->user->id, 'table_name' => 'projects'],
            ['default_view' => $view]
        )) {
            return response()->json(['error' => false, 'message' => 'Default View Set Successfully.']);
        } else {
            return response()->json(['error' => true, 'message' => 'Something Went Wrong.']);
        }
    }
}
