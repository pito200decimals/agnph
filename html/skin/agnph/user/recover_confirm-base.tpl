{% extends 'base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/user/recover-style.css')|raw }}
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
