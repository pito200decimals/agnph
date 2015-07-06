{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/recover-style.css" />
{% endblock %}

{% block scripts %}
{% endblock %}

{% block content %}
    <div class="recover-form">
        Your account {{ details }} has been changed. Click <a href="/">here</a> to continue.
    </div>
{% endblock %}
