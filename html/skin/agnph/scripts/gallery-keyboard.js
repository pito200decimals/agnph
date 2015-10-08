$(document).ready(function() {
    $(document).keydown(function(e) {
        if ($(document).width() > $(window).width()) return;
        if (e.altKey || e.ctrlKey || e.shiftKey || e.metaKey) return;
        if (e.target.tagName.toLowerCase() == "input" || e.target.tagName.toLowerCase() == "textarea") {
            switch (e.which) {
                case 27:
                    $(e.target).blur();
                break;
            }
            return;
        }
        switch (e.which) {
            case 37:
                var link = $("#previnpool")[0];
                if (link) link.click();
            break;
            case 39:
                var link = $("#nextinpool")[0];
                if (link) link.click();
            break;
            case 68:
                document.getElementById("download-link").click()
            break;
            case 69:
                $(".posteditbox").show()[0].scrollIntoView();
                if ($('.autocomplete-tag-input').length > 0) {
                    $('.autocomplete-tag-input').focus();
                } else if ($('#tags').length > 0) {
                    $('#tags').focus();
                }
            break;
            case 80:
                $(".posteditbox").show()[0].scrollIntoView();
                $('#parent').focus();
            break;
            case 83:
                $(".searchbox .search").focus().select();
            break;
            default: return;
        }
        e.preventDefault();
    });
});