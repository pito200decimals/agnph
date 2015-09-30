{% extends "about/base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/about/style.css') }}" />
{% endblock %}

{% block content %}
    <h3>AGNPH Help Pages</h3>
    <div class="block">
        <div class="header">Help</div>
        <div class="content">
        </div>
    </div>
{% endblock %}
