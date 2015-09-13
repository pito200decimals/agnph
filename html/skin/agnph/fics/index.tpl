{% extends 'fics/base.tpl' %}

{% block scripts %}
    {{ parent() }}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ skinDir }}/timezone.js"></script>
    {% endif %}
{% endblock %}

{% block content %}
{% endblock %}
