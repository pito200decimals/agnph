{% extends "base.tpl" %}

{% block scripts %}
    {# TODO: Move this to other pages, or just index? #}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ skinDir }}/timezone.js"></script>
    {% endif %}
{% endblock %}

{% block content %}
Site Index!
{% endblock %}
