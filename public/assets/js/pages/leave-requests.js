
'use strict';
function queryParamsLr(p) {
    return {
        "statuses": $('#lr_status_filter').val(),
        "user_ids": $('#lr_user_filter').val(),
        "action_by_ids": $('#lr_action_by_filter').val(),
        "start_date_from": $('#lr_start_date_from').val(),
        "start_date_to": $('#lr_start_date_to').val(),
        "end_date_from": $('#lr_end_date_from').val(),
        "end_date_to": $('#lr_end_date_to').val(),
        "types": $('#lr_type_filter').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
$('#lr_status_filter,#lr_user_filter,#lr_action_by_filter,#lr_type_filter').on('change', function (e, refreshTable) {
    e.preventDefault();
    if (typeof refreshTable === 'undefined' || refreshTable) {
        $('#lr_table').bootstrapTable('refresh');
    }
});

$(document).on('click', '.clear-leave-requests-filters', function (e) {
    e.preventDefault();
    $('#lr_start_date_between').val('');
    $('#lr_end_date_between').val('');
    $('#lr_start_date_from').val('');
    $('#lr_start_date_to').val('');
    $('#lr_end_date_from').val('');
    $('#lr_end_date_to').val('');
    $('#lr_status_filter').val('').trigger('change', [0]);
    $('#lr_user_filter').val('').trigger('change', [0]);
    $('#lr_action_by_filter').val('').trigger('change', [0]);
    $('#lr_type_filter').val('').trigger('change', [0]);
    $('#lr_table').bootstrapTable('refresh');
})

$('#lr_start_date_between').on('apply.daterangepicker', function (ev, picker) {
    var startDate = picker.startDate.format('YYYY-MM-DD');
    var endDate = picker.endDate.format('YYYY-MM-DD');

    $('#lr_start_date_from').val(startDate);
    $('#lr_start_date_to').val(endDate);

    $('#lr_table').bootstrapTable('refresh');
});

$('#lr_start_date_between').on('cancel.daterangepicker', function (ev, picker) {
    $('#lr_start_date_from').val('');
    $('#lr_start_date_to').val('');
    $('#lr_table').bootstrapTable('refresh');
    $('#lr_start_date_between').val('');
});

$('#lr_end_date_between').on('apply.daterangepicker', function (ev, picker) {
    var startDate = picker.startDate.format('YYYY-MM-DD');
    var endDate = picker.endDate.format('YYYY-MM-DD');

    $('#lr_end_date_from').val(startDate);
    $('#lr_end_date_to').val(endDate);

    $('#lr_table').bootstrapTable('refresh');
});
$('#lr_end_date_between').on('cancel.daterangepicker', function (ev, picker) {
    $('#lr_end_date_from').val('');
    $('#lr_end_date_to').val('');
    $('#lr_table').bootstrapTable('refresh');
    $('#lr_end_date_between').val('');
});


window.icons = {
    refresh: 'bx-refresh',
    toggleOn: 'bx-toggle-right',
    toggleOff: 'bx-toggle-left'
}

function loadingTemplate(message) {
    return '<i class="bx bx-loader-alt bx-spin bx-flip-vertical" ></i>'
}


$(document).ready(function () {
    if(!isAdminOrLe){
        $('.delete-selected ').addClass('d-none');
    }

});