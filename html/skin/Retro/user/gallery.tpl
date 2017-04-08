{% extends "user/gallery-base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/gallery/retro-postindex-style.css') }}" />
{% endblock %}
