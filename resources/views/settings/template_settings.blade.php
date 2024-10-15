@extends('layout')
@section('title')
<?= get_label('notification_templates', 'Notification Templates') ?>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-2 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{url('home')}}"><?= get_label('home', 'Home') ?></a>
                    </li>
                    <li class="breadcrumb-item">
                        <?= get_label('settings', 'Settings') ?>
                    </li>
                    <li class="breadcrumb-item active">
                        <?= get_label('notification_templates', 'Notification Templates') ?>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="demo-inline-spacing mt-3">
                        <!-- Tab Switcher for Email and SMS Templates -->
                        <div class="list-group list-group-horizontal-md text-md-center">
                            <a class="list-group-item list-group-item-action active" id="email-tab" data-bs-toggle="list" href="#email-templates"><?= get_label('email', 'Email') ?></a>
                            <a class="list-group-item list-group-item-action" id="sms-tab" data-bs-toggle="list" href="#sms-templates"><?= get_label('sms', 'SMS') ?></a>
                            <a class="list-group-item list-group-item-action" id="whatsapp-tab" data-bs-toggle="list" href="#whatsapp-templates"><?= get_label('whatsapp', 'WhatsApp') ?></a>
                            <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#system-templates"><?= get_label('system', 'System') ?></a>
                        </div>
                        <!-- Main Tab Content -->
                        <div class="tab-content px-0">
                            <!-- Email Templates Tab Content -->
                            <div class="tab-pane fade show active" id="email-templates">
                                <div class="alert alert-primary">{{get_label('default_email_template_info','A Default Subject and Message Will Be Used if a Specific Email Notification Template Is Not Set.')}}</div>
                                <div class="list-group list-group-horizontal-md text-md-center">
                                    <a class="list-group-item list-group-item-action active" id="email-account-creation-list-item" data-bs-toggle="list" href="#email-account-creation">{{get_label('account_creation','Account creation')}}</a>
                                    <a class="list-group-item list-group-item-action" id="email-verify-email-list-item" data-bs-toggle="list" href="#email-verify-email">{{get_label('email_verification','Email verification')}}</a>
                                    <a class="list-group-item list-group-item-action" id="email-forgot-password-list-item" data-bs-toggle="list" href="#email-forgot-password">{{get_label('forgot_password','Forgot password')}}</a>
                                    <a class="list-group-item list-group-item-action" id="email-project-list-item" data-bs-toggle="list" href="#email-project">{{get_label('project','Project')}}</a>
                                    <a class="list-group-item list-group-item-action" id="email-task-list-item" data-bs-toggle="list" href="#email-task">{{get_label('task','Task')}}</a>
                                    <a class="list-group-item list-group-item-action" id="email-workspace-assignment-list-item" data-bs-toggle="list" href="#email-workspace-assignment">{{get_label('workspace_assignment','Workspace assignment')}}</a>
                                    <a class="list-group-item list-group-item-action" id="email-meeting-assignment-list-item" data-bs-toggle="list" href="#email-meeting-assignment">{{get_label('meeting_assignment','Meeting assignment')}}</a>
                                    <a class="list-group-item list-group-item-action" id="email-leave-request-list-item" data-bs-toggle="list" href="#email-leave-request">{{get_label('leave_request','Leave Request')}}</a>
                                </div>
                                <div class="tab-content px-0 mt-0">
                                    <div class="tab-pane fade show active" id="email-account-creation">
                                        @php
                                        $account_creation_template = App\Models\Template::where('type', 'email')
                                        ->where('name', 'account_creation')
                                        ->first();
                                        @endphp
                                        <small class="text-light fw-semibold mb-1"><?= get_label('account_creation_email_info', 'This template will be used for the email notification sent to notify users/clients about the successful creation of their account.') ?></small>
                                        <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="type" value="email">
                                            <input type="hidden" name="name" value="account_creation">
                                            <input type="hidden" name="dnr">
                                            <label class="form-label mt-3"><?= get_label('subject', 'Subject') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {FIRST_NAME}, {LAST_NAME}, {COMPANY_TITLE})</small></label>
                                            <input type="text" class="form-control mb-3" name="subject" value="{{ $account_creation_template->subject ?? '' }}" placeholder="{{get_label('please_enter_email_subject','Please enter email subject')}}">
                                            <label class="form-label"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                            <textarea id="email_account_creation" name="content" class="form-control">{{ $account_creation_template->content ?? '' }}</textarea>
                                            <div class="col-md-6 mt-4 mb-5">
                                                <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('account_creation_email_will_not_sent', 'If Deactive, account creation email won\'t be sent') ?></small>)</label>
                                                <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                    <input type="radio" class="btn-check" id="email_account_creation_status_active" name="status" value="1" {{ !($account_creation_template) || $account_creation_template && $account_creation_template->status == 1 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="email_account_creation_status_active">{{ get_label('active', 'Active') }}</label>
                                                    <input type="radio" class="btn-check" id="email_account_creation_status_deactive" name="status" value="0" {{ $account_creation_template && $account_creation_template->status == 0 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="email_account_creation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                </div>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                <button type="button" class="btn btn-warning restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>{{get_label('placeholder','Placeholder')}}</th>
                                                            <th>{{get_label('action','Action')}}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="copyText">{FIRST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(0)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{LAST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(1)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{USER_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(2)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{PASSWORD}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(3)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(4)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_LOGO}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(5)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{SITE_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(6)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{CURRENT_YEAR}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(7)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="email-verify-email">
                                        @php
                                        $verify_email_template = App\Models\Template::where('type', 'email')
                                        ->where('name', 'verify_email')
                                        ->first();
                                        @endphp
                                        <small class="text-light fw-semibold mb-1"><?= get_label('verify_user_client_email_info', 'This template will be used for the email sent for verifying new user/client creation.') ?></small>
                                        <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="type" value="email">
                                            <input type="hidden" name="name" value="verify_email">
                                            <input type="hidden" name="status" value="1">
                                            <input type="hidden" name="dnr">
                                            <label class="form-label mt-3"><?= get_label('subject', 'Subject') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {FIRST_NAME}, {LAST_NAME}, {COMPANY_TITLE})</small></label>
                                            <input type="text" class="form-control mb-3" name="subject" value="{{ $verify_email_template->subject ?? '' }}" placeholder="{{get_label('please_enter_email_subject','Please enter email subject')}}">
                                            <label class="form-label"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                            <textarea id="email_verify_email" name="content" class="form-control">{{ $verify_email_template->content ?? '' }}</textarea>
                                            <div class="text-center mt-3">
                                                <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                <button type="button" class="btn btn-warning restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>{{get_label('placeholder','Placeholder')}}</th>
                                                            <th>{{get_label('action','Action')}}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="copyText">{FIRST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(8)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{LAST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(9)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{VERIFY_EMAIL_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(10)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(11)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_LOGO}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(12)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{SITE_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(13)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{CURRENT_YEAR}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(14)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="email-forgot-password">
                                        @php
                                        $forgot_password_template = App\Models\Template::where('type', 'email')
                                        ->where('name', 'forgot_password')
                                        ->first();
                                        @endphp
                                        <small class="text-light fw-semibold mb-1"><?= get_label('forgot_password_email_info', 'This template will be used for the email notification sent to users/clients to reset their password if they have forgotten it.') ?></small>
                                        <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="type" value="email">
                                            <input type="hidden" name="name" value="forgot_password">
                                            <input type="hidden" name="status" value="1">
                                            <input type="hidden" name="dnr">
                                            <label class="form-label mt-3"><?= get_label('subject', 'Subject') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {FIRST_NAME}, {LAST_NAME}, {COMPANY_TITLE})</small></label>
                                            <input type="text" class="form-control mb-3" name="subject" value="{{ $forgot_password_template->subject ?? '' }}" placeholder="{{get_label('please_enter_email_subject','Please enter email subject')}}">
                                            <label class="form-label"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                            <textarea id="email_forgot_password" name="content" class="form-control">{{ $forgot_password_template->content ?? '' }}</textarea>
                                            <div class="text-center mt-3">
                                                <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                <button type="button" class="btn btn-warning restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>{{get_label('placeholder','Placeholder')}}</th>
                                                            <th>{{get_label('action','Action')}}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="copyText">{FIRST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(15)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{LAST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(16)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{RESET_PASSWORD_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(17)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(18)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_LOGO}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(19)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{SITE_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(20)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{CURRENT_YEAR}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(21)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="email-project">
                                        <div class="list-group list-group-horizontal-md text-md-center">
                                            <a class="list-group-item list-group-item-action active" id="email-project-assignment-list-item" data-bs-toggle="list" href="#email-project-assignment">{{get_label('assignment','Assignment')}}</a>
                                            <a class="list-group-item list-group-item-action" id="email-project-status-updation-list-item" data-bs-toggle="list" href="#email-project-status-updation">{{get_label('status_updation','Status Updation')}}</a>
                                        </div>
                                        <div class="tab-content px-0">
                                            <div class="tab-pane fade show active" id="email-project-assignment">
                                                @php
                                                $project_assignment_template = App\Models\Template::where('type', 'email')
                                                ->where('name', 'project_assignment')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('project_assignment_email_info', 'This template will be used for the email notification sent to users/clients when they are assigned a project.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="email">
                                                    <input type="hidden" name="name" value="project_assignment">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('subject', 'Subject') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {PROJECT_ID}, {PROJECT_TITLE}, {FIRST_NAME}, {LAST_NAME}, {COMPANY_TITLE})</small></label>
                                                    <input type="text" class="form-control mb-3" name="subject" value="{{ $project_assignment_template->subject ?? '' }}" placeholder="{{get_label('please_enter_email_subject','Please enter email subject')}}">
                                                    <label class="form-label"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="email_project_assignment" name="content" class="form-control">{{ $project_assignment_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('project_assignment_email_will_not_sent', 'If Deactive, project assignment email won\'t be sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="email_project_assignment_status_active" name="status" value="1" {{ !($project_assignment_template) || $project_assignment_template && $project_assignment_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="email_project_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="email_project_assignment_status_deactive" name="status" value="0" {{ $project_assignment_template && $project_assignment_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="email_project_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(22)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(23)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(24)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(25)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(26)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(27)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_LOGO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(28)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(29)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{CURRENT_YEAR}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(30)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="email-project-status-updation">
                                                @php
                                                $project_status_updation_template = App\Models\Template::where('type', 'email')
                                                ->where('name', 'project_status_updation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('project_status_updation_email_info', 'This Template Will Be Used for the Email notification sent to the Users/Clients Upon the Status Updation of a Project.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="email">
                                                    <input type="hidden" name="name" value="project_status_updation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('subject', 'Subject') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {PROJECT_ID}, {PROJECT_TITLE}, {FIRST_NAME}, {LAST_NAME}, {UPDATER_FIRST_NAME}, {UPDATER_LAST_NAME}, {OLD_STATUS}, {NEW_STATUS}, {COMPANY_TITLE})</small></label>
                                                    <input type="text" class="form-control mb-3" name="subject" value="{{ $project_status_updation_template->subject ?? '' }}" placeholder="{{get_label('please_enter_email_subject','Please enter email subject')}}">
                                                    <label class="form-label"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="email_project_status_updation" name="content" class="form-control">{{ $project_status_updation_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('project_status_updation_email_will_not_sent', 'If Deactive, Project Status Updation Email won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="email_project_status_updation_status_active" name="status" value="1" {{ !($project_status_updation_template) || $project_status_updation_template && $project_status_updation_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="email_project_status_updation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="email_project_status_updation_status_deactive" name="status" value="0" {{ $project_status_updation_template && $project_status_updation_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="email_project_status_updation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(22)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(23)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(24)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(25)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{OLD_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{OLD_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{NEW_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{NEW_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(26)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(27)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_LOGO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(28)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(29)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{CURRENT_YEAR}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(30)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="email-task">
                                        <div class="list-group list-group-horizontal-md text-md-center">
                                            <a class="list-group-item list-group-item-action active" id="email-task-assignment-list-item" data-bs-toggle="list" href="#email-task-assignment">{{get_label('assignment','Assignment')}}</a>
                                            <a class="list-group-item list-group-item-action" id="email-task-status-updation-list-item" data-bs-toggle="list" href="#email-task-status-updation">{{get_label('status_updation','Status Updation')}}</a>
                                        </div>
                                        <div class="tab-content px-0">
                                            <div class="tab-pane fade show active" id="email-task-assignment">
                                                @php
                                                $task_assignment_template = App\Models\Template::where('type', 'email')
                                                ->where('name', 'task_assignment')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('task_assignment_email_info', 'This template will be used for the email notification sent to users/clients when they are assigned a task.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="email">
                                                    <input type="hidden" name="name" value="task_assignment">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('subject', 'Subject') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {TASK_ID}, {TASK_TITLE}, {FIRST_NAME}, {LAST_NAME}, {COMPANY_TITLE})</small></label>
                                                    <input type="text" class="form-control mb-3" name="subject" value="{{ $task_assignment_template->subject ?? '' }}" placeholder="{{get_label('please_enter_email_subject','Please enter email subject')}}">
                                                    <label class="form-label"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="email_task_assignment" name="content" class="form-control">{{ $task_assignment_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('task_assignment_email_will_not_sent', 'If Deactive, task assignment email won\'t be sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="email_task_assignment_status_active" name="status" value="1" {{ !($task_assignment_template) || $task_assignment_template && $task_assignment_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="email_task_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="email_task_assignment_status_deactive" name="status" value="0" {{ $task_assignment_template && $task_assignment_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="email_task_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{TASK_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(31)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(32)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(33)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(34)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(35)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(36)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_LOGO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(37)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(38)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{CURRENT_YEAR}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(39)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="email-task-status-updation">
                                                @php
                                                $task_status_updation_template = App\Models\Template::where('type', 'email')
                                                ->where('name', 'task_status_updation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('task_status_updation_email_info', 'This Template Will Be Used for the Email notification sent to the Users/Clients Upon the Status Updation of a Task.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="email">
                                                    <input type="hidden" name="name" value="task_status_updation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('subject', 'Subject') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {TASK_ID}, {TASK_TITLE}, {FIRST_NAME}, {LAST_NAME}, {UPDATER_FIRST_NAME}, {UPDATER_LAST_NAME}, {OLD_STATUS}, {NEW_STATUS}, {COMPANY_TITLE})</small></label>
                                                    <input type="text" class="form-control mb-3" name="subject" value="{{ $task_status_updation_template->subject ?? '' }}" placeholder="{{get_label('please_enter_email_subject','Please enter email subject')}}">
                                                    <label class="form-label"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="email_task_status_updation" name="content" class="form-control">{{ $task_status_updation_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('project_status_updation_email_will_not_sent', 'If Deactive, Project Status Updation Email won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="email_task_status_updation_status_active" name="status" value="1" {{ !($task_status_updation_template) || $task_status_updation_template && $task_status_updation_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="email_task_status_updation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="email_task_status_updation_status_deactive" name="status" value="0" {{ $task_status_updation_template && $task_status_updation_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="email_task_status_updation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{TASK_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(22)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(23)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(24)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(25)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{OLD_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{OLD_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{NEW_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{NEW_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(27)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_LOGO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(28)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(29)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{CURRENT_YEAR}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard(30)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="email-workspace-assignment">
                                        @php
                                        $workspace_assignment_template = App\Models\Template::where('type', 'email')
                                        ->where('name', 'workspace_assignment')
                                        ->first();
                                        @endphp
                                        <small class="text-light fw-semibold mb-1"><?= get_label('workspace_assignment_email_info', 'This template will be used for the email notification sent to users/clients when they are added to a workspace.') ?></small>
                                        <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="type" value="email">
                                            <input type="hidden" name="name" value="workspace_assignment">
                                            <input type="hidden" name="dnr">
                                            <label class="form-label mt-3"><?= get_label('subject', 'Subject') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {WORKSPACE_ID}, {WORKSPACE_TITLE}, {FIRST_NAME}, {LAST_NAME}, {COMPANY_TITLE})</small></label>
                                            <input type="text" class="form-control mb-3" name="subject" value="{{ $workspace_assignment_template->subject ?? '' }}" placeholder="{{get_label('please_enter_email_subject','Please enter email subject')}}">
                                            <label class="form-label"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                            <textarea id="email_workspace_assignment" name="content" class="form-control">{{ $workspace_assignment_template->content ?? '' }}</textarea>
                                            <div class="col-md-6 mt-4 mb-5">
                                                <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('workspace_assignment_email_will_not_sent', 'If Deactive, workspace assignment email won\'t be sent') ?></small>)</label>
                                                <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                    <input type="radio" class="btn-check" id="email_workspace_assignment_status_active" name="status" value="1" {{ !($workspace_assignment_template) || $workspace_assignment_template && $workspace_assignment_template->status == 1 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="email_workspace_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                    <input type="radio" class="btn-check" id="email_workspace_assignment_status_deactive" name="status" value="0" {{ $workspace_assignment_template && $workspace_assignment_template->status == 0 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="email_workspace_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                </div>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                <button type="button" class="btn btn-warning restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>{{get_label('placeholder','Placeholder')}}</th>
                                                            <th>{{get_label('action','Action')}}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="copyText">{WORKSPACE_ID}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(40)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{WORKSPACE_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(41)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{WORKSPACE_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(42)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{FIRST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(43)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{LAST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(44)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(45)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_LOGO}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(46)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{SITE_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(47)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{CURRENT_YEAR}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(48)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="email-meeting-assignment">
                                        @php
                                        $meeting_assignment_template = App\Models\Template::where('type', 'email')
                                        ->where('name', 'meeting_assignment')
                                        ->first();
                                        @endphp
                                        <small class="text-light fw-semibold mb-1"><?= get_label('meeting_assignment_email_info', 'This template will be used for the email notification sent to users/clients when they are added to a meeting.') ?></small>
                                        <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="type" value="email">
                                            <input type="hidden" name="name" value="meeting_assignment">
                                            <input type="hidden" name="dnr">
                                            <label class="form-label mt-3"><?= get_label('subject', 'Subject') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {MEETING_ID}, {MEETING_TITLE}, {FIRST_NAME}, {LAST_NAME}, {COMPANY_TITLE})</small></label>
                                            <input type="text" class="form-control mb-3" name="subject" value="{{ $meeting_assignment_template->subject ?? '' }}" placeholder="{{get_label('please_enter_email_subject','Please enter email subject')}}">
                                            <label class="form-label"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                            <textarea id="email_meeting_assignment" name="content" class="form-control">{{ $meeting_assignment_template->content ?? '' }}</textarea>
                                            <div class="col-md-6 mt-4 mb-5">
                                                <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('meeting_assignment_email_will_not_sent', 'If Deactive, meeting assignment email won\'t be sent') ?></small>)</label>
                                                <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                    <input type="radio" class="btn-check" id="email_meeting_assignment_status_active" name="status" value="1" {{ !($meeting_assignment_template) || $meeting_assignment_template && $meeting_assignment_template->status == 1 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="email_meeting_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                    <input type="radio" class="btn-check" id="email_meeting_assignment_status_deactive" name="status" value="0" {{ $meeting_assignment_template && $meeting_assignment_template->status == 0 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="email_meeting_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                </div>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                <button type="button" class="btn btn-warning restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>{{get_label('placeholder','Placeholder')}}</th>
                                                            <th>{{get_label('action','Action')}}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="copyText">{MEETING_ID}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(49)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{MEETING_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(50)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{MEETING_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(51)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{FIRST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(52)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{LAST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(53)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(54)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_LOGO}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(55)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{SITE_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(56)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{CURRENT_YEAR}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(57)" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="email-leave-request">
                                        <div class="list-group list-group-horizontal-md text-md-center">
                                            <a class="list-group-item list-group-item-action active" id="email-leave-request-creation-list-item" data-bs-toggle="list" href="#email-leave-request-creation">{{get_label('creation','Creation')}}</a>
                                            <a class="list-group-item list-group-item-action" id="email-leave-request-status-updation-list-item" data-bs-toggle="list" href="#email-leave-request-status-updation">{{get_label('status_updation','Status Updation')}}</a>
                                            <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#email-team-member-on-leave-alert">{{get_label('team_member_on_leave_alert','Team Member on Leave Alert')}}</a>
                                        </div>
                                        <div class="tab-content px-0">
                                            <div class="tab-pane fade show active" id="email-leave-request-creation">
                                                @php
                                                $leave_request_creation_template = App\Models\Template::where('type', 'email')
                                                ->where('name', 'leave_request_creation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('leave_request_creation_email_info', 'This Template Will Be Used for the Email notification sent to the Admin and Leave Editors Upon the Creation of a Leave Request.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="email">
                                                    <input type="hidden" name="name" value="leave_request_creation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('subject', 'Subject') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {ID}, {STATUS}, {REQUESTEE_FIRST_NAME}, {REQUESTEE_LAST_NAME}, {COMPANY_TITLE})</small></label>
                                                    <input type="text" class="form-control mb-3" name="subject" value="{{ $leave_request_creation_template->subject ?? '' }}" placeholder="{{get_label('please_enter_email_subject','Please enter email subject')}}">
                                                    <label class="form-label"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="email_leave_request_creation" name="content" class="form-control">{{ $leave_request_creation_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('leave_request_creation_email_will_not_sent', 'If Deactive, Leave Request Creation Email Won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="email_leave_request_creation_status_active" name="status" value="1" {{ !($leave_request_creation_template) || $leave_request_creation_template && $leave_request_creation_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="email_leave_request_creation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="email_leave_request_creation_status_deactive" name="status" value="0" {{ $leave_request_creation_template && $leave_request_creation_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="email_leave_request_creation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TYPE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TYPE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FROM}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FROM}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TO}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{DURATION}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{DURATION}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REASON}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REASON}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_LOGO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_LOGO}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{CURRENT_YEAR}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{CURRENT_YEAR}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="email-leave-request-status-updation">
                                                @php
                                                $leave_request_status_updation_template = App\Models\Template::where('type', 'email')
                                                ->where('name', 'leave_request_status_updation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('leave_request_status_updation_email_info', 'This Template Will Be Used for the Email notification sent to the Admin/Leave Editors/Requestee Upon the Status Updation of a Leave Request.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="email">
                                                    <input type="hidden" name="name" value="leave_request_status_updation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('subject', 'Subject') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {ID}, {OLD_STATUS}, {NEW_STATUS}, {COMPANY_TITLE})</small></label>
                                                    <input type="text" class="form-control mb-3" name="subject" value="{{ $leave_request_status_updation_template->subject ?? '' }}" placeholder="{{get_label('please_enter_email_subject','Please enter email subject')}}">
                                                    <label class="form-label"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="email_leave_request_status_updation" name="content" class="form-control">{{ $leave_request_status_updation_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('leave_request_status_updation_email_will_not_sent', 'If Deactive, Leave Request Status Updation Email Won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="email_leave_request_status_updation_status_active" name="status" value="1" {{ !($leave_request_status_updation_template) || $leave_request_status_updation_template && $leave_request_status_updation_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="email_leave_request_status_updation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="email_leave_request_status_updation_status_deactive" name="status" value="0" {{ $leave_request_status_updation_template && $leave_request_status_updation_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="email_leave_request_status_updation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TYPE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TYPE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FROM}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FROM}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TO}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{DURATION}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{DURATION}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{OLD_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{OLD_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{NEW_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{NEW_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REASON}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REASON}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_LOGO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_LOGO}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{CURRENT_YEAR}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{CURRENT_YEAR}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="email-team-member-on-leave-alert">
                                                @php
                                                $team_member_on_leave_alert_template = App\Models\Template::where('type', 'email')
                                                ->where('name', 'team_member_on_leave_alert')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('team_member_on_leave_alert_email_info', 'This template will be used for the email notification sent to team members upon approval of a leave request, informing them about the absence of the requestee.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="email">
                                                    <input type="hidden" name="name" value="team_member_on_leave_alert">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('subject', 'Subject') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {ID}, {REQUESTEE_FIRST_NAME}, {REQUESTEE_LAST_NAME}, {COMPANY_TITLE})</small></label>
                                                    <input type="text" class="form-control mb-3" name="subject" value="{{ $team_member_on_leave_alert_template->subject ?? '' }}" placeholder="{{get_label('please_enter_email_subject','Please enter email subject')}}">
                                                    <label class="form-label"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="email_team_member_on_leave_alert" name="content" class="form-control">{{ $team_member_on_leave_alert_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('team_member_on_leave_alert_email_will_not_sent', 'If Deactive, Team Member on Leave Alert Email Won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="email_team_member_on_leave_alert_status_active" name="status" value="1" {{ !($team_member_on_leave_alert_template) || $team_member_on_leave_alert_template && $team_member_on_leave_alert_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="email_team_member_on_leave_alert_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="email_team_member_on_leave_alert_status_deactive" name="status" value="0" {{ $team_member_on_leave_alert_template && $team_member_on_leave_alert_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="email_team_member_on_leave_alert_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TYPE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TYPE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FROM}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FROM}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TO}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{DURATION}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{DURATION}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_LOGO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_LOGO}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{CURRENT_YEAR}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{CURRENT_YEAR}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- SMS Templates Tab Content -->
                            <div class="tab-pane fade" id="sms-templates">
                                <div class="alert alert-primary">{{get_label('default_sms_template_info','A Default Message Will Be Used if a Specific SMS Notification Template Is Not Set.')}}</div>
                                <div class="list-group list-group-horizontal-md text-md-center">
                                    <a class="list-group-item list-group-item-action active" id="sms-project-list-item" data-bs-toggle="list" href="#sms-project">{{get_label('project','Project')}}</a>
                                    <a class="list-group-item list-group-item-action" id="sms-task-list-item" data-bs-toggle="list" href="#sms-task">{{get_label('task','Task')}}</a>
                                    <a class="list-group-item list-group-item-action" id="sms-workspace-assignment-list-item" data-bs-toggle="list" href="#sms-workspace-assignment">{{get_label('workspace_assignment','Workspace assignment')}}</a>
                                    <a class="list-group-item list-group-item-action" id="sms-meeting-assignment-list-item" data-bs-toggle="list" href="#sms-meeting-assignment">{{get_label('meeting_assignment','Meeting assignment')}}</a>
                                    <a class="list-group-item list-group-item-action" id="sms-leave-request-list-item" data-bs-toggle="list" href="#sms-leave-request">{{get_label('leave_request','Leave Request')}}</a>
                                </div>
                                <div class="tab-content px-0">
                                    <div class="tab-pane fade show active" id="sms-project">
                                        <div class="list-group list-group-horizontal-md text-md-center">
                                            <a class="list-group-item list-group-item-action active" id="sms-project-assignment-list-item" data-bs-toggle="list" href="#sms-project-assignment">{{get_label('assignment','Assignment')}}</a>
                                            <a class="list-group-item list-group-item-action" id="sms-project-status-updation-list-item" data-bs-toggle="list" href="#sms-project-status-updation">{{get_label('status_updation','Status Updation')}}</a>
                                        </div>
                                        <div class="tab-content px-0">
                                            <div class="tab-pane fade show active" id="sms-project-assignment">
                                                @php
                                                $sms_project_assignment_template = App\Models\Template::where('type', 'sms')
                                                ->where('name', 'project_assignment')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('project_assignment_sms_info', 'This template will be used for the SMS notification sent to users/clients when they are assigned a project.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="sms">
                                                    <input type="hidden" name="name" value="project_assignment">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="sms_project_assignment" name="content" class="form-control" rows="5">{{ $sms_project_assignment_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('project_assignment_sms_will_not_sent', 'If Deactive, project assignment SMS won\'t be sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="sms_project_assignment_status_active" name="status" value="1" {{ !($sms_project_assignment_template) || $sms_project_assignment_template && $sms_project_assignment_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="sms_project_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="sms_project_assignment_status_deactive" name="status" value="0" {{ $sms_project_assignment_template && $sms_project_assignment_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="sms_project_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="sms-project-status-updation">
                                                @php
                                                $sms_project_status_updation_template = App\Models\Template::where('type', 'sms')
                                                ->where('name', 'project_status_updation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('project_status_updation_sms_info', 'This Template Will Be Used for the SMS notification sent to the Users/Clients Upon the Status Updation of a Project.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="sms">
                                                    <input type="hidden" name="name" value="project_status_updation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="sms_project_status_updation" name="content" class="form-control" rows="5">{{ $sms_project_status_updation_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('project_status_updation_sms_will_not_sent', 'If Deactive, Project Status Updation SMS won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="sms_project_status_updation_status_active" name="status" value="1" {{ !($sms_project_status_updation_template) || $sms_project_status_updation_template && $sms_project_status_updation_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="sms_project_status_updation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="sms_project_status_updation_status_deactive" name="status" value="0" {{ $sms_project_status_updation_template && $sms_project_status_updation_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="sms_project_status_updation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{OLD_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{OLD_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{NEW_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{NEW_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="sms-task">
                                        <div class="list-group list-group-horizontal-md text-md-center">
                                            <a class="list-group-item list-group-item-action active" id="sms-task-assignment-list-item" data-bs-toggle="list" href="#sms-task-assignment">{{get_label('assignment','Assignment')}}</a>
                                            <a class="list-group-item list-group-item-action" id="sms-task-status-updation-list-item" data-bs-toggle="list" href="#sms-task-status-updation">{{get_label('status_updation','Status Updation')}}</a>
                                        </div>
                                        <div class="tab-content px-0">
                                            <div class="tab-pane fade show active" id="sms-task-assignment">
                                                @php
                                                $sms_task_assignment_template = App\Models\Template::where('type', 'sms')
                                                ->where('name', 'task_assignment')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('task_assignment_sms_info', 'This template will be used for the SMS notification sent to users/clients when they are assigned a task.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="sms">
                                                    <input type="hidden" name="name" value="task_assignment">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="sms_task_assignment" name="content" class="form-control" rows="5">{{ $sms_task_assignment_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('task_assignment_sms_will_not_sent', 'If Deactive, task assignment SMS won\'t be sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="sms_task_assignment_status_active" name="status" value="1" {{ !($sms_task_assignment_template) || $sms_task_assignment_template && $sms_task_assignment_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="sms_task_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="sms_task_assignment_status_deactive" name="status" value="0" {{ $sms_task_assignment_template && $sms_task_assignment_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="sms_task_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{TASK_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="sms-task-status-updation">
                                                @php
                                                $sms_task_status_updation_template = App\Models\Template::where('type', 'sms')
                                                ->where('name', 'task_status_updation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('task_status_updation_sms_info', 'This Template Will Be Used for the SMS notification sent to the Users/Clients Upon the Status Updation of a Task.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="sms">
                                                    <input type="hidden" name="name" value="task_status_updation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="sms_task_status_updation" name="content" class="form-control" rows="5">{{ $sms_task_status_updation_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('task_status_updation_sms_will_not_sent', 'If Deactive, Task Status Updation SMS won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="sms_task_status_updation_status_active" name="status" value="1" {{ !($sms_task_status_updation_template) || $sms_task_status_updation_template && $sms_task_status_updation_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="sms_task_status_updation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="sms_task_status_updation_status_deactive" name="status" value="0" {{ $sms_task_status_updation_template && $sms_task_status_updation_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="sms_task_status_updation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{TASK_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{OLD_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{OLD_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{NEW_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{NEW_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="sms-workspace-assignment">
                                        @php
                                        $sms_workspace_assignment_template = App\Models\Template::where('type', 'sms')
                                        ->where('name', 'workspace_assignment')
                                        ->first();
                                        @endphp
                                        <small class="text-light fw-semibold mb-1"><?= get_label('workspace_assignment_sms_info', 'This template will be used for the SMS notification sent to users/clients when they are added to a workspace.') ?></small>
                                        <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="type" value="sms">
                                            <input type="hidden" name="name" value="workspace_assignment">
                                            <input type="hidden" name="dnr">
                                            <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                            <textarea id="sms_workspace_assignment" name="content" class="form-control" rows="5">{{ $sms_workspace_assignment_template->content ?? '' }}</textarea>
                                            <div class="col-md-6 mt-4 mb-5">
                                                <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('workspace_assignment_sms_will_not_sent', 'If Deactive, workspace assignment SMS won\'t be sent') ?></small>)</label>
                                                <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                    <input type="radio" class="btn-check" id="sms_workspace_assignment_status_active" name="status" value="1" {{ !($sms_workspace_assignment_template) || $sms_workspace_assignment_template && $sms_workspace_assignment_template->status == 1 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="sms_workspace_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                    <input type="radio" class="btn-check" id="sms_workspace_assignment_status_deactive" name="status" value="0" {{ $sms_workspace_assignment_template && $sms_workspace_assignment_template->status == 0 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="sms_workspace_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                </div>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>{{get_label('placeholder','Placeholder')}}</th>
                                                            <th>{{get_label('action','Action')}}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="copyText">{WORKSPACE_ID}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{WORKSPACE_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{WORKSPACE_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{WORKSPACE_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{FIRST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{LAST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{WORKSPACE_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{WORKSPACE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{SITE_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="sms-meeting-assignment">
                                        @php
                                        $sms_meeting_assignment_template = App\Models\Template::where('type', 'sms')
                                        ->where('name', 'meeting_assignment')
                                        ->first();
                                        @endphp
                                        <small class="text-light fw-semibold mb-1"><?= get_label('meeting_assignment_sms_info', 'This template will be used for the SMS notification sent to users/clients when they are added to a meeting.') ?></small>
                                        <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="type" value="sms">
                                            <input type="hidden" name="name" value="meeting_assignment">
                                            <input type="hidden" name="dnr">
                                            <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                            <textarea id="sms_meeting_assignment" name="content" class="form-control" rows="5">{{ $sms_meeting_assignment_template->content ?? '' }}</textarea>
                                            <div class="col-md-6 mt-4 mb-5">
                                                <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('meeting_assignment_sms_will_not_sent', 'If Deactive, meeting assignment SMS won\'t be sent') ?></small>)</label>
                                                <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                    <input type="radio" class="btn-check" id="sms_meeting_assignment_status_active" name="status" value="1" {{ !($sms_meeting_assignment_template) || $sms_meeting_assignment_template && $sms_meeting_assignment_template->status == 1 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="sms_meeting_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                    <input type="radio" class="btn-check" id="sms_meeting_assignment_status_deactive" name="status" value="0" {{ $sms_meeting_assignment_template && $sms_meeting_assignment_template->status == 0 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="sms_meeting_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                </div>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>{{get_label('placeholder','Placeholder')}}</th>
                                                            <th>{{get_label('action','Action')}}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="copyText">{MEETING_ID}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{MEETING_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{MEETING_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{MEETING_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{FIRST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{LAST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{MEETING_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{MEETING_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{SITE_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="sms-leave-request">
                                        <div class="list-group list-group-horizontal-md text-md-center">
                                            <a class="list-group-item list-group-item-action active" id="sms-leave-request-creation-list-item" data-bs-toggle="list" href="#sms-leave-request-creation">{{get_label('creation','Creation')}}</a>
                                            <a class="list-group-item list-group-item-action" id="sms-leave-request-status-updation-list-item" data-bs-toggle="list" href="#sms-leave-request-status-updation">{{get_label('status_updation','Status Updation')}}</a>
                                            <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#sms-team-member-on-leave-alert">{{get_label('team_member_on_leave_alert','Team Member on Leave Alert')}}</a>
                                        </div>
                                        <div class="tab-content px-0">
                                            <div class="tab-pane fade show active" id="sms-leave-request-creation">
                                                @php
                                                $leave_request_creation_sms_template = App\Models\Template::where('type', 'sms')
                                                ->where('name', 'leave_request_creation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('leave_request_creation_sms_info', 'This Template Will Be Used for the SMS notification sent to the Admin and Leave Editors Upon the Creation of a Leave Request.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="sms">
                                                    <input type="hidden" name="name" value="leave_request_creation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea name="content" id="sms_leave_request_creation" class="form-control" rows="5">{{ $leave_request_creation_sms_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('leave_request_creation_sms_will_not_sent', 'If Deactive, Leave Request Creation SMS Won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="sms_leave_request_creation_status_active" name="status" value="1" {{ !($leave_request_creation_sms_template) || $leave_request_creation_sms_template && $leave_request_creation_sms_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="sms_leave_request_creation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="sms_leave_request_creation_status_deactive" name="status" value="0" {{ $leave_request_creation_sms_template && $leave_request_creation_sms_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="sms_leave_request_creation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TYPE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TYPE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FROM}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FROM}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TO}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{DURATION}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{DURATION}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REASON}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REASON}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="sms-leave-request-status-updation">
                                                @php
                                                $leave_request_status_updation_sms_template = App\Models\Template::where('type', 'sms')
                                                ->where('name', 'leave_request_status_updation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('leave_request_status_updation_sms_info', 'This Template Will Be Used for the SMS notification sent to the Admin/Leave Editors/Requestee Upon the Status Updation of a Leave Request.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="sms">
                                                    <input type="hidden" name="name" value="leave_request_status_updation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea name="content" id="sms_leave_request_status_updation" class="form-control" rows="5">{{ $leave_request_status_updation_sms_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('leave_request_status_updation_sms_will_not_sent', 'If Deactive, Leave Request Status Updation SMS Won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="sms_leave_request_status_updation_status_active" name="status" value="1" {{ !($leave_request_status_updation_sms_template) || $leave_request_status_updation_sms_template && $leave_request_status_updation_sms_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="sms_leave_request_status_updation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="sms_leave_request_status_updation_status_deactive" name="status" value="0" {{ $leave_request_status_updation_sms_template && $leave_request_status_updation_sms_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="sms_leave_request_status_updation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TYPE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TYPE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FROM}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FROM}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TO}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{DURATION}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{DURATION}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{OLD_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{OLD_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{NEW_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{NEW_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REASON}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REASON}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="sms-team-member-on-leave-alert">
                                                @php
                                                $team_member_on_leave_alert_sms_template = App\Models\Template::where('type', 'sms')
                                                ->where('name', 'team_member_on_leave_alert')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('team_member_on_leave_alert_sms_info', 'This template will be used for the SMS notification sent to team members upon approval of a leave request, informing them about the absence of the requestee.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="sms">
                                                    <input type="hidden" name="name" value="team_member_on_leave_alert">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea name="content" id="sms_team_member_on_leave_alert" class="form-control" rows="5">{{ $team_member_on_leave_alert_sms_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('team_member_on_leave_alert_sms_will_not_sent', 'If Deactive, Team Member on Leave Alert SMS Notification Won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="sms_team_member_on_leave_alert_status_active" name="status" value="1" {{ !($team_member_on_leave_alert_sms_template) || $team_member_on_leave_alert_sms_template && $team_member_on_leave_alert_sms_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="sms_team_member_on_leave_alert_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="sms_team_member_on_leave_alert_status_deactive" name="status" value="0" {{ $team_member_on_leave_alert_sms_template && $team_member_on_leave_alert_sms_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="sms_team_member_on_leave_alert_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TYPE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TYPE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FROM}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FROM}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TO}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{DURATION}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{DURATION}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- WhatsApp Templates Tab -->
                            <div class="tab-pane fade" id="whatsapp-templates">
                                <div class="alert alert-primary">{{get_label('default_whatsapp_template_info','A Default Message Will Be Used if a Specific WhatsApp Notification Template Is Not Set.')}}</div>
                                <div class="list-group list-group-horizontal-md text-md-center">
                                    <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#whatsapp-project">{{get_label('project','Project')}}</a>
                                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#whatsapp-task">{{get_label('task','Task')}}</a>
                                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#whatsapp-workspace-assignment">{{get_label('workspace_assignment','Workspace assignment')}}</a>
                                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#whatsapp-meeting-assignment">{{get_label('meeting_assignment','Meeting assignment')}}</a>
                                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#whatsapp-leave-request">{{get_label('leave_request','Leave Request')}}</a>
                                </div>
                                <div class="tab-content px-0">
                                    <div class="tab-pane fade show active" id="whatsapp-project">
                                        <div class="list-group list-group-horizontal-md text-md-center">
                                            <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#whatsapp-project-assignment">{{get_label('assignment','Assignment')}}</a>
                                            <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#whatsapp-project-status-updation">{{get_label('status_updation','Status Updation')}}</a>
                                        </div>
                                        <div class="tab-content px-0">
                                            <div class="tab-pane fade show active" id="whatsapp-project-assignment">
                                                @php
                                                $whatsapp_project_assignment_template = App\Models\Template::where('type', 'whatsapp')
                                                ->where('name', 'project_assignment')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('project_assignment_whatsapp_info', 'This template will be used for the WhatsApp notification sent to users/clients when they are assigned a project.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="whatsapp">
                                                    <input type="hidden" name="name" value="project_assignment">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="whatsapp_project_assignment" name="content" class="form-control" rows="5">{{ $whatsapp_project_assignment_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('project_assignment_whatsapp_will_not_sent', 'If Deactive, project assignment Whatsapp Notification won\'t be sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="whatsapp_project_assignment_status_active" name="status" value="1" {{ !($whatsapp_project_assignment_template) || $whatsapp_project_assignment_template && $whatsapp_project_assignment_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="whatsapp_project_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="whatsapp_project_assignment_status_deactive" name="status" value="0" {{ $whatsapp_project_assignment_template && $whatsapp_project_assignment_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="whatsapp_project_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="whatsapp-project-status-updation">
                                                @php
                                                $whatsapp_project_status_updation_template = App\Models\Template::where('type', 'whatsapp')
                                                ->where('name', 'project_status_updation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('project_status_updation_whatsapp_info', 'This Template Will Be Used for the Whatsapp notification sent to the Users/Clients Upon the Status Updation of a Project.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="whatsapp">
                                                    <input type="hidden" name="name" value="project_status_updation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="whatsapp_project_status_updation" name="content" class="form-control" rows="5">{{ $whatsapp_project_status_updation_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('project_status_updation_whatsapp_will_not_sent', 'If Deactive, Project Status Updation Whatsapp Notification won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="whatsapp_project_status_updation_status_active" name="status" value="1" {{ !($whatsapp_project_status_updation_template) || $whatsapp_project_status_updation_template && $whatsapp_project_status_updation_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="whatsapp_project_status_updation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="whatsapp_project_status_updation_status_deactive" name="status" value="0" {{ $whatsapp_project_status_updation_template && $whatsapp_project_status_updation_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="whatsapp_project_status_updation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{OLD_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{OLD_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{NEW_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{NEW_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="whatsapp-task">
                                        <div class="list-group list-group-horizontal-md text-md-center">
                                            <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#whatsapp-task-assignment">{{get_label('assignment','Assignment')}}</a>
                                            <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#whatsapp-task-status-updation">{{get_label('status_updation','Status Updation')}}</a>
                                        </div>
                                        <div class="tab-content px-0">
                                            <div class="tab-pane fade show active" id="whatsapp-task-assignment">
                                                @php
                                                $whatsapp_task_assignment_template = App\Models\Template::where('type', 'whatsapp')
                                                ->where('name', 'task_assignment')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('task_assignment_whatsapp_info', 'This template will be used for the whatsapp notification sent to users/clients when they are assigned a task.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="whatsapp">
                                                    <input type="hidden" name="name" value="task_assignment">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="whatsapp_task_assignment" name="content" class="form-control" rows="5">{{ $whatsapp_task_assignment_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('task_assignment_whatsapp_will_not_sent', 'If Deactive, task assignment whatsapp notification won\'t be sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="whatsapp_task_assignment_status_active" name="status" value="1" {{ !($whatsapp_task_assignment_template) || $whatsapp_task_assignment_template && $whatsapp_task_assignment_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="whatsapp_task_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="whatsapp_task_assignment_status_deactive" name="status" value="0" {{ $whatsapp_task_assignment_template && $whatsapp_task_assignment_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="whatsapp_task_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{TASK_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="whatsapp-task-status-updation">
                                                @php
                                                $whatsapp_task_status_updation_template = App\Models\Template::where('type', 'whatsapp')
                                                ->where('name', 'task_status_updation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('task_status_updation_whatsapp_info', 'This Template Will Be Used for the Whatsapp notification sent to the Users/Clients Upon the Status Updation of a Task.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="whatsapp">
                                                    <input type="hidden" name="name" value="task_status_updation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="whatsapp_task_status_updation" name="content" class="form-control" rows="5">{{ $whatsapp_task_status_updation_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('task_status_updation_whatsapp_will_not_sent', 'If Deactive, Task Status Updation Whatsapp Notification won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="whatsapp_task_status_updation_status_active" name="status" value="1" {{ !($whatsapp_task_status_updation_template) || $whatsapp_task_status_updation_template && $whatsapp_task_status_updation_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="whatsapp_task_status_updation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="whatsapp_task_status_updation_status_deactive" name="status" value="0" {{ $whatsapp_task_status_updation_template && $whatsapp_task_status_updation_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="whatsapp_task_status_updation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{TASK_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{OLD_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{OLD_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{NEW_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{NEW_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="whatsapp-workspace-assignment">
                                        @php
                                        $whatsapp_workspace_assignment_template = App\Models\Template::where('type', 'whatsapp')
                                        ->where('name', 'workspace_assignment')
                                        ->first();
                                        @endphp
                                        <small class="text-light fw-semibold mb-1"><?= get_label('workspace_assignment_whatsapp_info', 'This template will be used for the whatsapp notification sent to users/clients when they are added to a workspace.') ?></small>
                                        <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="type" value="whatsapp">
                                            <input type="hidden" name="name" value="workspace_assignment">
                                            <input type="hidden" name="dnr">
                                            <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                            <textarea id="whatsapp_workspace_assignment" name="content" class="form-control" rows="5">{{ $whatsapp_workspace_assignment_template->content ?? '' }}</textarea>
                                            <div class="col-md-6 mt-4 mb-5">
                                                <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('workspace_assignment_whatsapp_will_not_sent', 'If Deactive, workspace assignment whatsapp notification won\'t be sent') ?></small>)</label>
                                                <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                    <input type="radio" class="btn-check" id="whatsapp_workspace_assignment_status_active" name="status" value="1" {{ !($whatsapp_workspace_assignment_template) || $whatsapp_workspace_assignment_template && $whatsapp_workspace_assignment_template->status == 1 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="whatsapp_workspace_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                    <input type="radio" class="btn-check" id="whatsapp_workspace_assignment_status_deactive" name="status" value="0" {{ $whatsapp_workspace_assignment_template && $whatsapp_workspace_assignment_template->status == 0 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="whatsapp_workspace_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                </div>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>{{get_label('placeholder','Placeholder')}}</th>
                                                            <th>{{get_label('action','Action')}}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="copyText">{WORKSPACE_ID}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{WORKSPACE_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{WORKSPACE_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{WORKSPACE_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{FIRST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{LAST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{WORKSPACE_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{WORKSPACE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{SITE_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="whatsapp-meeting-assignment">
                                        @php
                                        $whatsapp_meeting_assignment_template = App\Models\Template::where('type', 'whatsapp')
                                        ->where('name', 'meeting_assignment')
                                        ->first();
                                        @endphp
                                        <small class="text-light fw-semibold mb-1"><?= get_label('meeting_assignment_whatsapp_info', 'This template will be used for the whatsapp notification sent to users/clients when they are added to a meeting.') ?></small>
                                        <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="type" value="whatsapp">
                                            <input type="hidden" name="name" value="meeting_assignment">
                                            <input type="hidden" name="dnr">
                                            <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                            <textarea id="whatsapp_meeting_assignment" name="content" class="form-control" rows="5">{{ $whatsapp_meeting_assignment_template->content ?? '' }}</textarea>
                                            <div class="col-md-6 mt-4 mb-5">
                                                <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('meeting_assignment_whatsapp_will_not_sent', 'If Deactive, meeting assignment whatsapp notification won\'t be sent') ?></small>)</label>
                                                <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                    <input type="radio" class="btn-check" id="whatsapp_meeting_assignment_status_active" name="status" value="1" {{ !($whatsapp_meeting_assignment_template) || $whatsapp_meeting_assignment_template && $whatsapp_meeting_assignment_template->status == 1 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="whatsapp_meeting_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                    <input type="radio" class="btn-check" id="whatsapp_meeting_assignment_status_deactive" name="status" value="0" {{ $whatsapp_meeting_assignment_template && $whatsapp_meeting_assignment_template->status == 0 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="whatsapp_meeting_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                </div>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>{{get_label('placeholder','Placeholder')}}</th>
                                                            <th>{{get_label('action','Action')}}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="copyText">{MEETING_ID}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{MEETING_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{MEETING_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{MEETING_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{FIRST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{LAST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{MEETING_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{MEETING_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{SITE_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="whatsapp-leave-request">
                                        <div class="list-group list-group-horizontal-md text-md-center">
                                            <a class="list-group-item list-group-item-action active" id="whatsapp-leave-request-creation-list-item" data-bs-toggle="list" href="#whatsapp-leave-request-creation">{{get_label('creation','Creation')}}</a>
                                            <a class="list-group-item list-group-item-action" id="whatsapp-leave-request-status-updation-list-item" data-bs-toggle="list" href="#whatsapp-leave-request-status-updation">{{get_label('status_updation','Status Updation')}}</a>
                                            <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#whatsapp-team-member-on-leave-alert">{{get_label('team_member_on_leave_alert','Team Member on Leave Alert')}}</a>
                                        </div>
                                        <div class="tab-content px-0">
                                            <div class="tab-pane fade show active" id="whatsapp-leave-request-creation">
                                                @php
                                                $leave_request_creation_whatsapp_template = App\Models\Template::where('type', 'whatsapp')
                                                ->where('name', 'leave_request_creation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('leave_request_creation_whatsapp_info', 'This Template Will Be Used for the Whatsapp notification sent to the Admin and Leave Editors Upon the Creation of a Leave Request.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="whatsapp">
                                                    <input type="hidden" name="name" value="leave_request_creation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea name="content" id="whatsapp_leave_request_creation" class="form-control" rows="5">{{ $leave_request_creation_whatsapp_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('leave_request_creation_whatsapp_will_not_sent', 'If Deactive, Leave Request Creation Whatsapp Notification Won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="whatsapp_leave_request_creation_status_active" name="status" value="1" {{ !($leave_request_creation_whatsapp_template) || $leave_request_creation_whatsapp_template && $leave_request_creation_whatsapp_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="whatsapp_leave_request_creation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="whatsapp_leave_request_creation_status_deactive" name="status" value="0" {{ $leave_request_creation_whatsapp_template && $leave_request_creation_whatsapp_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="whatsapp_leave_request_creation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TYPE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TYPE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FROM}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FROM}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TO}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{DURATION}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{DURATION}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REASON}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REASON}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="whatsapp-leave-request-status-updation">
                                                @php
                                                $leave_request_status_updation_whatsapp_template = App\Models\Template::where('type', 'whatsapp')
                                                ->where('name', 'leave_request_status_updation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('leave_request_status_updation_whatsapp_info', 'This Template Will Be Used for the Whatsapp notification sent to the Admin/Leave Editors/Requestee Upon the Status Updation of a Leave Request.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="whatsapp">
                                                    <input type="hidden" name="name" value="leave_request_status_updation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea name="content" id="whatsapp_leave_request_status_updation" class="form-control" rows="5">{{ $leave_request_status_updation_whatsapp_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('leave_request_status_updation_whatsapp_will_not_sent', 'If Deactive, Leave Request Status Updation Whatsapp Notification Won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="whatsapp_leave_request_status_updation_status_active" name="status" value="1" {{ !($leave_request_status_updation_whatsapp_template) || $leave_request_status_updation_whatsapp_template && $leave_request_status_updation_whatsapp_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="whatsapp_leave_request_status_updation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="whatsapp_leave_request_status_updation_status_deactive" name="status" value="0" {{ $leave_request_status_updation_whatsapp_template && $leave_request_status_updation_whatsapp_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="whatsapp_leave_request_status_updation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TYPE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TYPE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FROM}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FROM}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TO}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{DURATION}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{DURATION}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{OLD_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{OLD_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{NEW_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{NEW_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REASON}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REASON}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="whatsapp-team-member-on-leave-alert">
                                                @php
                                                $team_member_on_leave_alert_whatsapp_template = App\Models\Template::where('type', 'whatsapp')
                                                ->where('name', 'team_member_on_leave_alert')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('team_member_on_leave_alert_whatsapp_info', 'This template will be used for the WhatsApp notification sent to team members upon approval of a leave request, informing them about the absence of the requestee.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="whatsapp">
                                                    <input type="hidden" name="name" value="team_member_on_leave_alert">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea name="content" id="whatsapp_team_member_on_leave_alert" class="form-control" rows="5">{{ $team_member_on_leave_alert_whatsapp_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('team_member_on_leave_alert_whatsapp_will_not_sent', 'If Deactive, Team Member on Leave Alert WhatsApp Notification Won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="whatsapp_team_member_on_leave_alert_status_active" name="status" value="1" {{ !($team_member_on_leave_alert_whatsapp_template) || $team_member_on_leave_alert_whatsapp_template && $team_member_on_leave_alert_whatsapp_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="whatsapp_team_member_on_leave_alert_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="whatsapp_team_member_on_leave_alert_status_deactive" name="status" value="0" {{ $team_member_on_leave_alert_whatsapp_template && $team_member_on_leave_alert_whatsapp_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="whatsapp_team_member_on_leave_alert_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{USER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{USER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TYPE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TYPE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FROM}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FROM}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TO}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{DURATION}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{DURATION}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{SITE_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{SITE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- System Templates Tab -->
                            <div class="tab-pane fade" id="system-templates">
                                <div class="alert alert-primary">{{get_label('default_system_template_info','A Default Title and Message Will Be Used if a Specific System Notification Template Is Not Set.')}}</div>
                                <div class="list-group list-group-horizontal-md text-md-center">
                                    <a class="list-group-item list-group-item-action active" id="system-project-list-item" data-bs-toggle="list" href="#system-project">{{get_label('project','Project')}}</a>
                                    <a class="list-group-item list-group-item-action" id="system-task-list-item" data-bs-toggle="list" href="#system-task">{{get_label('task','Task')}}</a>
                                    <a class="list-group-item list-group-item-action" id="system-workspace-assignment-list-item" data-bs-toggle="list" href="#system-workspace-assignment">{{get_label('workspace_assignment','Workspace assignment')}}</a>
                                    <a class="list-group-item list-group-item-action" id="system-meeting-assignment-list-item" data-bs-toggle="list" href="#system-meeting-assignment">{{get_label('meeting_assignment','Meeting assignment')}}</a>
                                    <a class="list-group-item list-group-item-action" id="system-leave-request-list-item" data-bs-toggle="list" href="#system-leave-request">{{get_label('leave_request','Leave Request')}}</a>
                                </div>
                                <div class="tab-content px-0">
                                    <div class="tab-pane fade show active" id="system-project">
                                        <div class="list-group list-group-horizontal-md text-md-center">
                                            <a class="list-group-item list-group-item-action active" id="system-project-assignment-list-item" data-bs-toggle="list" href="#system-project-assignment">{{get_label('assignment','Assignment')}}</a>
                                            <a class="list-group-item list-group-item-action" id="system-project-status-updation-list-item" data-bs-toggle="list" href="#system-project-status-updation">{{get_label('status_updation','Status Updation')}}</a>
                                        </div>
                                        <div class="tab-content px-0">
                                            <div class="tab-pane fade show active" id="system-project-assignment">
                                                @php
                                                $system_project_assignment_template = App\Models\Template::where('type', 'system')
                                                ->where('name', 'project_assignment')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('project_assignment_system_info', 'This template will be used for the system notification sent to users/clients when they are assigned a project.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="system">
                                                    <input type="hidden" name="name" value="project_assignment">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('title', 'Title') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {PROJECT_ID}, {PROJECT_TITLE}, {ASSIGNEE_FIRST_NAME}, {ASSIGNEE_LAST_NAME}, {COMPANY_TITLE})</small></label>
                                                    <input type="text" class="form-control mb-3" name="subject" value="{{ $system_project_assignment_template->subject ?? '' }}" placeholder="{{get_label('please_enter_title','Please enter title')}}">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="system_project_assignment" name="content" class="form-control" rows="5">{{ $system_project_assignment_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('project_assignment_system_will_not_sent', 'If Deactive, project assignment system notification won\'t be sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="system_project_assignment_status_active" name="status" value="1" {{ !($system_project_assignment_template) || $system_project_assignment_template && $system_project_assignment_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="system_project_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="system_project_assignment_status_deactive" name="status" value="0" {{ $system_project_assignment_template && $system_project_assignment_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="system_project_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{ASSIGNEE_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ASSIGNEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{ASSIGNEE_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ASSIGNEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="system-project-status-updation">
                                                @php
                                                $system_project_status_updation_template = App\Models\Template::where('type', 'system')
                                                ->where('name', 'project_status_updation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('project_status_updation_system_info', 'This Template Will Be Used for the System notification sent to the Users/Clients Upon the Status Updation of a Project.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="system">
                                                    <input type="hidden" name="name" value="project_status_updation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('title', 'Title') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {PROJECT_ID}, {PROJECT_TITLE}, {UPDATER_FIRST_NAME}, {UPDATER_LAST_NAME}, {OLD_STATUS}, {NEW_STATUS}, {COMPANY_TITLE})</small></label>
                                                    <input type="text" class="form-control mb-3" name="subject" value="{{ $system_project_status_updation_template->subject ?? '' }}" placeholder="{{get_label('please_enter_title','Please enter title')}}">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="system_project_status_updation" name="content" class="form-control" rows="5">{{ $system_project_status_updation_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('project_status_updation_system_will_not_sent', 'If Deactive, Project Status Updation System Notification won\'t be sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="system_project_status_updation_status_active" name="status" value="1" {{ !($system_project_status_updation_template) || $system_project_status_updation_template && $system_project_status_updation_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="system_project_status_updation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="system_project_status_updation_status_deactive" name="status" value="0" {{ $system_project_status_updation_template && $system_project_status_updation_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="system_project_status_updation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{OLD_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{OLD_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{NEW_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{NEW_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{PROJECT_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{PROJECT_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="system-task">
                                        <div class="list-group list-group-horizontal-md text-md-center">
                                            <a class="list-group-item list-group-item-action active" id="system-task-assignment-list-item" data-bs-toggle="list" href="#system-task-assignment">{{get_label('assignment','Assignment')}}</a>
                                            <a class="list-group-item list-group-item-action" id="system-task-status-updation-list-item" data-bs-toggle="list" href="#system-task-status-updation">{{get_label('status_updation','Status Updation')}}</a>
                                        </div>
                                        <div class="tab-content px-0">
                                            <div class="tab-pane fade show active" id="system-task-assignment">
                                                @php
                                                $system_task_assignment_template = App\Models\Template::where('type', 'system')
                                                ->where('name', 'task_assignment')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('task_assignment_system_info', 'This template will be used for the system notification sent to users/clients when they are assigned a task.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="system">
                                                    <input type="hidden" name="name" value="task_assignment">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('title', 'Title') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {TASK_ID}, {TASK_TITLE}, {ASSIGNEE_FIRST_NAME}, {ASSIGNEE_LAST_NAME}, {COMPANY_TITLE})</small></label>
                                                    <input type="text" class="form-control mb-3" name="subject" value="{{ $system_task_assignment_template->subject ?? '' }}" placeholder="{{get_label('please_enter_title','Please enter title')}}">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="system_task_assignment" name="content" class="form-control" rows="5">{{ $system_task_assignment_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('task_assignment_system_will_not_sent', 'If Deactive, task assignment system notification won\'t be sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="system_task_assignment_status_active" name="status" value="1" {{ !($system_task_assignment_template) || $system_task_assignment_template && $system_task_assignment_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="system_task_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="system_task_assignment_status_deactive" name="status" value="0" {{ $system_task_assignment_template && $system_task_assignment_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="system_task_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{TASK_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{ASSIGNEE_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ASSIGNEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{ASSIGNEE_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ASSIGNEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="system-task-status-updation">
                                                @php
                                                $system_task_status_updation_template = App\Models\Template::where('type', 'system')
                                                ->where('name', 'task_status_updation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('task_status_updation_system_info', 'This Template Will Be Used for the System notification sent to the Users/Clients Upon the Status Updation of a Task.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="system">
                                                    <input type="hidden" name="name" value="task_status_updation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('title', 'Title') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {TASK_ID}, {TASK_TITLE}, {UPDATER_FIRST_NAME}, {UPDATER_LAST_NAME}, {OLD_STATUS}, {NEW_STATUS}, {COMPANY_TITLE})</small></label>
                                                    <input type="text" class="form-control mb-3" name="subject" value="{{ $system_task_status_updation_template->subject ?? '' }}" placeholder="{{get_label('please_enter_title','Please enter title')}}">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea id="system_task_status_updation" name="content" class="form-control" rows="5">{{ $system_task_status_updation_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('task_status_updation_system_will_not_sent', 'If Deactive, Task Status Updation system notification won\'t be sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="system_task_status_updation_status_active" name="status" value="1" {{ !($system_task_status_updation_template) || $system_task_status_updation_template && $system_task_status_updation_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="system_task_status_updation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="system_task_status_updation_status_deactive" name="status" value="0" {{ $system_task_status_updation_template && $system_task_status_updation_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="system_task_status_updation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{TASK_ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{UPDATER_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{UPDATER_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{OLD_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{OLD_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{NEW_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{NEW_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TASK_URL}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TASK_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="system-workspace-assignment">
                                        @php
                                        $system_workspace_assignment_template = App\Models\Template::where('type', 'system')
                                        ->where('name', 'workspace_assignment')
                                        ->first();
                                        @endphp
                                        <small class="text-light fw-semibold mb-1"><?= get_label('workspace_assignment_system_info', 'This template will be used for the system notification sent to users/clients when they are added to a workspace.') ?></small>
                                        <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="type" value="system">
                                            <input type="hidden" name="name" value="workspace_assignment">
                                            <input type="hidden" name="dnr">
                                            <label class="form-label mt-3"><?= get_label('title', 'Title') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {WORKSPACE_ID}, {WORKSPACE_TITLE}, {ASSIGNEE_FIRST_NAME}, {ASSIGNEE_LAST_NAME}, {COMPANY_TITLE})</small></label>
                                            <input type="text" class="form-control mb-3" name="subject" value="{{ $system_workspace_assignment_template->subject ?? '' }}" placeholder="{{get_label('please_enter_title','Please enter title')}}">
                                            <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                            <textarea id="system_workspace_assignment" name="content" class="form-control" rows="5">{{ $system_workspace_assignment_template->content ?? '' }}</textarea>
                                            <div class="col-md-6 mt-4 mb-5">
                                                <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('workspace_assignment_system_will_not_sent', 'If Deactive, workspace assignment system notification won\'t be sent') ?></small>)</label>
                                                <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                    <input type="radio" class="btn-check" id="system_workspace_assignment_status_active" name="status" value="1" {{ !($system_workspace_assignment_template) || $system_workspace_assignment_template && $system_workspace_assignment_template->status == 1 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="system_workspace_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                    <input type="radio" class="btn-check" id="system_workspace_assignment_status_deactive" name="status" value="0" {{ $system_workspace_assignment_template && $system_workspace_assignment_template->status == 0 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="system_workspace_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                </div>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>{{get_label('placeholder','Placeholder')}}</th>
                                                            <th>{{get_label('action','Action')}}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="copyText">{WORKSPACE_ID}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{WORKSPACE_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{WORKSPACE_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{WORKSPACE_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{ASSIGNEE_FIRST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{ASSIGNEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{ASSIGNEE_LAST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{ASSIGNEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{WORKSPACE_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{WORKSPACE_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="system-meeting-assignment">
                                        @php
                                        $system_meeting_assignment_template = App\Models\Template::where('type', 'system')
                                        ->where('name', 'meeting_assignment')
                                        ->first();
                                        @endphp
                                        <small class="text-light fw-semibold mb-1"><?= get_label('meeting_assignment_system_info', 'This template will be used for the system notification sent to users/clients when they are added to a meeting.') ?></small>
                                        <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="type" value="system">
                                            <input type="hidden" name="name" value="meeting_assignment">
                                            <input type="hidden" name="dnr">
                                            <label class="form-label mt-3"><?= get_label('title', 'Title') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {MEETING_ID}, {MEETING_TITLE}, {ASSIGNEE_FIRST_NAME}, {ASSIGNEE_LAST_NAME}, {COMPANY_TITLE})</small></label>
                                            <input type="text" class="form-control mb-3" name="subject" value="{{ $system_meeting_assignment_template->subject ?? '' }}" placeholder="{{get_label('please_enter_title','Please enter title')}}">
                                            <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                            <textarea id="system_meeting_assignment" name="content" class="form-control" rows="5">{{ $system_meeting_assignment_template->content ?? '' }}</textarea>
                                            <div class="col-md-6 mt-4 mb-5">
                                                <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('meeting_assignment_system_will_not_sent', 'If Deactive, meeting assignment system notification won\'t be sent') ?></small>)</label>
                                                <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                    <input type="radio" class="btn-check" id="system_meeting_assignment_status_active" name="status" value="1" {{ !($system_meeting_assignment_template) || $system_meeting_assignment_template && $system_meeting_assignment_template->status == 1 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="system_meeting_assignment_status_active">{{ get_label('active', 'Active') }}</label>
                                                    <input type="radio" class="btn-check" id="system_meeting_assignment_status_deactive" name="status" value="0" {{ $system_meeting_assignment_template && $system_meeting_assignment_template->status == 0 ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-primary" for="system_meeting_assignment_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                </div>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>{{get_label('placeholder','Placeholder')}}</th>
                                                            <th>{{get_label('action','Action')}}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="copyText">{MEETING_ID}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{MEETING_ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{MEETING_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{MEETING_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{ASSIGNEE_FIRST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{ASSIGNEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{ASSIGNEE_LAST_NAME}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{ASSIGNEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{MEETING_URL}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{MEETING_URL}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{COMPANY_TITLE}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="system-leave-request">
                                        <div class="list-group list-group-horizontal-md text-md-center">
                                            <a class="list-group-item list-group-item-action active" id="system-leave-request-creation-list-item" data-bs-toggle="list" href="#system-leave-request-creation">{{get_label('creation','Creation')}}</a>
                                            <a class="list-group-item list-group-item-action" id="system-leave-request-status-updation-list-item" data-bs-toggle="list" href="#system-leave-request-status-updation">{{get_label('status_updation','Status Updation')}}</a>
                                            <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#system-team-member-on-leave-alert">{{get_label('team_member_on_leave_alert','Team Member on Leave Alert')}}</a>
                                        </div>
                                        <div class="tab-content px-0">
                                            <div class="tab-pane fade show active" id="system-leave-request-creation">
                                                @php
                                                $leave_request_creation_system_template = App\Models\Template::where('type', 'system')
                                                ->where('name', 'leave_request_creation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('leave_request_creation_system_info', 'This Template Will Be Used for the System notification sent to the Admin and Leave Editors Upon the Creation of a Leave Request.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="system">
                                                    <input type="hidden" name="name" value="leave_request_creation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('title', 'Title') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {ID}, {STATUS}, {REQUESTEE_FIRST_NAME}, {REQUESTEE_LAST_NAME}, {COMPANY_TITLE})</small></label>
                                                    <input type="text" class="form-control mb-3" name="subject" value="{{ $leave_request_creation_system_template->subject ?? '' }}" placeholder="{{get_label('please_enter_title','Please enter title')}}">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea name="content" id="system_leave_request_creation" class="form-control" rows="5">{{ $leave_request_creation_system_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('leave_request_creation_system_will_not_sent', 'If Deactive, Leave Request Creation system notification won\'t be sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="system_leave_request_creation_status_active" name="status" value="1" {{ !($leave_request_creation_system_template) || $leave_request_creation_system_template && $leave_request_creation_system_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="system_leave_request_creation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="system_leave_request_creation_status_deactive" name="status" value="0" {{ $leave_request_creation_system_template && $leave_request_creation_system_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="system_leave_request_creation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TYPE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TYPE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FROM}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FROM}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TO}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{DURATION}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{DURATION}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REASON}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REASON}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="system-leave-request-status-updation">
                                                @php
                                                $leave_request_status_updation_system_template = App\Models\Template::where('type', 'system')
                                                ->where('name', 'leave_request_status_updation')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('leave_request_status_updation_system_info', 'This Template Will Be Used for the System notification sent to the Admin/Leave Editors/Requestee Upon the Status Updation of a Leave Request.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="system">
                                                    <input type="hidden" name="name" value="leave_request_status_updation">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('title', 'Title') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {ID}, {OLD_STATUS}, {NEW_STATUS}, {COMPANY_TITLE})</small></label>
                                                    <input type="text" class="form-control mb-3" name="subject" value="{{ $leave_request_status_updation_system_template->subject ?? '' }}" placeholder="{{get_label('please_enter_title','Please enter title')}}">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea name="content" id="system_leave_request_status_updation" class="form-control" rows="5">{{ $leave_request_status_updation_system_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('leave_request_status_updation_system_will_not_sent', 'If Deactive, Leave Request Status Updation System Notification won\'t be sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="system_leave_request_status_updation_status_active" name="status" value="1" {{ !($leave_request_status_updation_system_template) || $leave_request_status_updation_system_template && $leave_request_status_updation_system_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="system_leave_request_status_updation_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="system_leave_request_status_updation_status_deactive" name="status" value="0" {{ $leave_request_status_updation_system_template && $leave_request_status_updation_system_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="system_leave_request_status_updation_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TYPE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TYPE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FROM}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FROM}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TO}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{DURATION}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{DURATION}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{OLD_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{OLD_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{NEW_STATUS}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{NEW_STATUS}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REASON}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REASON}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="system-team-member-on-leave-alert">
                                                @php
                                                $team_member_on_leave_alert_system_template = App\Models\Template::where('type', 'system')
                                                ->where('name', 'team_member_on_leave_alert')
                                                ->first();
                                                @endphp
                                                <small class="text-light fw-semibold mb-1"><?= get_label('team_member_on_leave_alert_system_info', 'This template will be used for the system notification sent to team members upon approval of a leave request, informing them about the absence of the requestee.') ?></small>
                                                <form action="{{url('settings/store_template')}}" class="form-submit-event" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="type" value="system">
                                                    <input type="hidden" name="name" value="team_member_on_leave_alert">
                                                    <input type="hidden" name="dnr">
                                                    <label class="form-label mt-3"><?= get_label('title', 'Title') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {ID}, {REQUESTEE_FIRST_NAME}, {REQUESTEE_LAST_NAME}, {COMPANY_TITLE})</small></label>
                                                    <input type="text" class="form-control mb-3" name="subject" value="{{ $team_member_on_leave_alert_system_template->subject ?? '' }}" placeholder="{{get_label('please_enter_title','Please enter title')}}">
                                                    <label class="form-label mt-3"><?= get_label('message', 'Message') ?> <span class="asterisk">*</span> <small class="text-muted">({{get_label('possible_placeholders', 'Possible placeholders')}} : {{get_label('all_available_placeholders', 'All available placeholders')}})</small></label>
                                                    <textarea name="content" id="system_team_member_on_leave_alert" class="form-control" rows="5">{{ $team_member_on_leave_alert_system_template->content ?? '' }}</textarea>
                                                    <div class="col-md-6 mt-4 mb-5">
                                                        <label class="form-label" for=""><?= get_label('status', 'Status') ?> (<small class="text-muted mt-2"><?= get_label('team_member_on_leave_alert_system_will_not_sent', 'If Deactive, Team Member on Leave Alert System Notification Won\'t be Sent') ?></small>)</label>
                                                        <div class="btn-group btn-group d-flex justify-content-center" role="group" aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check" id="system_team_member_on_leave_alert_status_active" name="status" value="1" {{ !($team_member_on_leave_alert_system_template) || $team_member_on_leave_alert_system_template && $team_member_on_leave_alert_system_template->status == 1 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="system_team_member_on_leave_alert_status_active">{{ get_label('active', 'Active') }}</label>
                                                            <input type="radio" class="btn-check" id="system_team_member_on_leave_alert_status_deactive" name="status" value="0" {{ $team_member_on_leave_alert_system_template && $team_member_on_leave_alert_system_template->status == 0 ? 'checked' : '' }}>
                                                            <label class="btn btn-outline-primary" for="system_team_member_on_leave_alert_status_deactive">{{ get_label('deactive', 'Deactive') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-3">
                                                        <button type="submit" class="btn btn-primary" id="submit_btn"><?= get_label('save', 'Save') ?></button>
                                                        <button type="button" class="btn btn-warning sms-restore-default"><?= get_label('reset_to_default', 'Reset to default') ?></button>
                                                    </div>
                                                    <div class="table-responsive text-nowrap">
                                                        <h5 class="mt-5">{{get_label('available_placeholders', 'Available placeholders')}}</h5>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{get_label('placeholder','Placeholder')}}</th>
                                                                    <th>{{get_label('action','Action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="copyText">{ID}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{ID}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_FIRST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_FIRST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{REQUESTEE_LAST_NAME}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{REQUESTEE_LAST_NAME}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TYPE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TYPE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{FROM}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{FROM}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{TO}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{TO}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{DURATION}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{DURATION}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="copyText">{COMPANY_TITLE}</td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" onclick="copyToClipboard('{COMPANY_TITLE}')" title="{{get_label('copy_to_clipboard','Copy to clipboard')}}">
                                                                            <i class="bx bx-copy text-warning mx-2"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{asset('assets/js/pages/templates.js')}}"></script>
@endsection