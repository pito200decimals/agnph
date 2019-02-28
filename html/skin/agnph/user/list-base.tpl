{% extends "base.tpl" %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/list-style.css')|raw }}
    {{ inline_css_asset('/user/userlist-style.css')|raw }}
{% endblock %}

{% block scripts %}
    {{ parent() }}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ asset('timezone.js') }}"></script>
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
    <div class="list-search-bar">
        <form action="/user/list/" method="GET" accept-charset="UTF-8">
            {% if sortParam %}<input type="hidden" name="sort" value="{{ sortParam }}" />{% endif %}
            {% if orderParam %}<input type="hidden" name="order" value="{{ orderParam }}" />{% endif %}
            <label>Search by Name:</label>
            <div class="search">
                <input class="search" name="search" value="{{ search }}" type="text" required placeholder="Search" onfocus="javascript:$(this).attr('placeholder', '');" onblur="javascript:$(this).attr('placeholder', 'Search');" />
                <input type="submit" class="search-button" value="" />
            </div>
        </form>
    </div>
    <table class="list-table">
        <thead>
            <tr>
                <td><strong><a href="{{ statusSortUrl }}">Status</a></strong>{% if sortParam == "status" %}{{ block('sortArrow') }}{% endif %}</td>
                <td><strong><a href="{{ nameSortUrl }}">Name</a></strong>{% if sortParam == "name" %}{{ block('sortArrow') }}{% endif %}</td>
                <td><strong><a href="{{ positionSortUrl }}">Position</a></strong>{% if sortParam == "position" %}{{ block('sortArrow') }}{% endif %}</td>
                <td><span class="desktop-only"><strong><a href="{{ registerSortUrl }}">Date Registered</a></strong>{% if sortParam == "register" %}{{ block('sortArrow') }}{% endif %}</span></td>
                <td><span class="desktop-only"><strong>Viewing Page</strong></span></td>
            </tr>
        </thead>
        <tbody>
            {% if users|length > 0 %}
                {% for account in users %}
                    <tr>
                        <td>
                            {% if account.online %}
                                <div class="status-container">
                                    <span class="status-alignment" />
                                    <img class="status-icon" src="/images/user-online.png" />
                                    {% if account.hasAvatar %}<img class="user-avatar" src="{{ account.avatarURL }}" />{% endif %}
                                </div>
                            {% else %}
                                <div class="status-container">
                                    <span class="status-alignment" />
                                    <img class="status-icon"  src="/images/user-offline.png" />
                                    {% if account.hasAvatar %}<img class="user-avatar" src="{{ account.avatarURL }}" />{% endif %}
                                </div>
                            {% endif %}
                        </td>
                        <td><a href="/user/{{ account.UserId }}/">{{ account.DisplayName }}</a></td>
                        <td>
                            {% if account.administrator %}
                                Administrator
                            {% elseif account.banned %}
                                Banned
                            {% elseif account.inactive %}
                                Inactive User
                            {% else %}
                                User
                            {% endif %}
                        </td>
                        <td><span class="desktop-only">{{ account.dateJoined }}</span></td>
                        <td><span class="desktop-only">{{ account.viewingPage }}</span></td>
                    </tr>
                {% endfor %}
            {% else %}
                <tr>
                    <td></td>
                    <td colspan="3">No users found</td>
                </tr>
            {% endif %}
        </tbody>
    </table>
    <div class="iterator">
        {% autoescape false %}{{ iterator }}{% endautoescape %}
    </div>
{% endblock %}
