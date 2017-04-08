{% extends 'about/base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/no-left-panel-mobile-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/no-right-panel-style.css') }}" />
{% endblock %}

{% block page_title_bar %}
    <strong>About AGNPH</strong>
{% endblock %}