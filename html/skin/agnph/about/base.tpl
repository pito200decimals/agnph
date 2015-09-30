{% extends 'base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/about/style.css') }}" />
{% endblock %}

{% block section_navigation %}
    <ul class="section-nav">
        <li><a href="/about/">Info</a></li>
        <li><a href="/about/rules/">Rules</a></li>
        <li><a href="/about/staff/">Staff</a></li>
        <li><a href="/about/help/">Help</a></li>
        <li><a href="/about/irc/">IRC</a></li>
        <li><a href="/about/minecraft/">Minecraft</a></li>
    </ul>
{% endblock %}

{% block content %}
{% endblock %}
