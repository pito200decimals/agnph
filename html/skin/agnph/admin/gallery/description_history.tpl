{% extends 'admin/skin-base.tpl' %}

{% block sub_section_navigation %}
    <li><a href="/admin/gallery/">Settings</a></li>
    <li><a href="/admin/gallery/tags/">Tags</a></li>
    <li><a href="/admin/gallery/edit-history/">Edit History</a></li>
    <li class="selected-admin-tab"><a href="/admin/gallery/description-history/">Description History</a></li>
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
            {% if tagHistoryItems|length > 0 %}
                {% for item in tagHistoryItems %}
                    <tr>
                        <td><a href="/gallery/post/show/{{ item.PostId }}/">{{ item.PostId }}</a></td>
                        <td style="white-space: nowrap;"><small>{{ item.date }}</small></td>
                        <td><a href="/user/{{ item.user.UserId }}/gallery/">{{ item.user.DisplayName }}</a></td>
                        <td>{{ item.Description }}</td>
                    </tr>
                {% endfor %}
            {% else %}
                <tr>
                    <td></td>
                    <td colspan="3">No description history found</td>
                </tr>
            {% endif %}
        </tbody>
    </table>
    <div class="Clear">&nbsp;</div>
    <div class="iterator">
        {% autoescape false %}{{ postIterator }}{% endautoescape %}
    </div>
{% endblock %}
