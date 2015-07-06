{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/register-style.css" />
{% endblock %}

{% block scripts %}
{% endblock %}

{% block content %}
    <div class="register-form">
        A confirmation email has been sent to the address "<strong>{{ email }}</strong>". Please open that email and click the link inside to complete your account registration.
    </div>
{% endblock %}
