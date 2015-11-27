{% extends "base.tpl" %}

{% block styles %}
    {{ parent() }}
    <style>
        #error-div {
            text-align: center;
        }
    </style>
{% endblock %}

{% block content %}
    {{ block('banner') }}
    <div id="error-div">
        <h1>403</h1>
        <h3>Access Denied</h3>
        <img src="/images/403-shinx.png" />
        <p>
            You are not authorized to access this page.
        </p>
    </div>
{% endblock %}
