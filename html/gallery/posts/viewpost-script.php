<?php
// Script for the javascript on the viewpost page.
include_once("../../includes/constants.php");
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
    SetupEdit();
});
function prefix(input) {
    var index = input.indexOf(":");
    if (index > -1) {
        return input.substring(0, index);
    } else {
        return "";
    }
}
function suffix(input) {
    var index = input.indexOf(":");
    if (index > -1) {
        input = input.substring(index + 1);
    }
    return input;
}
function getExistingTag(tag) {
    suf = suffix(tag);
    var t = $('#edit-taglist li').filter(function(i,e) {
        tstr = suffix(e.innerHTML);
        return tstr == suf;
    });
    if (t.length == 0) return null;
    else return t;
}
function hasTag(tag) {
    return getExistingTag(tag) != null;
}
function SetupEdit() {
    if ($('#edit-taglist').length > 0) {
        $('#tag-input').autocomplete({
            serviceUrl: '/gallery/tagsearch/',
            onSelect: function(suggestion) {
                AddTag(suggestion.data.type);
            },
            transformResult: function(response, originalQuery) {
                response = JSON.parse(response);
                var remaining = $.grep(response.suggestions, function(tagData) {
                        return !hasTag(tagData.value);
                    });
                return {
                    suggestions: remaining
                };
            },
            showNoSuggestionNotice: true,
            tabDisabled: true,
            triggerSelectOnValidInput: false
        }).keydown(function(event) {
            if (event.keyCode == 13 || event.keyCode == 32) {
                AddTag(null);
                event.preventDefault();
                return false;
            }
        });
        $('#edit-taglist li').click(function() {
            RemoveTag($(this));
        });
    }
}
function AddTag(type) {
    var tag = $('#tag-input').val().toLowerCase();
    if (tag.length == 0) return;
    $('#tag-input').val("");
    var pre = prefix(tag);
    var suf = suffix(tag);
    var preclass = null;
    if (pre != null) {
    <?php
        foreach ($GALLERY_TAG_TYPES as $letter => $name) {
            $lower_letter_class = strtolower($letter)."typetag";
            $lower_name = strtolower($name);
            echo <<<EOF
if (pre.toLowerCase() == '$lower_name') {
    preclass = '$lower_letter_class';
}
EOF
;
        }
    ?>
    }
    if (hasTag(suf)) {
        var existing_tag = getExistingTag(suf);
        if (preclass == null || existing_tag.hasClass(preclass)) return;
        existing_tag.detach();
    }
    var elem = $('<li>'+tag+'</li>');
    $('#edit-taglist').append(elem);
    elem.click(function() {
        RemoveTag($(this));
    });
    if (preclass != null) {
        elem.addClass(preclass);
        return;
    }
    if(type != null) {
        elem.addClass(type+"typetag");
        return;
    }
    $.ajax('/gallery/tagsearch/', {
        data: { query: tag },
        success: function(val) {
            type = 'g'
            if (val.suggestions.length > 0) {
                for (i=0; i < val.suggestions.length; i++) {
                    if (val.suggestions[i].value == tag) {
                        type = val.suggestions[i].data.type;
                        break;
                    }
                }
            }
            elem.removeClass().addClass(type+"typetag");
        }
    });
}
function OnEditSubmit() {
    if ($('#edit-taglist').length > 0) {
        var tags = $('#edit-taglist li').map(function(i, opt) {
            return $(opt).text();
        }).toArray().join(' ');
        $('#tags').val(tags);
    }
}
function RemoveTag(elem) {
    elem.detach();
}
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
    in_flight = $.ajax("/gallery/pools/modify/<?php echo $postId; ?>/"+poolid+"/", {
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