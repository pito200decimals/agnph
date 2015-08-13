{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/list-style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/userlist-style.css" />
{% endblock %}

{% block section_navigation %}
{% endblock %}

{% block sortArrow %}
    {% if orderParam == "desc" %}
        ▼
    {% else %}
        ▲
    {% endif %}
{% endblock %}

{% block content %}
    {{ block('banner') }}
    <div class="mainpanel">
        <h3>Members</h3>
        <form action="/user/list/" method="GET" accept-charset="UTF-8">
            {% if sortParam %}<input type="hidden" name="sort" value="{{ sortParam }}" />{% endif %}
            {% if orderParam %}<input type="hidden" name="order" value="{{ orderParam }}" />{% endif %}
            <label>Search by Name:</label><input class="search" name="search" type="text" value="{{ search }}" required/>
        </form>
        {% if users|length > 0 %}
            <table class="list-table">
                <thead>
                    <tr>
                        <td><strong><a href="{{ statusSortUrl }}">Status</a></strong>{% if sortParam == "status" %}{{ block('sortArrow') }}{% endif %}</td>
                        <td><strong><a href="{{ nameSortUrl }}">Name</a></strong>{% if sortParam == "name" %}{{ block('sortArrow') }}{% endif %}</td>
                        <td><strong><a href="{{ positionSortUrl }}">Position</a></strong>{% if sortParam == "position" %}{{ block('sortArrow') }}{% endif %}</td>
                        <td><strong><a href="{{ registerSortUrl }}">Date Registered</a></strong>{% if sortParam == "register" or not sortParam %}{{ block('sortArrow') }}{% endif %}</td>
                    </tr>
                </thead>
                <tbody>
                    {% for account in users %}
                        <tr>
                            <td>{% if account.online %}ONLINE{% else %}OFFLINE{% endif %}</td>
                            <td><a href="/user/{{ account.UserId }}/">{{ account.DisplayName }}</a></td>
                            <td>{% if account.administrator %}Administrator{% else %}User{% endif %}</td>
                            <td>{{ account.dateJoined }}</td>
                        </tr>
                    {% endfor %}
                </tbody>
            </table>
        {% else %}
            No users found.
        {% endif %}
    </div>
    <div class="iterator">
        {% autoescape false %}{{ iterator }}{% endautoescape %}
    </div>
    <div class="Clear">&nbsp;</div>
{% endblock %}
