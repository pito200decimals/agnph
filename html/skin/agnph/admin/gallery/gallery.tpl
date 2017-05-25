{% extends 'admin/skin-base.tpl' %}

{% block sub_section_navigation %}
    <li class="selected-admin-tab"><a href="/admin/gallery/">Settings</a></li>
    <li><a href="/admin/gallery/tags/">Tags</a></li>
    <li><a href="/admin/gallery/edit-history/">Edit History</a></li>
    <li><a href="/admin/gallery/description-history/">Description History</a></li>
    <li><a href="/admin/gallery/log/">Log</a></li>
{% endblock %}

{% block styles %}
    {{ parent() }}
    <style>
        td {
            vertical-align: top;
            padding-bottom: 10px;
        }
    </style>
{% endblock %}

{% block scripts %}
    {{ parent() }}
    <script src="{{ asset('/scripts/jquery.sortable.js') }}"></script>
    <script>
        function AddReason(reason) {
            if (reason == undefined || reason == null) {
                reason = $("#add-reason-text").val();
            }
            if (reason.length == 0) return;
            $("#add-reason-text").val("");
            $("#flag-reason-list").append($('<li>'+reason+'<input type="hidden" name="flag_reason[]" value="'+reason+'" /><input style="margin-left: 10px;" type="button" value="Delete" onclick="DeleteReason(this)" /></li>'));
            $(".sortable").sortable('destroy').sortable();
        }
        function DeleteReason(e) {
            $(e).parent().remove();
            $(".sortable").sortable('destroy').sortable();
        }
        $(document).ready(function() {
            {% for reason in flag_reasons %}
                AddReason("{{ reason }}");
            {% endfor %}

            $(".sortable").css("cursor", "move");
        });
    </script>
{% endblock %}

{% block content %}
    <h3>Gallery Administrator Control Panel</h3>
    {{ block('banner') }}
    <form action="" method="POST" accept-encoding="UTF-8">
        <table>
            <tr><td><label>Board for News Posts:</label></td><td><input name="news-posts-board" type="text" value="{{ news_posts_board }}" /></td></tr>
            <tr><td colspan="2">
                <strong>Flag Reasons:</strong>
                <ul id="flag-reason-list" class="sortable"></ul>
                <input type="search" id="add-reason-text" />
                <input type="button" onclick="AddReason()" value="Add Flag Reason" />
            </td></tr>
        </table>
        <input type="submit" name="submit" value="Save Changes" />
    </form>
{% endblock %}
