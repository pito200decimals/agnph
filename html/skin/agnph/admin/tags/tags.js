﻿var tag_data = null;
$(document).ready(function() {
    var in_flight_ajax = null;
    function DoSearchAjax(do_create) {
        if (in_flight_ajax) {
            in_flight_ajax.abort();
        }
        in_flight_ajax = DoAjax(do_create, SelectTag);
    }
    $("#search, #tag-filter, #alias-filter, #implication-filter").change(function() {DoSearchAjax(false);});
    $("#create-filter").click(function() {DoSearchAjax(true);});
    $("#tag-list").change(function(e) {
        var elem = $("#tag-list option:selected")[0];
        var tag = $.data(elem, "tag");
        SelectTag(tag);
    });
    $("#update-tag-counts").click(UpdateAllTagCounts);
    $("#processing-span").hide();
    DoAjax(false);
});
function DoAjax(do_create, cb) {
    var searchTerm = $("#search").val();
    var filter = "tag";
    if ($("#tag-filter").is(":checked")) filter = "tag";
    if ($("#alias-filter").is(":checked")) filter = "alias";
    if ($("#implication-filter").is(":checked")) filter = "implication";
    if (do_create) {
        if (searchTerm.length == 0) {
            cb(false);
        }
        filter = "create";
    }
    $("#searching-span").show();
    return $.ajax({
        url: "/admin/" + SECTION + "/fetch_tag/",
        data: {
            search: searchTerm,
            filter: filter
        },
        dataType: "json",
        method: "GET",
        success: function(data) {
            $("#searching-span").hide();
            in_flight_ajax = null;
            if (filter == "create") $("#tag-filter").prop("checked", true);
            SetTags(data);
            $("#tag-container").empty();
            if (cb) cb();
        },
        error: function() {
            $("#searching-span").hide();
        }});
}
function SetTags(tags) {
    var tag_list = $("#tag-list");
    tag_list.empty();
    if (tags.length > 0) {
        tags.forEach(function(tag) {
            var option_text = tag.name + " (" + tag.count + ")";
            if (tag.alias != null) {
                option_text += " → " + tag.alias.name + " (" + tag.alias.count + ")";
            }
            var option = $("<option>"+option_text+"</option>");
            option.addClass(tag.class).val(tag.name);
            if (tag.hide_tag == true) {
                option.addClass("hidden-autocomplete-strikethrough");
            }
            if (tag.addLock == true) {
                option.css("font-weight", "bold");
            }
            tag_list.append(option);
            $.data(option[0], "tag", tag);
        });
    }
}
function SelectTag(tag) {
    var container = $("#tag-container");
    container.empty();
    if (tag) {
        container.append($("<h3>Tag: <span class='"+tag.class+"'>"+tag.name+"</span></h3>"));
        var type_select = $("<select id='tag-type'></select>");
        TYPE_LIST.forEach(function(item) {
            type_select.append($("<option "+(tag.type==item?"selected":"")+">"+item+"</option>"));
        });
        container.append($("<p></p>").append($("<label>Type:</label>")).append(type_select));
        container.append($("<p><label>Type Lock:</label><select id='edit-lock'><option "+(tag.editLock==1?"":"selected")+">- - -</option><option "+(tag.editLock==1?"selected":"")+">Locked</option></select></p>"));
        container.append($("<p><label>Add/Remove Lock:</label><select id='add-lock'><option "+(tag.addLock==1?"":"selected")+">- - -</option><option "+(tag.addLock==1?"selected":"")+">Locked</option></select></p>"));
        container.append($("<p><label>Alias:</label><input id='alias' type='text' value='"+(tag.alias==null?"":tag.alias.name)+"' /><span class='radio-button-group'><input type='checkbox' id='hide-tag' value='hide' "+(tag.hide_tag==true?"checked":"")+"/>Hide tag from Autocomplete</span></p>"));
        if (tag.aliased_by != null && tag.aliased_by.length > 0) {
            var alias_list = $("<ul style='list-style: none; display: inline-block; padding: 0px; margin: 0px;'></ul>");
            tag.aliased_by.forEach(function(tag) {
                alias_list.append($("<li style='display: inline-block; margin-left: 5px; margin-right: 5px;'><span class='"+tag.class+"'>"+tag.name+"</span></li>"));
            });
            container.append($("<p></p>").append($("<label>Aliased by:</label>")).append(alias_list));
        }
        var implied_list = "";
        if (tag.implies != null && tag.implies.length > 0) {
            implied_list = tag.implies.map(function(tag) { return tag.name; }).join(" ");
        }
        container.append($("<p><label style='vertical-align: top;'>Implies:</label><textarea style='width: 250px; height: 50px;' id='implied-tags'>"+implied_list+"</textarea>"));
        if (tag.implied_by != null && tag.implied_by.length > 0) {
            var implied_by_list = $("<ul style='list-style: none; display: inline-block; padding: 0px; margin: 0px;'></ul>");
            tag.implied_by.forEach(function(tag) {
                implied_by_list.append($("<li style='display: inline-block; margin-left: 5px; margin-right: 5px;'><span class='"+tag.class+"'>"+tag.name+"</span></li>"));
            });
            container.append($("<p></p>").append($("<label>Implied by:</label>")).append(implied_by_list));
        }
        container.append($("<p><label style='vertical-align: top;'>Notes:</label><textarea style='width: 250px; height: 75px;' id='note'>"+tag.note+"</textarea></p>"));
        container.append($("<p><input id='save-button' type='button' value='Save Changes' /></p>"));
        InitFormEvents();
        tag_data = tag;
    }
}
function SaveChanges(cb) {
    $("#save-button, #search").prop("disabled", true);
    $.ajax({
        url: "/admin/" + SECTION + "/save_tag/",
        data: {
            id: tag_data.id,
            type: $("#tag-type").val(),
            edit: $("#edit-lock").val(),
            add: $("#add-lock").val(),
            alias: $("#alias").val(),
            hide_tag: $("#hide-tag").is(':checked'),
            implied: $("#implied-tags").val(),
            note: $("#note").val()
        },
        method: "POST",
        success: function() {
            $("#save-button, #search").prop("disabled", false);
            cb(true);
        },
        error: function() {
            $("#save-button, #search").prop("disabled", false);
            cb(false);
        }});
}
function InitFormEvents() {
    $("#edit-lock").change(function() {
        if ($(this).val() == "Locked") {
            $("#tag-type").prop("disabled", true);
        } else {
            $("#tag-type").prop("disabled", false);
        }
    }).change();
    $("#save-button").click(function() {
        SaveChanges(function(success) {
            if (!success) alert("Failed to save changes");
            var selected_value = $("#tag-list").val();
            $("#tag-list").val("");
            DoAjax(false, function() {
                if ($("#tag-list option[value='"+selected_value+"']").length > 0) {
                    $("#tag-list").val(selected_value).change();
                }
            });
        });
    });
}
function UpdateAllTagCounts() {
    $("#update-tag-counts").prop("disabled", true);
    $("#processing-span").show();
    $.ajax({
        url: "/admin/" + SECTION + "/update_tag_counts/",
        data: {},
        dataType: "html",
        method: "POST",
        success: function() {
            $("#processing-span").hide();
            $("#update-tag-counts").prop("disabled", false);
        },
        error: function() {
            $("#processing-span").hide();
            $("#update-tag-counts").prop("disabled", false);
            alert("Failed to update tag counts");
        }});
}