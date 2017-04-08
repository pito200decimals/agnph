{% extends 'user/base.tpl' %}

{% block styles %}
    {# Don't inherit styles! #}
    <link rel="stylesheet" type="text/css" href="{{ asset('/user/retro-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/no-left-panel-mobile-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/no-right-panel-style.css') }}" />
{% endblock %}

{% block section_navigation %}
    <li><a href="/user/{{ profile.user.UserId }}/">Profile</a></li>
    <li><a href="/user/{{ profile.user.UserId }}/forums/">Forums</a></li>
    <li><a href="/user/{{ profile.user.UserId }}/gallery/">Gallery</a></li>
    <li><a href="/user/{{ profile.user.UserId }}/fics/">Fics</a></li>
    <li><a href="/user/{{ profile.user.UserId }}/oekaki/">Oekaki</a></li>
{% endblock %}

{% block extra_account_menu_options_logged_in %}
    {% if profile.user.UserId == user.UserId %}
        <li><a href="/user/{{ user.UserId }}/mail/">Messages{% if unread_message_count > 0 %} <span class="unread-messages">({{ unread_message_count }})</span>{% endif %}</a></li>
        <li><a href="/user/{{ user.UserId }}/preferences/">Settings</a></li>
        <li class="divider"></li>
        <li>
            <form action="/logout/" method="POST" accept-encoding="UTF-8">
                <input type="submit" name="submit" value="Log Out" />
            </form>
        </li>
    {% endif %}
{% endblock %}
