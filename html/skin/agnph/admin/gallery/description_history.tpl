{% extends 'admin/base.tpl' %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li><a href="/admin/gallery/">Settings</a></li>
        <li><a href="/admin/gallery/tags/">Tags</a></li>
        <li><a href="/admin/gallery/edit-history/">Edit History</a></li>
        <li id="selected-gallery-tab"><a href="/admin/gallery/description-history/">Description History</a></li>
    </ul>
{% endblock %}

{% block styles %}
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
        #gallery-admin-tab {
            background-color: rgb(191,223,255);
        }
        #selected-gallery-tab {
            background-color: rgb(191,223,255);
        }
    </style>
{% endblock %}

{% block content %}
    <h3>Gallery Edit History</h3>
    {{ block('banner') }}
    <form method="GET" accept-encoding="UTF-8">
    Search: <input type="text" class="search" name="search" value="{{ search }}" required />
    </form>
    {% if tagHistoryItems|length > 0 %}
        {# Display tag history index. #}
        <table class="list-table">
            <thead>
                <tr>
                    <td><strong>Post</strong></td>
                    <td><strong>Date</strong></td>
                    <td><strong>Edited by</strong></td>
                    <td><strong>Description</strong></td>
                </tr>
            </thead>
            <tbody>
                {% for item in tagHistoryItems %}
                    <tr>
                        <td><a href="/gallery/post/show/{{ item.PostId }}/">{{ item.PostId }}</a></td>
                        <td>{{ item.date }}</td>
                        <td><a href="/user/{{ item.user.UserId }}/gallery/">{{ item.user.DisplayName }}</a></td>
                        <td>{{ item.Description }}</td>
                    </tr>
                {% endfor %}
            </tbody>
        </table>
        <div class="Clear">&nbsp;</div>
        <div class="iterator">
            {% autoescape false %}{{ postIterator }}{% endautoescape %}
        </div>
    {% else %}
        {# No history items here. #}
        No tag history found.
    {% endif %}
{% endblock %}
