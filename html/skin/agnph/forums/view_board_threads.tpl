{% extends 'forums/view_board_base.tpl' %}

{% block sortArrow %}
    {% if orderParam == "desc" %}
        ▼
    {% else %}
        ▲
    {% endif %}
{% endblock %}

{% block content %}
    <h3>{{ board.Name }}</h3>
    {% if threads|length > 0 %}
    <table class="list-table">
        <thead>
            <tr>
                <td></td>
                <td><a href="{{ titleSortUrl }}">Title</a>{% if sortParam == "title" %}{{ block('sortArrow') }}{% endif %}</td>
                <td><a href="{{ repliesSortUrl }}">Replies</a>{% if sortParam == "replies" %}{{ block('sortArrow') }}{% endif %} / <a href="{{ viewsSortUrl }}">Views</a>{% if sortParam == "views" %}{{ block('sortArrow') }}{% endif %}</td>
                <td><a href="{{ lastpostSortUrl }}">Last Post</a>{% if sortParam == "lastpost" %}{{ block('sortArrow') }}{% endif %}</td>
            </tr>
        </thead>
        <tbody>
            {% for thread in threads %}
                <tr>
                    <td>{% if thread.Sticky %}[STICKY]{% endif %}{% if thread.Locked %}[LOCKED]{% endif %}</td>
                    <td>
                        <a href="/forums/thread/{{ thread.PostId }}/">{{ thread.Title }}</a><br />
                        Started by <a href="/user/{{ thread.user.UserId }}/">{{ thread.user.DisplayName }}</a>
                    </td>
                    <td>
                        {{ thread.Replies }} replies<br />
                        {{ thread.Views }} views
                    </td>
                    <td>Last Post Date/User</td>
                </tr>
            {% endfor %}
        </tbody>
    </table>
    {#
    <ul class="list">
        {% for thread in threads %}
            <li>{{ thread.Title }}</li>
        {% endfor %}
    </ul>#}
    <div class="iterator">
        {% autoescape false %}{{ iterator }}{% endautoescape %}
    </div>
    {% else %}
        No threads found
    {% endif %}
{% endblock %}
