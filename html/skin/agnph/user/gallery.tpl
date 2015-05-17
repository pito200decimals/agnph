{% extends "user/base.tpl" %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
{% endblock %}

{% block content %}
    <h1>Profile for user {{ profile.user.DisplayName }}:</h1>
    {% if profile.user.Avatar|length > 0 %}
        {# avatar image #}
        <img class="avatarimg" src="{{ profile.user.Avatar }}" />
    {% else %}
        {# default avatar image #}
        <img class="avatarimg" src="http://i.imgur.com/CKd8AGC.png" />
    {% endif %}
    <div class="bioblock">
        {% autoescape false %}
            {{ profile.user.bio }}
        {% endautoescape %}
    </div>
{% endblock %}
