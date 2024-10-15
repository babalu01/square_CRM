
'use strict';
function queryParams(p) {
    return {
        "statuses": $('#status_filter').val(),
        "user_ids": $('#meeting_user_filter').val(),
        "client_ids": $('#meeting_client_filter').val(),
        "start_date_from": $('#meeting_start_date_from').val(),
        "start_date_to": $('#meeting_start_date_to').val(),
        "end_date_from": $('#meeting_end_date_from').val(),
        "end_date_to": $('#meeting_end_date_to').val(),
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

$('#status_filter,#meeting_user_filter,#meeting_client_filter').on('change', function (e, refreshTable) {
    e.preventDefault();
    if (typeof refreshTable === 'undefined' || refreshTable) {
        $('#meetings_table').bootstrapTable('refresh');
    }
});
$(document).on('click', '.clear-meetings-filters', function (e) {
    e.preventDefault();
    $('#meeting_start_date_between').val('');
    $('#meeting_end_date_between').val('');
    $('#meeting_start_date_from').val('');
    $('#meeting_start_date_to').val('');
    $('#meeting_end_date_from').val('');
    $('#meeting_end_date_to').val('');
    $('#status_filter').val('').trigger('change', [0]);
    $('#meeting_user_filter').val('').trigger('change', [0]);
    $('#meeting_client_filter').val('').trigger('change', [0]);
    $('#meetings_table').bootstrapTable('refresh');
})