{% extends 'base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/gallery/style.css') }}" />
{% endblock %}

{% block section_navigation %}
    <ul class="section-nav">
        <li><a href="/gallery/post/">Index</a></li>
        <li><a href="/gallery/tags/">Tags</a></li>
        <li><a href="/gallery/pools/">Pools</a></li>
        <li><a href="/gallery/post/?search=order%3Apopular">Popular</a></li>
        {% if user %}
            <li><a href="/gallery/upload/">Upload</a></li>
            <li><a href="/user/{{ user.UserId }}/gallery/">My Gallery</a></li>
        {% endif %}
    </ul>
{% endblock %}

{% block content %}
{% endblock %}
