<?php

use App\Models\Tax;
use App\Models\User;
use App\Models\Client;
use App\Models\Update;
use App\Models\Setting;
use App\Models\Template;
use App\Models\LeaveEditor;
use App\Models\Workspace;
use App\Models\LeaveRequest;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\UserClientPreference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use App\Notifications\AssignmentNotification;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Twilio\Rest\Client as TwilioClient;
use Illuminate\Support\Arr;
use Carbon\Carbon;

use function Ramsey\Uuid\v1;

if (!function_exists('get_timezone_array')) {
    // 1.Get Time Zone
    function get_timezone_array()
    {
        $list = DateTimeZone::listAbbreviations();
        $idents = DateTimeZone::listIdentifiers();

        $data = $offset = $added = array();
        foreach ($list as $abbr => $info) {
            foreach ($info as $zone) {
                if (
                    !empty($zone['timezone_id'])
                    and
                    !in_array($zone['timezone_id'], $added)
                    and
                    in_array($zone['timezone_id'], $idents)
                ) {
                    $z = new DateTimeZone($zone['timezone_id']);
                    $c = new DateTime("", $z);
                    $zone['time'] = $c->format('h:i A');
                    $offset[] = $zone['offset'] = $z->getOffset($c);
                    $data[] = $zone;
                    $added[] = $zone['timezone_id'];
                }
            }
        }

        array_multisort($offset, SORT_ASC, $data);
        $i = 0;
        $temp = array();
        foreach ($data as $key => $row) {
            $temp[0] = $row['time'];
            $temp[1] = formatOffset($row['offset']);
            $temp[2] = $row['timezone_id'];
            $options[$i++] = $temp;
        }

        return $options;
    }
}
if (!function_exists('formatOffset')) {
    function formatOffset($offset)
    {
        $hours = $offset / 3600;
        $remainder = $offset % 3600;
        $sign = $hours > 0 ? '+' : '-';
        $hour = (int) abs($hours);
        $minutes = (int) abs($remainder / 60);
        if ($hour == 0 and $minutes == 0) {
            $sign = ' ';
        }
        return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0');
    }
}
if (!function_exists('relativeTime')) {
    function relativeTime($time)
    {
        if (!ctype_digit($time))
            $time = strtotime($time);
        $d[0] = array(1, "second");
        $d[1] = array(60, "minute");
        $d[2] = array(3600, "hour");
        $d[3] = array(86400, "day");
        $d[4] = array(604800, "week");
        $d[5] = array(2592000, "month");
        $d[6] = array(31104000, "year");

        $w = array();

        $return = "";
        $now = time();
        $diff = ($now - $time);
        $secondsLeft = $diff;

        for ($i = 6; $i > -1; $i--) {
            $w[$i] = intval($secondsLeft / $d[$i][0]);
            $secondsLeft -= ($w[$i] * $d[$i][0]);
            if ($w[$i] != 0) {
                $return .= abs($w[$i]) . " " . $d[$i][1] . (($w[$i] > 1) ? 's' : '') . " ";
            }
        }

        $return .= ($diff > 0) ? "ago" : "left";
        return $return;
    }
}
if (!function_exists('get_settings')) {

    function get_settings($variable)
    {
        $fetched_data = Setting::all()->where('variable', $variable)->values();
        if (isset($fetched_data[0]['value']) && !empty($fetched_data[0]['value'])) {
            if (isJson($fetched_data[0]['value'])) {
                $fetched_data = json_decode($fetched_data[0]['value'], true);
            }
            return $fetched_data;
        }
    }
}
if (!function_exists('isJson')) {
    function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
if (!function_exists('create_label')) {
    function create_label($variable, $title = '', $locale = '')
    {
        if ($title == '') {
            $title = $variable;
        }
        $value = htmlspecialchars(get_label($variable, $title, $locale), ENT_QUOTES, 'UTF-8');
        return "
        
        <div class='mb-3 col-md-6'>
                    <label class='form-label' for='$variable'>$title</label>
                    <div class='input-group input-group-merge'>
                        <input type='text' name='$variable' class='form-control' value='$value'>
                    </div>
                </div>
        
        ";
    }
}

if (!function_exists('get_label')) {

    function get_label($label, $default, $locale = '')
    {
        if (Lang::has('labels.' . $label, $locale)) {
            return trans('labels.' . $label, [], $locale);
        } else {
            return $default;
        }
    }
}
if (!function_exists('empty_state')) {

    function empty_state($url)
    {
        return "
    <div class='card text-center'>
    <div class='card-body'>
        <div class='misc-wrapper'>
            <h2 class='mb-2 mx-2'>Data Not Found </h2>
            <p class='mb-4 mx-2'>Oops! 😖 Data doesn't exists.</p>
            <a href='/$url' class='btn btn-primary'>Create now</a>
            <div class='mt-3'>
                <img src='../assets/img/illustrations/page-misc-error-light.png' alt='page-misc-error-light' width='500' class='img-fluid' data-app-dark-img='illustrations/page-misc-error-dark.png' data-app-light-img='illustrations/page-misc-error-light.png' />
            </div>
        </div>
    </div>
</div>";
    }
}
if (!function_exists('format_date')) {
    function format_date($date, $time = false, $from_format = null, $to_format = null, $apply_timezone = true)
    {
        if ($date) {
            $from_format = $from_format ?? 'Y-m-d';
            $to_format = $to_format ?? get_php_date_time_format();
            $time_format = get_php_date_time_format(true);
            if ($time) {
                if ($apply_timezone) {
                    if (!$date instanceof \Carbon\Carbon) {
                        $dateObj = \Carbon\Carbon::createFromFormat($from_format . ' H:i:s', $date)
                            ->setTimezone(config('app.timezone'));
                    } else {
                        $dateObj = $date->setTimezone(config('app.timezone'));
                    }
                } else {
                    if (!$date instanceof \Carbon\Carbon) {
                        $dateObj = \Carbon\Carbon::createFromFormat($from_format . ' H:i:s', $date);
                    } else {
                        $dateObj = $date;
                    }
                }
            } else {
                if (!$date instanceof \Carbon\Carbon) {
                    $dateObj = \Carbon\Carbon::createFromFormat($from_format, $date);
                } else {
                    $dateObj = $date;
                }
            }


            $timeFormat = $time ? ' ' . $time_format : '';
            $date = $dateObj->format($to_format . $timeFormat);
            return $date;
        } else {
            return '-';
        }
    }
}
if (!function_exists('getAuthenticatedUser')) {
    function getAuthenticatedUser($idOnly = false, $withPrefix = false)
    {
        $prefix = '';

        // Check the 'web' guard (users)
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $prefix = 'u_';
        }

        // Check the 'client' guard (clients)
        elseif (Auth::guard('client')->check()) {
            $user = Auth::guard('client')->user();
            $prefix = 'c_';
        }

        // Check the 'sanctum' guard (API tokens)
        elseif (Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();
            // Optionally set a prefix for sanctum-authenticated users
            // $prefix = 's_';
        }

        // No user is authenticated
        else {
            return null;
        }

        if ($idOnly) {
            if ($withPrefix) {
                return $prefix . $user->id;
            } else {
                return $user->id;
            }
        }

        return $user;
    }
}

