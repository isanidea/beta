var href_arr = window.location.href.split('.html?id=');
if (!href_arr[1] || isNaN(href_arr[1])) {
    window.location.href = 'index.html';
} else {
    var id = href_arr[1];
}
