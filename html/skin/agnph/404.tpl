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
        <h1>404</h1>
        <h3>Page Not Found</h3>
        <img src="/images/404-luxio.png" />
        <p>
            Sorry, the page you have requested could not be found.
        </p>
    </div>
{% endblock %}
