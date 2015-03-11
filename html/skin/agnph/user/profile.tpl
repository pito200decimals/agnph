{% extends "base.tpl" %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
{% endblock %}

{% block content %}
    Profile for user <h1>{{ profile.user.DisplayName }}</h1>
{% endblock %}
