{% extends 'admin/base.tpl' %}

{% block scripts %}
    <script>
        var tag_data = null;
        $(document).ready(function() {
            var in_flight_ajax = null;
            $("#search").change(function() {
                if (in_flight_ajax) {
                    in_flight_ajax.abort();
                }
                in_flight_ajax = DoAjax(SelectTag);
            });
            $("#tag-list").change(function(e) {
                var elem = $("#tag-list option:selected")[0];
                var tag = $.data(elem, "tag");
                SelectTag(tag);
            });
            DoAjax();
        });
        function DoAjax(cb) {
            var searchTerm = $("#search").val();
            $("#searching-span").show();
            return $.ajax({
                url: "/admin/{{ section }}/fetch_tag_ajax.php",
                data: {
                    search: searchTerm
                },
                dataType: "json",
                method: "GET",
                success: function(data) {
                    $("#searching-span").hide();
                    in_flight_ajax = null;
                    SetTags(data);
                    if (cb) cb();
                },
                error: function() {
                    $("#searching-span").hide();
                }});
        }
        function SetTags(tags) {
            var tag_list = $("#tag-list");
            tag_list.empty();
            if (tags.length == 0) {
                tag_list.append("<p>No tags found</p>");
            } else {
                tags.forEach(function(tag) {
                    var option = $("<option class='"+tag.class+"'>"+tag.name+"</option>");
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
                {% block type_list %}[]{% endblock %}.forEach(function(item) {
                    type_select.append($("<option "+(tag.type==item?"selected":"")+">"+item+"</option>"));
                });
                container.append($("<p></p>").append($("<label>Type:</label>")).append(type_select));
                container.append($("<p><label>Type Lock:</label><select id='edit-lock'><option "+(tag.editLock==1?"":"selected")+">- - -</option><option "+(tag.editLock==1?"selected":"")+">Locked</option></select></p>"));
                container.append($("<p><label>Add Lock:</label><select id='add-lock'><option "+(tag.addLock==1?"":"selected")+">- - -</option><option "+(tag.addLock==1?"selected":"")+">Locked</option></select></p>"));
                container.append($("<p><input id='save-button' type='button' value='Save Changes'/></p>"));
                InitFormEvents();
                tag_data = tag;
            }
        }
        function SaveChanges(cb) {
            $("#save-button, #search").prop("disabled", true);
            $.ajax({
                url: "/admin/{{ section }}/save_tag_ajax.php",
                data: {
                    id: tag_data.id,
                    type: $("#tag-type").val(),
                    edit: $("#edit-lock").val(),
                    add: $("#add-lock").val()
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
                    DoAjax(function() {
                        $("#tag-list").val(selected_value).change();
                    });
                });
            });
        }
    </script>
{% endblock %}

{% block content %}
    <h3>{% block section %}[Section]{% endblock %} Tags</h3>
    <p>Tag: <input id="search" type="text" />&nbsp;<small id="searching-span">Searching...</small></p>
    <select id="tag-list" size="10" style="width: 100%;">
    </select>
    <div id="tag-container">
    </div>
{% endblock %}