if (!function_exists('isUser')) {

    function isUser()
    {
        return Auth::guard('web')->check(); // Assuming 'role' is a field in the user model.
    }
}
if (!function_exists('isClient')) {

    function isClient()
    {
        return Auth::guard('client')->check(); // Assuming 'role' is a field in the user model.
    }
}
if (!function_exists('generateUniqueSlug')) {
    function generateUniqueSlug($title, $model, $id = null)
    {
        $slug = Str::slug($title);
        $count = 2;

        // If an ID is provided, add a where clause to exclude it
        if ($id !== null) {
            while ($model::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = Str::slug($title) . '-' . $count;
                $count++;
            }
        } else {
            while ($model::where('slug', $slug)->exists()) {
                $slug = Str::slug($title) . '-' . $count;
                $count++;
            }
        }

        return $slug;
    }
}
if (!function_exists('duplicateRecord')) {
    function duplicateRecord($model, $id, $relatedTables = [], $title = '')
    {
        // Find the original record with related data
        $originalRecord = $model::with($relatedTables)->find($id);
        if (!$originalRecord) {
            return false; // Record not found
        }
        // Start a new database transaction to ensure data consistency
        DB::beginTransaction();

        try {
            // Duplicate the original record
            $duplicateRecord = $originalRecord->replicate();
            // Set the title if provided
            if (!empty($title)) {
                $duplicateRecord->title = $title;
            }
            $duplicateRecord->save();

            foreach ($relatedTables as $relatedTable) {
                if ($relatedTable === 'tasks') {
                    // Handle 'tasks' relationship separately
                    foreach ($originalRecord->$relatedTable as $task) {
                        // Duplicate the related task
                        $duplicateTask = $task->replicate();
                        $duplicateTask->project_id = $duplicateRecord->id;
                        $duplicateTask->save();
                        foreach ($task->users as $user) {
                            // Attach the duplicated user to the duplicated task
                            $duplicateTask->users()->attach($user->id);
                        }
                    }
                }
            }
            // Handle many-to-many relationships separately
            if (in_array('users', $relatedTables)) {
                $originalRecord->users()->each(function ($user) use ($duplicateRecord) {
                    $duplicateRecord->users()->attach($user->id);
                });
            }

            if (in_array('clients', $relatedTables)) {
                $originalRecord->clients()->each(function ($client) use ($duplicateRecord) {
                    $duplicateRecord->clients()->attach($client->id);
                });
            }

            if (in_array('tags', $relatedTables)) {
                $originalRecord->tags()->each(function ($tag) use ($duplicateRecord) {
                    $duplicateRecord->tags()->attach($tag->id);
                });
            }

            // Commit the transaction
            DB::commit();

            return $duplicateRecord;
        } catch (\Exception $e) {
            // Handle any exceptions and rollback the transaction on failure
            DB::rollback();

            return false;
        }
    }
}
if (!function_exists('is_admin_or_leave_editor')) {
    function is_admin_or_leave_editor($user = null)
    {
        if (!$user) {
            $user = getAuthenticatedUser();
        }

        // Check if the user is an admin or a leave editor based on their presence in the leave_editors table
        if ($user->hasRole('admin') || LeaveEditor::where('user_id', $user->id)->exists()) {
            return true;
        }
        return false;
    }
}
if (!function_exists('get_php_date_time_format')) {
    function get_php_date_time_format($timeFormat = false)
    {
        $general_settings = get_settings('general_settings');
        if ($timeFormat) {
            return $general_settings['time_format'] ?? 'H:i:s';
        } else {
            $date_format = $general_settings['date_format'] ?? 'DD-MM-YYYY|d-m-Y';
            $date_format = explode('|', $date_format);
            return $date_format[1];
        }
    }
}
if (!function_exists('get_system_update_info')) {
    function get_system_update_info()
    {
        $updatePath = Config::get('constants.UPDATE_PATH');
        $updaterPath = $updatePath . 'updater.json';
        $subDirectory = (File::exists($updaterPath) && File::exists($updatePath . 'update/updater.json')) ? 'update/' : '';

        if (File::exists($updaterPath) || File::exists($updatePath . $subDirectory . 'updater.json')) {
            $updaterFilePath = File::exists($updaterPath) ? $updaterPath : $updatePath . $subDirectory . 'updater.json';
            $updaterContents = File::get($updaterFilePath);

            // Check if the file contains valid JSON data
            if (!json_decode($updaterContents)) {
                throw new \RuntimeException('Invalid JSON content in updater.json');
            }

            $linesArray = json_decode($updaterContents, true);

            if (!isset($linesArray['version'], $linesArray['previous'], $linesArray['manual_queries'], $linesArray['query_path'])) {
                throw new \RuntimeException('Invalid JSON structure in updater.json');
            }
        } else {
            throw new \RuntimeException('updater.json does not exist');
        }

        $dbCurrentVersion = Update::latest()->first();
        $data['db_current_version'] = $dbCurrentVersion ? $dbCurrentVersion->version : '1.0.0';
        if ($data['db_current_version'] == $linesArray['version']) {
            $data['updated_error'] = true;
            $data['message'] = 'Oops!. This version is already updated into your system. Try another one.';
            return $data;
        }
        if ($data['db_current_version'] == $linesArray['previous']) {
            $data['file_current_version'] = $linesArray['version'];
        } else {
            $data['sequence_error'] = true;
            $data['message'] = 'Oops!. Update must performed in sequence.';
            return $data;
        }

        $data['query'] = $linesArray['manual_queries'];
        $data['query_path'] = $linesArray['query_path'];

        return $data;
    }
}
if (!function_exists('escape_array')) {
    function escape_array($array)
    {
        if (empty($array)) {
            return $array;
        }

        $db = DB::connection()->getPdo();

        if (is_array($array)) {
            return array_map(function ($value) use ($db) {
                return $db->quote($value);
            }, $array);
        } else {
            // Handle single non-array value
            return $db->quote($array);
        }
    }
}
if (!function_exists('isEmailConfigured')) {

    function isEmailConfigured()
    {
        $email_settings = get_settings('email_settings');
        if (
            isset($email_settings['email']) && !empty($email_settings['email']) &&
            isset($email_settings['password']) && !empty($email_settings['password']) &&
            isset($email_settings['smtp_host']) && !empty($email_settings['smtp_host']) &&
            isset($email_settings['smtp_port']) && !empty($email_settings['smtp_port'])
        ) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('get_current_version')) {

    function get_current_version()
    {
        $dbCurrentVersion = Update::latest()->first();
        return $dbCurrentVersion ? $dbCurrentVersion->version : '1.0.0';
    }
}

if (!function_exists('isAdminOrHasAllDataAccess')) {
    function isAdminOrHasAllDataAccess($type = null, $id = null)
    {
        // Get authenticated user
        $authenticatedUser = getAuthenticatedUser();
        if ($type == 'user' && $id !== null) {
            $user = User::find($id);
            if ($user) {
                return $user->hasRole('admin') || $user->can('access_all_data');
            }
        } elseif ($type == 'client' && $id !== null) {
            $client = Client::find($id);
            if ($client) {
                return $client->hasRole('admin') || $client->can('access_all_data');
            }
        } elseif ($type === null && $id === null) {
            if ($authenticatedUser) {
                return $authenticatedUser->hasRole('admin') || $authenticatedUser->can('access_all_data');
            }
        }

        return false;
    }
}




if (!function_exists('getControllerNames')) {

    function getControllerNames()
    {
        $controllersPath = app_path('Http/Controllers');
        $files = File::files($controllersPath);

        $excludedControllers = [
            'ActivityLogController',
            'Controller',
            'HomeController',
            'InstallerController',
            'LanguageController',
            'ProfileController',
            'RolesController',
            'SearchController',
            'SettingsController',
            'UpdaterController',
            'EstimatesInvoicesController',
            'SwaggerController'
        ];

        $controllerNames = [];

        foreach ($files as $file) {
            $fileName = pathinfo($file, PATHINFO_FILENAME);

            // Skip controllers in the excluded list
            if (in_array($fileName, $excludedControllers)) {
                continue;
            }

            if (str_ends_with($fileName, 'Controller')) {
                // Convert to singular form, snake_case, and remove 'Controller' suffix
                $controllerName = Str::snake(Str::singular(str_replace('Controller', '', $fileName)));
                $controllerNames[] = $controllerName;
            }
        }

        // Add manually defined types
        $manuallyDefinedTypes = [
            'contract_type',
            'media',
            'estimate',
            'invoice',
            'milestone'
            // Add more types as needed
        ];

        $controllerNames = array_merge($controllerNames, $manuallyDefinedTypes);

        return $controllerNames;
    }
    if (!function_exists('formatSize')) {
        function formatSize($bytes)
        {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];

            $i = 0;
            while ($bytes >= 1024 && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }

            return round($bytes, 2) . ' ' . $units[$i];
        }
    }
    if (!function_exists('getStatusColor')) {
        function getStatusColor($status)
        {
            switch ($status) {
                case 'sent':
                    return 'primary';
                case 'accepted':
                case 'fully_paid':
                    return 'success';
                case 'draft':
                    return 'secondary';
                case 'declined':
                case 'due':
                    return 'danger';
                case 'expired':
                case 'partially_paid':
                    return 'warning';
                case 'not_specified':
                    return 'secondary';
                default:
                    return 'info';
            }
        }
    }
    if (!function_exists('getStatusCount')) {
        function getStatusCount($status, $type)
        {
            $estimates_invoices = isAdminOrHasAllDataAccess() ? Workspace::find(getWorkspaceId())->estimates_invoices($status, $type) : getAuthenticatedUser()->estimates_invoices($status, $type);
            return $estimates_invoices->count();
        }
    }

    if (!function_exists('format_currency')) {
        function format_currency($amount, $is_currency_symbol = 1, $include_separators = true)
        {
            if ($amount == '') {
                return '';
            }
            $general_settings = get_settings('general_settings');
            $currency_symbol = $general_settings['currency_symbol'] ?? '₹';
            $currency_format = $general_settings['currency_formate'] ?? 'comma_separated';
            $decimal_points = intval($general_settings['decimal_points_in_currency'] ?? '2');
            $currency_symbol_position = $general_settings['currency_symbol_position'] ?? 'before';

            // Determine the appropriate separators based on the currency format and $use_commas parameter
            if ($include_separators) {
                $thousands_separator = ($currency_format == 'comma_separated') ? ',' : '.';
            } else {
                $thousands_separator = '';
            }

            // Format the amount with the determined separators
            $formatted_amount = number_format($amount, $decimal_points, '.', $thousands_separator);
            if ($is_currency_symbol) {
                // Format currency symbol position
                if ($currency_symbol_position === 'before') {
                    $currency_amount = $currency_symbol . ' ' . $formatted_amount;
                } else {
                    $currency_amount = $formatted_amount . ' ' . $currency_symbol;
                }
                return $currency_amount;
            }
            return $formatted_amount;
        }
    }

    function get_tax_data($tax_id, $total_amount, $currency_symbol = 0)
    {
        // Check if tax_id is not empty
        if ($tax_id != '') {
            // Retrieve tax data from the database using the tax_id
            $tax = Tax::find($tax_id);

            // Check if tax data is found
            if ($tax) {
                // Get tax rate and type
                $taxRate = $tax->amount;
                $taxType = $tax->type;

                // Calculate tax amount based on tax rate and type
                $taxAmount = 0;
                $disp_tax = '';

                if ($taxType == 'percentage') {
                    $taxAmount = ($total_amount * $tax->percentage) / 100;
                    $disp_tax = format_currency($taxAmount, $currency_symbol) . '(' . $tax->percentage . '%)';
                } elseif ($taxType == 'amount') {
                    $taxAmount = $taxRate;
                    $disp_tax = format_currency($taxAmount, $currency_symbol);
                }

                // Return the calculated tax data
                return [
                    'taxAmount' => $taxAmount,
                    'taxType' => $taxType,
                    'dispTax' => $disp_tax,
                ];
            }
        }

        // Return empty data if tax_id is empty or tax data is not found
        return [
            'taxAmount' => 0,
            'taxType' => '',
            'dispTax' => '',
        ];
    }

    if (!function_exists('processNotifications')) {
        function processNotifications($data, $recipients)
        {
            // Define an array of types for which email notifications should be sent
            $emailNotificationTypes = ['project_assignment', 'project_status_updation', 'task_assignment', 'task_status_updation', 'workspace_assignment', 'meeting_assignment', 'leave_request_creation', 'leave_request_status_updation', 'team_member_on_leave_alert'];
            $smsNotificationTypes = ['project_assignment', 'project_status_updation', 'task_assignment', 'task_status_updation', 'workspace_assignment', 'meeting_assignment', 'leave_request_creation', 'leave_request_status_updation', 'team_member_on_leave_alert'];
            if (!empty($recipients)) {
                $type = $data['type'] == 'task_status_updation' ? 'task' : ($data['type'] == 'project_status_updation' ? 'project' : ($data['type'] == 'leave_request_creation' || $data['type'] == 'leave_request_status_updation' || $data['type'] == 'team_member_on_leave_alert' ? 'leave_request' : $data['type']));
                $template = getNotificationTemplate($data['type'], 'system');
                if (!$template || ($template->status !== 0)) {
                    $notification = Notification::create([
                        'workspace_id' => getWorkspaceId(),
                        'from_id' => getGuardName() == 'client' ? 'c_' . getAuthenticatedUser()->id : 'u_' . getAuthenticatedUser()->id,
                        'type' => $type,
                        'type_id' => $data['type_id'],
                        'action' => $data['action'],
                        'title' => getTitle($data),
                        'message' => get_message($data, NULL, 'system'),
                    ]);
                }
                // Exclude creator from receiving notification
                $loggedInUserId = getGuardName() == 'client' ? 'c_' . getAuthenticatedUser()->id : 'u_' . getAuthenticatedUser()->id;
                $recipients = array_diff($recipients, [$loggedInUserId]);

                $recipients = array_unique($recipients);
                foreach ($recipients as $recipient) {
                    $enabledNotifications = getUserPreferences('notification_preference', 'enabled_notifications', $recipient);
                    $recipientId = substr($recipient, 2);
                    if (substr($recipient, 0, 2) === 'u_') {
                        $recipientModel = User::find($recipientId);
                    } elseif (substr($recipient, 0, 2) === 'c_') {
                        $recipientModel = Client::find($recipientId);
                    }
                    // Check if recipient was found
                    if ($recipientModel) {
                        if (!$template || ($template->status !== 0)) {
                            if ((is_array($enabledNotifications) && empty($enabledNotifications)) || (
                                is_array($enabledNotifications) && (
                                    in_array('system_' . $data['type'] . '_assignment', $enabledNotifications) ||
                                    in_array('system_' . $data['type'], $enabledNotifications)
                                )
                            )) {
                                $recipientModel->notifications()->attach($notification->id);
                            }
                        }
                        if (in_array($data['type'] . '_assignment', $emailNotificationTypes) || in_array($data['type'], $emailNotificationTypes)) {
                            if ((is_array($enabledNotifications) && empty($enabledNotifications)) || (
                                is_array($enabledNotifications) && (
                                    in_array('email_' . $data['type'] . '_assignment', $enabledNotifications) ||
                                    in_array('email_' . $data['type'], $enabledNotifications)
                                )
                            )) {
                                try {
                                    sendEmailNotification($recipientModel, $data);
                                } catch (\Exception $e) {
                                    // dd($e->getMessage());
                                } catch (TransportExceptionInterface $e) {
                                    // dd($e->getMessage());
                                } catch (Throwable $e) {
                                    // dd($e->getMessage());
                                    // Catch any other throwable, including non-Exception errors
                                }
                            }
                        }
                        if (in_array($data['type'] . '_assignment', $smsNotificationTypes) || in_array($data['type'], $smsNotificationTypes)) {
                            if ((is_array($enabledNotifications) && empty($enabledNotifications)) || (
                                is_array($enabledNotifications) && (
                                    in_array('sms_' . $data['type'] . '_assignment', $enabledNotifications) ||
                                    in_array('sms_' . $data['type'], $enabledNotifications)
                                )
                            )) {
                                try {
                                    sendSMSNotification($data, $recipientModel);
                                } catch (\Exception $e) {
                                }
                            }
                        }

                        if ((is_array($enabledNotifications) && empty($enabledNotifications)) || (
                            is_array($enabledNotifications) && (
                                in_array('whatsapp_' . $data['type'] . '_assignment', $enabledNotifications) ||
                                in_array('whatsapp_' . $data['type'], $enabledNotifications)
                            )
                        )) {
                            try {
                                sendWhatsAppNotification($data, $recipientModel);
                            } catch (\Exception $e) {
                            }
                        }
                    }
                }
            }
        }
    }

    if (!function_exists('sendEmailNotification')) {
        function sendEmailNotification($recipientModel, $data)
        {
            $template = getNotificationTemplate($data['type']);

            if (!$template || ($template->status !== 0)) {
                $recipientModel->notify(new AssignmentNotification($recipientModel, $data));
            }
        }
    }

    if (!function_exists('sendSMSNotification')) {
        function sendSMSNotification($data, $recipient)
        {
            $template = getNotificationTemplate($data['type'], 'sms');

            if (!$template || ($template->status !== 0)) {
                send_sms($data, $recipient);
            }
        }
    }

    if (!function_exists('sendWhatsAppNotification')) {
        function sendWhatsAppNotification($data, $recipient)
        {
            $template = getNotificationTemplate($data['type'], 'whatsapp');

            if (!$template || ($template->status !== 0)) {
                send_whatsapp_notification($data, $recipient);
            }
        }
    }

    if (!function_exists('getNotificationTemplate')) {
        function getNotificationTemplate($type, $emailOrSMS = 'email')
        {
            $template = Template::where('type', $emailOrSMS)
                ->where('name', $type . '_assignment')
                ->first();

            if (!$template) {
                // If template with $type . '_assignment' name not found, check for template with $type name
                $template = Template::where('type', $emailOrSMS)
                    ->where('name', $type)
                    ->first();
            }

            return $template;
        }
    }

    if (!function_exists('send_sms')) {
        function send_sms( $recipient,$itemData = NULL, $message = NULL)
        {
            $msg = $itemData ? get_message($itemData, $recipient) : $message;
            try {
                $sms_gateway_settings = get_settings('sms_gateway_settings', true);
                $data = [
                    "base_url" => $sms_gateway_settings['base_url'],
                    "sms_gateway_method" => $sms_gateway_settings['sms_gateway_method']
                ];

                $data["body"] = [];
                if (isset($sms_gateway_settings["body_formdata"])) {
                    foreach ($sms_gateway_settings["body_formdata"] as $key => $value) {
                        $value = parse_sms($value, $recipient->phone, $msg, $recipient->country_code);
                        $data["body"][$key] = $value;
                    }
                }

                $data["header"] = [];
                if (isset($sms_gateway_settings["header_data"])) {
                    foreach ($sms_gateway_settings["header_data"] as $key => $value) {
                        $value = parse_sms($value, $recipient->phone, $msg, $recipient->country_code);
                        $data["header"][] = $key . ": " . $value;
                    }
                }

                $data["params"] = [];
                if (isset($sms_gateway_settings["params_data"])) {
                    foreach ($sms_gateway_settings["params_data"] as $key => $value) {
                        $value = parse_sms($value, $recipient->phone, $msg, $recipient->country_code);
                        $data["params"][$key] = $value;
                    }
                }
                $response = curl_sms($data["base_url"], $data["sms_gateway_method"], $data["body"], $data["header"]);
                // print_r($response);
                if ($itemData == NULL) {
                    return $response;
                }
            } catch (Exception $e) {
                // Handle the exception
                if ($itemData == NULL) {
                    throw new Exception('Failed to send SMS: ' . $e->getMessage());
                }
            }
        }
    }

    if (!function_exists('send_whatsapp_notification')) {
        function send_whatsapp_notification($recipient,$itemData = NULL,  $message = NULL)
        {
            $msg = $itemData ? get_message($itemData, $recipient, 'whatsapp') : $message;
            try {
                $whatsapp_settings = get_settings('whatsapp_settings', true);
                $sid = $whatsapp_settings['account_sid'];
                $token = $whatsapp_settings['auth_token'];
                $twilio = new TwilioClient($sid, $token);

                $response = $twilio->messages
                    ->create(
                        "whatsapp:" . $recipient->country_code . $recipient->phone, // to
                        array(
                            "from" => "whatsapp:" . $whatsapp_settings['from'],
                            "body" => $msg
                        )
                    );

                if ($itemData == NULL) {
                    $responseArray = $response->toArray();
                    return $responseArray;
                }
            } catch (Exception $e) {
                if ($itemData == NULL) {
                    throw new Exception('Failed to send WhatsApp Message: ' . $e->getMessage());
                }
            }
        }
    }

    if (!function_exists('curl_sms')) {
        function curl_sms($url, $method = 'GET', $data = [], $headers = [])
        {
            $ch = curl_init();
            $curl_options = array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_HEADER => 0,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded',
                )
            );

            if (count($headers) != 0) {
                $curl_options[CURLOPT_HTTPHEADER] = $headers;
            }

            if (strtolower($method) == 'post') {
                $curl_options[CURLOPT_POST] = 1;
                $curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
            } else {
                $curl_options[CURLOPT_CUSTOMREQUEST] = 'GET';
            }
            curl_setopt_array($ch, $curl_options);

            $result = array(
                'body' => json_decode(curl_exec($ch), true),
                'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            );

            return $result;
        }
    }

    if (!function_exists('parse_sms')) {
        function parse_sms($template, $phone, $msg, $country_code)
        {
            // Implement your parsing logic here
            // This is just a placeholder
            return str_replace(['{only_mobile_number}', '{message}', '{country_code}'], [$phone, $msg, $country_code], $template);
        }
    }
    if (!function_exists('get_message')) {
        function get_message($data, $recipient, $type = 'sms')
        {
            static $authUser = null;
            static $company_title = null;
            if ($authUser === null) {
                $authUser = getAuthenticatedUser();
            }
            if ($company_title === null) {
                $general_settings = get_settings('general_settings');
                $company_title = $general_settings['company_title'] ?? 'Taskify';
            }

            $siteUrl = request()->getSchemeAndHttpHost();
            $fetched_data = Template::where('type', $type)
                ->where('name', $data['type'] . '_assignment')
                ->first();

            if (!$fetched_data) {
                // If template with $this->data['type'] . '_assignment' name not found, check for template with $this->data['type'] name
                $fetched_data = Template::where('type', $type)
                    ->where('name', $data['type'])
                    ->first();
            }


            $templateContent = 'Default Content';
            $contentPlaceholders = []; // Initialize outside the switch

            // Customize content based on type
            if ($type === 'system') {
                switch ($data['type']) {
                    case 'project':
                        $contentPlaceholders = [
                            '{PROJECT_ID}' => $data['type_id'],
                            '{PROJECT_TITLE}' => $data['type_title'],
                            '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                            '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                            '{COMPANY_TITLE}' => $company_title,
                            '{PROJECT_URL}' => $siteUrl . '/' . $data['access_url']
                        ];
                        $templateContent = '{ASSIGNEE_FIRST_NAME} {ASSIGNEE_LAST_NAME} assigned you new project: {PROJECT_TITLE}, ID:#{PROJECT_ID}.';
                        break;
                    case 'project_status_updation':
                        $contentPlaceholders = [
                            '{PROJECT_ID}' => $data['type_id'],
                            '{PROJECT_TITLE}' => $data['type_title'],
                            '{UPDATER_FIRST_NAME}' => $data['updater_first_name'],
                            '{UPDATER_LAST_NAME}' => $data['updater_last_name'],
                            '{OLD_STATUS}' => $data['old_status'],
                            '{NEW_STATUS}' => $data['new_status'],
                            '{PROJECT_URL}' => $siteUrl . '/' . $data['access_url'],
                            '{COMPANY_TITLE}' => $company_title
                        ];
                        $templateContent = '{UPDATER_FIRST_NAME} {UPDATER_LAST_NAME} has updated the status of project {PROJECT_TITLE}, ID:#{PROJECT_ID}, from {OLD_STATUS} to {NEW_STATUS}.';
                        break;
                    case 'task':
                        $contentPlaceholders = [
                            '{TASK_ID}' => $data['type_id'],
                            '{TASK_TITLE}' => $data['type_title'],
                            '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                            '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                            '{COMPANY_TITLE}' => $company_title,
                            '{TASK_URL}' => $siteUrl . '/' . $data['access_url']
                        ];
                        $templateContent = '{ASSIGNEE_FIRST_NAME} {ASSIGNEE_LAST_NAME} assigned you new task: {TASK_TITLE}, ID:#{TASK_ID}.';
                        break;
                    case 'task_status_updation':
                        $contentPlaceholders = [
                            '{TASK_ID}' => $data['type_id'],
                            '{TASK_TITLE}' => $data['type_title'],
                            '{UPDATER_FIRST_NAME}' => $data['updater_first_name'],
                            '{UPDATER_LAST_NAME}' => $data['updater_last_name'],
                            '{OLD_STATUS}' => $data['old_status'],
                            '{NEW_STATUS}' => $data['new_status'],
                            '{TASK_URL}' => $siteUrl . '/' . $data['access_url'],
                            '{COMPANY_TITLE}' => $company_title
                        ];
                        $templateContent = '{UPDATER_FIRST_NAME} {UPDATER_LAST_NAME} has updated the status of task {TASK_TITLE}, ID:#{TASK_ID}, from {OLD_STATUS} to {NEW_STATUS}.';
                        break;
                    case 'workspace':
                        $contentPlaceholders = [
                            '{WORKSPACE_ID}' => $data['type_id'],
                            '{WORKSPACE_TITLE}' => $data['type_title'],
                            '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                            '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                            '{COMPANY_TITLE}' => $company_title,
                            '{WORKSPACE_URL}' => $siteUrl . '/workspaces'
                        ];
                        $templateContent = '{ASSIGNEE_FIRST_NAME} {ASSIGNEE_LAST_NAME} added you in a new workspace {WORKSPACE_TITLE}, ID:#{WORKSPACE_ID}.';
                        break;
                    case 'meeting':
                        $contentPlaceholders = [
                            '{MEETING_ID}' => $data['type_id'],
                            '{MEETING_TITLE}' => $data['type_title'],
                            '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                            '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                            '{COMPANY_TITLE}' => $company_title,
                            '{MEETING_URL}' => $siteUrl . '/meetings'
                        ];
                        $templateContent = '{ASSIGNEE_FIRST_NAME} {ASSIGNEE_LAST_NAME} added you in a new meeting {MEETING_TITLE}, ID:#{MEETING_ID}.';
                        break;

                    case 'leave_request_creation':
                        $contentPlaceholders = [
                            '{ID}' => $data['type_id'],
                            '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                            '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                            '{TYPE}' => $data['leave_type'],
                            '{FROM}' => $data['from'],
                            '{TO}' => $data['to'],
                            '{DURATION}' => $data['duration'],
                            '{REASON}' => $data['reason'],
                            '{STATUS}' => $data['status'],
                            '{COMPANY_TITLE}' => $company_title
                        ];
                        $templateContent = 'New Leave Request ID:#{ID} Has Been Created By {REQUESTEE_FIRST_NAME} {REQUESTEE_LAST_NAME}.';
                        break;

                    case 'leave_request_status_updation':
                        $contentPlaceholders = [
                            '{ID}' => $data['type_id'],
                            '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                            '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                            '{TYPE}' => $data['leave_type'],
                            '{FROM}' => $data['from'],
                            '{TO}' => $data['to'],
                            '{DURATION}' => $data['duration'],
                            '{REASON}' => $data['reason'],
                            '{OLD_STATUS}' => $data['old_status'],
                            '{NEW_STATUS}' => $data['new_status'],
                            '{COMPANY_TITLE}' => $company_title
                        ];
                        $templateContent = 'Leave Request ID:#{ID} Status Updated From {OLD_STATUS} To {NEW_STATUS}.';
                        break;

                    case 'team_member_on_leave_alert':
                        $contentPlaceholders = [
                            '{ID}' => $data['type_id'],
                            '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                            '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                            '{TYPE}' => $data['leave_type'],
                            '{FROM}' => $data['from'],
                            '{TO}' => $data['to'],
                            '{DURATION}' => $data['duration'],
                            '{COMPANY_TITLE}' => $company_title
                        ];
                        $templateContent = '{REQUESTEE_FIRST_NAME} {REQUESTEE_LAST_NAME} will be on {TYPE} leave from {FROM} to {TO}.';
                        break;
                }
            } else {
                switch ($data['type']) {
                    case 'project':
                        $contentPlaceholders = [
                            '{PROJECT_ID}' => $data['type_id'],
                            '{PROJECT_TITLE}' => $data['type_title'],
                            '{FIRST_NAME}' => $recipient->first_name,
                            '{LAST_NAME}' => $recipient->last_name,
                            '{COMPANY_TITLE}' => $company_title,
                            '{PROJECT_URL}' => $siteUrl . '/' . $data['access_url'],
                            '{SITE_URL}' => $siteUrl
                        ];
                        $templateContent = 'Hello, {FIRST_NAME} {LAST_NAME} You have been assigned a new project {PROJECT_TITLE}, ID:#{PROJECT_ID}.';
                        break;
                    case 'project_status_updation':
                        $contentPlaceholders = [
                            '{PROJECT_ID}' => $data['type_id'],
                            '{PROJECT_TITLE}' => $data['type_title'],
                            '{FIRST_NAME}' => $recipient->first_name,
                            '{LAST_NAME}' => $recipient->last_name,
                            '{UPDATER_FIRST_NAME}' => $data['updater_first_name'],
                            '{UPDATER_LAST_NAME}' => $data['updater_last_name'],
                            '{OLD_STATUS}' => $data['old_status'],
                            '{NEW_STATUS}' => $data['new_status'],
                            '{PROJECT_URL}' => $siteUrl . '/' . $data['access_url'],
                            '{SITE_URL}' => $siteUrl,
                            '{COMPANY_TITLE}' => $company_title
                        ];
                        $templateContent = '{UPDATER_FIRST_NAME} {UPDATER_LAST_NAME} has updated the status of project {PROJECT_TITLE}, ID:#{PROJECT_ID}, from {OLD_STATUS} to {NEW_STATUS}.';
                        break;
                    case 'task':
                        $contentPlaceholders = [
                            '{TASK_ID}' => $data['type_id'],
                            '{TASK_TITLE}' => $data['type_title'],
                            '{FIRST_NAME}' => $recipient->first_name,
                            '{LAST_NAME}' => $recipient->last_name,
                            '{COMPANY_TITLE}' => $company_title,
                            '{TASK_URL}' => $siteUrl . '/' . $data['access_url'],
                            '{SITE_URL}' => $siteUrl
                        ];
                        $templateContent = 'Hello, {FIRST_NAME} {LAST_NAME} You have been assigned a new task {TASK_TITLE}, ID:#{TASK_ID}.';
                        break;
                    case 'task_status_updation':
                        $contentPlaceholders = [
                            '{TASK_ID}' => $data['type_id'],
                            '{TASK_TITLE}' => $data['type_title'],
                            '{FIRST_NAME}' => $recipient->first_name,
                            '{LAST_NAME}' => $recipient->last_name,
                            '{UPDATER_FIRST_NAME}' => $data['updater_first_name'],
                            '{UPDATER_LAST_NAME}' => $data['updater_last_name'],
                            '{OLD_STATUS}' => $data['old_status'],
                            '{NEW_STATUS}' => $data['new_status'],
                            '{TASK_URL}' => $siteUrl . '/' . $data['access_url'],
                            '{SITE_URL}' => $siteUrl,
                            '{COMPANY_TITLE}' => $company_title
                        ];
                        $templateContent = '{UPDATER_FIRST_NAME} {UPDATER_LAST_NAME} has updated the status of task {TASK_TITLE}, ID:#{TASK_ID}, from {OLD_STATUS} to {NEW_STATUS}.';
                        break;
                    case 'workspace':
                        $contentPlaceholders = [
                            '{WORKSPACE_ID}' => $data['type_id'],
                            '{WORKSPACE_TITLE}' => $data['type_title'],
                            '{FIRST_NAME}' => $recipient->first_name,
                            '{LAST_NAME}' => $recipient->last_name,
                            '{COMPANY_TITLE}' => $company_title,
                            '{WORKSPACE_URL}' => $siteUrl . '/workspaces',
                            '{SITE_URL}' => $siteUrl
                        ];
                        $templateContent = 'Hello, {FIRST_NAME} {LAST_NAME} You have been added in a new workspace {WORKSPACE_TITLE}, ID:#{WORKSPACE_ID}.';
                        break;
                    case 'meeting':
                        $contentPlaceholders = [
                            '{MEETING_ID}' => $data['type_id'],
                            '{MEETING_TITLE}' => $data['type_title'],
                            '{FIRST_NAME}' => $recipient->first_name,
                            '{LAST_NAME}' => $recipient->last_name,
                            '{COMPANY_TITLE}' => $company_title,
                            '{MEETING_URL}' => $siteUrl . '/meetings',
                            '{SITE_URL}' => $siteUrl
                        ];
                        $templateContent = 'Hello, {FIRST_NAME} {LAST_NAME} You have been added in a new meeting {MEETING_TITLE}, ID:#{MEETING_ID}.';
                        break;

                    case 'leave_request_creation':
                        $contentPlaceholders = [
                            '{ID}' => $data['type_id'],
                            '{USER_FIRST_NAME}' => $recipient->first_name,
                            '{USER_LAST_NAME}' => $recipient->last_name,
                            '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                            '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                            '{TYPE}' => $data['leave_type'],
                            '{FROM}' => $data['from'],
                            '{TO}' => $data['to'],
                            '{DURATION}' => $data['duration'],
                            '{REASON}' => $data['reason'],
                            '{STATUS}' => $data['status'],
                            '{COMPANY_TITLE}' => $company_title,
                            '{SITE_URL}' => $siteUrl,
                            '{CURRENT_YEAR}' => date('Y')
                        ];
                        $templateContent = 'New Leave Request ID:#{ID} Has Been Created By {REQUESTEE_FIRST_NAME} {REQUESTEE_LAST_NAME}.';
                        break;

                    case 'leave_request_status_updation':
                        $contentPlaceholders = [
                            '{ID}' => $data['type_id'],
                            '{USER_FIRST_NAME}' => $recipient->first_name,
                            '{USER_LAST_NAME}' => $recipient->last_name,
                            '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                            '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                            '{TYPE}' => $data['leave_type'],
                            '{FROM}' => $data['from'],
                            '{TO}' => $data['to'],
                            '{DURATION}' => $data['duration'],
                            '{REASON}' => $data['reason'],
                            '{OLD_STATUS}' => $data['old_status'],
                            '{NEW_STATUS}' => $data['new_status'],
                            '{COMPANY_TITLE}' => $company_title,
                            '{SITE_URL}' => $siteUrl,
                            '{CURRENT_YEAR}' => date('Y')
                        ];
                        $templateContent = 'Leave Request ID:#{ID} Status Updated From {OLD_STATUS} To {NEW_STATUS}.';
                        break;

                    case 'team_member_on_leave_alert':
                        $contentPlaceholders = [
                            '{ID}' => $data['type_id'],
                            '{USER_FIRST_NAME}' => $recipient->first_name,
                            '{USER_LAST_NAME}' => $recipient->last_name,
                            '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                            '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                            '{TYPE}' => $data['leave_type'],
                            '{FROM}' => $data['from'],
                            '{TO}' => $data['to'],
                            '{DURATION}' => $data['duration'],
                            '{COMPANY_TITLE}' => $company_title,
                            '{SITE_URL}' => $siteUrl,
                            '{CURRENT_YEAR}' => date('Y')
                        ];
                        $templateContent = '{REQUESTEE_FIRST_NAME} {REQUESTEE_LAST_NAME} will be on {TYPE} leave from {FROM} to {TO}.';
                        break;
                }
            }
            if (filled(Arr::get($fetched_data, 'content'))) {
                $templateContent = $fetched_data->content;
            }
            // Replace placeholders with actual values
            $content = str_replace(array_keys($contentPlaceholders), array_values($contentPlaceholders), $templateContent);

            return $content;
        }
    }
    if (!function_exists('format_budget')) {
        function format_budget($amount)
        {
            // Check if the input is numeric or can be converted to a numeric value.
            if (!is_numeric($amount)) {
                // If the input is not numeric, return null or handle the error as needed.
                return null;
            }

            // Remove non-numeric characters from the input string.
            $amount = preg_replace('/[^0-9.]/', '', $amount);

            // Convert the input to a float.
            $amount = (float) $amount;

            // Define suffixes for thousands, millions, etc.
            $suffixes = ['', 'K', 'M', 'B', 'T'];

            // Determine the appropriate suffix and divide the amount accordingly.
            $suffixIndex = 0;
            while ($amount >= 1000 && $suffixIndex < count($suffixes) - 1) {
                $amount /= 1000;
                $suffixIndex++;
            }

            // Format the amount with the determined suffix.
            return number_format($amount, 2) . $suffixes[$suffixIndex];
        }
    }
    if (!function_exists('canSetStatus')) {
        function canSetStatus($status)
        {
            $user = getAuthenticatedUser();
            $isAdminOrHasAllDataAccess = isAdminOrHasAllDataAccess();

            // Ensure the user and their first role exist
            $userRoleId = $user && $user->roles->isNotEmpty() ? $user->roles->first()->id : null;

            // Check if the user has permission for this status
            $hasPermission = $userRoleId && $status->roles->contains($userRoleId) || $isAdminOrHasAllDataAccess;

            return $hasPermission;
        }
    }


    if (!function_exists('checkPermission')) {
        function checkPermission($permission)
        {
            static $user = null;

            if ($user === null) {
                $user = getAuthenticatedUser();
            }

            return $user->can($permission);
        }
    }

    if (!function_exists('getUserPreferences')) {
        function getUserPreferences($table, $column = 'visible_columns', $userId = null)
        {
            if ($userId === null) {
                $userId = getAuthenticatedUser(true, true);
            }

            $result = UserClientPreference::where('user_id', $userId)
                ->where('table_name', $table)
                ->first();

            switch ($column) {
                case 'default_view':
                    if ($table == 'projects') {
                        return $result && $result->default_view && $result->default_view == 'list' ? 'projects/list' : 'projects';
                    } elseif ($table == 'tasks') {
                        return $result && $result->default_view && $result->default_view == 'draggable' ? 'tasks/draggable' : 'tasks';
                    }
                    break;
                case 'visible_columns':
                    return $result && $result->visible_columns ? $result->visible_columns : [];
                    break;
                case 'enabled_notifications':
                case 'enabled_notifications':
                    if ($result) {
                        if ($result->enabled_notifications === null) {
                            return null;
                        }
                        return json_decode($result->enabled_notifications, true);
                    }
                    return [];
                    break;
                    break;
                default:
                    return null;
                    break;
            }
        }
    }
    if (!function_exists('getOrdinalSuffix')) {
        function getOrdinalSuffix($number)
        {
            if (!in_array(($number % 100), [11, 12, 13])) {
                switch ($number % 10) {
                    case 1:
                        return 'st';
                    case 2:
                        return 'nd';
                    case 3:
                        return 'rd';
                }
            }
            return 'th';
        }
    }

    if (!function_exists('getTitle')) {
        function getTitle($data)
        {
            static $authUser = null;
            static $companyTitle = null;

            if ($authUser === null) {
                $authUser = getAuthenticatedUser();
            }
            if ($companyTitle === null) {
                $general_settings = get_settings('general_settings');
                $companyTitle = $general_settings['company_title'] ?? 'Taskify';
            }

            $fetched_data = Template::where('type', 'system')
                ->where('name', $data['type'] . '_assignment')
                ->first();

            if (!$fetched_data) {
                $fetched_data = Template::where('type', 'system')
                    ->where('name', $data['type'])
                    ->first();
            }

            $subject = 'Default Subject'; // Set a default subject
            $subjectPlaceholders = [];

            // Customize subject based on type
            switch ($data['type']) {
                case 'project':
                    $subjectPlaceholders = [
                        '{PROJECT_ID}' => $data['type_id'],
                        '{PROJECT_TITLE}' => $data['type_title'],
                        '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                        '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                        '{COMPANY_TITLE}' => $companyTitle
                    ];
                    break;
                case 'task':
                    $subjectPlaceholders = [
                        '{TASK_ID}' => $data['type_id'],
                        '{TASK_TITLE}' => $data['type_title'],
                        '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                        '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                        '{COMPANY_TITLE}' => $companyTitle
                    ];
                    break;
                case 'workspace':
                    $subjectPlaceholders = [
                        '{WORKSPACE_ID}' => $data['type_id'],
                        '{WORKSPACE_TITLE}' => $data['type_title'],
                        '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                        '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                        '{COMPANY_TITLE}' => $companyTitle
                    ];
                    break;
                case 'meeting':
                    $subjectPlaceholders = [
                        '{MEETING_ID}' => $data['type_id'],
                        '{MEETING_TITLE}' => $data['type_title'],
                        '{ASSIGNEE_FIRST_NAME}' => $authUser->first_name,
                        '{ASSIGNEE_LAST_NAME}' => $authUser->last_name,
                        '{COMPANY_TITLE}' => $companyTitle
                    ];
                    break;
                case 'leave_request_creation':
                    $subjectPlaceholders = [
                        '{ID}' => $data['type_id'],
                        '{STATUS}' => $data['status'],
                        '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                        '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                        '{COMPANY_TITLE}' => $companyTitle
                    ];
                    break;
                case 'leave_request_status_updation':
                    $subjectPlaceholders = [
                        '{ID}' => $data['type_id'],
                        '{OLD_STATUS}' => $data['old_status'],
                        '{NEW_STATUS}' => $data['new_status'],
                        '{COMPANY_TITLE}' => $companyTitle
                    ];
                    break;
                case 'team_member_on_leave_alert':
                    $subjectPlaceholders = [
                        '{ID}' => $data['type_id'],
                        '{REQUESTEE_FIRST_NAME}' => $data['team_member_first_name'],
                        '{REQUESTEE_LAST_NAME}' => $data['team_member_last_name'],
                        '{COMPANY_TITLE}' => $companyTitle
                    ];
                    break;
                case 'project_status_updation':
                    $subjectPlaceholders = [
                        '{PROJECT_ID}' => $data['type_id'],
                        '{PROJECT_TITLE}' => $data['type_title'],
                        '{UPDATER_FIRST_NAME}' => $data['updater_first_name'],
                        '{UPDATER_LAST_NAME}' => $data['updater_last_name'],
                        '{OLD_STATUS}' => $data['old_status'],
                        '{NEW_STATUS}' => $data['new_status'],
                        '{COMPANY_TITLE}' => $companyTitle
                    ];
                    break;
                case 'task_status_updation':
                    $subjectPlaceholders = [
                        '{TASK_ID}' => $data['type_id'],
                        '{TASK_TITLE}' => $data['type_title'],
                        '{UPDATER_FIRST_NAME}' => $data['updater_first_name'],
                        '{UPDATER_LAST_NAME}' => $data['updater_last_name'],
                        '{OLD_STATUS}' => $data['old_status'],
                        '{NEW_STATUS}' => $data['new_status'],
                        '{COMPANY_TITLE}' => $companyTitle
                    ];
                    break;
            }
            if (filled(Arr::get($fetched_data, 'subject'))) {
                $subject = $fetched_data->subject;
            } else {
                if ($data['type'] == 'leave_request_creation') {
                    $subject = 'Leave Requested';
                } elseif ($data['type'] == 'leave_request_status_updation') {
                    $subject = 'Leave Request Status Updated';
                } elseif ($data['type'] == 'team_member_on_leave_alert') {
                    $subject = 'Team Member on Leave Alert';
                } elseif ($data['type'] == 'project_status_updation') {
                    $subject = 'Project Status Updated';
                } elseif ($data['type'] == 'task_status_updation') {
                    $subject = 'Task Status Updated';
                } else {
                    $subject = 'New ' . ucfirst($data['type']) . ' Assigned';
                }
            }

            $subject = str_replace(array_keys($subjectPlaceholders), array_values($subjectPlaceholders), $subject);

            return $subject;
        }
    }
    if (!function_exists('hasPrimaryWorkspace')) {
        function hasPrimaryWorkspace()
        {
            $primaryWorkspace = \App\Models\Workspace::where('is_primary', 1)->first();

            return $primaryWorkspace ? $primaryWorkspace->id : 0;
        }
    }
    if (!function_exists('getWorkspaceId')) {
        function getWorkspaceId()
        {
            $workspaceId = 0;
            $authenticatedUser = getAuthenticatedUser();

            if ($authenticatedUser) {
                if (session()->has('workspace_id')) {
                    $workspaceId = session('workspace_id'); // Retrieve workspace_id from session
                } else {
                    $workspaceId = request()->header('workspace_id');
                }
            }
            return $workspaceId;
        }
    }

    if (!function_exists('getGuardName')) {
        function getGuardName()
        {
            static $guardName = null;

            // If the guard name is already determined, return it
            if ($guardName !== null) {
                return $guardName;
            }

            // Check the 'web' guard (users)
            if (Auth::guard('web')->check()) {
                $guardName = 'web';
            }
            // Check the 'client' guard (clients)
            elseif (Auth::guard('client')->check()) {
                $guardName = 'client';
            }
            // Check the 'sanctum' guard (API tokens)
            elseif (Auth::guard('sanctum')->check()) {
                $user = Auth::guard('sanctum')->user();

                // Determine if the sanctum user is a user or a client
                if ($user instanceof \App\Models\User) {
                    $guardName = 'web';
                } elseif ($user instanceof \App\Models\Client) {
                    $guardName = 'client';
                }
            }

            return $guardName;
        }
    }
    if (!function_exists('formatProject')) {
        function formatProject($project)
        {
            return [
                'id' => $project->id,
                'title' => $project->title,
                'status' => $project->status->title,
                'status_id' => $project->status->id,
                'priority' => $project->priority ? $project->priority->title : 'Default',
                'priority_id' => $project->priority ? $project->priority->id : 0,
                'users' => $project->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'photo' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')
                    ];
                }),
                'user_id' => $project->users->pluck('id')->toArray(),
                'clients' => $project->clients->map(function ($client) {
                    return [
                        'id' => $client->id,
                        'first_name' => $client->first_name,
                        'last_name' => $client->last_name,
                        'photo' => $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')
                    ];
                }),
                'client_id' => $project->clients->pluck('id')->toArray(),
                'tags' => $project->tags->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'title' => $tag->title
                    ];
                }),
                'tag_ids' => $project->tags->pluck('id')->toArray(),
                'start_date' => $project->start_date ? format_date($project->start_date) : null,
                'end_date' => $project->end_date ? format_date($project->end_date) : null,
                'budget' => $project->budget ?? null,
                'task_accessibility' => $project->task_accessibility,
                'description' => $project->description,
                'note' => $project->note,
                'favorite' => $project->is_favorite,
                'created_at' => format_date($project->created_at, true),
                'updated_at' => format_date($project->updated_at, true),
            ];
        }
    }

    if (!function_exists('formatTask')) {
        function formatTask($task)
        {
            return [
                'id' => $task->id,
                'workspace_id' => $task->workspace_id,
                'title' => $task->title,
                'status' => $task->status->title,
                'status_id' => $task->status->id,
                'priority' => $task->priority ? $task->priority->title : 'Default',
                'priority_id' => $task->priority ? $task->priority->id : 0,
                'users' => $task->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'photo' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')
                    ];
                }),
                'user_id' => $task->users->pluck('id')->toArray(),
                'clients' => $task->project->clients->map(function ($client) {
                    return [
                        'id' => $client->id,
                        'first_name' => $client->first_name,
                        'last_name' => $client->last_name,
                        'photo' => $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')
                    ];
                }),
                'start_date' => $task->start_date ? format_date($task->start_date) : null,
                'due_date' => $task->due_date ? format_date($task->due_date) : null,
                'project' => $task->project->title,
                'project_id' => $task->project->id,
                'description' => $task->description,
                'note' => $task->note,
                'created_at' => format_date($task->created_at, true),
                'updated_at' => format_date($task->updated_at, true),
            ];
        }
    }
    if (!function_exists('formatWorkspace')) {
        function formatWorkspace($workspace)
        {
            $authUser = getAuthenticatedUser();
            return [
                'id' => $workspace->id,
                'title' => $workspace->title,
                'primaryWorkspace' => $workspace->is_primary,
                'defaultWorkspace' => $authUser->default_workspace_id == $workspace->id ? 1 : 0,
                'users' => $workspace->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'photo' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')
                    ];
                }),
                'user_ids' => $workspace->users->pluck('id')->toArray(),
                'clients' => $workspace->clients->map(function ($client) {
                    return [
                        'id' => $client->id,
                        'first_name' => $client->first_name,
                        'last_name' => $client->last_name,
                        'photo' => $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')
                    ];
                }),
                'client_ids' => $workspace->clients->pluck('id')->toArray(),
                'created_at' => format_date($workspace->created_at, true),
                'updated_at' => format_date($workspace->updated_at, true),
            ];
        }
    }
    if (!function_exists('formatMeeting')) {
        function formatMeeting($meeting)
        {
            $currentDateTime = Carbon::now(config('app.timezone'));
            $status = (($currentDateTime < \Carbon\Carbon::parse($meeting->start_date_time, config('app.timezone'))) ? 'Will start in ' . $currentDateTime->diff(\Carbon\Carbon::parse($meeting->start_date_time, config('app.timezone')))->format('%a days %H hours %I minutes %S seconds') : (($currentDateTime > \Carbon\Carbon::parse($meeting->end_date_time, config('app.timezone')) ? 'Ended before ' . \Carbon\Carbon::parse($meeting->end_date_time, config('app.timezone'))->diff($currentDateTime)->format('%a days %H hours %I minutes %S seconds') : 'Ongoing')));
            return [
                'id' => $meeting->id,
                'title' => $meeting->title,
                'start_date' => \Carbon\Carbon::parse($meeting->start_date_time)->format(get_php_date_time_format()),
                'start_time' => \Carbon\Carbon::parse($meeting->start_date_time)->format('H:i:s'),
                'end_date' => \Carbon\Carbon::parse($meeting->end_date_time)->format(get_php_date_time_format()),
                'end_time' => \Carbon\Carbon::parse($meeting->start_end_time)->format('H:i:s'),
                'users' => $meeting->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'photo' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')
                    ];
                }),
                'user_ids' => $meeting->users->pluck('id')->toArray(),
                'clients' => $meeting->clients->map(function ($client) {
                    return [
                        'id' => $client->id,
                        'first_name' => $client->first_name,
                        'last_name' => $client->last_name,
                        'photo' => $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')
                    ];
                }),
                'client_ids' => $meeting->clients->pluck('id')->toArray(),
                'status' => $status,
                'ongoing' => $status == 'Ongoing' ? 1 : 0,
                'created_at' => format_date($meeting->created_at, true),
                'updated_at' => format_date($meeting->updated_at, true)
            ];
        }
    }

    if (!function_exists('formatNotification')) {
        function formatNotification($notification)
        {
            $labelRead = get_label('read', 'Read');
            $labelUnread = get_label('unread', 'Unread');
            $status = is_null($notification->read_at) ? $labelUnread : $labelRead;

            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'users' => $notification->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'photo' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')
                    ];
                }),
                'clients' => $notification->clients->map(function ($client) {
                    return [
                        'id' => $client->id,
                        'first_name' => $client->first_name,
                        'last_name' => $client->last_name,
                        'photo' => $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')
                    ];
                }),
                'type' => ucfirst(str_replace('_', ' ', $notification->type)),
                'type_id' => $notification->type_id,
                'message' => $notification->message,
                'status' => $status,
                'read_at' => $notification->read_at ? format_date($notification->read_at, true) : null,
                'created_at' => format_date($notification->created_at, true),
                'updated_at' => format_date($notification->updated_at, true)
            ];
        }
    }

    if (!function_exists('formatLeaveRequest')) {
        function formatLeaveRequest($leaveRequest)
        {
            $leaveRequest = LeaveRequest::select(
                'leave_requests.*',
                'users.photo AS user_photo',
                DB::raw('CONCAT(users.first_name, " ", users.last_name) AS user_name'),
                DB::raw('CONCAT(action_users.first_name, " ", action_users.last_name) AS action_by_name'),
                'leave_requests.action_by as action_by_id'
            )
                ->leftJoin('users', 'leave_requests.user_id', '=', 'users.id')
                ->leftJoin('users AS action_users', 'leave_requests.action_by', '=', 'action_users.id')
                ->where('leave_requests.workspace_id', getWorkspaceId())
                ->find($leaveRequest->id);
            // Calculate the duration in hours if both from_time and to_time are provided
            $fromDate = Carbon::parse($leaveRequest->from_date);
            $toDate = Carbon::parse($leaveRequest->to_date);

            $fromDateDayOfWeek = $fromDate->format('D');
            $toDateDayOfWeek = $toDate->format('D');

            if ($leaveRequest->from_time && $leaveRequest->to_time) {
                $duration = 0;
                // Loop through each day
                while ($fromDate->lessThanOrEqualTo($toDate)) {
                    // Create Carbon instances for the start and end times of the leave request for the current day
                    $fromDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $leaveRequest->from_time);
                    $toDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $leaveRequest->to_time);

                    // Calculate the duration for the current day and add it to the total duration
                    $duration += $fromDateTime->diffInMinutes($toDateTime) / 60; // Duration in hours

                    // Move to the next day
                    $fromDate->addDay();
                }
            } else {
                // Calculate the inclusive duration in days
                $duration = $fromDate->diffInDays($toDate) + 1;
            }

            if ($leaveRequest->visible_to_all == 1) {
                $visibleTo = [];
            } else {
                $visibleTo = $leaveRequest->visibleToUsers->isEmpty()
                    ? null
                    : $leaveRequest->visibleToUsers->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'photo' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')
                        ];
                    });
            }
            $visibleToIds = $leaveRequest->visibleToUsers->pluck('id')->toArray();

            return [
                'id' => $leaveRequest->id,
                'user_id' => $leaveRequest->user_id,
                'user_name' => $leaveRequest->user_name,
                'user_photo' => $leaveRequest->user_photo ? asset('storage/' . $leaveRequest->user_photo) : asset('storage/photos/no-image.jpg'),
                'action_by' => $leaveRequest->action_by_name,
                'action_by_id' => $leaveRequest->action_by_id,
                'from_date' => $leaveRequest->from_date,
                'from_time' => Carbon::parse($leaveRequest->from_time)->format('h:i A'),
                'to_date' => $leaveRequest->to_date,
                'to_time' => Carbon::parse($leaveRequest->to_time)->format('h:i A'),
                'type' => $leaveRequest->from_time && $leaveRequest->to_time ? get_label('partial', 'Partial') : get_label('full', 'Full'),
                'leaveVisibleToAll' => $leaveRequest->visible_to_all ? 'on' : 'off',
                'partialLeave' => $leaveRequest->from_time && $leaveRequest->to_time ? 'on' : 'off',
                'duration' => $leaveRequest->from_time && $leaveRequest->to_time ? (float) number_format($duration, 2) : $duration,
                'reason' => $leaveRequest->reason,
                'status' => get_label($leaveRequest->status, ucfirst($leaveRequest->status)),
                'visible_to' => $visibleTo ?? [],
                'visible_to_ids' => $visibleToIds ?? [],
                'created_at' => format_date($leaveRequest->created_at, true),
                'updated_at' => format_date($leaveRequest->updated_at, true),
            ];
        }
    }

    if (!function_exists('formatUser')) {
        function formatUser($user, $isSignup = false)
        {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->getRoleNames()->first(),
                'role_id' => $user->roles()->first()->id,
                'email' => $user->email,
                'phone' => $user->phone,
                'country_code' => $user->country_code,
                'country_iso_code' => $user->country_iso_code,
                'password' => $user->password,
                'password_confirmation' => $user->password,
                'type' => 'member',
                'dob' => $user->dob ? format_date($user->dob) : null,
                'doj' => $user->doj ? format_date($user->doj) : null,
                'address' => $user->address,
                'city' => $user->city,
                'state' => $user->state,
                'country' => $user->country,
                'zip' => $user->zip,
                'profile' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg'),
                'status' => $user->status,
                'fcm_token' => $user->fcm_token,
                'created_at' => format_date($user->created_at, true),
                'updated_at' => format_date($user->updated_at, true),
                'assigned' => $isSignup ? [
                    'projects' => 0,
                    'tasks' => 0
                ] : (
                    isAdminOrHasAllDataAccess('user', $user->id) ? [
                        'projects' => Workspace::find(getWorkspaceId())->projects()->count(),
                        'tasks' => Workspace::find(getWorkspaceId())->tasks()->count(),
                    ] : [
                        'projects' => $user->projects()->count(),
                        'tasks' => $user->tasks()->count()
                    ]
                )
            ];
        }
    }

    if (!function_exists('formatClient')) {
        function formatClient($client, $isSignup = false)
        {
            return [
                'id' => $client->id,
                'first_name' => $client->first_name,
                'last_name' => $client->last_name,
                'role' => $client->getRoleNames()->first(),
                'company' => $client->company,
                'email' => $client->email,
                'phone' => $client->phone,
                'country_code' => $client->country_code,
                'country_iso_code' => $client->country_iso_code,
                'password' => $client->password,
                'password_confirmation' => $client->password,
                'type' => 'client',
                'address' => $client->address ? $client->address : null,
                'city' => $client->city,
                'state' => $client->state,
                'country' => $client->country,
                'zip' => $client->zip,
                'profile' => $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg'),
                'status' => $client->status,
                'fcm_token' => $client->fcm_token,
                'internal_purpose' => $client->internal_purpose,
                'email_verification_mail_sent' => $client->email_verification_mail_sent,
                'email_verified_at' => $client->email_verified_at,
                'created_at' => format_date($client->created_at, true),
                'updated_at' => format_date($client->updated_at, true),
                'assigned' => $isSignup ? [
                    'projects' => 0,
                    'tasks' => 0
                ] : (
                    isAdminOrHasAllDataAccess('client', $client->id) ? [
                        'projects' => Workspace::find(getWorkspaceId())->projects()->count(),
                        'tasks' => Workspace::find(getWorkspaceId())->tasks()->count(),
                    ] : [
                        'projects' => $client->projects()->count(),
                        'tasks' => $client->tasks()->count()
                    ]
                )
            ];
        }
    }

    if (!function_exists('formatNote')) {
        function formatNote($note)
        {
            return [
                'id' => $note->id,
                'title' => $note->title,
                'color' => $note->color,
                'description' => $note->description,
                'workspace_id' => $note->workspace_id,
                'creator_id' => $note->creator_id,
                'created_at' => format_date($note->created_at, true),
                'updated_at' => format_date($note->updated_at, true),
            ];
        }
    }

    if (!function_exists('formatTodo')) {
        function formatTodo($todo)
        {
            return [
                'id' => $todo->id,
                'title' => $todo->title,
                'description' => $todo->description,
                'priority' => $todo->priority,
                'is_completed' => $todo->is_completed,
                'created_at' => format_date($todo->created_at, true),
                'updated_at' => format_date($todo->updated_at, true),
            ];
        }
    }

    if (!function_exists('formatRole')) {
        function formatRole($role)
        {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'created_at' => format_date($role->created_at, true),
                'updated_at' => format_date($role->updated_at, true),
            ];
        }
    }


    if (!function_exists('validate_date_format_and_order')) {
        /**
         * Validate if a date matches the format specified and ensure the start date is before or equal to the end date.
         *
         * @param string|null $startDate
         * @param string|null $endDate
         * @param string|null $format
         * @param string $startDateLabel
         * @param string $endDateLabel
         * @param string $startDateKey
         * @param string $endDateKey
         * @return array
         */
        function validate_date_format_and_order(
            $startDate,
            $endDate,
            $format = null,
            $startDateLabel = 'start date',
            $endDateLabel = 'end date',
            $startDateKey = 'start_date',
            $endDateKey = 'end_date'
        ) {
            $matchFormat = $format ?? get_php_date_time_format();

            $errors = [];

            // Validate start date format
            if ($startDate && !validate_date_format($startDate, $matchFormat)) {
                $errors[$startDateKey][] = 'The ' . $startDateLabel . ' does not follow the format set in settings.';
            }

            // Validate end date format
            if ($endDate && !validate_date_format($endDate, $matchFormat)) {
                $errors[$endDateKey][] = 'The ' . $endDateLabel . ' does not follow the format set in settings.';
            }

            // Validate date order
            if ($startDate && $endDate) {
                $parsedStartDate = \DateTime::createFromFormat($matchFormat, $startDate);
                $parsedEndDate = \DateTime::createFromFormat($matchFormat, $endDate);

                if ($parsedStartDate && $parsedEndDate && $parsedStartDate > $parsedEndDate) {
                    $errors[$startDateKey][] = 'The ' . $startDateLabel . ' must be before or equal to the ' . $endDateLabel . '.';
                }
            }

            return $errors;
        }
    }


    if (!function_exists('validate_date_format')) {
        /**
         * Validate if a date matches the format specified in settings.
         *
         * @param string $date
         * @param string|null $format
         * @return bool
         */
        function validate_date_format($date, $format = null)
        {
            $format = $format ?? get_php_date_time_format();
            $parsedDate = \DateTime::createFromFormat($format, $date);
            return $parsedDate && $parsedDate->format($format) === $date;
        }
    }

    if (!function_exists('validate_currency_format')) {
        function validate_currency_format($value, $label)
        {
            $regex = '/^(?:\d{1,3}(?:,\d{3})*|\d+)(\.\d+)?$/';
            if (!preg_match($regex, $value)) {
                return "The $label format is invalid.";
            }
            return null;
        }
    }

    if (!function_exists('formatApiResponse')) {
        function formatApiResponse($error, $message, array $optionalParams = [], $statusCode = 200)
        {
            $response = [
                'error' => $error,
                'message' => $message,
            ];

            // Merge optional parameters into the response if they are provided
            $response = array_merge($response, $optionalParams);

            return response()->json($response, $statusCode);
        }
    }

    if (!function_exists('isSanctumAuth')) {
        function isSanctumAuth()
        {
            return Auth::guard('web')->check() || Auth::guard('client')->check() ? false : true;
        }
    }

    if (!function_exists('formatApiValidationError')) {
        function formatApiValidationError($isApi, $errors, $defaultMessage = 'Validation errors occurred')
        {
            if ($isApi) {
                $messages = collect($errors)->flatten()->implode("\n");
                return response()->json([
                    'error' => true,
                    'message' => $messages,
                ], 422);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => $defaultMessage,
                    'errors' => $errors,
                ], 422);
            }
        }
    }
}
