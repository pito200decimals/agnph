{% extends "base.tpl" %}

{% block styles %}
<link rel="stylesheet" type="text/css" href="{{ skinDir }}/login-style.css" />
{% endblock %}

{% block content %}
<div class="logoutbox">
    <p>You have been successfully logged out.</p>
    <p><a href="/">Click here to continue</a></p>
</div>
{% endblock %}
