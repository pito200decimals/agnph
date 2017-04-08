{% extends 'fics/tag_help-base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/no-right-panel-style.css') }}" />
{% endblock %}