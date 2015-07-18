{% extends 'base.tpl' %}

{% block styles %}
    <style>
        #register-form {
            max-width: 800px;
        }
    </style>
{% endblock %}

{% block scripts %}
{% endblock %}

{% block content %}
    <div id="register-form" class="form-box">
        A confirmation email has been sent to the address "<strong>{{ email }}</strong>". Please open that email and click the link inside to complete your account registration.
    </div>
{% endblock %}
