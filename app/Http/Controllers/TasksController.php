<?php

namespace App\Http\Controllers;

use PDO;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Status;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Workspace;
use App\Models\UserClientPreference;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Exception;
use Illuminate\Validation\ValidationException;

class TasksController extends Controller
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
    public function index($id = '')
    {
        $project = (object)[];
        if ($id) {
            $project = Project::findOrFail($id);
            $tasks = isAdminOrHasAllDataAccess() ? $project->tasks : $this->user->project_tasks($id);
        } else {
            $tasks = isAdminOrHasAllDataAccess() ? $this->workspace->tasks : $this->user->tasks();
        }
        $tasks = $tasks->count();
        return view('tasks.tasks', ['project' => $project, 'tasks' => $tasks]);
    }

    /**
     * Create a new task.
     *
     * This endpoint creates a new task with the provided details. The user must be authenticated to perform this action. The request validates various fields, including title, status, priority, start and due dates, project association, and optional notes.
     *
     * @authenticated
     *
     * @group Task Management
     *
     * @bodyParam title string required The title of the task. Example: New Task
     * @bodyParam status_id integer required The status of the task. Must exist in the `statuses` table. Example: 1
     * @bodyParam priority_id integer required The priority of the task. Must exist in the `priorities` table. Example: 2
     * @bodyParam start_date string|null optional The start date of the task in the format specified in the general settings. Example: 2024-07-20
     * @bodyParam due_date string|null optional The due date of the task in the format specified in the general settings. Example: 2024-08-20
     * @bodyParam description string nullable A description of the task. Example: This is a detailed description of the task.
     * @bodyParam project integer required The ID of the project associated with the task. Must exist in the `projects` table. Example: 10
     * @bodyParam note string nullable Additional notes about the task. Example: Urgent
     * @bodyParam user_id array nullable An array of user IDs to be assigned to the task. Example: [1, 2, 3]
     *
     * @response 200 {
     * "error": false,
     * "message": "Task created successfully.",
     * "id": 280,
     * "parent_id": "420",
     * "parent_type": "project",
     * "data": {
     *   "id": 280,
     *   "workspace_id": 6,
     *   "title": "Res Test",
     *   "status": "Default",
     *   "status_id": "0",
     *   "priority": "Default",
     *   "priority_id": "0",
     *   "users": [
     *     {
     *       "id": 7,
     *       "first_name": "Madhavan",
     *       "last_name": "Vaidya",
     *       "photo": "https://test-taskify.infinitietech.com/storage/photos/yxNYBlFLALdLomrL0JzUY2USPLILL9Ocr16j4n2o.png"
     *     }
     *   ],
     *   "user_id": [1,2],
     *   "clients": [
     *     {
     *       "id": 173,
     *       "first_name": "666",
     *       "last_name": "666",
     *       "photo": "https://test-taskify.infinitietech.com/storage/photos/no-image.jpg"
     *     }
     *   ],
     *   "start_date": "07-08-2024",
     *   "due_date": "07-08-2024",
     *   "project": {
     *     "id": 420,
     *     "title": "Updated Project Title"
     *   },
     *   "description": "Test Desc",
     *   "note": "Test Note",
     *   "created_at": "07-08-2024 13:02:52",
     *   "updated_at": "07-08-2024 13:02:52"
     * }
     *
     * }
     * @response 422 {
     *  "error": true,
     *  "message": "Validation errors occurred",
     *  "errors": {
     *    "title": ["The title field is required."],
     *    "status_id": ["The selected status_id is invalid."],
     *    ...
     *  }
     * }
     * @response 500 {
     *  "error": true,
     *  "message": "An error occurred while creating the task."
     * }
     */
    public function store(Request $request)
    {
        $isApi = request()->get('isApi', false);
        $rules = [
            'title' => 'required',
            'status_id' => 'required|exists:statuses,id',
            'priority_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value != 0 && !\DB::table('priorities')->where('id', $value)->exists()) {
                        $fail('The selected ' . $attribute . ' is invalid.');
                    }
                },
            ],
            'start_date' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $endDate = request()->input('due_date');
                    $errors = validate_date_format_and_order($value, $endDate);

                    if (!empty($errors['start_date'])) {
                        foreach ($errors['start_date'] as $error) {
                            $fail($error);
                        }
                    }
                },
            ],
            'due_date' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $startDate = request()->input('start_date');
                    $errors = validate_date_format_and_order($startDate, $value, endDateKey: 'due_date');

                    if (!empty($errors['due_date'])) {
                        foreach ($errors['due_date'] as $error) {
                            $fail($error);
                        }
                    }
                },
            ],
            'description' => 'nullable|string',
            'project' => 'required|exists:projects,id',
            'note' => 'nullable|string',
            'user_id' => 'nullable|array',
            'user_id.*' => 'exists:users,id', // Validate that each user_id exists in the users table
        ];

        $messages = [
            'status_id.required' => 'The status field is required.'
        ];
        try {
            $formFields = $request->validate($rules, $messages);
            $status = Status::findOrFail($request->input('status_id'));
            if (canSetStatus($status)) {
                $project_id = $request->input('project');
                $start_date = $request->input('start_date');
                $due_date = $request->input('due_date');
                if ($start_date) {
                    $formFields['start_date'] = format_date($start_date, false, app('php_date_format'), 'Y-m-d');
                }
                if ($due_date) {
                    $formFields['due_date'] = format_date($due_date, false, app('php_date_format'), 'Y-m-d');
                }

                $formFields['workspace_id'] = getWorkspaceId();
                $formFields['created_by'] = $this->user->id;

                $formFields['project_id'] = $project_id;
                $userIds = $request->input('user_id', []);
                unset($formFields['user_id']);
                $new_task = Task::create($formFields);
                $task_id = $new_task->id;
                $task = Task::find($task_id);
                $task->users()->attach($userIds);


                $notification_data = [
                    'type' => 'task',
                    'type_id' => $task_id,
                    'type_title' => $task->title,
                    'access_url' => 'tasks/information/' . $task->id,
                    'action' => 'assigned'
                ];
                // $clientIds = $project->clients()->pluck('clients.id')->toArray();
                // $recipients = array_merge(
                //     array_map(function ($userId) {
                //         return 'u_' . $userId;
                //     }, $userIds),
                //     array_map(function ($clientId) {
                //         return 'c_' . $clientId;
                //     }, $clientIds)
                // );
                $recipients = array_map(function ($userId) {
                    return 'u_' . $userId;
                }, $userIds);
                processNotifications($notification_data, $recipients);
                return formatApiResponse(
                    false,
                    'Task created successfully.',
                    [
                        'id' => $new_task->id,
                        'parent_id' => $project_id,
                        'parent_type' => 'project',
                        'data' => formatTask($task)
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
                'message' => 'An error occurred while creating the task.'
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
        $task = Task::findOrFail($id);
        return view('tasks.task_information', ['task' => $task, 'auth_user' => $this->user]);
    }

    public function get($id)
    {
        $task = Task::with('users')->findOrFail($id);
        $project = $task->project()->with('users')->firstOrFail();

        return response()->json(['error' => false, 'task' => $task, 'project' => $project]);
    }

    /**
     * Update an existing task.
     *
     * This endpoint updates the details of an existing task. The user must be authenticated to perform this action. The request validates various fields including title, status, priority, start and due dates, and optional notes. It also handles user assignments and notifies relevant parties of any status changes.
     *
     * @authenticated
     *
     * @group Task Management
     *
     * @bodyParam id integer required The ID of the task to be updated. Must exist in the `tasks` table. Example: 267
     * @bodyParam title string required The title of the task. Example: Updated Task
     * @bodyParam status_id integer required The status of the task. Must exist in the `statuses` table. Example: 2
     * @bodyParam priority_id integer nullable The priority of the task. Must exist in the `priorities` table. Example: 1
     * @bodyParam start_date string|null optional The start date of the task in the format specified in the general settings. Example: 2024-07-20
     * @bodyParam due_date string|null optional The due date of the task in the format specified in the general settings. Example: 2024-08-20
     * @bodyParam description string nullable A description of the task. Example: Updated task description.
     * @bodyParam note string nullable Additional notes about the task. Example: Needs immediate attention.
     * @bodyParam user_id array nullable An array of user IDs to be assigned to the task. Example: [2, 3]
     *
     * @response 200 {
     * "error": false,
     * "message": "Task updated successfully.",
     * "id": 280,
     * "parent_id": "420",
     * "parent_type": "project",
     * "data": {
     *   "id": 280,
     *   "workspace_id": 6,
     *   "title": "Res Test",
     *   "status": "Default",
     *   "priority": "Default",
     *   "users": [
     *     {
     *       "id": 7,
     *       "first_name": "Madhavan",
     *       "last_name": "Vaidya",
     *       "photo": "https://test-taskify.infinitietech.com/storage/photos/yxNYBlFLALdLomrL0JzUY2USPLILL9Ocr16j4n2o.png"
     *     }
     *   ],
     *   "clients": [
     *     {
     *       "id": 173,
     *       "first_name": "666",
     *       "last_name": "666",
     *       "photo": "https://test-taskify.infinitietech.com/storage/photos/no-image.jpg"
     *     }
     *   ],
     *   "start_date": "07-08-2024",
     *   "due_date": "07-08-2024",
     *   "project": {
     *     "id": 420,
     *     "title": "Updated Project Title"
     *   },
     *   "description": "Test Desc",
     *   "note": "Test Note",
     *   "created_at": "07-08-2024 13:02:52",
     *   "updated_at": "07-08-2024 13:02:52"
     * }
     *
     * }
     * @response 422 {
     *  "error": true,
     *  "message": "Validation errors occurred",
     *  "errors": {
     *    "id": ["The selected id is invalid."],
     *    "title": ["The title field is required."],
     *    "status_id": ["The selected status_id is invalid."],
     *    ...
     *  }
     * }
     * @response 500 {
     *  "error": true,
     *  "message": "An error occurred while updating the task."
     * }
     */

    public function update(Request $request)
    {
        $isApi = request()->get('isApi', false);
        $rules = [
            'id' => 'required|exists:tasks,id',
            'title' => 'required',
            'status_id' => 'required|exists:statuses,id',
            'priority_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value != 0 && !\DB::table('priorities')->where('id', $value)->exists()) {
                        $fail('The selected ' . $attribute . ' is invalid.');
                    }
                },
            ],
            'start_date' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $endDate = request()->input('due_date');
                    $errors = validate_date_format_and_order($value, $endDate);

                    if (!empty($errors['start_date'])) {
                        foreach ($errors['start_date'] as $error) {
                            $fail($error);
                        }
                    }
                },
            ],
            'due_date' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $startDate = request()->input('start_date');
                    $errors = validate_date_format_and_order($startDate, $value, endDateKey: 'due_date');

                    if (!empty($errors['due_date'])) {
                        foreach ($errors['due_date'] as $error) {
                            $fail($error);
                        }
                    }
                },
            ],
            'description' => 'nullable|string',
            'note' => 'nullable|string',
            'user_id' => 'nullable|array',
            'user_id.*' => 'exists:users,id', // Validate that each user_id exists in the users table
        ];
        $messages = [
            'status_id.required' => 'The status field is required.'
        ];
        try {
            $request->validate($rules, $messages);
            $status = Status::findOrFail($request->input('status_id'));
            $id = $request->input('id');
            $task = Task::findOrFail($id);
            $currentStatusId = $task->status_id;

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
                'description' => $request->input('description'),
                'note' => $request->input('note'),
            ];

            // Handle start_date
            if ($request->filled('start_date')) {
                $formFieldsToUpdate['start_date'] = format_date($request->input('start_date'), false, app('php_date_format'), 'Y-m-d');
            } else {
                $formFieldsToUpdate['start_date'] = null;
            }

            // Handle due_date
            if ($request->filled('due_date')) {
                $formFieldsToUpdate['due_date'] = format_date($request->input('due_date'), false, app('php_date_format'), 'Y-m-d');
            } else {
                $formFieldsToUpdate['due_date'] = null;
            }

            $userIds = $request->input('user_id', []);

            $task = Task::findOrFail($id);
            $task->update($formFieldsToUpdate);

            // Get the current users associated with the task
            $currentUsers = $task->users->pluck('id')->toArray();
            $currentClients = $task->project->clients->pluck('id')->toArray();

            // Sync the users for the task
            $task->users()->sync($userIds);

            // Get the new users associated with the task
            $newUsers = array_diff($userIds, $currentUsers);

            // Prepare notification data for new users
            $notification_data = [
                'type' => 'task',
                'type_id' => $id,
                'type_title' => $task->title,
                'access_url' => 'tasks/information/' . $task->id,
                'action' => 'assigned'
            ];

            // Notify only the new users
            $recipients = array_map(function ($userId) {
                return 'u_' . $userId;
            }, $newUsers);

            // Process notifications for new users
            processNotifications($notification_data, $recipients);

            if ($currentStatusId != $request->input('status_id')) {
                $currentStatus = Status::findOrFail($currentStatusId);
                $newStatus = Status::findOrFail($request->input('status_id'));

                $notification_data = [
                    'type' => 'task_status_updation',
                    'type_id' => $id,
                    'type_title' => $task->title,
                    'updater_first_name' => $this->user->first_name,
                    'updater_last_name' => $this->user->last_name,
                    'old_status' => $currentStatus->title,
                    'new_status' => $newStatus->title,
                    'access_url' => 'tasks/information/' . $id,
                    'action' => 'status_updated'
                ];

                $currentRecipients = array_merge(
                    array_map(function ($userId) {
                        return 'u_' . $userId;
                    }, $currentUsers),
                    array_map(function ($clientId) {
                        return 'c_' . $clientId;
                    }, $currentClients)
                );
                processNotifications($notification_data, $currentRecipients);
            }
            $task = $task->fresh();
            return formatApiResponse(
                false,
                'Task updated successfully.',
                [
                    'id' => $task->id,
                    'parent_id' => $task->project->id,
                    'parent_type' => 'project',
                    'data' => formatTask($task)
                ]
            );
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while updating the task.'
            ], 500);
        }
    }

    /**
     * Remove the specified task.
     *
     * This endpoint deletes a task based on the provided ID. The user must be authenticated to perform this action.
     *
     * @authenticated
     *
     * @group Task Management
     *
     * @urlParam id int required The ID of the task to be deleted. Example: 1
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Task deleted successfully.",
     *   "id": "262",
     *   "title": "From API",
     *   "parent_id": 377,
     *   "parent_type": "project",
     *   "data": [] 
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "Task not found.",
     *   "data": []
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred while deleting the task."
     * }
     */

    public function destroy($id)
    {
        $task = Task::find($id);
        $response = DeletionService::delete(Task::class, $id, 'Task');
        $responseData = json_decode($response->getContent(), true);

        if ($responseData['error']) {
            // Handle error response
            return response()->json($responseData);
        }
        return formatApiResponse(
            false,
            'Task deleted successfully.',
            [
                'id' => $id,
                'title' => $task->title,
                'parent_id' => $task->project_id,
                'parent_type' => 'project',
                'data' => []
            ]
        );
    }

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:tasks,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedTasks = [];
        $deletedTaskTitles = [];
        $parentIds = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $task = Task::find($id);
            if ($task) {
                $deletedTaskTitles[] = $task->title;
                DeletionService::delete(Task::class, $id, 'Task');
                $deletedTasks[] = $id;
                $parentIds[] = $task->project_id;
            }
        }

        return response()->json(['error' => false, 'message' => 'Task(s) deleted successfully.', 'id' => $deletedTasks, 'titles' => $deletedTaskTitles, 'parent_id' => $parentIds, 'parent_type' => 'project']);
    }


    public function list(Request $request, $id = '')
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $status_ids = request('status_ids', []);
        $priority_ids = request('priority_ids', []);
        $user_ids = request('user_ids', []);
        $client_ids = request('client_ids', []);
        $project_ids = request('project_ids', []);
        $start_date_from = (request('task_start_date_from')) ? trim(request('task_start_date_from')) : "";
        $start_date_to = (request('task_start_date_to')) ? trim(request('task_start_date_to')) : "";
        $end_date_from = (request('task_end_date_from')) ? trim(request('task_end_date_from')) : "";
        $end_date_to = (request('task_end_date_to')) ? trim(request('task_end_date_to')) : "";
        $where = [];


        if ($id) {
            $id = explode('_', $id);
            $belongs_to = $id[0];
            $belongs_to_id = $id[1];
            if ($belongs_to == 'project') {
                $project = Project::find($belongs_to_id);
                $tasks = $project->tasks();
            } else {
                $userOrClient = $belongs_to == 'user' ? User::find($belongs_to_id) : Client::find($belongs_to_id);
                $tasks = isAdminOrHasAllDataAccess($belongs_to, $belongs_to_id) ? $this->workspace->tasks() : $userOrClient->tasks();
            }
        } else {
            $tasks = isAdminOrHasAllDataAccess() ? $this->workspace->tasks() : $this->user->tasks();
        }
        if (!empty($user_ids)) {
            $tasks = $tasks->whereHas('users', function ($query) use ($user_ids) {
                $query->whereIn('users.id', $user_ids);
            });
        }
    
        if (!empty($client_ids)) {
            $tasks = $tasks->whereHas('project', function ($query) use ($client_ids) {
                $query->whereHas('clients', function ($query) use ($client_ids) {
                    $query->whereIn('clients.id', $client_ids);
                });
            });
        }

        if (!empty($project_ids)) {
            $tasks->whereIn('project_id', $project_ids);
        }
        if (!empty($status_ids)) {
            $tasks->whereIn('status_id', $status_ids);
        }
        if (!empty($priority_ids)) {
            $tasks->whereIn('priority_id', $priority_ids);
        }
        if ($start_date_from && $start_date_to) {
            $tasks->whereBetween('start_date', [$start_date_from, $start_date_to]);
        }
        if ($end_date_from && $end_date_to) {
            $tasks->whereBetween('due_date', [$end_date_from, $end_date_to]);
        }
        if ($search) {
            $tasks = $tasks->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        // Apply where clause to $tasks
        $tasks = $tasks->where($where);

        // Count total tasks before pagination
        $totaltasks = $tasks->count();

        $canCreate = checkPermission('create_tasks');
        $canEdit = checkPermission('edit_tasks');
        $canDelete = checkPermission('delete_tasks');

        $statuses = Status::all();
        $priorities = Priority::all();
        $isHome = $request->query('from_home');
        $webGuard = Auth::guard('web')->check();
        // Paginate tasks and format them
        $tasks = $tasks->orderBy($sort, $order)->paginate(request('limit'))->through(function ($task) use ($statuses, $priorities, $canEdit, $canDelete, $canCreate, $isHome, $webGuard) {
            $statusOptions = '';
            foreach ($statuses as $status) {
                $disabled = canSetStatus($status)  ? '' : 'disabled';
                $selected = $task->status_id == $status->id ? 'selected' : '';
                $statusOptions .= "<option value='{$status->id}' class='badge bg-label-{$status->color}' {$selected} {$disabled}>{$status->title}</option>";
            }

            $priorityOptions = '';
            foreach ($priorities as $priority) {
                $selectedPriority = $task->priority_id == $priority->id ? 'selected' : '';
                $priorityOptions .= "<option value='{$priority->id}' class='badge bg-label-{$priority->color}' {$selectedPriority}>{$priority->title}</option>";
            }

            $actions = '';

            if ($canEdit) {
                $actions .= '<a href="javascript:void(0);" class="edit-task" data-id="' . $task->id . '" title="' . get_label('update', 'Update') . '">' .
                    '<i class="bx bx-edit mx-1"></i>' .
                    '</a>';
            }

            if ($canDelete) {
                $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $task->id . '" data-type="tasks" data-table="task_table" data-reload="' . ($isHome ? 'true' : '') . '">' .
                    '<i class="bx bx-trash text-danger mx-1"></i>' .
                    '</button>';
            }

            if ($canCreate) {
                $actions .= '<a href="javascript:void(0);" class="duplicate" data-id="' . $task->id . '" data-title="' . $task->title . '" data-type="tasks" data-table="task_table" data-reload="' . ($isHome ? 'true' : '') . '" title="' . get_label('duplicate', 'Duplicate') . '">' .
                    '<i class="bx bx-copy text-warning mx-2"></i>' .
                    '</a>';
            }

            $actions .= '<a href="javascript:void(0);" class="quick-view" data-id="' . $task->id . '" title="' . get_label('quick_view', 'Quick View') . '">' .
                '<i class="bx bx-info-circle mx-3"></i>' .
                '</a>';

            $actions = $actions ?: '-';

            $userHtml = '';
            if (!empty($task->users) && count($task->users) > 0) {
                $userHtml .= '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
                foreach ($task->users as $user) {
                    $userHtml .= "<li class='avatar avatar-sm pull-up'><a href='" . url("/users/profile/{$user->id}") . "' target='_blank' title='{$user->first_name} {$user->last_name}'><img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                }
                if ($canEdit) {
                    $userHtml .= '<li title=' . get_label('update', 'Update') . '><a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-task update-users-clients" data-id="' . $task->id . '"><span class="bx bx-edit"></span></a></li>';
                }
                $userHtml .= '</ul>';
            } else {
                $userHtml = '<span class="badge bg-primary">' . get_label('not_assigned', 'Not Assigned') . '</span>';
                if ($canEdit) {
                    $userHtml .= '<a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-task update-users-clients" data-id="' . $task->id . '">' .
                        '<span class="bx bx-edit"></span>' .
                        '</a>';
                }
            }

            $clientHtml = '';
            if (!empty($task->project->clients) && count($task->project->clients) > 0) {
                $clientHtml .= '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
                foreach ($task->project->clients as $client) {
                    $clientHtml .= "<li class='avatar avatar-sm pull-up'><a href='" . url("/clients/profile/{$client->id}") . "' target='_blank' title='{$client->first_name} {$client->last_name}'><img src='" . ($client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                }
                $clientHtml .= '</ul>';
            } else {
                $clientHtml = '<span class="badge bg-primary">' . get_label('not_assigned', 'Not Assigned') . '</span>';
            }

            return [
                'id' => $task->id,
                'title' => "<a href='" . url("/tasks/information/{$task->id}") . "' target='_blank'><strong>{$task->title}</strong></a> " . ($webGuard ?
                    "<a href='" . url('/chat?type=task&id=' . $task->id) . "' class='mx-2' target='_blank'>
            <i class='bx bx-message-rounded-dots text-danger' data-bs-toggle='tooltip' data-bs-placement='right' title='" . get_label('discussion', 'Discussion') . "'></i>
        </a>"
                    : ""),
                'project_id' => "<a href='" . url("/projects/information/{$task->project->id}") . "' target='_blank'>
        <strong>{$task->project->title}</strong>
    </a> 
    <a href='javascript:void(0);' class='mx-2'>
        <i class='bx " . ($task->project->is_favorite ? 'bxs' : 'bx') . "-star favorite-icon text-warning' data-favorite='{$task->project->is_favorite}' data-id='{$task->project->id}' title='" . ($task->project->is_favorite ? get_label('remove_favorite', 'Click to remove from favorite') : get_label('add_favorite', 'Click to mark as favorite')) . "'></i>
    </a>",

                'users' => $userHtml,
                'clients' => $clientHtml,
                'start_date' => format_date($task->start_date),
                'due_date' => format_date($task->due_date),
                'status_id' => "<div class='d-flex align-items-center'><select class='form-select form-select-sm select-bg-label-{$task->status->color} fixed-width-select' id='statusSelect' data-id='{$task->id}' data-original-status-id='{$task->status->id}' data-original-color-class='select-bg-label-{$task->status->color}' data-type='task'" . ($isHome ? " data-reload='true'" : "") . ">{$statusOptions}</select>" . ($task->note ? 
                                "<i class='bx bx-notepad ms-2 text-primary' title='{$task->note}'></i>"
                                : "") . "</div>",
                'priority_id' => "<select class='form-select form-select-sm select-bg-label-" . ($task->priority ? $task->priority->color : 'secondary') . "' id='prioritySelect' data-id='{$task->id}' data-original-priority-id='" . ($task->priority ? $task->priority->id : '') . "' data-original-color-class='select-bg-label-" . ($task->priority ? $task->priority->color : 'secondary') . "' data-type='task'>{$priorityOptions}</select>",
                'created_at' => format_date($task->created_at, true),
                'updated_at' => format_date($task->updated_at, true),
                'actions' => $actions
            ];
        });

        // Return JSON response with formatted tasks and total count
        return response()->json([
            "rows" => $tasks->items(),
            "total" => $totaltasks,
        ]);
    }

    /**
     * List or search tasks.
     * 
     * This endpoint retrieves a list of tasks based on various filters. The user must be authenticated to perform this action. The request allows filtering by multiple statuses, users, clients, projects, date ranges, and other parameters.
     * 
     * @authenticated
     * 
     * @group Task Management
     *
     * @urlParam id int optional The ID of the task to retrieve. Example: 1
     * 
     * @queryParam search string optional The search term to filter tasks by title or id. Example: Task
     * @queryParam sort string optional The field to sort by. Defaults to "id". Sortable fields include: id, title, project, status, priority, start_date, due_date, created_at, and updated_at. Example: title
     * @queryParam order string optional The sort order, either "ASC" or "DESC". Defaults to "DESC". Example: ASC
     * @queryParam status_ids int[] optional The status IDs to filter tasks by. Example: [1, 2]
     * @queryParam priority_ids int[] optional The priority IDs to filter tasks by. Example: [3, 4]
     * @queryParam user_ids int[] optional The user IDs to filter tasks by. Example: [7, 8]
     * @queryParam client_ids int[] optional The client IDs to filter tasks by. Example: [5, 6]
     * @queryParam project_ids int[] optional The project IDs to filter tasks by. Example: [10, 11]
     * @queryParam task_start_date_from string optional The start date range's start in YYYY-MM-DD format. Example: 2024-01-01
     * @queryParam task_start_date_to string optional The start date range's end in YYYY-MM-DD format. Example: 2024-12-31
     * @queryParam task_end_date_from string optional The end date range's start in YYYY-MM-DD format. Example: 2024-01-01
     * @queryParam task_end_date_to string optional The end date range's end in YYYY-MM-DD format. Example: 2024-12-31
     * @queryParam limit int optional The number of tasks per page for pagination. Example: 10
     * @queryParam offset int optional The offset for pagination, indicating the starting point of results. Example: 0
     * 
     * @response 200 {
     *   "error": false,
     *   "message": "Tasks retrieved successfully",
     *   "total": 1,
     *   "data": [
     *     {
     *       "id": 268,
     *       "workspace_id": 6,
     *       "title": "sdff",
     *       "status": "Default",
     *       "priority": "Default",
     *       "users": [
     *         {
     *           "id": 7,
     *           "first_name": "Madhavan",
     *           "last_name": "Vaidya",
     *           "photo": "https://test-taskify.infinitietech.com/storage/photos/yxNYBlFLALdLomrL0JzUY2USPLILL9Ocr16j4n2o.png"
     *         }
     *       ],
     *       "clients": [
     *         {
     *           "id": 102,
     *           "first_name": "Test",
     *           "last_name": "Client",
     *           "photo": "https://test-taskify.infinitietech.com/storage/photos/no-image.jpg"
     *         }
     *       ],
     *       "start_date": "23-07-2024",
     *       "due_date": "24-07-2024",
     *       "project": {
     *         "id": 379,
     *         "title": "From API"
     *       },
     *       "description": "<p>Test Desc</p>",
     *       "note": "Test note",
     *       "created_at": "23-07-2024 17:50:09",
     *       "updated_at": "23-07-2024 19:08:16"
     *     }
     *   ]
     * }
     * 
     * @response 200 {
     *   "error": true,
     *   "message": "Task not found",
     *   "total": 0,
     *   "data": []
     * }
     * 
     * @response 200 {
     *   "error": true,
     *   "message": "Tasks not found",
     *   "total": 0,
     *   "data": []
     * }
     */

    public function apiList(Request $request, $id = '')
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $status_ids = $request->input('status_ids', []);
        $priority_ids = $request->input('priority_ids', []);
        $user_ids = $request->input('user_ids', []);
        $client_ids = $request->input('client_ids', []);
        $project_ids = $request->input('project_ids', []);
        $start_date_from = $request->input('task_start_date_from', '');
        $start_date_to = $request->input('task_start_date_to', '');
        $end_date_from = $request->input('task_end_date_from', '');
        $end_date_to = $request->input('task_end_date_to', '');
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);

        $where = [];

        if ($id) {
            $task = Task::find($id);
            if (!$task) {
                return formatApiResponse(
                    false,
                    'Task not found',
                    [
                        'total' => 0,
                        'data' => []
                    ]
                );
            } else {
                return formatApiResponse(
                    false,
                    'Task retrieved successfully',
                    [
                        'total' => 1,
                        'data' => [formatTask($task)]
                    ]
                );
            }
        } else {
            $tasksQuery = isAdminOrHasAllDataAccess() ? $this->workspace->tasks() : $this->user->tasks();

            if (!empty($user_ids)) {
                $taskIds = DB::table('task_user')->whereIn('user_id', $user_ids)->pluck('task_id')->toArray();
                $tasksQuery = $tasksQuery->whereIn('id', $taskIds);
            }

            if (!empty($client_ids)) {
                $projectIds = DB::table('client_project')->whereIn('client_id', $client_ids)->pluck('project_id')->toArray();
                $tasksQuery = $tasksQuery->whereIn('project_id', $projectIds);
            }

            if (!empty($project_ids)) {
                $tasksQuery->whereIn('project_id', $project_ids);
            }

            if (!empty($status_ids)) {
                $tasksQuery->whereIn('status_id', $status_ids);
            }

            if (!empty($priority_ids)) {
                $tasksQuery->whereIn('priority_id', $priority_ids);
            }

            if ($start_date_from && $start_date_to) {
                $tasksQuery->whereBetween('start_date', [$start_date_from, $start_date_to]);
            }

            if ($end_date_from && $end_date_to) {
                $tasksQuery->whereBetween('due_date', [$end_date_from, $end_date_to]);
            }

            if ($search) {
                $tasksQuery->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('id', 'like', '%' . $search . '%');
                });
            }

            $tasksQuery->where($where);
            $total = $tasksQuery->count();

            $tasks = $tasksQuery->orderBy($sort, $order)
                ->skip($offset)
                ->take($limit)
                ->get();

            if ($tasks->isEmpty()) {
                return formatApiResponse(
                    false,
                    'Tasks not found',
                    [
                        'total' => 0,
                        'data' => []
                    ]
                );
            }

            $data = $tasks->map(function ($task) {
                return formatTask($task);
            });

            return formatApiResponse(
                false,
                'Tasks retrieved successfully',
                [
                    'total' => $total,
                    'data' => $data,
                ]
            );
        }
    }


    public function dragula($id = '')
    {
        $project = (object)[];
        if ($id) {
            $project = Project::findOrFail($id);
            $tasks = isAdminOrHasAllDataAccess() ? $project->tasks : $this->user->project_tasks($id);
        } else {
            $tasks = isAdminOrHasAllDataAccess() ? $this->workspace->tasks : $this->user->tasks()->get();
        }
        if (request()->has('status')) {
            $tasks = $tasks->where('status_id', request()->status);
        }
        if (request()->has('project')) {
            $project = Project::findOrFail(request()->project);
            $tasks = $tasks->where('project_id', request()->project);
        }
        $total_tasks = $tasks->count();
        return view('tasks.board_view', ['project' => $project, 'tasks' => $tasks, 'total_tasks' => $total_tasks]);
    }

    public function updateStatus($id, $newStatus)
    {
        $status = Status::findOrFail($newStatus);
        if (canSetStatus($status)) {
            $task = Task::findOrFail($id);
            $current_status = $task->status->title;
            $task->status_id = $newStatus;
            if ($task->save()) {
                $task->refresh();
                $new_status = $task->status->title;

                $notification_data = [
                    'type' => 'task_status_updation',
                    'type_id' => $id,
                    'type_title' => $task->title,
                    'updater_first_name' => $this->user->first_name,
                    'updater_last_name' => $this->user->last_name,
                    'old_status' => $current_status,
                    'new_status' => $new_status,
                    'access_url' => 'tasks/information/' . $id,
                    'action' => 'status_updated'
                ];
                $userIds = $task->users->pluck('id')->toArray();
                $clientIds = $task->project->clients->pluck('id')->toArray();
                $recipients = array_merge(
                    array_map(function ($userId) {
                        return 'u_' . $userId;
                    }, $userIds),
                    array_map(function ($clientId) {
                        return 'c_' . $clientId;
                    }, $clientIds)
                );
                processNotifications($notification_data, $recipients);

                return response()->json(['error' => false, 'message' => 'Task status updated successfully.', 'id' => $id, 'activity_message' => trim($this->user->first_name) . ' ' . trim($this->user->last_name) . ' updated task status from ' . trim($current_status) . ' to ' . trim($new_status)]);
            } else {
                return response()->json(['error' => true, 'message' => 'Task status couldn\'t updated.']);
            }
        } else {
            return response()->json(['error' => true, 'message' => 'You are not authorized to set this status.']);
        }
    }


    /**
     * Update the status of a task.
     *
     * This endpoint updates the status of a specified task. The user must be authenticated and have permission to set the new status. A notification will be sent to all users and clients associated with the task.
     *
     * @authenticated
     *
     * @group Task Management
     *
     * @urlParam id int required The ID of the task whose status is to be updated. Example: 1
     * @bodyParam statusId int required The ID of the new status to set for the task. Must exist in the `statuses` table. Example: 2
     * @bodyParam note string optional An optional note to attach to the task update. Example: Updated due to client request.
     *
     * @response 200 {
     * "error": false,
     * "message": "Status updated successfully.",
     * "id": "278",
     * "type": "task",
     * "activity_message": "Madhavan Vaidya updated task status from Ongoing to Completed",
     * "data": {
     * "id": 278,
     * "workspace_id": 6,
     * "title": "New Task",
     * "status": "Completed",
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
     * "id": 173,
     * "first_name": "666",
     * "last_name": "666",
     * "photo": "https://test-taskify.infinitietech.com/storage/photos/no-image.jpg"
     * }
     * ],
     * "start_date": "20-08-2024",
     * "due_date": null,
     * "project": {
     * "id": 419,
     * "title": "Updated Project Title"
     * },
     * "description": "This is a detailed description of the task.",
     * "note": null,
     * "created_at": "06-08-2024 11:42:13",
     * "updated_at": "12-08-2024 15:18:09"
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

    //For status change from dropdown
    public function update_status(Request $request, $id = null)
    {
        $isApi = request()->get('isApi', false);
        if ($id) {
            $request->merge(['id' => $id]);
        }
        $rules = [
            'id' => 'required|exists:tasks,id',
            'statusId' => 'required|exists:statuses,id'
        ];
        try {
            $request->validate($rules);
            $id = $request->id;
            $statusId = $request->statusId;
            $status = Status::findOrFail($statusId);
            if (canSetStatus($status)) {
                $task = Task::findOrFail($id);
                if ($task->status->id != $statusId) {
                    $currentStatus = $task->status->title;
                    $task->status_id = $statusId;
                    $task->note = $request->note;
                    if ($task->save()) {
                        $task = $task->fresh();
                        $newStatus = $task->status->title;

                        $notification_data = [
                            'type' => 'task_status_updation',
                            'type_id' => $id,
                            'type_title' => $task->title,
                            'updater_first_name' => $this->user->first_name,
                            'updater_last_name' => $this->user->last_name,
                            'old_status' => $currentStatus,
                            'new_status' => $newStatus,
                            'access_url' => 'tasks/information/' . $id,
                            'action' => 'status_updated'
                        ];
                        $userIds = $task->users->pluck('id')->toArray();
                        $clientIds = $task->project->clients->pluck('id')->toArray();
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
                                'type' => 'task',
                                'activity_message' => trim($this->user->first_name) . ' ' . trim($this->user->last_name) . ' updated task status from ' . trim($currentStatus) . ' to ' . trim($newStatus),
                                'data' => formatTask($task)
                            ]
                        );
                    } else {
                        return response()->json(['error' => true, 'message' => 'Status couldn\'t updated.']);
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

    public function duplicate($id)
    {
        // Define the related tables for this meeting
        $relatedTables = ['users']; // Include related tables as needed

        // Use the general duplicateRecord function
        $title = (request()->has('title') && !empty(trim(request()->title))) ? request()->title : '';
        $duplicate = duplicateRecord(Task::class, $id, $relatedTables, $title);

        if (!$duplicate) {
            return response()->json(['error' => true, 'message' => 'Task duplication failed.']);
        }
        if (request()->has('reload') && request()->input('reload') === 'true') {
            Session::flash('message', 'Task duplicated successfully.');
        }
        return response()->json(['error' => false, 'message' => 'Task duplicated successfully.', 'id' => $id, 'parent_id' => $duplicate->project->id, 'parent_type' => 'project']);
    }

    public function upload_media(Request $request)
    {
        $maxFileSizeBytes = config('media-library.max_file_size');
        $maxFileSizeKb = $maxFileSizeBytes / 1024;

        // Round to an integer (Laravel validation rules expect integer values)
        $maxFileSizeKb = (int)$maxFileSizeKb;
        try {
            $validatedData = $request->validate([
                'id' => 'integer|exists:tasks,id',
                'media_files.*' => 'file|max:' . $maxFileSizeKb
            ]);

            $mediaIds = [];

            if ($request->hasFile('media_files')) {
                $task = Task::find($validatedData['id']);
                $mediaFiles = $request->file('media_files');

                foreach ($mediaFiles as $mediaFile) {
                    $mediaItem = $task->addMedia($mediaFile)
                        ->sanitizingFileName(function ($fileName) use ($task) {
                            // Replace special characters and spaces with hyphens
                            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));

                            // Generate a unique identifier based on timestamp and random component
                            $uniqueId = time() . '_' . mt_rand(1000, 9999);

                            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
                            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);

                            return "{$baseName}-{$uniqueId}.{$extension}";
                        })
                        ->toMediaCollection('task-media');

                    $mediaIds[] = $mediaItem->id;
                }
                return response()->json(['error' => false, 'message' => 'File(s) uploaded successfully.', 'id' => $mediaIds, 'type' => 'media', 'parent_type' => 'task', 'parent_id' => $task->id]);
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
        $task = Task::findOrFail($id);
        $media = $task->getMedia('task-media');

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
                ? asset('storage/task-media/' . $mediaItem->file_name)
                : $mediaItem->getFullUrl();


            $fileExtension = pathinfo($fileUrl, PATHINFO_EXTENSION);

            // Check if file extension corresponds to an image type
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            $isImage = in_array(strtolower($fileExtension), $imageExtensions);

            if ($isImage) {
                $html = '<a href="' . $fileUrl . '" data-lightbox="task-media">';
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
                $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $mediaItem->id . '" data-type="task-media" data-table="task_media_table">' .
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

        return response()->json(['error' => false, 'message' => 'File deleted successfully.', 'id' => $mediaId, 'title' => $mediaItem->file_name, 'parent_id' => $mediaItem->model_id,  'type' => 'media', 'parent_type' => 'task']);
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

        return response()->json(['error' => false, 'message' => 'Files(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles, 'parent_id' => $parentIds, 'type' => 'media', 'parent_type' => 'task']);
    }

    /**
     * Update the priority of a task.
     * 
     * This endpoint updates the priority of a specified task. The user must be authenticated and have permission to set the new priority.
     * 
     * @authenticated
     * 
     * @group Task Management
     *
     * @urlParam id int required The ID of the task whose priority is to be updated. Example: 1
     * @bodyParam priorityId int required The ID of the new priority to set for the task. Must exist in the `priorities` table. Example: 3
     *
     * @response 200 {
     * "error": false,
     * "message": "Priority updated successfully.",
     * "id": "278",
     * "type": "task",
     * "activity_message": "Madhavan Vaidya updated task priority from Medium to High",
     * "data": {
     * "id": 278,
     * "workspace_id": 6,
     * "title": "New Task",
     * "status": "Completed",
     * "priority": "High",
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
     * "id": 173,
     * "first_name": "666",
     * "last_name": "666",
     * "photo": "https://test-taskify.infinitietech.com/storage/photos/no-image.jpg"
     * }
     * ],
     * "start_date": "20-08-2024",
     * "due_date": null,
     * "project": {
     * "id": 419,
     * "title": "Updated Project Title"
     * },
     * "description": "This is a detailed description of the task.",
     * "note": null,
     * "created_at": "06-08-2024 11:42:13",
     * "updated_at": "12-08-2024 15:40:41"
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
     *       "The selected priorityId is invalid."
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
            'id' => 'required|exists:tasks,id',
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
            $task = Task::findOrFail($id);
            if ($task->priority_id != $priorityId) {
                $currentPriority = $task->priority ? $task->priority->title : 'Default';
                $task->priority_id = $priorityId;
                if ($task->save()) {
                    // Reload the task to get updated priority information
                    $task = $task->fresh();
                    $newPriority = $task->priority ? $task->priority->title : 'Default';
                    $message = trim($this->user->first_name) . ' ' . trim($this->user->last_name) . ' updated task priority from ' . trim($currentPriority) . ' to ' . trim($newPriority);
                    return formatApiResponse(
                        false,
                        'Priority updated successfully.',
                        [
                            'id' => $id,
                            'type' => 'task',
                            'activity_message' => $message,
                            'data' => formatTask($task)
                        ]
                    );
                } else {
                    return response()->json(['error' => true, 'message' => 'Priority couldn\'t updated.']);
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
        UserClientPreference::updateOrCreate(
            ['user_id' => $prefix . $this->user->id, 'table_name' => 'tasks'],
            ['default_view' => $view]
        );
        return response()->json(['error' => false, 'message' => 'Default View Set Successfully.']);
    }
}