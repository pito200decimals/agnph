{% extends 'base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/user/recover-style.css') }}" />
    <style>
        h4 {
            text-align: center;
        }
        #recover-form {
            max-width: 800px;
        }
    </style>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}

{% block content %}
    <div id="recover-form" class="form-box">
        Your account {{ details }} has been changed. Click <a href="/">here</a> to continue.
    </div>
{% endblock %}
