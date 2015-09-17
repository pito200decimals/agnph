
$(document).ready(function() {
    if ($('#edit-taglist').length > 0) {
        $('#tag-input').autocomplete({
            serviceUrl: tag_search_url,
            onSelect: function(suggestion) {
                AddTag($('#tag-input').val().trim().toLowerCase(), suggestion.data.type);
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
            if (event.keyCode == 13 && $('#tag-input').val().trim().length == 0) return;
            if (event.keyCode == 13 || event.keyCode == 32) {
                AddTag($('#tag-input').val().trim().toLowerCase(), null);
                event.preventDefault();
                return false;
            }
        });
        $('#edit-taglist li .close').click(function() {
            RemoveTag($(this).parent());
        });
        $('#edit-taglist li').mousedown(function(e) {
            e.preventDefault();
        });
    }
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
        inner = e.innerHTML;
        inner = inner.substr(0, inner.indexOf('<span')).trim();
        tstr = suffix(inner);
        return tstr == suf;
    });
    if (t.length == 0) return null;
    else return t;
}
function hasTag(tag) {
    return getExistingTag(tag) != null;
}
function AddTag(tag, type) {
    if (tag.length == 0) return;
    $('#tag-input').val("");
    var pre = prefix(tag);
    var suf = suffix(tag);
    var preclass = null;
    if (pre != null) {
        preclass = GetPreclass(pre);
    }
    if (hasTag(suf)) {
        var existing_tag = getExistingTag(suf);
        if (preclass == null || existing_tag.hasClass(preclass)) return;
        existing_tag.detach();
    }
    var elem = $('<li>'+tag+'<span class="close">&nbsp;</span></li>');
    var close = elem.find('.close');
    $('#edit-taglist').append(elem);
    close.click(function() {
        RemoveTag($(elem));
    });
    if (preclass != null) {
        elem.addClass(preclass);
        return;
    }
    if(type != null) {
        elem.addClass(type+"typetag");
        return;
    }
    $.ajax(tag_search_url, {
        data: { query: tag },
        success: function(val) {
            type = 'm'
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
            return $(opt).clone().children().remove().end().text().trim();
        }).toArray().join(' ');
        $('#tags').val(tags);
        console.log(tags);
        console.log($('#tags').val())
    }
}
function RemoveTag(elem) {
    elem.detach();
}
