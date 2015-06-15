<?php
// Script for the javascript on the viewpost page.
if (!isset($_GET['pi']) || !isset($_GET['ppi'])) die();
$postId = $_GET['pi'];
$parentPoolId = $_GET['ppi'];
$enable_keys = isset($_GET['keynav']);
?>
var in_flight = null;
function toggleEdit() {
    $(".posteditbox").toggle()[0].scrollIntoView();
    return false;
}
$(document).ready(function() {
    $("#tags").keydown(function(e) {
        if (e.keyCode == 13) {
            $(this.form).submit();
            return false;
        }
    });
    $("#pooleditfield").keypress(function() {
        PopulatePoolList(this.value);
    });
    <?php if ($parentPoolId == -1) { ?>
        SetupAdd();
    <?php } else { ?>
        SetupRemove(<?php echo $parentPoolId; ?>);
        <?php if ($enable_keys) { ?>
        InitKeynav();
        <?php } ?>
    <?php } ?>
    SetupFlag();
    SetupComments();
});
function SetupFlag() {
    $("#flagaction").click(function() {
        $(".flageditbox").toggle();
        $("#flag-edit-text").focus();
        return false;
    }).text("Flag for Deletion");
}
function SetupAdd() {
    $("#poolaction").off("click").click(function() {
        $(".pooleditbox").toggle();
        $("#pooleditfield").focus();
        return false;
    }).text("Add to pool");
}
function SetupRemove(poolid) {
    $("#poolaction").off("click").click(function() {
        RemoveFromPool(poolid);
        return false;
    }).text("Remove from pool");
}
function PopulatePoolList(prefix) {
    if (in_flight != null) {
        in_flight.abort();
        in_flight = null;
    }
    $("#poolactionworking").show();
    in_flight = $.ajax("/gallery/pools/list/?prefix="+encodeURIComponent(prefix), {
        success: function(pools) {
            var elements = $();
            for (i = 0; i < pools.length; i++) {
                (function(pool) {
                    elem = $('<li><a href="">Add to <span>'+pool.name+'</span></a></li>').data("id", pool.id);
                    elem.find("a").click(function(e) {
                        e.preventDefault();
                        AddToPool(pool.id);
                        return false;
                    });
                    elements = elements.add(elem);
                })(pools[i]);
            }
            $("#poolautocomplete").empty().append(elements);
            $("#poolactionworking").hide();
        },
        error: function(e) {
            $("#poolactionworking").hide();
        }
    });
}
function AddToPool(poolid) {
    if (in_flight != null) {
        in_flight.abort();
        in_flight = null;
    }
    $("#poolactionworking").show();
    in_flight = $.ajax("/gallery/pools/modify/<?php echo $postId; ?>/"+poolid+"/", {
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
    in_flight = $.ajax("/gallery/pools/addremove_pool.php?post=<?php echo $postId; ?>&pool="+poolid, {
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
function SetupComments() {
    tinymce.init({
        selector: "textarea.commenttextbox",
        plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
        target_list: [ {title: 'New page', value: '_blank'} ],
        toolbar: "undo redo | bold italic underline | bullist numlist | link",
        contextmenu: "image link | hr",
        autoresize_max_height: 150,
        resize: false,
        menubar: false
    });
    $("#commentbutton").click(function() {
        $("#commentbutton").hide();
        $("#commentform").show();
        $("html body").animate(
            { scrollTop: $("#commentform").offset().top },
            { duration: 0,
              complete: function() {
                tinyMCE.get("commenttextbox").focus();
            }});
    });
}
<?php if ($enable_keys) { ?>
function InitKeynav() {
    $(document).keydown(function(e) {
        if ($(document).width() > $(window).width()) return;
        if (e.altKey || e.ctrlKey || e.shiftKey || e.metaKey) return;
        if (e.target.tagName.toLowerCase() == "input") return;
        if (e.target.tagName.toLowerCase() == "textarea") return;
        switch(e.which) {
            case 37:
                var link = $("#previnpool")[0];
                if (link) link.click();
            break;
            case 39:
                var link = $("#nextinpool")[0];
                if (link) link.click();
            break;
            default: return;
        }
        e.preventDefault();
    });
}
<?php } ?>