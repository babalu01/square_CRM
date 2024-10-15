'use strict';

function queryParamsProjects(p) {
    return {
        "status_ids": $('#project_status_filter').val(),
        "priority_ids": $('#project_priority_filter').val(),
        "user_ids": $('#project_user_filter').val(),
        "client_ids": $('#project_client_filter').val(),
        "tag_ids": $('#project_tag_filter').val(),
        "project_start_date_from": $('#project_start_date_from').val(),
        "project_start_date_to": $('#project_start_date_to').val(),
        "project_end_date_from": $('#project_end_date_from').val(),
        "project_end_date_to": $('#project_end_date_to').val(),
        "is_favorites": $('#is_favorites').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}


window.icons = {
    refresh: 'bx-refresh',
    toggleOn: 'bx-toggle-right',
    toggleOff: 'bx-toggle-left'
}

function loadingTemplate(message) {
    return '<i class="bx bx-loader-alt bx-spin bx-flip-vertical" ></i>'
}

function assignedFormatter(value, row, index) {
    return '<div class="d-flex justify-content-start align-items-center"><div class="text-center mx-4"><span class="badge rounded-pill bg-primary" >' + row.projects + '</span><div>' + label_projects + '</div></div>' +
        '<div class="text-center"><span class="badge rounded-pill bg-primary" >' + row.tasks + '</span><div>' + label_tasks + '</div></div></div>'
}

function queryParamsUsersClients(p) {
    return {
        type: $('#type').val(),
        typeId: $('#typeId').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

$('#project_status_filter, #project_priority_filter, #project_user_filter, #project_client_filter, #project_tag_filter').on('change', function (e, refreshTable) {
    e.preventDefault();
    if (typeof refreshTable === 'undefined' || refreshTable) {
        $('#projects_table').bootstrapTable('refresh');
    }
});

$(document).on('click', '.clear-projects-filters', function (e) {
    e.preventDefault();
    $('#project_start_date_between').val('');
    $('#project_end_date_between').val('');
    $('#project_start_date_from').val('');
    $('#project_start_date_to').val('');
    $('#project_end_date_from').val('');
    $('#project_end_date_to').val('');    
    $('#project_user_filter').val('').trigger('change', [0]);
    $('#project_client_filter').val('').trigger('change', [0]);
    $('#project_status_filter').val('').trigger('change', [0]);
    $('#project_priority_filter').val('').trigger('change', [0]);
    $('#project_tag_filter').val('').trigger('change', [0]);
    $('#projects_table').bootstrapTable('refresh');
})

$('#viewAssignedModal').on('hidden.bs.modal', function (e) {
    e.preventDefault();
    $('.clear-projects-filters').trigger('click');
})
