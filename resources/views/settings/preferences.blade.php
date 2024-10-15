@extends('layout')
@section('title')
<?= get_label('preferences', 'Preferences') ?>
@endsection
@php
$enabledNotifications = getUserPreferences('notification_preference','enabled_notifications',getAuthenticatedUser(true,true));
@endphp
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-2 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{url('home')}}"><?= get_label('home', 'Home') ?></a>
                    </li>
                    <li class="breadcrumb-item active">
                        <?= get_label('preferences', 'Preferences') ?>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header"><?= get_label('notification_preferences', 'Notification Preferences') ?></h5>
                <div class="card-body">
                    <form action="{{url('save-notification-preferences')}}" class="form-submit-event" method="POST">
                        <input type="hidden" name="dnr">
                        <div class="table-responsive">
                            <table class="table table-striped table-borderless border-bottom">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-check">
                                                <input type="checkbox" id="selectAllPreferences" class="form-check-input">
                                                <label class="form-check-label" for="selectAllPreferences"><?= get_label('select_all', 'Select all') ?></label>
                                            </div>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="text-nowrap">{{get_label('type','Type')}}</th>
                                        <th class="text-nowrap text-center">{{get_label('email','Email')}}</th>
                                        <th class="text-nowrap text-center">{{get_label('sms','SMS')}}</th>
                                        <th class="text-nowrap text-center">{{get_label('whatsapp','WhatsApp')}}</th>
                                        <th class="text-nowrap text-center">{{get_label('system','System')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-nowrap">{{get_label('project_assignment','Project Assignment')}}</td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck1" name="enabled_notifications[]" value="email_project_assignment" {{ (is_array($enabledNotifications) && (in_array('email_project_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck2" name="enabled_notifications[]" value="sms_project_assignment" {{ (is_array($enabledNotifications) && (in_array('sms_project_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck2" name="enabled_notifications[]" value="whatsapp_project_assignment" {{ (is_array($enabledNotifications) && (in_array('whatsapp_project_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck3" name="enabled_notifications[]" value="system_project_assignment" {{ (is_array($enabledNotifications) && (in_array('system_project_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-nowrap">{{get_label('project_status_updation','Project Status Updation')}}</td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck4" name="enabled_notifications[]" value="email_project_status_updation" {{ (is_array($enabledNotifications) && (in_array('email_project_status_updation', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck5" name="enabled_notifications[]" value="sms_project_status_updation" {{ (is_array($enabledNotifications) && (in_array('sms_project_status_updation', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck5" name="enabled_notifications[]" value="whatsapp_project_status_updation" {{ (is_array($enabledNotifications) && (in_array('whatsapp_project_status_updation', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck6" name="enabled_notifications[]" value="system_project_status_updation" {{ (is_array($enabledNotifications) && (in_array('system_project_status_updation', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-nowrap">{{get_label('task_assignment','Task Assignment')}}</td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck7" name="enabled_notifications[]" value="email_task_assignment" {{ (is_array($enabledNotifications) && (in_array('email_task_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck8" name="enabled_notifications[]" value="sms_task_assignment" {{ (is_array($enabledNotifications) && (in_array('sms_task_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck8" name="enabled_notifications[]" value="whatsapp_task_assignment" {{ (is_array($enabledNotifications) && (in_array('whatsapp_task_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck9" name="enabled_notifications[]" value="system_task_assignment" {{ (is_array($enabledNotifications) && (in_array('system_task_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-nowrap">{{get_label('task_status_updation','Task Status Updation')}}</td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck10" name="enabled_notifications[]" value="email_task_status_updation" {{ (is_array($enabledNotifications) && (in_array('email_task_status_updation', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck11" name="enabled_notifications[]" value="sms_task_status_updation" {{ (is_array($enabledNotifications) && (in_array('sms_task_status_updation', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck11" name="enabled_notifications[]" value="whatsapp_task_status_updation" {{ (is_array($enabledNotifications) && (in_array('whatsapp_task_status_updation', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck12" name="enabled_notifications[]" value="system_task_status_updation" {{ (is_array($enabledNotifications) && (in_array('system_task_status_updation', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-nowrap">{{get_label('workspace_assignment','Workspace Assignment')}}</td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck7" name="enabled_notifications[]" value="email_workspace_assignment" {{ (is_array($enabledNotifications) && (in_array('email_workspace_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck8" name="enabled_notifications[]" value="sms_workspace_assignment" {{ (is_array($enabledNotifications) && (in_array('sms_workspace_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck8" name="enabled_notifications[]" value="whatsapp_workspace_assignment" {{ (is_array($enabledNotifications) && (in_array('whatsapp_workspace_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck9" name="enabled_notifications[]" value="system_workspace_assignment" {{ (is_array($enabledNotifications) && (in_array('system_workspace_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-nowrap">{{get_label('meeting_assignment','Meeting Assignment')}}</td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck7" name="enabled_notifications[]" value="email_meeting_assignment" {{ (is_array($enabledNotifications) && (in_array('email_meeting_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck8" name="enabled_notifications[]" value="sms_meeting_assignment" {{ (is_array($enabledNotifications) && (in_array('sms_meeting_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck8" name="enabled_notifications[]" value="whatsapp_meeting_assignment" {{ (is_array($enabledNotifications) && (in_array('whatsapp_meeting_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck9" name="enabled_notifications[]" value="system_meeting_assignment" {{ (is_array($enabledNotifications) && (in_array('system_meeting_assignment', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                    </tr>
                                    @if(!isClient())
                                    @php
                                    $isAdminOrLeaveEditor = is_admin_or_leave_editor();
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap">{{get_label('leave_request_creation','Leave Request Creation')}}</td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck7" name="enabled_notifications[]" value="email_leave_request_creation" {{$isAdminOrLeaveEditor ? '' : 'disabled'}} {{ (is_array($enabledNotifications) && (in_array('email_leave_request_creation', $enabledNotifications) || (empty($enabledNotifications) && $isAdminOrLeaveEditor))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck8" name="enabled_notifications[]" value="sms_leave_request_creation" {{$isAdminOrLeaveEditor ? '' : 'disabled'}} {{ (is_array($enabledNotifications) && (in_array('sms_leave_request_creation', $enabledNotifications) || (empty($enabledNotifications) && $isAdminOrLeaveEditor))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck8" name="enabled_notifications[]" value="whatsapp_leave_request_creation" {{$isAdminOrLeaveEditor ? '' : 'disabled'}} {{ (is_array($enabledNotifications) && (in_array('whatsapp_leave_request_creation', $enabledNotifications) || (empty($enabledNotifications) && $isAdminOrLeaveEditor))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck9" name="enabled_notifications[]" value="system_leave_request_creation" {{$isAdminOrLeaveEditor ? '' : 'disabled'}} {{ (is_array($enabledNotifications) && (in_array('system_leave_request_creation', $enabledNotifications) || (empty($enabledNotifications) && $isAdminOrLeaveEditor))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-nowrap">{{get_label('leave_request_status_updation','Leave Request Status Updation')}}</td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck10" name="enabled_notifications[]" value="email_leave_request_status_updation" {{ (is_array($enabledNotifications) && (in_array('email_leave_request_status_updation', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck11" name="enabled_notifications[]" value="sms_leave_request_status_updation" {{ (is_array($enabledNotifications) && (in_array('sms_leave_request_status_updation', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck11" name="enabled_notifications[]" value="whatsapp_leave_request_status_updation" {{ (is_array($enabledNotifications) && (in_array('whatsapp_leave_request_status_updation', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck12" name="enabled_notifications[]" value="system_leave_request_status_updation" {{ (is_array($enabledNotifications) && (in_array('system_leave_request_status_updation', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-nowrap">{{get_label('team_member_on_leave_alert','Team Member on Leave Alert')}}</td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck13" name="enabled_notifications[]" value="email_team_member_on_leave_alert" {{ (is_array($enabledNotifications) && (in_array('email_team_member_on_leave_alert', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck14" name="enabled_notifications[]" value="sms_team_member_on_leave_alert" {{ (is_array($enabledNotifications) && (in_array('sms_team_member_on_leave_alert', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck15" name="enabled_notifications[]" value="whatsapp_team_member_on_leave_alert" {{ (is_array($enabledNotifications) && (in_array('whatsapp_team_member_on_leave_alert', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" id="defaultCheck16" name="enabled_notifications[]" value="system_team_member_on_leave_alert" {{ (is_array($enabledNotifications) && (in_array('system_team_member_on_leave_alert', $enabledNotifications) || empty($enabledNotifications))) ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2" id="submit_btn"><?= get_label('update', 'Update') ?></button>
                            <button type="reset" class="btn btn-outline-secondary"><?= get_label('cancel', 'Cancel') ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection