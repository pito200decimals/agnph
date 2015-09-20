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
        A confirmation email has been sent to the address "<strong>{{ email }}</strong>". Please open that email and click the link inside to complete your account recovery.
    </div>
{% endblock %}
