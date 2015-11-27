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
        <h1>500</h1>
        <h3>Internal Server Error</h3>
        <img src="/images/500-emboar.png" />
        <p>
            Sorry, the server encountered an error.
        </p>
        <p>
            Please try again after Emboar calms down.
        </p>
    </div>
{% endblock %}
