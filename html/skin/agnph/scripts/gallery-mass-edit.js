$(document).ready(function() {
    $("#mass-tag-edit-toggle").click(function() {
        $("#mass-edit-action").val("tagedit");
        $("#mass-tag-edit").toggle();
    });
    $("#delete-all-button").click(function() {
        if (confirm("Are you absolutely sure you want to delete all posts in this search?")) {
            $("#mass-edit-action").val("delete");
            $("#mass-tag-edit").hide();
            $("#mass-edit-submit-button").click();
        }
    });
});
