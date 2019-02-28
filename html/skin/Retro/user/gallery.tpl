{% extends "user/gallery-base.tpl" %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/gallery/retro-postindex-style.css')|raw }}
{% endblock %}
