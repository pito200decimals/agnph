{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/list-style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/userlist-style.css" />
{% endblock %}

{% block scripts %}
    {{ parent() }}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ skinDir }}/timezone.js"></script>
    {% endif %}
{% endblock %}

{% block sortArrow %}
    {% if orderParam == "desc" %}
        ▼
    {% else %}
        ▲
    {% endif %}
{% endblock %}

{% block content %}
    <h3>Users</h3>
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
                    <td><span class="omit-mobile"><strong><a href="{{ registerSortUrl }}">Date Registered</a></strong>{% if sortParam == "register" %}{{ block('sortArrow') }}{% endif %}</span></td>
                </tr>
            </thead>
            <tbody>
                {% for account in users %}
                    <tr>
                        <td>
                            {% if account.online %}
                                <div class="status-container">
                                    <span class="status-alignment" />
                                    <img class="status-icon" src="/images/user-online.png" />
                                    <img class="user-avatar" src="{{ account.avatarURL }}" />
                                </div>
                            {% else %}
                                <div class="status-container">
                                    <span class="status-alignment" />
                                    <img class="status-icon"  src="/images/user-offline.png" />
                                </div>
                            {% endif %}
                        </td>
                        <td><a href="/user/{{ account.UserId }}/">{{ account.DisplayName }}</a></td>
                        <td>{% if account.administrator %}Administrator{% else %}User{% endif %}</td>
                        <td><span class="omit-mobile">{{ account.dateJoined }}</span></td>
                    </tr>
                {% endfor %}
            </tbody>
        </table>
        <div class="iterator">
            {% autoescape false %}{{ iterator }}{% endautoescape %}
        </div>
    {% else %}
        No users found.
    {% endif %}
{% endblock %}
