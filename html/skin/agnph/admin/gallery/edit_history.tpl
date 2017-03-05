{% extends 'admin/skin-base.tpl' %}

{% block sub_section_navigation %}
    <li><a href="/admin/gallery/">Settings</a></li>
    <li><a href="/admin/gallery/tags/">Tags</a></li>
    <li class="selected-admin-tab"><a href="/admin/gallery/edit-history/">Edit History</a></li>
    <li><a href="/admin/gallery/description-history/">Description History</a></li>
    <li><a href="/admin/gallery/log/">Log</a></li>
{% endblock %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/list-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/gallery/style.css') }}" />
    <style>
        .tag-edit {
            margin: 5px;
        }
        #revert-button {
            float: right;
            margin: 5px;
        }
    </style>
{% endblock %}

{% block scripts %}
    {{ parent() }}
    <script>
        $(document).ready(function() {
            $("#revert-all-box").change(function() {
                if ($(this).is(":checked")) {
                    $(".revert-line-box").prop("checked", true);
                } else {
                    $(".revert-line-box").prop("checked", false);
                }
            });
        });
    </script>
{% endblock %}

{% block content %}
    <h3>Gallery Edit History</h3>
    {{ block('banner') }}
    <div class="list-search-bar">
        <form method="GET" accept-charset="UTF-8">
            <div class="search">
                <input class="search" name="search" value="{{ search }}" type="text" required placeholder="Search" />
                <input type="submit" class="search-button" value="" />
            </div>
        </form>
    </div>
    <form action="/admin/gallery/revert-edit/" method="POST" accept-encoding="UTF-8">
        <table class="list-table">
            <thead>
                <tr>
                    <td><strong>Post</strong></td>
                    <td><strong>Date</strong></td>
                    <td><strong>Edited by</strong></td>
                    <td><strong>Tag Changes</strong></td>
                    <td><input type="checkbox" id="revert-all-box" /></td>
                </tr>
            </thead>
            <tbody>
                {% if tagHistoryItems|length > 0 %}
                    {% for item in tagHistoryItems %}
                        <tr>
                            <td><a href="/gallery/post/show/{{ item.PostId }}/">{{ item.PostId }}</a></td>
                            <td style="white-space: nowrap;"><small>{{ item.date }}</small></td>
                            <td><a href="/user/{{ item.user.UserId }}/gallery/">{{ item.user.DisplayName }}</a></td>
                            <td>{% autoescape false %}{{ item.tagChanges }}{% endautoescape %}</td>
                            <td><input class="revert-line-box" type="checkbox" name="revert-id[]" value="{{ item.Id }}" /></td>
                        </tr>
                    {% endfor %}
                {% else %}
                    <tr>
                        <td></td>
                        <td colspan="4">No tag history found</td>
                    </tr>
                {% endif %}
            </tbody>
        </table>
        <input id="revert-button" type="submit" value="Revert selected" />
    </form>
    <div class="Clear">&nbsp;</div>
    <div class="iterator">
        {% autoescape false %}{{ postIterator }}{% endautoescape %}
    </div>
{% endblock %}
