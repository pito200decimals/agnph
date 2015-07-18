{% extends "base.tpl" %}

{% block styles %}
<style>
    #logout-box {
        width: 320px;
    }
</style>
{% endblock %}

{% block content %}
<div class="form-box" id="logout-box">
    <p>You have been successfully logged out.</p>
    <p><a href="/">Click here to continue</a></p>
</div>
{% endblock %}
