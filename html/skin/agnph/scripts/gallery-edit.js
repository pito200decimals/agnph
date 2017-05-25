function toggleEdit() {
    $(".posteditbox").show()[0].scrollIntoView();
    if ($('.autocomplete-tag-input').length > 0) {
        $('.autocomplete-tag-input').focus();
    } else if ($('#tags').length > 0) {
        $('#tags').focus();
    }
    return false;
}
$(document).ready(function() {
    if ($("#tags").length > 0) {
        $("#tags").keydown(function(e) {
            if (e.keyCode == 13) {
                $(this.form).submit();
                return false;
            }
        });
    }
});



// Pools.
var in_flight = null;
$(document).ready(function() {
    $("#pool-edit-field").autocomplete({
        serviceUrl: '/gallery/poolsearch/',
        onSelect: function(suggestion) {
            AddToPool(suggestion.data.id);
        },
        showNoSuggestionNotice: true,
        tabDisabled: true,
        triggerSelectOnValidInput: false
    });
    if (ppi == -1) {
        SetupAdd();
    } else {
        SetupRemove(ppi);
    }
});
function SetupAdd() {
    $("#poolaction").off("click").click(function() {
        $("#pooleditbox").toggle();
        $("#pool-edit-field").focus();
        return false;
    }).text("Add to pool");
}
function SetupRemove(poolid) {
    $("#poolaction").off("click").click(function() {
        RemoveFromPool(poolid);
        return false;
    }).text("Remove from pool");
}
function AddToPool(poolid) {
    if (in_flight != null) {
        in_flight.abort();
        in_flight = null;
    }
    $("#poolactionworking").show();
    in_flight = $.ajax("/gallery/pools/modify/"+pi+"/"+poolid+"/", {
        data: {
            action: "add"
        },
        method: "POST",
        success: function() {
            location.reload();
        },
        error: function() {
            location.reload();
        }
    });
}
function RemoveFromPool(poolid) {
    if (in_flight != null) {
        in_flight.abort();
        in_flight = null;
    }
    $("#poolactionworking").show();
    in_flight = $.ajax("/gallery/pools/modify/"+pi+"/"+poolid+"/", {
        data: {
            action: "remove"
        },
        method: "POST",
        success: function() {
            location.reload();
        },
        error: function() {
            location.reload();
        }
    });
}

function RefreshFlagReasonExtraTextBox() {
    var reason = $("#flag-reason-select").find(":selected").text();
    if (reason.includes("#")) {
        $("#extra-reason-text").show().prop("required", true);
    } else {
        $("#extra-reason-text").hide().prop("required", false);
    }
}


// Flagging.
$(document).ready(function() {
    $("#flagaction").click(function() {
        RefreshFlagReasonExtraTextBox();
        $(".flageditbox").toggle();
        $("#flag-reason-select").focus();
        return false;
    }).text("Flag for Deletion");
    $("#flag-reason-select").change(RefreshFlagReasonExtraTextBox);
});
