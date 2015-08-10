{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/userlist-style.css" />
{% endblock %}

{% block section_navigation %}
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
            <table class="user-table">
                <thead>
                    <tr>
                        <td><div><a href="{{ statusSortUrl }}">Status</a></div></td>
                        <td><div><a href="{{ nameSortUrl }}">Name</a></div></td>
                        <td><div><a href="{{ positionSortUrl }}">Position</a></div></td>
                        <td><div><a href="{{ registerSortUrl }}">Date Registered</a></div></td>
                    </tr>
                </thead>
                <tbody>
                    {% for account in users %}
                        <tr>
                            <td><div>{% if account.online %}ONLINE{% else %}OFFLINE{% endif %}</div></td>
                            <td><div><a href="/user/{{ account.UserId }}/">{{ account.DisplayName }}</a></div></td>
                            <td><div>{% if account.administrator %}Administrator{% else %}User{% endif %}</div></td>
                            <td><div>{{ account.dateJoined }}</div></td>
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
