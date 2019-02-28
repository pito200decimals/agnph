{% extends 'base.tpl' %}

{% block styles %}
    {{ inline_css_asset('/user/style.css')|raw }}
{% endblock %}

{% block section_navigation %}
    <ul class="section-nav">
        <li><a href="/user/{{ profile.user.UserId }}/">Profile</a></li>
        <li><a href="/user/{{ profile.user.UserId }}/forums/">Forums</a></li>
        <li><a href="/user/{{ profile.user.UserId }}/gallery/">Gallery</a></li>
        <li><a href="/user/{{ profile.user.UserId }}/fics/">Fics</a></li>
        <li><a href="/user/{{ profile.user.UserId }}/oekaki/">Oekaki</a></li>
        {% if user and profile.user.UserId == user.UserId %}
            <li><a href="/user/{{ user.UserId }}/mail/">Messages{% if unread_message_count + unread_notification_count > 0 %} <span class="unread-messages">({{ unread_message_count + unread_notification_count }})</span>{% endif %}</a></li>
            <li><a href="/user/{{ user.UserId }}/preferences/">Settings</a></li>
        {% endif %}
    </ul>
{% endblock %}

{% block admin_link_block %}
    {% for link in adminLinks %}
        {% if link == "break" %}
            <br />
        {% else %}
            <li>
                <form id="{{ link.formId }}-form" action="/user/{{ profile.user.UserId }}/admin/" method="POST" accept-encoding="UTF-8" hidden>
                    {% for action in link.actions %}
                        <input type="hidden" name="action[]" value="{{ action }}" />
                    {% endfor %}
                </form>
                <a href="/user/{{ profile.user.UserId }}/admin/" onclick="document.getElementById('{{ link.formId }}-form').submit();return false;">
                    {{ link.text }}
                </a>
            </li>
        {% endif %}
    {% endfor %}
{% endblock %}

{% block content %}
    {{ block('banner') }}
    <div class="userpage">
        <div class="userpage-container">
            <div class="profile-sidepanel">
                {% block profile_sidepanel %}
                    <div class="sidepanel-section">
                        <img class="profile-avatarimg" src="{{ profile.user.avatarURL }}" />
                        <div>
                            {% if profile.user.online %}
                                <img class="status-icon" src="/images/user-online.png" /><small>Online</small>
                            {% else %}
                                <img class="status-icon"  src="/images/user-offline.png" /><small>Offline</small>
                            {% endif %}
                        </div>
                    </div>
                    {# Other actions to perform on this user #}
                    <div class="sidepanel-section">
                        {% block sidebar %}
                            {% if user %}
                                <input type="checkbox" hidden id="expand-action-checkbox" />
                                <label for="expand-action-checkbox" id="expand-action-checkbox-label"><h4>Actions</h4></label>
                                <div class="expand-action-tray">
                                    {% block sidebar_actions %}
                                    {% endblock %}
                                </div>
                            {% endif %}
                        {% endblock %}
                    </div>
                {% endblock %}
            </div>
            <div class="profile-content">
                <h2>{{ profile.user.DisplayName }}</h2>
                <ul class="admin-badges">
                    {% for badge in profile.user.admin %}
                        <li>
                            {% if badge.src %}
                                <img class="{{ badge.class }}" src="{{ badge.src }}" />
                            {% elseif badge.html %}
                                {% autoescape false %}{{ badge.html }}{% endautoescape %}
                            {% else %}
                                <span class="{{ badge.class }}">{{ badge.name }}</span>
                            {% endif %}
                        </li>
                    {% endfor %}
                </ul>
                {% block usercontent %}
                {% endblock %}
            </div>
        </div>
    </div>
{% endblock %}
