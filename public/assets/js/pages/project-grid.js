$('#sort').on('change', function (e) {
    var sort = $(this).val();
    location.href = setUrlParameter(location.href, 'sort', sort);
});

$('#filter').click(function () {
    // Get the selected values from status select and other filters
    var statuses = $('#selected_statuses').val(); // Array of selected statuses
    var sort = $('#sort').val();
    // Get selected tags using Select2
    var selectedTags = $('#selected_tags').val(); // Array of selected tags

    // Form the URL with the selected filters
    var url = baseUrl + "/projects";
    var params = [];

    if (statuses && statuses.length > 0) {
        params.push("statuses[]=" + statuses.join("&statuses[]="));
    }

    if (sort) {
        params.push("sort=" + sort);
    }

    if (selectedTags && selectedTags.length > 0) {
        params.push("tags[]=" + selectedTags.join("&tags[]="));
    }

    if (params.length > 0) {
        url += "?" + params.join("&");
    }

    // Redirect to the URL
    window.location.href = url;
});

// Helper function to set URL parameter for arrays
function setUrlParameterArray(url, paramName, paramValues) {
    if (!Array.isArray(paramValues)) {
        return url;
    }
    
    paramValues = paramValues.map(value => encodeURIComponent(value)).join("&" + paramName + "=");
    
    var pattern = new RegExp('\\b(' + paramName + '=).*?(&|#|$)');
    if (url.search(pattern) >= 0) {
        return url.replace(pattern, '$1' + paramValues + '$2');
    }
    
    url = url.replace(/[?#]$/, '');
    return url + (url.indexOf('?') > 0 ? '&' : '?') + paramName + '=' + paramValues;
}
