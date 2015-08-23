{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
{% endblock %}

{% block section_navigation %}
    <ul class="section-nav">
        <li><a href="/user/{{ profile.user.UserId }}/">Profile</a></li>
        <li><a href="/user/{{ profile.user.UserId }}/forums/">Forums</a></li>
        <li><a href="/user/{{ profile.user.UserId }}/gallery/">Gallery</a></li>
        <li><a href="/user/{{ profile.user.UserId }}/fics/">Fics</a></li>
        <li><a href="/user/{{ profile.user.UserId }}/oekaki/">Oekaki</a></li>
        {% if user and profile.user.UserId == user.UserId %}
        <li><a href="/user/{{ user.UserId }}/mail/">Messages{% if unreadMessages %} <span class="unread-messages">({{ unreadMessages }})</span>{% endif %}</a></li>
        <li><a href="/user/{{ user.UserId }}/preferences/">Preferences</a></li>
        {% endif %}
    </ul>
{% endblock %}

{% block content %}
    {{ block('banner') }}
    <div class="userpage">
        <div class="userpage-container">
            <div class="profile-sidepanel">
                {% block profile_sidepanel %}
                    <div class="sidepanel-section">
                        <img class="profile-avatarimg" src="{{ profile.user.avatarURL }}" />
                    </div>
                    {# Other actions to perform on this user #}
                    <div class="sidepanel-section">
                        {% block sidebar %}
                        {% endblock %}
                    </div>
                {% endblock %}
            </div>
            <div class="profile-content">
                <h2>{{ profile.user.DisplayName }}</h2>
                {% if profile.user.admin|length > 0 %}<div>{{ profile.user.admin }}</div>{% endif %}
                {% block usercontent %}
                {% endblock %}
            </div>
        </div>
    </div>
{% endblock %}
