$(document).ready(function() {
    var d = new Date();
    var offset = d.getTimezoneOffset();
    $.ajax("/timezone/", {
        data: {
            offset: offset
        },
        method: "POST",
    });
});
