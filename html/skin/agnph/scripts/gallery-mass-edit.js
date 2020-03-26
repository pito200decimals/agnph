$(document).ready(function() {
    let originalSearch = $("input.search").val();
    let selectedPostIds = new Set();
    let toggleSelection = e => {
        e.preventDefault();
        let elem = e.currentTarget;
        let postId = elem.dataset.postid;
        if (selectedPostIds.has(postId)) {
            selectedPostIds.delete(postId);
            elem.classList.remove("mass-edit-selected");
        } else {
            selectedPostIds.add(postId);
            elem.classList.add("mass-edit-selected");
        }
        let enableEdits = (selectedPostIds.size === 0);
        $("#mass-edit-submit-button").prop("disabled", !enableEdits);
        $("#delete-all-button").prop("disabled", !enableEdits);
        let searchString = "";
        if (selectedPostIds.size === 0) {
            searchString = originalSearch;
        } else {
            selectedPostIds.forEach(v => {
                searchString = searchString + ` ~id:${v}`;
            });
            searchString = searchString.trim();
        }
        $("input.search").val(searchString);
    };
    $("#mass-tag-edit-toggle").click(function() {
        $("#mass-edit-action").val("tagedit");
        $("#mass-tag-edit").toggle();
        if ($("#mass-tag-edit").is(":visible")) {
            $(".postlink").on("click", toggleSelection);
        } else {
            $(".postlink").off("click", toggleSelection);
        }
    });
    $("#delete-all-button").click(function() {
        if (confirm("Are you absolutely sure you want to delete all posts in this search?")) {
            $("#mass-edit-action").val("delete");
            $("#mass-tag-edit").hide();
            $("#mass-edit-submit-button").click();
        }
    });
});
