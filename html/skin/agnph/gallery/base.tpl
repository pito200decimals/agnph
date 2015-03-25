{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
{% endblock %}

{% block content %}
    <ul class="gallerynav">
        <li><a href="/gallery/post/">Index</a></li>
        <li><a href="/gallery/upload/">Upload</a></li>
        <li><a href="/gallery/tags/">Tags</a></li>
        <li><a href="/gallery/pools/">Pools</a></li>
    </ul>
    {% block gallerycontent %}
    {% endblock %}
{% endblock %}
